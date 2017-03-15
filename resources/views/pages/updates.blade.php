@extends('layouts.master')
@section('head')
<script type="text/javascript">
  var loggedin = "{{ Auth::check() }}";
</script>
@stop

@section('content')
<div class="container" style="padding-top: 50px;">
  <table class="table table-bordered">
    <thead>
      <tr>
        <th style="width: 15%;">Date</th>
        <th style="width: 15%;">Version</th>
        <th style="width: 70%;">Description</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>21 Feb 2017</td>
        <td>Ver 1.0.0</td>
        <td>The first version was freezed.</td>
      </tr>
    </tbody>
  </table>
</div>
@stop
