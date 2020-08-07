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

        $withdrawalsSummary = Finance::select('finance.id as id', 'finance.value as formattedValue', 'finance.compensation_date as date', 'bank.name as bank', 'ledger_bank_account.account as bankAccount')
                                ->join('ledger', 'finance.ledger_id', '=', 'ledger.id')
                                ->join($enviroment, 'ledger.'.$enviroment.'_id', '=', $enviroment.'.id')
                                ->join('ledger_bank_account', 'finance.ledger_bank_account_id', 'ledger_bank_account.id')
                                ->join('bank', 'ledger_bank_account.bank_id', 'bank.id')
                                ->where('finance.ledger_id', '=', $ledgerId)
                                ->where('finance.reason', '=', 'WITHDRAW')
                                ->orderBy('finance.id', 'desc')
                                ->get();
        
        foreach($withdrawalsSummary as $withdral) {
            $withdral->formattedValue = currency_format($withdral->formattedValue);
        }
        return $withdrawalsSummary;
    }
}
