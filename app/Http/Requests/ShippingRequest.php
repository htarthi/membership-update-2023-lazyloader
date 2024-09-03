<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

class ShippingRequest extends FormRequest
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
            case 'shipping.store':
            {
                $rules['data.name'] = 'required';

                foreach( $data['shipping_zones'] as $key=>$val ){
                    $rules['data.shipping_zones.'. $key .'.zone_name'] = 'required';
                    $rules['data.shipping_zones.'. $key .'.zone_country'] = 'required|array';
                    $rules['data.shipping_zones.'. $key .'.rate_name'] = 'required';
                    $rules['data.shipping_zones.'. $key .'.rate_value'] = 'required|numeric|between:0,99999999.99';
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
        $data = $this::all();
        $data = $data['data'];
        $rules['data.profile_name.*'] = 'required';

        foreach( $data['shipping_zones'] as $key=>$val ){
            $rules['data.shipping_zones.'. $key .'.zone_name.*'] = 'required';
            $rules['data.shipping_zones.'. $key .'.zone_country.*'] = 'required';
            $rules['data.shipping_zones.'. $key .'.rate_name.*'] = 'required';
            $rules['data.shipping_zones.'. $key .'.rate_value.required'] = 'required';
            $rules['data.shipping_zones.'. $key .'.rate_value.*'] = 'value must be a number';
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
