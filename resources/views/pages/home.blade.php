@extends('layouts.master')

@section('content')
	<div class="container" style="padding-top:50px;">
		<div class="alert alert-danger">
			<p><strong>Here you can find a detailed list of the error codes. We kindly advise thorough consultation of this troubleshooting list prior to seeking assistance through Google groups. <a href="https://groups.google.com/g/fuma-gwas-users/c/JdxuqR4ihS4">Troubleshooting List</a></strong></p>
		</div>
		<div class="alert alert-success">
			<p><strong>Maintenance Notice:</strong> FUMA Maintenance has been Completed</p>
			<br/>
			Dear FUMA Users, <br/>
			We would like to inform you that FUMA maintenance has been completed. The FUMA server is now back online and available for use.
			<br/>
			<br/>
			<strong>Key Points:</strong>
			<ul>
				<li><p><strong>Server Update:</strong> Fuma backend is now updated and more computational resources are available for use.</p></li>
				<li><p><strong>Database Cleaning:</strong> We will perform a thorough cleaning of the database in the next few days. All faulty jobs will be deleted, excluding public jobs. However, any faulty jobs that still have the potential to produce results will be retained.</p></li>
				<li><p><strong>Manual Deletion Activated:</strong> The manual deletion of jobs is active again.</p></li>
				<li><p><strong>Job ID Changes:</strong> Job IDs are updated to be compatible with the new database. However, rest assured that the new IDs point to the same jobs, and your data are not affected.</p></li>
				<li><p><strong>Trial Period:</strong> We will be monitoring the server for the next few days to ensure that everything is working as intended. If you encounter any issues, please reach out to our support team.</p></li>
			</ul>
			<br/>
			<p>We appreciate your understanding and patience during the maintenance period. If you have any concerns or questions, please reach out to our support team.</p>
			<p>Thank you for being a valued member of the FUMA community.</p>
		</div>

		<div style="text-align: center;">
			<h2>FUMA GWAS</h2>
			<h2>Functional Mapping and Annotation of Genome-Wide Association Studies</h2>
		</div>
		<br/>
		<p>
			<strong style="font-size: large;">About FUMA</strong><br/>
			FUMA is a platform that can be used to annotate, prioritize, visualize and interpret GWAS results.
			<br/>
			The <a href="{{ Config::get('app.subdir') }}/snp2gene">SNP2GENE</a> function takes GWAS summary statistics as an input,
			and provides extensive functional annotation for all SNPs in genomic areas identified by lead SNPs.
			<br/>
			The <a href="{{ Config::get('app.subdir') }}/gene2func">GENE2FUNC</a> function takes a list of gene IDs (as identified by SNP2GENE or as provided manually)
			and annotates genes in biological context
			<br/>
			To submit your own GWAS, login is required for security reason.
			If you have't registered yet, you can do from <a href="{{ url('/register') }}">here</a>.
			<br/>
			You can browse public results of FUMA (including example jobs) from <a href="{{ Config::get('app.subdir') }}/browse">Browse Public Results</a> without registration or login.
		</p>
		<p>
			Please post any questions, suggestions and bug reports on Google Forum: <a target="_blank" href="https://groups.google.com/forum/#!forum/fuma-gwas-users">FUMA GWAS users</a>.<br/>
		</p>
		<p>
			<strong style="font-size: large;">News</strong><br/>
			<b>26 February 2023: </b> FUMA is upgraded to version 1.5.2.<br/>
			A frequently asked questions page is added. In addition, some other minor updates to wording on the website was done.<br/>
			<b>03 February 2023: </b> FUMA is upgraded to version 1.5.1.<br/>
			Starting from FUMA version 1.5.1, as default MAGMA is unchecked. If you want to obtain results from MAGMA, please select 'Perform MAGMA' in step 6 on the submission page. <br/>
			For other updates, please see the <a href="{{ Config::get('app.subdir') }}/updates">Updates</a> page. 
		</p>
		<p>
			<strong style="font-size: large;">Citation</strong><br/>
			When using FUMA, please cite the following.<br/>
			K. Watanabe, E. Taskesen, A. van Bochoven and D. Posthuma. Functional mapping and annotation of genetic associations with FUMA. <i>Nat. Commun.</i> <b>8</b>:1826. (2017).<br/><a href="{{ Config::get('app.subdir') }}/links">links</a>
			<a target="_blank" href="https://www.nature.com/articles/s41467-017-01261-5">https://www.nature.com/articles/s41467-017-01261-5</a>
			<br>
			When using cell type analysis, please cite the following.<br/>
			K. Watanabe, M. Umicevic Mirkov, C. de Leeuw, M. van den Heuvel and D. Posthuma. Genetic mapping of cell type specificity for complex traits. <i>Nat. Commun.</i> <b>10</b>:3222. (2019).<br/>
			<a target="_blank" href="https://www.nature.com/articles/s41467-019-11181-1">https://www.nature.com/articles/s41467-019-11181-1</a>
			<br>
			Depending on which results you are going to report, please also cite the original study of data sources/tools used in FUMA
			(references are available at <a href="{{ Config::get('app.subdir') }}/links">links</a> or
			<a href="{{ Config::get('app.subdir') }}/tutorial#celltype">tutorial for the cell type specificity analysis</a> for scRNA-seq data).
		</p>
		<br/>

		<div class="row">
			<div class="col-md-6 col-xs-6 col-sm-6" style="text-align:center; padding: 20px;">
				<div style="background-color: #dfdfdf; padding-top:20px; padding-bottom:20px;">
					<!-- <h4 class="blinking" style="color:#000099">Start from here with GWAS summary statistics</h4> -->
					<button id="snp2genebtn" class="btn btn-primary">SNP2GENE</button>
					<br/><br/>
					<img src="{{ URL::asset('/image/homeSNP2GENE.png') }}" align="middle" style="width:90%;">
				</div>
			</div>
			<div class="col-md-6 col-xs-6 col-sm-6" style="text-align:center; padding: 20px;">
				<div style="background-color: #dfdfdf; padding-top:20px; padding-bottom:20px;">
					<!-- <h4 class="blinking" style="color:#000099">Start from here with a list of genes</h4> -->
					<button id="gene2funcbtn" class="btn btn-success">GENE2FUNC</button>
					<br/><br/>
					<img src="{{ URL::asset('/image/homeGENE2FUNC.png') }}" align="middle" style="width:90%;">
				</div>
			</div>
		</div>
	</div>
	</br>
@endsection

@section('scripts')
	{{-- Imports from the web --}}

	{{-- Imports from the project --}}

	{{-- Hand written ones --}}
	<script type="text/javascript">
		var loggedin = "{{ Auth::check() }}";
		$(document).ready(function(){
			$('#snp2genebtn').on('click', function(){
				window.location.href="{{ Config::get('app.subdir') }}/snp2gene";
			});

			$('#gene2funcbtn').on('click', function(){
				window.location.href="{{ Config::get('app.subdir') }}/gene2func";
			});
		});
	</script>

@endsection
