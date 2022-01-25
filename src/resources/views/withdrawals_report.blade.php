<?php $layout = ''; ?>
@switch($enviroment)
    @case('admin')
		<?php $layout = '.master'; ?>
        @break

	@case('corp')
		<?php $layout = '.corp.master'; ?>
        @break

	@case('user')
		<?php $layout = '.user.master'; ?>
	@break

	@case('provider')
		<?php $layout = '.provider.master'; ?>
	@break

    @default
		@break
@endswitch
@extends('layout'.$layout)

@section('breadcrumbs')
<div class="row page-titles">
	<div class="col-md-6 col-8 align-self-center">

		<h3 class="text-themecolor m-b-0 m-t-0">{{ trans('libTans::withdrawals.withdrawals_report') }}</h3>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="javascript:void(0)">{{ trans('libTans::withdrawals.home') }}</a></li>
			<li class="breadcrumb-item active">{{ trans('libTans::withdrawals.withdrawals_report') }}</li>
		</ol>
	</div>
</div>	
@stop


@switch($enviroment)
    @case('corp')
		@section('vue_content')
        @break

    @case('admin')
		@section('content')
		<div id="VueJs" class="col-sm-12">
        @break

	@case('user')
		@section('content')
		<div id="VueJs">
        @break

	@case('provider')
		@section('content')
		<div id="VueJs" class="col-md-12">
        @break
	
    @default
		@section('content')
		@break
@endswitch

	<withdrawalsreport 
		id="{{ $id }}"
		enviroment="{{ $enviroment }}"
		with-draw-request-route="{{ $enviroment == 'admin' ? null : URL::Route($enviroment.'WithdrawAdd') }}"
		create-bank-account-route="{{ $enviroment == 'admin' ? null : URL::Route($enviroment.'AddBankAccount') }}"
		financial-entry-route="{{ $enviroment == 'admin' ? URL::Route('addFinancialEntry',[ 'type' => $user_provider_type, 'id' => intval($id)]) : '' }}"
		ledger="{{ json_encode($ledger) }}"
		withdrawals-report="{{ $withdrawals_report }}"
		current-balance="{{ $current_balance }}"
		bank-accounts="{{ json_encode($bankaccounts) }}"
		bank-list="{{ json_encode($banks) }}"
		account-types="{{ json_encode($account_types) }}"
		with-draw-settings="{{ json_encode($withdrawsettings) }}"
		currency-symbol="{{\Settings::getCurrency()}}">
	</withdrawalsreport>
	
</div>

	
@switch($enviroment)
@case('admin')
	</div>
	@break
@case('user')
	</div>
	@break

@default
	@break
@endswitch

@stop

@section('styles')
<link rel="stylesheet" href="{{asset('css/provider_financial.css')}}" />
@stop

@section('javascripts')
<script src="/libs/withdrawals/lang.trans/withdrawals"> </script> 

@switch($enviroment)
	@case('user')
		<script type="text/javascript">
			window.onload = function () {  
				sessionStorage.setItem('token', JSON.stringify('<?= Auth::guard("clients") ? Auth::guard("clients")->user()->token : null ?>'));
				sessionStorage.setItem('user_id', <?= Auth::guard("clients") ? Auth::guard("clients")->user()->id : null ?>);
				if (sessionStorage.getItem('provider_id')) sessionStorage.removeItem('provider_id');
			}
		</script>
        @break

	@case('provider')
		<script type="text/javascript">
			window.onload = function () {  
				sessionStorage.setItem('token', JSON.stringify('<?= Auth::guard("providers") ? Auth::guard("providers")->user()->token : null ?>'));
				sessionStorage.setItem('provider_id', <?= Auth::guard("providers") && Auth::guard("providers")->user() ? Auth::guard("providers")->user()->id : null ?>);
				if (sessionStorage.getItem('user_id')) sessionStorage.removeItem('user_id');
			}
		</script>
        @break

    @default
		@break
@endswitch


		<script src="{{ asset('vendor/codificar/withdrawals/withdrawals.vue.js') }}"> </script> 
       
@stop
