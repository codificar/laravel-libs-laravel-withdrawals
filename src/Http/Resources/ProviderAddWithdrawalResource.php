<?php

namespace Codificar\Withdrawals\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

use Finance, LedgerBankAccount;

use Codificar\Withdrawals\Models\Withdrawals;

/**
 * Class ProviderAddWithdrawalResource
 *
 * @package MotoboyApp
 *
 *
 * @OA\Schema(
 *         schema="ProviderAddWithdrawalResource",
 *         type="object",
 *         description="Adiciona um saque para o prestador",
 *         title="Withdrawals Details Resource",
 *        allOf={
 *           @OA\Schema(ref="#/components/schemas/ProviderAddWithdrawalResource"),
 *           @OA\Schema(
 *              required={"success", "request"},
 *           )
 *       }
 * )
 */
class ProviderAddWithdrawalResource extends JsonResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {

        $settings = $this['withdraw_settings'];
        $value = $this['withdraw_value'];
        $balance = $this['current_balance'];
        $ledger = $this['ledger'];
        $bankAccountId = $this['bank_account_id'];
        
        //Get the bankAccountId. If provider not select the bank, so get the first bank
        if(!$bankAccountId) {
            $bankAccountId = LedgerBankAccount::where('ledger_id', '=' , $ledger->id)->first()->id;
        }

        // Check if withdraw isn't enabled
        if ($settings['with_draw_enabled'] == false) {
            return [
                'success' => false,
                'error' => 'O saque nao esta ativo'
            ];
        }

         // Check if withdraw_value is NOT between max and min values allowed (less than min OR greater than max)
         else if ($value < $settings['with_draw_min_limit'] || $value > $settings['with_draw_max_limit'] ) {
            $msgError =  'O valor minmo e ' . $settings['with_draw_min_limit'] . ' e o maximo ' . $settings['with_draw_max_limit'];
            return [
                'success' => false,
                'messages' => [$msgError],
                'error' => $msgError
            ];
        }

        // Check if (value + tax is) > than current_balance. If yes, cannot be withdrawn
        else if ($value + $settings['with_draw_tax'] > $balance) {
            return [
                'success' => false,
                'error' => 'Saldo indisponivel'
            ];
        }

        //Check if bank not exists.
        else if (!LedgerBankAccount::find($bankAccountId)) {
            return [
                'success' => false,
                'error' => 'Dados bancarios nao existem'
            ];
        }
    
        // Add withdrawal
        else {
            $value = -$value;

            // Add withdraw debit in Finance table
            $returnWithdraw = Finance::createWithDrawRequest($ledger->id, $value, $bankAccountId, null);
            
            // Add withdraw Tax debit in Finance table
            $returnTax = null;
            if ($settings['with_draw_tax'] > 0) {
                $settings['with_draw_tax'] = -$settings['with_draw_tax'];
                $returnTax = Finance::createCustomEntryWithBankAccountId($ledger->id, Finance::WITHDRAW, trans('finance.withdraw_tax'), $settings['with_draw_tax'], null, $bankAccountId);
            }

            // Add withdraw in Withdraw table
            $withdraw = new Withdrawals;
            $withdraw->finance_withdraw_id = $returnWithdraw->id;
            if($returnTax) {
                $withdraw->finance_withdraw_tax_id = $returnTax->id;
            }
            $withdraw->type = "requested";
            $withdraw->save();

            // Return data
            return [
                'success' => true,
                'withdraw_value' => $this['withdraw_value']
            ];
        }
    
    }

}
