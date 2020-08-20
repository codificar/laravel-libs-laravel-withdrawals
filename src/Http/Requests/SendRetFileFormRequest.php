<?php

namespace Codificar\Withdrawals\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Class SendRetFileFormRequest
 *
 */
class SendRetFileFormRequest extends FormRequest {

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
            'cnab_id'   => ['required', 'integer'],
			'cnab_ret_file' => ['required', 'file']
		];
	}

	public function messages() {
		return [
            'cnab_id.required'  => 'Id do saque é necessário',
            'cnab_ret_file.required'=> 'É necessário anexar o arquivo de retorno',
            'cnab_ret_file.file'    => 'É necessário anexar um arquivo'
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
