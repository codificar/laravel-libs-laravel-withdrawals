<?php

namespace Codificar\Withdrawals\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Class ConfirmWithdrawFormRequest
 *
 */
class ConfirmWithdrawFormRequest extends FormRequest {

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
		// dd($this->note->getClientOriginalExtension());
		return [
            'withdraw_id'   => ['required', 'integer'],
			'file_withdraw' => ['required', 'file'],
			'date' => ['required', 'string']
		];
	}

	public function messages() {
		return [
            'withdraw_id.required'  => 'Id do saque é necessário',
            'file_withdraw.required'=> 'É necessário anexar o comprovante',
            'file_withdraw.file'    => 'É necessário anexar um documento',
            'date.required'         => 'É necessário preencher a data'
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
