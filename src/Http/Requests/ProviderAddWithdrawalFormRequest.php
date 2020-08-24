<?php

namespace Codificar\Withdrawals\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Class ProviderAddWithdrawalFormRequest
 *
 */
class ProviderAddWithdrawalFormRequest extends FormRequest {

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
    public function rules() {

        $this->ride = \Requests::find(request()->request_id);
        
        return [
            'provider_id'       => ['required', 'integer'],
            'withdraw_value'    => ['required', 'numeric'],
            'bank_account_id'   => ['integer']
        ];
    }

    public function messages() {
        return [
            'provider_id'       => 'provider_id is integer and required',
            'withdraw_value'    => 'withdraw_value is float and required',
            'bank_account_id'   => 'bank_account_id is int'
        ];
    }

    /**
     * retorna um json caso a validação falhe.
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
