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
            'settings.rem_operation'        => ['string'], //operacao nao eh opcional, nao eh required
            'settings.rem_type_compromise'  => ['required', 'string'],
            'settings.rem_code_compromise'  => ['required', 'string'],
            'settings.rem_param_transmission'=> ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'settings.rem_company_name.required'        => 'O campo nome da empresa é necessário',
            'settings.rem_cpf_or_cnpj.required'         => 'O campo tipo de documento é necessário',
            'settings.rem_document.required'            => 'O campo documento é necessário',
            'settings.rem_agency.required'              => 'O campo agência é necessário',
            'settings.rem_agency_dv.required'           => 'O campo digito da agência é necessário',
            'settings.rem_account.required'             => 'O campo conta é necessário',
            'settings.rem_account_dv.required'          => 'O campo dígito da conta é necessário',
            'settings.rem_bank_code.required'           => 'O campo código do banco é necessário',
            'settings.rem_agreement_number.required'    => 'O campo convênio é necessário',
            'settings.rem_transfer_type.required'       => 'O campo tipo de transferência é necessário',

            'settings.rem_environment.required'         => 'O campo ambiente é necessário',
            'settings.rem_address.required'             => 'O campo endereço é necessário',
            'settings.rem_address_number.required'      => 'O campo número do endereço é necessário',
            'settings.rem_city.required'                => 'O campo cidade é necessário',
            'settings.rem_cep.required'                 => 'O campo cep é necessário',
            'settings.rem_state.required'               => 'O campo estado é necessário',
            'settings.rem_operation.required'           => 'O campo operação é necessário',
            'settings.rem_type_compromise.required'     => 'O campo tipo de compromisso é necessário',
            'settings.rem_code_compromise.required'     => 'O campo código do compromisso é necessário',
            'settings.rem_param_transmission.required'  => 'O campo parâmetro de transmissão é necessário'
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