<?php

namespace Codificar\Withdrawals\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

use Finance, Bank;
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

        // Check if withdraw isn't enabled
        if ($settings['with_draw_enabled'] == false) {
            return [
                'success' => false,
                'error' => 'O saque nao esta ativo'
            ];
        }

         // Check if withdraw_value is NOT between max and min values allowed (less than min OR greater than max)
         else if ($value < $settings['with_draw_min_limit'] || $value > $settings['with_draw_max_limit'] ) {
            return [
                'success' => false,
                'error' => 'O valor minmo e ' . $settings['with_draw_min_limit'] . ' e o maximo ' . $settings['with_draw_max_limit']
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
        else if (!Bank::find($bankAccountId)) {
            return [
                'success' => false,
                'error' => 'Dados bancarios nao existem'
            ];
        }
    
        // Add withdrawal
        else {
            $value = -$value;
            $return = Finance::createWithDrawRequest($ledger->id, $value, $bankAccountId, 0);
            
            // If has tax
            if ($settings['with_draw_tax'] > 0) {
                $settings['with_draw_tax'] = -$settings['with_draw_tax'];
                Finance::createCustomEntryWithBankAccountId($ledger->id, Finance::WITHDRAW, trans('finance.withdraw_tax'), $settings['with_draw_tax'], 0, $bankAccountId);
            }

            // Return data
            return [
                'success' => true,
                'withdraw_value' => $this['withdraw_value']
            ];
        }
    
    }

}
