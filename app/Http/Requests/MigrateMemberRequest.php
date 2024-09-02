<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class MigrateMemberRequest extends FormRequest
{
	public static $rules = [];
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = Self::$rules;
        $data = $this::all();
        // logger('==============> Marchant Migrate data', $data);
        $importType = $this->input('importType');
        $data = (@$data['data']) ? $data['data'] : [];

        switch (Route::currentRouteName()) {
            case 'merchantmigrate':
            {
                if(!empty($data)){
                    if($importType == 0){
                        $rules['data.firstname'] = 'required';
                        $rules['data.lastname'] = 'required';
                        $rules['data.email'] = 'required';
                    }
                }


                break;
            }
            default:
                break;
        }
        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        $rules = Self::$rules;
        $data = $this::all();
        // logger('==============> Marchant Migrate data', $data);
        $importType = $this->input('importType');
        $data = (@$data['data']) ? $data['data'] : [];

        switch (Route::currentRouteName()) {
            case 'merchantmigrate':
            {
                if(!empty($data)){
                    if($importType == 0){
                        $rules['data.firstname'] = ' The firstname field is required.';
                        $rules['data.lastname'] = ' The lastname field is required.';
                        $rules['data.email'] = ' The email field is required.';
                    }
                }


                break;
            }
            default:
                break;
        }
        return $rules;
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->ajax() || $this->wantsJson()) {
            $response = new JsonResponse($validator->errors(), 422);
            throw new ValidationException($validator, $response);
        }

        throw (new ValidationException($validator))
            ->errorBag($this->errorBag);
    }
}
