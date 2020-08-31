<?php

namespace Codificar\Withdrawals\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class getWithdrawSettingsResource
 *
 * @package MotoboyApp
 *
 *
 * @OA\Schema(
 *         schema="getWithdrawSettingsResource",
 *         type="object",
 *         description="Retorna as configuracoes do saque",
 *         title="Withdrawals Details Resource",
 *        allOf={
 *           @OA\Schema(ref="#/components/schemas/getWithdrawSettingsResource"),
 *           @OA\Schema(
 *              required={"success", "request"},
 *           )
 *       }
 * )
 */
class getWithdrawSettingsResource extends JsonResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {

        return [
            'success' => true,
            'withdraw_settings' => $this['withdraw_settings'],
            'current_balance' => $this['current_balance']
        ];
    }

}
