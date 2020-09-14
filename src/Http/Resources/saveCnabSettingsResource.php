<?php

namespace Codificar\Withdrawals\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use DB;
/**
 * Class saveCnabSettingsResource
 *
 * @package MotoboyApp
 *
 *
 * @OA\Schema(
 *         schema="saveCnabSettingsResource",
 *         type="object",
 *         description="Retorno Retorno do relatorio de saques do prestador",
 *         title="Withdrawals Details Resource",
 *        allOf={
 *           @OA\Schema(ref="#/components/schemas/saveCnabSettingsResource"),
 *           @OA\Schema(
 *              required={"success", "request"},
 *           )
 *       }
 * )
 */
class saveCnabSettingsResource extends JsonResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {

        //update data
        DB::table('settings')->where('key', 'rem_company_name')->update(array('value' => $this['rem_company_name']));
        DB::table('settings')->where('key', 'rem_cpf_or_cnpj')->update(array('value' => $this['rem_cpf_or_cnpj']));
        DB::table('settings')->where('key', 'rem_document')->update(array('value' => $this['rem_document']));
        DB::table('settings')->where('key', 'rem_agency')->update(array('value' => $this['rem_agency']));
        DB::table('settings')->where('key', 'rem_agency_dv')->update(array('value' => $this['rem_agency_dv']));
        DB::table('settings')->where('key', 'rem_account')->update(array('value' => $this['rem_account']));
        DB::table('settings')->where('key', 'rem_account_dv')->update(array('value' => $this['rem_account_dv']));
        DB::table('settings')->where('key', 'rem_bank_code')->update(array('value' => $this['rem_bank_code']));
        DB::table('settings')->where('key', 'rem_agreement_number')->update(array('value' => $this['rem_agreement_number']));
        DB::table('settings')->where('key', 'rem_transfer_type')->update(array('value' => $this['rem_transfer_type']));

        DB::table('settings')->where('key', 'rem_environment')->update(array('value' => $this['rem_environment']));
        DB::table('settings')->where('key', 'rem_address')->update(array('value' => $this['rem_address']));
        DB::table('settings')->where('key', 'rem_address_number')->update(array('value' => $this['rem_address_number']));
        DB::table('settings')->where('key', 'rem_city')->update(array('value' => $this['rem_city']));
        DB::table('settings')->where('key', 'rem_cep')->update(array('value' => $this['rem_cep']));
        DB::table('settings')->where('key', 'rem_state')->update(array('value' => $this['rem_state']));
        DB::table('settings')->where('key', 'rem_operation')->update(array('value' => $this['rem_operation']));
        return [
            'success' => true,
            'message' => $this['message']
        ];
    }

}
