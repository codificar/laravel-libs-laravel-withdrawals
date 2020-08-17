<?php

namespace Codificar\Withdrawals\Models;

use Illuminate\Database\Eloquent\Relations\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;
use Eloquent;
use Finance;
use DB;


class Withdrawals extends Eloquent
{

    protected $table = 'withdraw';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;


    public static function getWithdrawalsSummary($ledgerId = null, $enviroment)
    {
        $withdrawalsSummary = null;
        if ($enviroment == 'admin') {
            $withdrawalsSummary = DB::table('withdraw')->select('withdraw.*', 'withdraw.id as id', 'ledger_bank_account.*', 'finance.*', 'bank.name as bank', 'provider.email as provider_email', 'provider.first_name as provider_first_name', 'provider.last_name as provider_last_name', 'user.email as user_email', 'user.first_name as user_first_name', 'user.last_name as user_last_name')
                ->join('finance', 'finance.id', '=', 'withdraw.finance_withdraw_id')
                ->join('ledger', 'finance.ledger_id', '=', 'ledger.id')
                ->leftJoin('provider', 'ledger.provider_id', '=', 'provider.id')
                ->leftJoin('user', 'ledger.user_id', '=', 'user.id')
                ->join('ledger_bank_account', 'finance.ledger_bank_account_id', 'ledger_bank_account.id')
                ->join('bank', 'ledger_bank_account.bank_id', 'bank.id')
                ->where('finance.reason', '=', 'WITHDRAW')
                ->orderBy('withdraw.id', 'desc')
                ->get();
        } else if ($enviroment == 'provider') {
            $withdrawalsSummary = DB::table('withdraw')->select('withdraw.*', 'withdraw.id as id', 'finance.value as formattedValue', 'finance.compensation_date as date', 'bank.name as bank', 'ledger_bank_account.account as bankAccount')
                ->join('finance', 'finance.id', '=', 'withdraw.finance_withdraw_id')    
                ->join('ledger', 'finance.ledger_id', '=', 'ledger.id')
                ->join($enviroment, 'ledger.'.$enviroment.'_id', '=', $enviroment.'.id')
                ->join('ledger_bank_account', 'finance.ledger_bank_account_id', 'ledger_bank_account.id')
                ->join('bank', 'ledger_bank_account.bank_id', 'bank.id')
                ->where('finance.ledger_id', '=', $ledgerId)
                ->where('finance.reason', '=', 'WITHDRAW')
                ->orderBy('withdraw.id', 'desc')
                ->get();
        }
        foreach($withdrawalsSummary as $withdral) {
            $withdral->formattedValue = currency_format($withdral->formattedValue);
        }
        return $withdrawalsSummary;
    }


    public static function addWithdraw($finance_withdraw_id, $finance_withdraw_tax_id)
    {

        $withdrawalsSummary = Finance::select('finance.id as id', 'finance.value as formattedValue', 'finance.compensation_date as date', 'bank.name as bank', 'ledger_bank_account.account as bankAccount')
                                ->join('ledger', 'finance.ledger_id', '=', 'ledger.id')
                                ->join($enviroment, 'ledger.'.$enviroment.'_id', '=', $enviroment.'.id')
                                ->join('ledger_bank_account', 'finance.ledger_bank_account_id', 'ledger_bank_account.id')
                                ->join('bank', 'ledger_bank_account.bank_id', 'bank.id')
                                ->where('finance.ledger_id', '=', $ledgerId)
                                ->where('finance.reason', '=', 'WITHDRAW')
                                ->orderBy('finance.id', 'desc')
                                ->get();
        
        return $withdrawalsSummary;
    }

    public static function addWithdrawReceiptAndConfirm($id, $picture_url) {
        DB::table('withdraw')
            ->where('id', '=', $id)
            ->update(
                [
                    'bank_receipt_url' => $picture_url,
                    'type' => 'concluded',
                ]
            );
    }

    public static function getWithdrawalsSettings() {

        $keys = array(
            "rem_company_name",
            "rem_cpf_or_cnpj",
            "rem_document",
            "rem_agency",
            "rem_agency_dv",
            "rem_account",
            "rem_account_dv",
            "rem_bank_code",
            "rem_agreement_number",
            "rem_transfer_type"
        );

        $query = DB::table('settings')
            ->select('key', 'value')
            ->whereIn('key', $keys)
            ->get();

        return $query;
    }
}
