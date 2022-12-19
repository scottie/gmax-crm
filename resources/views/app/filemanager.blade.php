@extends('layouts.app')

@section('title', 'Page Title')

@section('content')
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
<style>
    body
    {
        font-size: 14px;
    }
    </style>


<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="/vendor/file-manager/css/file-manager.css">

<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ __('File_Manager') }}</h3> 
      </div>
    <div class="card-body">
        <div id="fm" style="height: 800px; float:left;">

        </div>
    </div>
</div>
<script src="/vendor/file-manager/js/file-manager.js"></script>

@endsection
