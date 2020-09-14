<?php

namespace Codificar\Withdrawals\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Class SaveCnabSettingsFormRequest
 *
 */
class SaveCnabSettingsFormRequest extends FormRequest {

    public $ride;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'settings.rem_company_name'     => ['required', 'string'],
            'settings.rem_cpf_or_cnpj'      => ['required', 'string'],
            'settings.rem_document'         => ['required'],
            'settings.rem_agency'           => ['required'],
            'settings.rem_agency_dv'        => ['required'],
            'settings.rem_account'          => ['required'],
            'settings.rem_account_dv'       => ['required'],
            'settings.rem_bank_code'        => ['required'],
            'settings.rem_agreement_number' => ['required', 'string'],
            'settings.rem_transfer_type'    => ['required', 'string'],

            'settings.rem_environment'      => ['required', 'string'],
            'settings.rem_address'          => ['required', 'string'],
            'settings.rem_address_number'   => ['required', 'string'],
            'settings.rem_city'             => ['required', 'string'],
            'settings.rem_cep'              => ['required'],
            'settings.rem_state'            => ['required', 'string'],
            'settings.rem_operation'        => ['string'] //operacao nao eh opcional, nao eh required
        ];
    }

    public function messages()
    {
        return [
            'settings.rem_company_name'     => 'rem_company_name is required',
            'settings.rem_cpf_or_cnpj'      => 'rem_cpf_or_cnpj  is required',
            'settings.rem_document'         => 'rem_document is required',
            'settings.rem_agency'           => 'rem_agency is required',
            'settings.rem_agency_dv'        => 'rem_agency_dv is required',
            'settings.rem_account'          => 'rem_account is required',
            'settings.rem_account_dv'       => 'rem_account_dv is required',
            'settings.rem_bank_code'        => 'rem_bank_code is required',
            'settings.rem_agreement_number' => 'rem_agreement_number is required',
            'settings.rem_transfer_type'    => 'rem_transfer_type is required',

            'settings.rem_environment'      => 'rem_environment is required',
            'settings.rem_address'          => 'rem_address is required',
            'settings.rem_address_number'   => 'rem_address_number is required',
            'settings.rem_city'             => 'rem_city is required',
            'settings.rem_cep'              => 'rem_cep is required',
            'settings.rem_state'            => 'rem_state is required',
            'settings.rem_operation'        => 'rem_operation is string'
        ];
    }

    /**
     * Retorna um json caso a validação falhe.
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator) {
        throw new HttpResponseException(
        response()->json(
                [
                    'success' => false,
                    'errors' => $validator->errors()->all(),
                    'error_code' => \ApiErrors::REQUEST_FAILED
                ]
        ));
    }
}