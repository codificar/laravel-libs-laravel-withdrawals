<?php

namespace Codificar\Withdrawals\Models;

use Illuminate\Database\Eloquent\Relations\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;
use Eloquent;
use Finance, Ledger;
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

    public static function updateStatusAndFileAssociated($withdrawId, $fileAssociated) {
        DB::table('withdraw')
            ->where('id', '=', $withdrawId)
            ->update(
                [
                    'type' => 'awaiting_return',
                    'cnab_file_id' => $fileAssociated
                ]
            );
    }

    public static function getCnabSettings() {

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
            "rem_transfer_type",
            "rem_environment",
            "rem_address",
            "rem_address_number",
            "rem_city",
            "rem_cep",
            "rem_state",
            "rem_operation"
        );

        $query = DB::table('settings')
            ->select('key', 'value')
            ->whereIn('key', $keys)
            ->get();

        return $query;
    }



    public static function getSettingsKey($key){
		$settings = DB::table('settings')->where('key', $key)->first();

		if($settings)
			return $settings->value;
		else
			return false ;
	}


    public static function getWithdrawalsSettings($isFormattedValues = false) {
        if($isFormattedValues) {
            $query = array(
                'with_draw_enabled' => Withdrawals::getSettingsKey('with_draw_enabled'),
                'with_draw_max_limit' => currency_format(Withdrawals::getSettingsKey('with_draw_max_limit')),
                'with_draw_min_limit' => currency_format(Withdrawals::getSettingsKey('with_draw_min_limit')),
                'with_draw_tax' => currency_format(Withdrawals::getSettingsKey('with_draw_tax'))
            );
        } else {
            $query = array(
                'with_draw_enabled' => Withdrawals::getSettingsKey('with_draw_enabled'),
                'with_draw_max_limit' => Withdrawals::getSettingsKey('with_draw_max_limit'),
                'with_draw_min_limit' => Withdrawals::getSettingsKey('with_draw_min_limit'),
                'with_draw_tax' => Withdrawals::getSettingsKey('with_draw_tax')
            );
        }
        

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
        $query = DB::table('withdraw')
            ->where('withdraw.type', '=', 'awaiting_return')
            ->join('finance', 'finance.id', '=', 'withdraw.finance_withdraw_id')  
            ->select( DB::raw('sum( finance.value ) as totalValue') )
            ->get();
        $total = -1 * $query[0]->totalValue;
        return $total;
    }
    public static function getTotalErroWithdrawals()
    {
        $query = DB::table('withdraw')
            ->where('withdraw.type', '=', 'error')
            ->join('finance', 'finance.id', '=', 'withdraw.finance_withdraw_id')  
            ->select( DB::raw('sum( finance.value ) as totalValue') )
            ->get();
        $total = -1 * $query[0]->totalValue;
        return $total;
    }


    public static function getProviderDataToCreateCnab()
    {
        $query = DB::table('withdraw')
            ->select(
                'withdraw.*', 
                'withdraw.id as id', 
                'finance.value as totalValue', 
                'bank.name as bank',
                'bank.code as bank_code', 
                'ledger_bank_account.account as account', 
                'ledger_bank_account.account_digit as account_digit',
                'ledger_bank_account.agency as agency',
                'ledger_bank_account.agency_digit as agency_digit',
                'ledger_bank_account.document as document',
                'ledger_bank_account.person_type as person_type',
                'ledger_bank_account.holder as favoredName',
                'ledger_bank_account.account_type as account_type',
                'provider.address as address',
                'provider.address_number as address_number',
                'provider.address_neighbour as address_neighbour',
                'provider.address_city as address_city',
                'provider.zipcode as zipcode',
                'provider.state as state'
            )
            ->where('withdraw.type', '=', 'requested')
            ->join('finance', 'finance.id', '=', 'withdraw.finance_withdraw_id')
            ->join('ledger', 'finance.ledger_id', '=', 'ledger.id')
            ->join('ledger_bank_account', 'finance.ledger_bank_account_id', 'ledger_bank_account.id')
            ->join('bank', 'ledger_bank_account.bank_id', 'bank.id')
            ->join('provider', 'ledger.provider_id', 'provider.id')
            ->get();

        foreach($query as $totalValue) {
            $totalValue->totalValue *= -1;
        }
        return $query;
    }


    public static function updateWithdrawWhenDeleteCnab($cnab_id) {
        
        //Atualiza o status que estao 'aguardando o retorno' desse arquivo, para 'solicitado'
        DB::table('withdraw')
            ->where('cnab_file_id', '=', $cnab_id)
            ->where('type', '=', 'awaiting_return')
            ->update(['type' => 'requested']);

        
        //Remove a associacao do arquivo de remessa com o saque (independentemente do status)
        DB::table('withdraw')
            ->where('cnab_file_id', '=', $cnab_id)
            ->update(['cnab_file_id' => null]
        );
    }


    public static function getledgerBankAccount($ledgerId)
    {
        $query = DB::table('ledger_bank_account')
            ->select(
                'ledger_bank_account.id as id', 
                'ledger_bank_account.account as account', 
                'bank.name as bank'
            )
            ->where('ledger_bank_account.ledger_id', '=', $ledgerId)
            ->join('bank', 'ledger_bank_account.bank_id', 'bank.id')
            ->get();

        return $query;
    }


    /**
     * get withdrawals report with filter or not
     *
     * @param Boolean       $hasPaginate        true or false. If has paginate or not
     * @param String        $enviroment         enviroment can be "admin" or "provider"
     * @param String        $status_filter      status_filter is a string, need be equal the type column on withdraw table
     * @param Number        $receipt_filter     receipt_filter is a number, if is 2 so get the withdraw with receipt. If 1, get withdraw withou receipt. If other value, get all withdraw
     *
     * @return Array        $withdrawals_report     
     */
    public static function getWithdrawals($hasPaginate, $enviroment, $ledgerId = null, $status_filter = null, $receipt_filter = null)
    {
        $query = DB::table('withdraw')
            ->select(
                'withdraw.id as id',
                'withdraw.type as type',
                'withdraw.created_at as date',
                'withdraw.bank_receipt_url as bank_receipt_url',
                'withdraw.error_msg as error_msg',
                'finance.value as value',
                'finance.value as formattedValue',
                'ledger_bank_account.document as document',
                'ledger_bank_account.account as account',
                'ledger_bank_account.account_digit as account_digit',
                'ledger_bank_account.account as bankAccount',
                'ledger_bank_account.holder as name',
                'ledger_bank_account.agency as agency',
                'ledger_bank_account.agency_digit as agency_digit',
                'ledger_bank_account.account as account',
                'ledger_bank_account.account_digit as account_digit',
                'bank.name as bank',
                'provider.email as email'
            )
            ->join('finance', 'finance.id', '=', 'withdraw.finance_withdraw_id')
            ->join('ledger_bank_account', 'finance.ledger_bank_account_id', 'ledger_bank_account.id')
            ->join('ledger', 'finance.ledger_id', '=', 'ledger.id')
            ->join('bank', 'ledger_bank_account.bank_id', 'bank.id')
            ->join('provider', 'ledger.provider_id', '=', 'provider.id')
            ->where('finance.reason', '=', 'WITHDRAW')
            
            //if is provider, so just get the withdraw that provider
            ->when($enviroment == "provider", function ($query, $model) use ($ledgerId) {
                $query->where('finance.ledger_id', '=', $ledgerId);
            })
            
            //If has status 
            ->when($status_filter, function ($query, $model) use ($status_filter) {
                $query->where('withdraw.type', '=', $status_filter);
            })

            //If has receipt filter
            ->when($receipt_filter == 1 || $receipt_filter == 2, function ($query, $model) use ($receipt_filter) {
                if($receipt_filter == 2) {
                    $query->where('withdraw.bank_receipt_url', '!=', null);
                } else if ($receipt_filter == 1) {
                    $query->where('withdraw.bank_receipt_url', '=', null);
                }
            })
            ->orderBy('withdraw.id', 'DESC');
            
        if($hasPaginate) {
            $query = $query->paginate(10);
        } else {
            $query = $query->get();
        }
        //Converte o valor negativo para positivo
        foreach($query as $withdraw) {
            $withdraw->value = -1 * $withdraw->value;
            $withdraw->formattedValue = currency_format($withdraw->value);
            $withdraw->bankAccount = $withdraw->account . "-" . $withdraw->account_digit;
        }
        return $query;
    }
}
