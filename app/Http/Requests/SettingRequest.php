<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

class SettingRequest extends FormRequest
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
        $data = $data['data'];

        switch (Route::currentRouteName()) {
            case 'setting.store':
            {
                $notifyValues = [];
                $settings = $data['setting'];
                $notifyValues[] = $settings['notify_new'];
                $notifyValues[] = $settings['notify_cancel'];
                $notifyValues[] = $settings['notify_revoke'];
                $notifyValues[] = $settings['notify_paymentfailed'];

                if(in_array(true, $notifyValues)){
                    $rules['data.setting.notify_email'] = 'required|regex:/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}/';
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
        $rules = [];
        $rules['data.setting.notify_email.required'] = 'required';
        $rules['data.setting.notify_email.*'] = 'Enter valid email address';

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
