<?php

namespace Codificar\Withdrawals\Http\Controllers;

use Codificar\Withdrawals\Models\Withdrawals;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

//Resource
use Codificar\Withdrawals\Http\Resources\ProviderWithdrawalsReportResource;

use Input;
use Provider;
class WithdrawalsController extends Controller {

    public function getWithdrawalsReport()
    {
        // Get the provider id (some projects is 'provider_id' and others is just 'id')
        $providerId = Input::get('provider_id') ? Input::get('provider_id') : Input::get('id');
        $provider = Provider::find($providerId);
        
        $withdrawals_report = Withdrawals::getWithdrawalsSummary($provider->ledger->id, 'provider');
        
        // Return data
		return new ProviderWithdrawalsReportResource([
			'withdrawals_report' => $withdrawals_report
		]);
    }

}