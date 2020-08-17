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
        foreach($withdrawalsSummary as $withdral) {
            $withdral->formattedValue = currency_format($withdral->formattedValue);
        }
        return $withdrawalsSummary;
    }


    public static function getWithdrawalsSummaryWeb($ledgerId = null, $enviroment)
    {
        if ($enviroment == 'admin') {
            $balance = Finance::orderBy('compensation_date', 'ASC');

            $withdrawalsSummary = DB::table('withdraw')->select('ledger_bank_account.*', 'finance.*',  'withdraw.*', 'withdraw.id as id', 'bank.name as bank', 'provider.email as provider_email', 'provider.first_name as provider_first_name', 'provider.last_name as provider_last_name', 'user.email as user_email', 'user.first_name as user_first_name', 'user.last_name as user_last_name')
                ->join('finance', 'finance.id', '=', 'withdraw.finance_withdraw_id')
                ->join('ledger', 'finance.ledger_id', '=', 'ledger.id')
                ->leftJoin('provider', 'ledger.provider_id', '=', 'provider.id')
                ->leftJoin('user', 'ledger.user_id', '=', 'user.id')
                ->join('ledger_bank_account', 'finance.ledger_bank_account_id', 'ledger_bank_account.id')
                ->join('bank', 'ledger_bank_account.bank_id', 'bank.id')
                ->where('finance.reason', '=', 'WITHDRAW')
                ->orderBy('withdraw.id', 'desc')
                ->get();
            $response = array(
                'success' => true,
                'provider_id' => null,
                'current_balance' => 0,
                'total_balance' => 0,
                'detailed_balance' => $balance->paginate(20),
                'withdrawals_list' => $withdrawalsSummary,
            );
        } else if ($ledger = Ledger::find($ledgerId)) {

            $currentBalance = Finance::sumValueByLedgerId($ledgerId);
            $totalBalance = Finance::sumAllValueByLedgerId($ledgerId);
            $balance = Finance::orderBy('compensation_date', 'ASC');

            if ($ledgerId != '') {
                $balance->where('ledger_id', $ledgerId);
            }

            if ($enviroment == 'provider') {
                $withdrawalsSummary = DB::table('withdraw')->select('ledger_bank_account.*', 'finance.*', 'withdraw.*', 'withdraw.id as id', 'bank.name as bank', 'provider.email as provider_email', 'provider.first_name as provider_first_name', 'provider.last_name as provider_last_name')
                    ->join('finance', 'finance.id', '=', 'withdraw.finance_withdraw_id')
                    ->join('ledger', 'finance.ledger_id', '=', 'ledger.id')
                    ->join('provider', 'ledger.provider_id', '=', 'provider.id')
                    ->join('ledger_bank_account', 'finance.ledger_bank_account_id', 'ledger_bank_account.id')
                    ->join('bank', 'ledger_bank_account.bank_id', 'bank.id')
                    ->where('finance.ledger_id', '=', $ledgerId)
                    ->where('finance.reason', '=', 'WITHDRAW')
                    ->orderBy('withdraw.id', 'desc')
                    ->get();
            } else if ($enviroment == 'user') {
                $withdrawalsSummary = Finance::select('ledger_bank_account.*', 'finance.*', 'finance.id as id', 'bank.name as bank', 'user.email as user_email', 'user.first_name as user_first_name', 'user.last_name as user_last_name')
                    ->join('ledger', 'finance.ledger_id', '=', 'ledger.id')
                    ->join('user', 'ledger.user_id', '=', 'user.id')
                    ->join('ledger_bank_account', 'finance.ledger_bank_account_id', 'ledger_bank_account.id')
                    ->join('bank', 'ledger_bank_account.bank_id', 'bank.id')
                    ->where('finance.ledger_id', '=', $ledgerId)
                    ->where('finance.reason', '=', 'WITHDRAW')
                    ->orderBy('finance.id', 'desc')
                    ->get();
            }
            $response = array(
                'success' => true,
                'provider_id' => isset($provider_id) ? $provider_id : null,
                'current_balance' => $currentBalance,
                'total_balance' => $totalBalance,
                'detailed_balance' => $balance->paginate(20),
                'withdrawals_list' => $withdrawalsSummary,
            );
        } else {
            $response = "Ledger not found";
        }
        return $response;
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


    public static function getRemCnabfiles()
    {
        $query = DB::table('cnab_files')->select('cnab_files.*')
            ->where('cnab_files.type', '=', 'rem')
            ->orderBy('cnab_files.id', 'desc')
            ->get();
        
        return $query;
    }

    public static function getTotalValueRequestedWithdrawals()
    {
        $query = DB::table('withdraw')
            ->where('withdraw.type', '=', 'requested')
            ->join('finance', 'finance.id', '=', 'withdraw.finance_withdraw_id')  
            ->select( DB::raw('sum( finance.value ) as totalValue') )
            ->get();
        $total = -1 * $query[0]->totalValue;
        return $total;
    }
    public static function getTotalValueAwaitingReturnWithdrawals()
    {
        $query = DB::table('cnab_files')->select('cnab_files.*')
            ->where('cnab_files.type', '=', 'rem')
            ->orderBy('cnab_files.id', 'desc')
            ->get();
        
        return $query;
    }
    public static function getTotalErroWithdrawals()
    {
        $query = DB::table('cnab_files')->select('cnab_files.*')
            ->where('cnab_files.type', '=', 'rem')
            ->orderBy('cnab_files.id', 'desc')
            ->get();
        
        return $query;
    }
}
