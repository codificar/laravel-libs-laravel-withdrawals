<?php

namespace Codificar\Withdrawals\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Class SaveWithdrawalsSettingsFormRequest
 *
 */
class SaveWithdrawalsSettingsFormRequest extends FormRequest {

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
            'settings.with_draw_enabled'     => ['required', 'integer'],
            'settings.with_draw_max_limit'   => ['required', 'numeric'],
            'settings.with_draw_min_limit'   => ['required', 'numeric'],
            'settings.with_draw_tax'         => ['required', 'numeric'],
		];
	}

	public function messages() {
		return [
            'settings.with_draw_enabled'    => 'with_draw_enabled is required',
            'settings.with_draw_max_limit'  => 'with_draw_max_limit is required',
            'settings.with_draw_min_limit'  => 'with_draw_min_limit is required',
            'settings.with_draw_tax'        => 'with_draw_tax is required'
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
