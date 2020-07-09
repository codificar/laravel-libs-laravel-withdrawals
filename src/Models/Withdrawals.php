<?php

namespace Codificar\Withdrawals\Models;

use Illuminate\Database\Eloquent\Relations\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;
use Eloquent;
use Finance;


class Withdrawals extends Eloquent
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    // protected $table = 'finance';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;


    public static function getWithdrawalsSummary($ledgerId = null, $enviroment)
    {

        if($enviroment == 'provider') {
            $withdrawalsSummary = Finance::select('ledger_bank_account.*','finance.*', 'finance.id as id', 'bank.name as bank', 'provider.email as provider_email', 'provider.first_name as provider_first_name', 'provider.last_name as provider_last_name')
                                    ->join('ledger', 'finance.ledger_id', '=', 'ledger.id')
                                    ->join('provider', 'ledger.provider_id', '=', 'provider.id')
                                    ->join('ledger_bank_account', 'finance.ledger_bank_account_id', 'ledger_bank_account.id')
                                    ->join('bank', 'ledger_bank_account.bank_id', 'bank.id')
                                    ->where('finance.ledger_id', '=', $ledgerId)
                                    ->where('finance.reason', '=', 'WITHDRAW')
                                    ->orderBy('finance.id', 'desc')
                                    ->get();
        } else if($enviroment == 'user') {
            $withdrawalsSummary = Finance::select('ledger_bank_account.*','finance.*', 'finance.id as id', 'bank.name as bank', 'user.email as user_email', 'user.first_name as user_first_name', 'user.last_name as user_last_name')
                                    ->join('ledger', 'finance.ledger_id', '=', 'ledger.id')
                                    ->join('user', 'ledger.user_id', '=', 'user.id')
                                    ->join('ledger_bank_account', 'finance.ledger_bank_account_id', 'ledger_bank_account.id')
                                    ->join('bank', 'ledger_bank_account.bank_id', 'bank.id')
                                    ->where('finance.ledger_id', '=', $ledgerId)
                                    ->where('finance.reason', '=', 'WITHDRAW')
                                    ->orderBy('finance.id', 'desc')
                                    ->get();
        }
        return $withdrawalsSummary;
    }
}
