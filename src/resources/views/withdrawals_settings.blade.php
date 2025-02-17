<?php $layout = '.master'; ?>
       
@extends('layout'.$layout)

@section('breadcrumbs')
<div class="row page-titles">
	<div class="col-md-6 col-8 align-self-center">

		<h3 class="text-themecolor m-b-0 m-t-0">{{ trans('libTans::withdrawals.withdrawals_settings')}}</h3>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="javascript:void(0)">{{ trans('libTans::withdrawals.home') }}</a></li>
			<li class="breadcrumb-item active">{{ trans('libTans::withdrawals.settings') }}</li>
		</ol>
	</div>
</div>	
@stop


@section('content')
	<div id="VueJs" class="col-sm-12">
		<withdrawalssettings 
			settings="{{ json_encode($settings)}}"	
		>
		</withdrawalssettings>
		
	</div>

		

	</div>

@stop

@section('javascripts')
<script src="/libs/withdrawals/lang.trans/withdrawals"> </script> 



<script src="{{ asset('vendor/codificar/withdrawals/withdrawals.vue.js') }}"> </script> 
       
@stop
