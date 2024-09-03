<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PlanRequest extends FormRequest
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
        // logger("dasdasd");

        switch (Route::currentRouteName()) {
            case 'plan-group': {
                    $rules['data.name'] = 'required';
                    $rules['data.options.0'] = 'required';
                    break;
                }

            case 'tiers.store': {
                    $rules['data.product.id'] = 'required';
                    $rules['data.tiers'] = 'required|array';
                    $rules['data.tiers.*.name'] = 'required|distinct:ignore_case';
                    $rules['data.tiers.*.tag_customer'] = 'required';
                    // $rules['data.tiers.*.tag_order'] = 'required';

                    $rules['data.tiers.*.membershipLength'] = 'required|array';
                    $rules['data.tiers.*.membershipLength.*.billing_interval_count'] = 'required|integer|min:1';
                    $rules['data.tiers.*.membershipLength.*.pricing_adjustment_value'] = 'required|numeric|min:0|max:999999';
                    //$rules['data.tiers.*.membershipLength.*.name'] = 'required|distinct:ignore_case';
                    $rules['data.tiers.*.membershipLength.*.description'] = 'required';
                    $membershipLength = data_get($this->all(), 'data.tiers.*.membershipLength');

                    $automaticLength = data_get($this->all(), 'data.tiers.*.automatic_checkout_discount');

                    foreach ($automaticLength as $akey => $val) {
                        // dd($data['tiers'][$akey]['discount_type']);
                        if($data['tiers'][$akey]['discount_type'] == 2)
                        if (isset($data['tiers'][$akey]['active_shipping_dic']) && $data['tiers'][$akey]['activate_shipping_discount']) {

                            if(strlen($data['tiers'][$akey]['shipping_discount_message']) > 50){
                                $rules['data.tiers.*.shipping_discount_message'] = 'sometimes|nullable|max:50';
                                $rules['data.tiers.' . $akey . '.automaticError'] = 'required';
                            }

                            if($data['tiers'][$akey]['shipping_discount_code'] == '' ||$data['tiers'][$akey]['shipping_discount_code'] < 1){
                                $rules['data.tiers.*.shipping_discount_code'] = 'required|numeric|min:1';
                                $rules['data.tiers.' . $akey . '.automaticError'] = 'required';
                            }

                            if ($data['tiers'][$akey]['active_shipping_dic'] == '$') {
                                if ($data['tiers'][$akey]['shipping_discount_code'] > 100000) {
                                    $rules['data.tiers.*.shipping_discount_code'] = 'sometimes|nullable|numeric|max:100000';
                                    $rules['data.tiers.' . $akey . '.automaticError'] = 'required';
                                }
                            } else if ($data['tiers'][$akey]['active_shipping_dic'] == '%') {
                                if ($data['tiers'][$akey]['shipping_discount_code'] > 100) {
                                    $rules['data.tiers.*.shipping_discount_code'] = 'sometimes|nullable|numeric|max:100';
                                    $rules['data.tiers.' . $akey . '.automaticError'] = 'required';
                                }
                            }
                        }
                        $getDiscount = data_get($this->all(), 'data.tiers.' . $akey . '.automatic_checkout_discount.*');
                        if ($data['tiers'][$akey]['activate_product_discount']) {
                            // collection_message
                            if (!empty($getDiscount) && count($getDiscount)) {
                                foreach ($getDiscount as $diskey => $dis) {
                                    if(strlen($dis['collection_message']) > 50){
                                        $rules['data.tiers.' . $akey . '.automatic_checkout_discount.' . $diskey . '.collection_message'] = 'sometimes|nullable|max:50';
                                        $rules['data.tiers.' . $akey . '.automaticError'] = 'required';
                                    }

                                    if($dis['collection_discount'] == '' || $dis['collection_discount'] < 1){
                                        $rules['data.tiers.' . $akey . '.automatic_checkout_discount.' . $diskey . '.collection_discount'] = 'required|numeric|min:1';
                                        $rules['data.tiers.' . $akey . '.automaticError'] = 'required';
                                    }
                                    if ($dis['collection_discount_type'] == '$') {
                                        if ($dis['collection_discount'] > 100000) {
                                            $rules['data.tiers.' . $akey . '.automatic_checkout_discount.' . $diskey . '.collection_discount'] = 'sometimes|nullable|numeric|max:100000';
                                            $rules['data.tiers.' . $akey . '.automaticError'] = 'required';
                                        }
                                    }else {
                                        if ($dis['collection_discount_type'] == '%') {
                                            if ($dis['collection_discount'] > 100) {
                                                $rules['data.tiers.' . $akey . '.automatic_checkout_discount.' . $diskey . '.collection_discount'] = 'sometimes|nullable|numeric|max:100';
                                                $rules['data.tiers.' . $akey . '.automaticError'] = 'required';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }


                    foreach ($membershipLength as $key => $val) {
                        $rules['data.tiers.' . $key . '.membershipLength.*.name'] = 'required|distinct:ignore_case';
                        $plans = data_get($this->all(), 'data.tiers.' . $key . '.membershipLength.*');

                        if (!empty($plans) && count($plans)) {

                            foreach ($plans as $pkey => $plan) {
                                if ($plan['billing_interval'] == 'year') {
                                    // $rules['data.tiers.*.membershipLength.*.billing_interval_count'] .= '|max:5';
                                    $rules['data.tiers.' . $key . '.membershipLength.' . $pkey . '.billing_interval_count'] = 'required|integer|min:1|max:5';
                                }

                                if ($plan['is_advance_option'] == "true") {


                                    if ($plan['is_set_min'] == "true") {
                                        $rules['data.tiers.' . $key . '.membershipLength.' . $pkey . '.billing_min_cycles'] = 'sometimes|nullable|integer|min:1|max:100';
                                    }

                                    if ($plan['is_set_max'] == "true") {

                                        $rules['data.tiers.' . $key . '.membershipLength.' . $pkey . '.billing_max_cycles'] = 'sometimes|nullable|integer|min:1|max:1000';
                                    }

                                    if ($plan['trial_available']) {

                                        if ($plan['trial_type'] == "orders") {
                                            $rules['data.tiers.' . $key . '.membershipLength.' . $pkey . '.pricing2_after_cycle'] = 'sometimes|nullable|required|integer|min:1|max:365';
                                        }

                                        if ($plan['trial_type'] == "days") {
                                            $rules['data.tiers.' . $key . '.membershipLength.' . $pkey . '.trial_days'] = 'sometimes|nullable|required|integer|min:1|max:365';
                                        }
                                    }
                                }
                            }
                        }
                        $formFields = data_get($this->all(), 'data.tiers.' . $key . '.formFields.*');
                        $discounts = data_get($this->all(), 'data.tiers.' . $key . '.discounts.*');

                        foreach ($discounts as $dkey => $val) {
                            $rules['data.tiers.' . $key . '.discounts.' . $dkey . '.discount_name'] = 'required';
                            $rules['data.tiers.' . $key . '.discounts.' . $dkey . '.discount_amount'] = 'required|numeric|min:1';
                        }

                        foreach ($formFields as $fkey => $val) {
                            $rules['data.tiers.' . $key . '.formFields.' . $fkey . '.field_label'] = 'required';

                            if ($val['field_type'] == 'Dropdown List' || $val['field_type'] == 'Radio Group' || $val['field_type'] == 'Checkbox Group') {
                                $rules['data.tiers.' . $key . '.formFields.' . $fkey . '.field_options'] = 'required';
                            }
                        }
                    }


                    $rules['data.tiers.*.membershipLength.*.pricing2_adjustment_value'] = 'sometimes|nullable|required_if:data.tiers.*.membershipLength.*.trial_available,true|numeric';
                    $rules['data.tiers.*.membershipLength.*.store_credit_amount'] = 'sometimes|nullable|required_if:data.tiers.*.membershipLength.*.store_credit,true|numeric';

                    break;
                }
            case 'plan.store': {
                    $rules['data.name'] = 'required';
                    $rules['data.tag_customer'] = 'required';
                    // $rules['data.tag_order'] = 'required';
                    $rules['data.product.id'] = 'required';

                    foreach ($data['membershipLength'] as $key => $val) {
                        $rules['data.membershipLength.' . $key . '.pricing_adjustment_value'] = 'required|numeric|min:0|max:999999';
                        $rules['data.membershipLength.' . $key . '.name'] = 'required|distinct:ignore_case';
                        $rules['data.membershipLength.' . $key . '.description'] = 'required';

                        if ($val['is_advance_option'] == "true") {
                            if ($val['is_set_min']) {
                                $rules['data.membershipLength.' . $key . '.billing_min_cycles'] = 'required|integer|min:1|max:100';
                            }
                            if ($val['is_set_max']) {
                                $rules['data.membershipLength.' . $key . '.billing_max_cycles'] = 'required|integer|min:1|max:1000';
                            }
                            logger("Trial avliable is  an " . $key . $val['trial_available']);
                            if ($val['trial_available']) {

                                if ($val['trial_type'] == 'orders') {
                                    $rules['data.membershipLength.' . $key . '.pricing2_after_cycle'] = 'required|integer|min:1|max:365';
                                } else if ($val['trial_type'] == 'orders' && $val['trial_days' != 0]) {
                                    $rules['data.membershipLength.' . $key . '.trial_days'] = 'required|integer|min:1|max:365';
                                }
                                $rules['data.membershipLength.' . $key . '.pricing2_adjustment_value'] = 'required|numeric';
                            }
                            if ($val['store_credit']) {
                                $rules['data.membershipLength.' . $key . '.store_credit_amount'] = 'required|numeric';
                            }
                        }
                    }

                    // Credit rules
                    foreach ($data['creditRules'] as $mlkey => $mlval) {
                        $rules['data.creditRules.' . $mlkey . '.trigger'] = 'required';
                        $rules['data.creditRules.' . $mlkey . '.value_type'] = 'required';

                        if ($mlval['value_type'] != 'membership_value') {
                            $rules['data.creditRules.' . $mlkey . '.value_amount'] = 'required|numeric';
                        }
                    }

                    $ruleTypes = ['page', 'blog', 'article', 'product', 'collection'];
                    foreach ($data['rules'] as $key => $val) {
                        if (in_array($val['rule_type'], $ruleTypes)) {
                            $rules['data.rules.' . $key . '.rule_attribute1'] = 'required';
                        }
                    }
                    foreach ($data['formFields'] as $key => $val) {
                        $rules['data.formFields.' . $key . '.field_label'] = 'required';

                        if ($val['field_type'] == 'Dropdown List' || $val['field_type'] == 'Radio Group' || $val['field_type'] == 'Checkbox Group') {
                            $rules['data.formFields.' . $key . '.field_options'] = 'required';
                        }
                    }

                    foreach ($data['discounts'] as $key => $val) {
                        $rules['data.discounts.' . $key . '.discount_name'] = 'required';
                        $rules['data.discounts.' . $key . '.discount_amount'] = 'required|numeric|min:1';
                    }

                    if (($data['discount_code'] != '' || $data['discount_code'] != null) && ($data['is_display_on_cart_page'] == 1 || $data['is_display_on_member_login'] == 1)) {
                        $rules['data.discount_code_members'] = 'required';
                    }
                    break;
                }
            case 'mail': {
                    $rules['data.subject'] = 'required';
                    $rules['data.html_body'] = 'required';
                    $rules['data.mailto'] = 'required|email';
                    break;
                }
            case 'email-body': {
                    $rules['data.subject']  = 'required';
                    $rules['data.html_body'] = 'required';
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

        switch (Route::currentRouteName()) {


            case 'tiers.store':
                $rules['data.product.id'] = 'Please select a product.';
                $rules['data.tiers'] = 'At least one tier is required';
                $rules['data.tiers.*.name.distinct'] = 'Name of the selling plan is already used.';
                $rules['data.tiers.*.name.*'] = 'Please enter the name for this tier.';
                $rules['data.tiers.*.tag_customer'] = 'Customer tag is required.';
                // $rules['data.tiers.*.tag_order'] = 'Order tag is required.';
                $rules['data.tiers.*.membershipLength.*'] = 'This membership product requires at least one membership tier before saving';
                $rules['data.tiers.*.membershipLength.*.billing_interval_count.max'] = 'Please enter a length shorter than 5 years';
                $rules['data.tiers.*.membershipLength.*.billing_interval_count.*'] = 'Must be at least 1';

                $rules['data.tiers.*.automatic_checkout_discount.*.collection_discount.max'] = 'When the discount type is %, the maximum is 100, and when it is $, the maximum is 100000';
                $rules['data.tiers.*.automatic_checkout_discount.*.collection_discount.min'] = 'Must be at least 1';
                $rules['data.tiers.*.automatic_checkout_discount.*.collection_discount.required'] = 'Must be at least 1';
                $rules['data.tiers.*.automatic_checkout_discount.*.collection_message.max'] = 'The discount name not be greater than 50 characters.';

                $rules['data.tiers.*.shipping_discount_code.max'] = 'When the discount type is %, the maximum is 100, and when it is $, the maximum is 100000.';
                $rules['data.tiers.*.shipping_discount_code.min'] = 'Must be at least 1';
                $rules['data.tiers.*.shipping_discount_code.required'] = 'Must be at least 1';
                $rules['data.tiers.*.shipping_discount_message.max'] =  'The discount name not be greater than 50 characters.';


                $rules['data.tiers.*.membershipLength.*.pricing_adjustment_value.*'] = 'Must be at least 0 and max 999999';
                // $rules['data.tiers.*.membershipLength.*.name.required'] =  'Display name is required.';
                $membershipLength = data_get($this->all(), 'data.tiers.*.membershipLength');

                foreach ($membershipLength as $key => $val) {
                    $rules['data.tiers.' . $key . '.membershipLength.*.name.distinct'] = 'Tier length has a duplicate name values';
                    $rules['data.tiers.' . $key . '.membershipLength.*.name.required'] = 'Display name is required.';


                    $plans = data_get($this->all(), 'data.tiers.' . $key . '.membershipLength.*');
                    if (!empty($plans) && count($plans)) {

                        foreach ($plans as $pkey => $plan) {

                            if ($plan['is_advance_option'] == "true") {
                                $rules['data.tiers.' . $key . '.membershipLength.' . $pkey . '.billing_min_cycles.*'] = 'Must be between 1 and 100';
                                $rules['data.tiers.' . $key . '.membershipLength.' . $pkey . '.billing_max_cycles.*'] = 'Must be between 1 and 1000';
                                $rules['data.tiers.' . $key . '.membershipLength.' . $pkey . '.pricing2_after_cycle.*'] = 'Must be between 1 and 365';
                                $rules['data.tiers.' . $key . '.membershipLength.' . $pkey . '.trial_days.*'] = 'Must be between 1 and 365';
                                $rules['data.tiers.' . $key . '.membershipLength.' . $pkey . '.pricing2_adjustment_value.required'] = 'Price is required.';
                                $rules['data.tiers.' . $key . '.membershipLength.' . $pkey . '.pricing2_adjustment_value.*'] = 'Value must be integer';
                                $rules['data.tiers.' . $key . '.membershipLength.' . $pkey . '.store_credit_amount.required'] = 'Store Credit Amount is required.';
                                $rules['data.tiers.' . $key . '.membershipLength.' . $pkey . '.store_credit_amount.*'] = 'Store Credit Amount is required.';
                            }
                        }
                    }
                    $discounts = data_get($this->all(), 'data.tiers.' . $key . '.discounts.*');
                    foreach ($discounts as $dkey => $val) {
                        $rules['data.tiers.' . $key . '.discounts.' . $dkey . '.discount_name.required'] = 'Discount name is required.';
                        $rules['data.tiers.' . $key . '.discounts.' . $dkey . '.discount_amount.*'] = 'Value must be integer';
                    }

                    $formFields = data_get($this->all(), 'data.tiers.' . $key . '.formFields.*');

                    foreach ($formFields as $fkey => $val) {
                        $rules['data.tiers.' . $key . '.formFields.' . $fkey . '.field_label'] = 'Label is required';

                        if ($val['field_type'] == 'Dropdown List' || $val['field_type'] == 'Radio Group' || $val['field_type'] == 'Checkbox Group') {
                            $rules['data.tiers.' . $key . '.formFields.' . $fkey . '.field_options'] = 'Type is required';
                        }
                    }
                }

                $rules['data.tiers.*.membershipLength.*.description.*'] =  'Description is required.';



                break;
            default:
                $rules['data.name.*'] = 'Please enter the name for this tier.';
                $rules['data.html_body.*'] = 'required';
                $rules['data.subject.*'] = 'required';
                $rules['data.mailto.required'] = 'required';
                $rules['data.mailto.email'] = 'Enter valid email address';
                $rules['data.tag_customer'] = 'Customer tag is required.';
                $rules['data.tag_order'] = 'Order tag is required.';
                $rules['data.product.id'] = 'Please select a product.';

                if (@$data['membershipLength']) {
                    foreach ($data['membershipLength'] as $key => $val) {
                        $rules['data.membershipLength.' . $key . '.billing_interval_count.*'] = 'Must be at least 1';
                        $rules['data.membershipLength.' . $key . '.pricing_adjustment_value.*'] = 'Must be at least 0 and max 999999';
                        $rules['data.membershipLength.' . $key . '.name.distinct'] = 'Tier has a duplicate values';
                        $rules['data.membershipLength.' . $key . '.name.required'] = 'Display name is required.';
                        $rules['data.membershipLength.' . $key . '.description.*'] = 'Description is required.';

                        if ($val['is_advance_option'] == "true") {
                            if ($val['is_set_min']) {
                                $rules['data.membershipLength.' . $key . '.billing_min_cycles.*'] = 'Must be between 1 and 100';
                            }
                            if ($val['is_set_max']) {
                                $rules['data.membershipLength.' . $key . '.billing_max_cycles.*'] = 'Must be between 1 and 1000';
                            }
                            if ($val['trial_available']) {
                                if ($val['trial_type'] == 'orders') {
                                    $rules['data.membershipLength.' . $key . '.pricing2_after_cycle.*'] = 'Must be between 1 and 365';
                                } else if ($val['trial_type'] == 'orders' && $val['trial_days' != 0]) {
                                    $rules['data.membershipLength.' . $key . '.trial_days.*'] = 'Must be between 1 and 365';
                                }

                                $rules['data.membershipLength.' . $key . '.pricing2_adjustment_value.required'] = 'Price is required.';
                                $rules['data.membershipLength.' . $key . '.pricing2_adjustment_value.*'] = 'value must be integer';
                            }
                            if ($val['store_credit']) {
                                $rules['data.membershipLength.' . $key . '.store_credit_amount.required'] = 'Store Credit Amount is required.';
                                $rules['data.membershipLength.' . $key . '.store_credit_amount.*'] = 'value must be integer';
                            }
                        }
                    }
                }
                break;
        }



        if (@$data['discounts']) {
            foreach ($data['discounts'] as $key => $val) {
                $rules['data.discounts.' . $key . '.discount_name.*'] = 'required';
                $rules['data.discounts.' . $key . '.discount_amount.required'] = 'required';
                $rules['data.discounts.' . $key . '.discount_amount.*'] = 'must be at least 1';
            }
        }

        $rules['data.discount_code_members.*'] = 'required';
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
