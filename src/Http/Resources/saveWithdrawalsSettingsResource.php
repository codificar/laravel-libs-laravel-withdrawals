<?php

namespace Codificar\Withdrawals\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use DB;
/**
 * Class saveWithdrawalsSettingsResource
 *
 * @package MotoboyApp
 *
 *
 * @OA\Schema(
 *         schema="saveWithdrawalsSettingsResource",
 *         type="object",
 *         description="Retorno de sucesso, dos dados salvos das configuracoes de saque",
 *         title="Withdrawals Details Resource",
 *        allOf={
 *           @OA\Schema(ref="#/components/schemas/saveWithdrawalsSettingsResource"),
 *           @OA\Schema(
 *              required={"success", "request"},
 *           )
 *       }
 * )
 */
class saveWithdrawalsSettingsResource extends JsonResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {

        //update data
        DB::table('settings')->where('key', 'with_draw_enabled')->update(array('value' => $this['with_draw_enabled']));
        DB::table('settings')->where('key', 'with_draw_max_limit')->update(array('value' => $this['with_draw_max_limit']));
        DB::table('settings')->where('key', 'with_draw_min_limit')->update(array('value' => $this['with_draw_min_limit']));
        DB::table('settings')->where('key', 'with_draw_tax')->update(array('value' => $this['with_draw_tax']));
    
        return [
            'success' => true,
            'message' => $this['message']
        ];
    }

}
