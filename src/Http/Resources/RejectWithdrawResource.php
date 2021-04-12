<?php

namespace Codificar\Withdrawals\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class RejectWithdrawResource
 *
 * @package MotoboyApp
 *
 *
 * @OA\Schema(
 *         schema="RejectWithdrawResource",
 *         type="object",
 *         description="Retorno Retorno do relatorio de saques do prestador",
 *         title="Withdrawals Details Resource",
 *        allOf={
 *           @OA\Schema(ref="#/components/schemas/RejectWithdrawResource"),
 *           @OA\Schema(
 *              required={"success", "request"},
 *           )
 *       }
 * )
 */
class RejectWithdrawResource extends JsonResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {
        return [
            'success' => true,
            'message' => $this['message'],
        ];
    }
}
