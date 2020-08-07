<?php $layout = '.master'; ?>
       
@extends('layout'.$layout)

@section('breadcrumbs')
<div class="row page-titles">
	<div class="col-md-6 col-8 align-self-center">

		<h3 class="text-themecolor m-b-0 m-t-0">{{ trans('finance.withdrawals_report') }}</h3>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="javascript:void(0)">{{ trans('dashboard.home') }}</a></li>
			<li class="breadcrumb-item active">{{ trans('finance.withdrawals_report') }}</li>
		</ol>
	</div>
</div>	
@stop


@section('content')
	<div id="VueJs">
		
		<withdrawalsreport 
			id="{{ $id }}"
			
		>
		</withdrawalsreport>
		
	</div>

		

	</div>

@stop

@section('styles')
<link rel="stylesheet" href="{{elixir('css/provider_financial.css')}}" />
@stop

@section('javascripts')
<script src="/js/lang.trans/finance,dashboard"> </script> 



<script src="{{ elixir('js/main.vue.js') }}"> </script> 
       
@stop
