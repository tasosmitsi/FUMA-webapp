<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Helper;
use Auth;
use Illuminate\Support\Str;
use App\Jobs\CelltypeProcess;

class CellController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // 
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index($id = null)
    {
        return view('pages.celltype', ['id' => $id, 'status' => null, 'page' => 'celltype', 'prefix' => 'celltype']);
    }

    public function authcheck($jobID)
    {
        $email = Auth::user()->email;
        $check = DB::table('celltype')->where('jobID', $jobID)->first();
        if ($check->email == $email) {
            return view('pages.celltype', ['id' => $jobID, 'status' => 'jobquery', 'page' => 'celltype', 'prefix' => 'celltype']);
        } else {
            return view('pages.celltype', ['id' => null, 'status' => null, 'page' => 'celltype', 'prefix' => 'celltype']);
        }
    }

    public function checkJobStatus($jobID)
    {
        $job = DB::table('celltype')->where('jobID', $jobID)
            ->where('email', Auth::user()->email)->first();
        if (!$job) {
            return "Notfound";
        }
        return $job->status;
    }

    public function getS2GIDs()
    {
        $email = Auth::user()->email;
        $results = DB::select('SELECT jobID, title FROM SubmitJobs WHERE email=? AND status="OK"', [$email]);
        return $results;
    }

    public function checkMagmaFile(Request $request)
    {
        $id = $request->input('id');
        if (Storage::exists(config('app.jobdir') . '/jobs/' . $id . "/magma.genes.raw")) {
            return 1;
        } else {
            return 0;
        }
    }

    public function getJobList()
    {
        $email = Auth::user()->email;

        if ($email) {
            $results = DB::table('celltype')->where('email', $email)
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $results = array();
        }

        $this->queueNewJobs();

        return response()->json($results);
    }

    public function queueNewJobs()
    {
        $user = Auth::user();
        $email = $user->email;
        $newJobs = DB::table('celltype')->where('email', $email)->where('status', 'NEW')->get()->all();
        if (count($newJobs) > 0) {
            foreach ($newJobs as $job) {
                $jobID = $job->jobID;
                DB::table('celltype')->where('jobID', $jobID)
                    ->update(['status' => 'QUEUED']);

                CelltypeProcess::dispatch($user, $jobID);
            }
        }
        return;
    }

    public function deleteJob(Request $request)
    {
        $jobID = $request->input('jobID');
        Storage::deleteDirectory(config('app.jobdir') . '/celltype/' . $jobID);
        DB::table('celltype')->where('jobID', $jobID)->delete();
        return;
    }

    public function newJob(Request $request)
    {
        $date = date('Y-m-d H:i:s');
        $email = Auth::user()->email;
        $s2gID = $request->input('s2gID');
        $ensg = 0;
        if ($request->filled('ensg_id')) {
            $ensg = 1;
        }
        if ($s2gID > 0) {
            $ensg = 1;
        }
        $ds = implode(":", $request->input('cellDataSets'));
        $adjPmeth = $request->input('adjPmeth');
        $step2 = 0;
        if ($request->filled('step2')) {
            $step2 = 1;
        }
        $step3 = 0;
        if ($request->filled('step3')) {
            $step3 = 1;
        }
        if ($request->filled("title")) {
            $title = $request->input('title');
        } else {
            $title = "None";
        }
        $s2gTitle = "None";
        if ($s2gID > 0) {
            $s2gTitle = DB::table('SubmitJobs')->where('jobID', $s2gID)
                ->first()->title;
        }
        if ($s2gID == 0) {
            $jobID = DB::table('celltype')->insertGetId(
                ['title' => $title, 'email' => $email, 'created_at' => $date, 'status' => 'NEW']
            );
        } else {
            $jobID = DB::table('celltype')->insertGetId(
                [
                    'title' => $title, 'email' => $email, 'snp2gene' => $s2gID,
                    'snp2geneTitle' => $s2gTitle, 'created_at' => $date, 'status' => 'NEW'
                ]
            );
        }

        $filedir = config('app.jobdir') . '/celltype/' . $jobID;
        Storage::makeDirectory($filedir);
        if ($s2gID == 0) {
            Storage::putFileAs($filedir, $request->file('genes_raw'), 'magma.genes.raw');
        } else {
            $s2gfiledir = config('app.jobdir') . '/jobs/' . $s2gID . '/';
            Storage::copy($s2gfiledir . 'magma.genes.raw', $filedir . '/magma.genes.raw');
        }

        if ($s2gID == 0) {
            $s2gID = "NA";
        }
        $inputfile = "NA";
        if ($request->hasFile('genes_raw')) {
            $inputfile = $_FILES["genes_raw"]["name"];
        }
        $app_config = parse_ini_file(Helper::scripts_path('app.config'), false, INI_SCANNER_RAW);
        $paramfile = $filedir . '/params.config';
        Storage::put($paramfile, "[jobinfo]");
        Storage::append($paramfile, "created_at=$date");
        Storage::append($paramfile, "title=$title");

        Storage::append($paramfile, "\n[version]");
        Storage::append($paramfile, "FUMA=" . $app_config['FUMA']);
        Storage::append($paramfile, "MAGMA=" . $app_config['MAGMA']);

        Storage::append($paramfile, "\n[params]");
        Storage::append($paramfile, "snp2geneID=$s2gID");
        Storage::append($paramfile, "inputfile=$inputfile");
        Storage::append($paramfile, "ensg_id=$ensg");
        Storage::append($paramfile, "datasets=$ds");
        Storage::append($paramfile, "adjPmeth=$adjPmeth");
        Storage::append($paramfile, "step2=$step2");
        Storage::append($paramfile, "step3=$step3");

        return redirect("/celltype#joblist");
    }

    public function checkFileList(Request $request)
    {
        $id = $request->input('id');
        $filedir = config('app.jobdir') . '/celltype/' . $id;
        $params = parse_ini_string(Storage::get($filedir . '/params.config'), false, INI_SCANNER_RAW);
        if ($params['MAGMA'] == "v1.06") {
            $step1 = count(glob($filedir . "/*.gcov.out"));
            $step1_2 = 0;
            $step2 = 0;
            $step3 = 0;
        } else {
            // $step1 = count(glob($filedir . "/*.gsa.out"));
            $step1 = count(Helper::my_glob($filedir, "/.*\.gsa\.out/"));
            $step1_2 = (int) Storage::exists($filedir . "/step1_2_summary.txt");
            $step2 = (int) Storage::exists($filedir . "/magma_celltype_step2.txt");
            $step3 = (int) Storage::exists($filedir . "/magma_celltype_step3.txt");
        }
        return json_encode([$step1, $step1_2, $step2, $step3]);
    }

    public function getDataList(Request $request)
    {
        $id = $request->input('id');
        $filedir = config('app.jobdir') . '/celltype/' . $id;
        $params = parse_ini_string(Storage::get($filedir . '/params.config'), false, INI_SCANNER_RAW);
        $ds = explode(":", $params['datasets']);
        return json_encode($ds);
    }

    public function filedown(Request $request)
    {
        $id = $request->input('id');
        $prefix = $request->input('prefix');
        $filedir = config('app.jobdir') . '/' . $prefix . '/' . $id . '/';
        $params = parse_ini_string(Storage::get($filedir . 'params.config'), false, INI_SCANNER_RAW);

        $checked = $request->input('files');
        $files = [];
        $files[] = "params.config";

        if (in_array("step1", $checked)) {
            $ds = explode(":", $params['datasets']);
            if ($params['MAGMA'] == "v1.06") {
                for ($i = 0; $i < count($ds); $i++) {
                    $files[] = "magma_celltype_" . $ds[$i] . ".gcov.out";
                    $files[] = "magma_celltype_" . $ds[$i] . ".log";
                }
            } else {
                for ($i = 0; $i < count($ds); $i++) {
                    $files[] = "magma_celltype_" . $ds[$i] . ".gsa.out";
                    $files[] = "magma_celltype_" . $ds[$i] . ".log";
                }
            }
            $files[] = "magma_celltype_step1.txt";
        }
        if (in_array("step1_2", $checked)) {
            $files[] = "step1_2_summary.txt";
        }
        if (in_array("step2", $checked)) {
            $files[] = "magma_celltype_step2.txt";
        }
        if (in_array("step3", $checked)) {
            $files[] = "magma_celltype_step3.txt";
        }

        # check if zip file exists, if yes, delete it
        $zipfile = $filedir . "FUMA_celltype" . $id . ".zip";
        if (Storage::exists($zipfile)) {
            Storage::delete($zipfile);
        }

        # create zip file and open it
        $zip = new \ZipArchive();
        $zip->open(Storage::path($zipfile), \ZipArchive::CREATE);

        # add README file if exists in the public storage
        if (Storage::disk('public')->exists('README_cell.txt')) {
            $zip->addFile(Storage::disk('public')->path('README_cell.txt'), "README_cell");
        }

        # for each file, check if exists in the storage and add to zip file
        foreach ($files as $f) {
            if (Storage::exists($filedir . $f)) {
                $abs_path = Storage::path($filedir . $f);
                $zip->addFile($abs_path, $f);
            }
        }

        # close zip file
        $zip->close();

        # download zip file and delete it after download
        return response()->download(Storage::path($zipfile))->deleteFileAfterSend(true);
    }

    public function getPerDatasetData(Request $request)
    {
        $jobID = $request->input('id');
        $ds = $request->input('ds');
        $uuid = Str::uuid();
        $cmd = "docker run --rm --name job-$jobID-$uuid -v " . config('app.abs_path_of_cell_jobs_on_host') . "/$jobID/:/app/job -w /app laradock-fuma-celltype_plot_data /bin/sh -c 'python celltype_perDatasetPlotData.py job/ $ds'";
        $json = shell_exec($cmd);
        return $json;
    }

    public function getStepPlotData(Request $request)
    {
        $jobID = $request->input('id');
        $uuid = Str::uuid();
        $cmd = "docker run --rm --name job-$jobID-$uuid -v " . config('app.abs_path_of_cell_jobs_on_host') . "/$jobID/:/app/job -w /app laradock-fuma-celltype_plot_data /bin/sh -c 'python celltype_stepPlotData.py job/'";
        $json = shell_exec($cmd);
        return $json;
    }
}
