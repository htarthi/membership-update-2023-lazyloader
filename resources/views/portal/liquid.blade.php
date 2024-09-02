<?php
use Liquid\Liquid;
use Liquid\Template;
use Liquid\Cache\Local;

Liquid::set('INCLUDE_SUFFIX', '');
Liquid::set('INCLUDE_PREFIX', '');
Liquid::set('INCLUDE_ALLOW_EXT', true);
Liquid::set('ESCAPE_BY_DEFAULT', true);

$template = new Template();

$template->registerFilter('pluralize', function ($str, $count) {
    return ( $count > 1 || $count == 0 ) ? $str . 's' : $str;
});
$template->registerFilter('pluralizeBrand', function ($str) {
   if( $str == 'dinersclub' ){
        return "Diner's Club";
    }else{
        $str = strtolower($str);
        $b = str_replace("_", " ", $str);
        return ucwords($b);
    }
});
$template->registerFilter('pluralizeBrandImg', function ($str, $brand, $payment_method) {
    if($payment_method == 'credit_card'){
        $b = preg_replace("/[^a-zA-Z;]+/", "", $brand);
        return $str . '/' . strtolower($b) . '.svg';
    }else if($payment_method == 'shop_pay'){
        return $str . '/' . 'shoppay.svg';
    }
});
$template->registerFilter('member_count', function ($number) {
    return str_pad($number, 6, '0', STR_PAD_LEFT);
});

// $template->parse(' <div class="container">
//    <div class="simplee-portal__wrapper">
//       <div class="simplee-portal__wrapper_inner container">
//           {% if contract.size > 0 %}
//               <div class="simplee-portal__text_inner pb-5">
//                 <div class="subscription_heading mb-3">
//                   <h1 class="black-color font-700">{{languages.portal_title_details}} <span class="grey-color font-400 d-md-inline-block">&#8212;  {{languages.member_number}}: {{contract.member_number | member_count}}</span></h1>
//                 </div>
//                 {% unless feature["hide-portal-part"] %}
//                   <div class="subscription_select d-flex align-items-center">
//                     <h5 class="grey-color font-400 me-3 mb-0 mt-0 me-1">{{languages.portal_title_subscriptions}}</h5>
//                    <select class="form-select form-select-sm w-auto mb-0" id="dp_membership_contracts" aria-label=".form-select-sm example">
//                       {% for ocontract in otherContracts %}
//                          {{ ocontract.status }}
//                           <option value="{{ ocontract.id }}" {% if ocontract.id == contract.id %} selected {% endif %}>{{ languages.portal_dropdown_label }} #{{ocontract.shopify_contract_id}} (
//                             {% if ocontract.status == "active" %}<span>{{languages.portal_general_active}}</span>{% endif %}
//                             {% if ocontract.status == "paused" %}<span>{{languages.portal_general_paused}}</span> {% endif %}
//                             {% if ocontract.status == "cancelled" %}<span>{{languages.portal_general_cancelled}}</span> {% endif %}
//                             {% if ocontract.status == "expired" %}<span>{{languages.portal_general_expired}}</span> {% endif %}
//                             )
//                           </option>
//                         {% endfor %}
//                     </select>
//                   </div>
//                 {% endunless %}
//               </div>
//               <div class="row">
//               <!-- sidebar container -->
//                 <div class="col-lg-3 col-md-3 d-none d-md-block">
//                   <div class="simplee-portal__sidebar">
//                     <div class="simplee-portal__sidebar_inner">
//                       <ul>
//                         <li class="mb-3">
//                           <a href="#products" class="black-color font-600">{{languages.portal_products_title}}</a>
//                         </li>
//                         {% unless contract.is_onetime_payment %}
//                           <li class="mb-3">
//                             <a href="#yourorder" class="black-color font-600 active">{{languages.portal_order_title}}</a>
//                             <ul class="ps-3 mt-3">
//                               <li class="mb-3">
//                                 <a href="#yourorder" class="black-color font-600">{{languages.portal_order_delivery}}</a>
//                               </li>
//                             </ul>
//                           </li>
//                         {% endunless %}
//                         {% unless contract.is_onetime_payment %}
//                           <li class="mb-3">
//                             <a href="#billing" class="black-color font-600">{{languages.portal_billing_title}}</a>
//                           </li>
//                         {% endunless %}
//                       </ul>
//                     </div>
//                   </div>
//                 </div>
//               <!-- main content container -->
//               <div class="col-lg-9 col-md-9 subscription_right_column">
//                 <div class="simplee-portal__rightside">
//                     <div class="subscription_right_inner is_waiting" style="text-align: center;">{{languages.portal_is_waiting}}</div>
//                     <div class="subscription_right_inner not_waiting">
//                       <!-- paused status container -->
//                       {% if contract.status == "paused" %}
//                         <div class="simplee-portal__important bg-white border-radius box_spacing d-flex align-items-center justify-content-between">
//                           <div class="subscription_status">
//                             <h5 class="black-color font-400 mb-1">{{languages.portal_general_status}}</h5>
//                             <h2 class="black-color font-700 mb-0">{{languages.portal_general_paused}}</h2>
//                           </div>
//                           <div class="subscription_status_btn">
//                             <a href="javascript:void(0)" class="btn-secondary border-0 font-400 open_modal" data-modal="resume_subscription">{{languages.portal_action_resume}}</a>
//                           </div>
//                         </div>
//                       {% endif %}

//                       <!-- Failed payment notice container -->
//                       {% if contract.failed_payment_count > 0 %}
//                         <div class="simplee-portal__important bg-white border-radius box_spacing">
//                           <h5 class="black-color font-700 mb-2">{{languages.portal_warning_title}}</h5>
//                           <h5 class="mb-0 red-color font-400">{{languages.portal_warning_text}}</h5>
//                         </div>
//                       {% endif %}

//                       <!-- product list container -->
//                       <div class="simplee-portal__products subscription_products bg-white border-radius  mb-4" id="products">
//                         <div class="subscription_product_heading box_spacing mb-0 border-bottom d-flex align-items-center justify-content-between">
//                           <h2 class="black-color font-700 mb-0">{{languages.portal_products_title}}</h2>
//                         </div>

//                         {% unless isShowProductEdit %}
//                           <div class="subscirption_products_inner box_spacing">
//                             {% for lineitem in contract.lineItems %}
//                               <div class="subscription_products mb-4">
//                                 <div class="d-flex align-items-center">
//                                   <div class="flex-shrink-0 col-md-3">
//                                     {% if lineitem.shopify_variant_image %}
//                                       <img src="{{ lineitem.shopify_variant_image }}" alt="product-img" />
//                                     {% else %}
//                                       <img src="{{ images.no_img }}" alt="product">
//                                     {% endif %}
//                                   </div>
//                                   <div class="flex-grow-1 ms-4">
//                                     <div class="subscription_product_content">
//                                       <h4 class="blue-color font-700 mb-1">
//                                         <span class="Polaris-Link">{{ lineitem.title }}</span>
//                                       </h4>
//                                       <a href="#">
//                                         <p class="mb-0 black-color font-600 mb-1"><i class="fas fa-redo me-1"></i> {{languages.portal_products_title}}</p>
//                                       </a>

//                                       {% if lineitem.sh_variants.size > 1 %}<p class="mb-0 black-color mb-1">{{ lineitem.shopify_variant_title }}</p>{% endif %}

//                                       <p class="mb-0 black-color mb-1">{{languages.protal_products_price}}: <span class="font-600"> {{ lineitem.currency_symbol }}
//                                         {% if contract.currency_code == "JPY" %}<span>{{ lineitem.price | amount_no_decimals }} </span>
//                                         {% else %}<span>{{ lineitem.price }}</span>{% endif %}
//                                         </span>
//                                       </p>
//                                     </div>
//                                   </div>
//                                 </div>
//                               </div>
//                             {% endfor %}
//                             <div class="subscription_product_subtotal d-flex align-items-center justify-content-between border-top mt-5 pt-3">
//                               <h5 class="black-color font-400 mb-0">{{languages.portal_products_subtotal}}</h5>
//                               <h4 class="black-color font-700 mb-0">{{contract.delivery_price_currency_symbol}}
//                                 {% if contract.currency_code == "JPY" %}<span class="sub-total-price"></span>
//                                 {% else %}<span class="sub-total-price"></span>{% endif %}
//                               </h4>
//                             </div>
//                           </div>
//                         {% endunless %}

//                         <!-- product edit container -->
//                         {% if isShowProductEdit %}
//                           <div class="simplee-portal__product_edit subscription_product_edit box_spacing">
//                               <div class="subscription_product_edit_inner subscription_city">
//                                   {% for lineitem in contract.lineItems %}
//                                     <div class="subscription_single_edit mb-5">
//                                       <h4 class="blue-color font-700 mb-2 d-flex align-items-center">
//                                         <a href="https://{{shop.domain}}/admin/products/{{lineitem.shopify_product_id}}" target="_blank" class="Polaris-Link">{{ lineitem.title }}</a>
//                                         {% if contract.lineItems.length > 1 %}<i class="fas fa-times grey-color ms-3"></i>{% endif %}
//                                       </h4>
//                                       <p class="mb-0 black-color mb-3 font-600">{{ lineitem.currency_symbol }}{{ lineitem.price }}</p>
//                                       <div class="subscirption_variants">
//                                         <div class="row">
//                                           <div class="col-md-6">
//                                             <div class="mb-2">
//                                               <label for="Zip" class="form-label font-700 black-color">{{languages.portal_products_variant}}</label>
//                                               <select class="form-select" aria-label="Default select example">
//                                                 {% for lshvar in lineitem.sh_variants %}
//                                                   <option selected>{{lshvar.title}}</option>
//                                                 {% endfor %}
//                                               </select>
//                                             </div>
//                                           </div>
//                                         </div>
//                                       </div>
//                                       <div class="subscription_quntity">
//                                         <label class="form-label font-700 black-color d-block">QTY</label>
//                                         {% if settings.portal_can_change_qty %}
//                                           <div class="quntity_main d-inline-block px-1 py-1">
//                                             <button><span class="minus grey-color">-</span></button>
//                                             <input type="text" v-model="lineitem.quantity" />
//                                             <button><span class="plus grey-color">+</span></button>
//                                           </div>
//                                         {% else %}
//                                           <p class="mb-0 black-color mb-1" v-else>{{ lineitem.quantity }}</p>
//                                         {% endif %}
//                                       </div>
//                                     </div>
//                                   {% endfor %}
//                                   <div class="subscription_total_amount d-flex align-items-center justify-content-between border-top pt-3">
//                                     <div class="subscription_product_total">
//                                       <p class="black-color font-400 mb-1">Total Subscription</p>
//                                       <p class="black-color font-700 mb-0">{{contract.delivery_price_currency_symbol}}<span class="sub-total-price"></span></p>
//                                     </div>
//                                     <div class="subscription_btn d-flex align-items-center mt-3">
//                                       <a href="javascript:void(0)" class="btn-primary bg-blue" disabled>
//                                         <i class="fa fa-spinner fa-spin sm_saving"></i>
//                                         <span class="sm_save">{{languages.portal_products_update}}</span>
//                                       </a>
//                                       <a class="blue-color ms-3">{{languages.portal_products_cancel}}</a>
//                                     </div>
//                                   </div>
//                                 </div>
//                           </div>
//                         {% endif %}
//                       </div>

//                       <!-- next order information container -->
//                       {% unless contract.is_onetime_payment %}
//                       <div class="simplee-portal__order subscription_order subscription_order_next bg-white border-radius  mb-0" id="yourorder">
//                         <div class="subscription_product_heading box_spacing mb-0 border-bottom d-flex align-items-center justify-content-between">
//                           <h2 class="black-color font-700 mb-0">{{languages.portal_order_title}}</h2>

//                           {% if contract.ship_address1 or contract.ship_address2 and contract.ship_name and contract.status != "cancelled" %}
//                             <div class="subscription_edit">
//                               <a href="javascript:void(0)" id="simplee_order_edit_icon" class="d-flex align-items-center justify-content-center"><i class="fas fa-pen"></i></a>
//                             </div>
//                           {% endif %}
//                         </div>
//                         {% unless isShowEditNextOrder %}
//                           <div class="subscription_products box_spacing" id="simplee_order_info">
//                             {% if contract.status != "cancelled" %}
//                               <ul>
//                               <li class="mb-4">
//                                 <h5 class="black-color font-700 mb-1">{{languages.portal_order_to}}</h5>
//                                 <p class="black-color font-400 mb-0">{{contract.ship_name}}</p>
//                                 <p class="black-color font-400 mb-0">{{ customer.email }}
//                                   {% if contract.ship_phone and customer.email  %}<span>|</span> {{contract.ship_phone}} {% endif %}
//                                 </p>
//                               </li>
//                               {% if contract.ship_name and contract.ship_address1 or contract.ship_address2 %}
//                                 <li class="mb-4">
//                                   <h5 class="black-color font-700 mb-1">{{languages.portal_order_address}}</h5>
//                                   <p class="black-color font-400 mb-0">{{contract.ship_address1}}, {{ contract.ship_city }} {{ contract.ship_province }} {{ contract.ship_zip }}</p>
//                                 </li>
//                               {% endif %}
//                               {% if contract.ship_name and contract.ship_address1 or contract.ship_address2 %}
//                                 <li class="mb-4">
//                                   <h5 class="black-color font-700 mb-1">{{languages.portal_order_method}}</h5>
//                                   <p class="black-color font-400 mb-0">{{contract.shipping_carrier}} <span class="font-500"> {{contract.shipping_presentmentTitle}} -  {{contract.delivery_price_currency_symbol}}</span><span class="shipping-price">{{ shippingPrice | round: 2}}</span></p>
//                                 </li>
//                               {% endif %}

//                               {% if contract.is_prepaid == true and contract.prepaid_renew == true or contract.is_prepaid == false  %}
//                                 <li class="mb-4">
//                                   <h5 class="black-color font-700 mb-1">{{languages.portal_order_billing}}</h5>
//                                   <p class="black-color font-400 mb-0">{{contract.next_processing_date}}</p>
//                                 </li>
//                               {% endif %}

//                               <li class="mb-4">
//                                 <h5 class="black-color font-700 mb-1">{{languages.portal_order_frequency}}</h5>
//                                 <p class="black-color font-400 mb-0">{{contract.delivery_interval_count}} {{contract.delivery_interval | pluralize : contract.delivery_interval_count}}</p>
//                               </li>

//                               {% if contract.is_prepaid == false %}
//                                 <li class="mb-0 d-flex align-items-end justify-content-between">
//                                   <div class="subscription_nex_order">
//                                     <h5 class="black-color font-700 mb-1">{{languages.portal_next_renewal}}</h5>
//                                     <p class="black-color font-400 mb-0">{{contract.next_order_date}}</p>
//                                   </div>
//                                 </li>
//                               {% endif %}
//                             </ul>
//                             {% else %}
//                             <ul>
//                               <li class="black-color">
//                                 {% if contract.isPast %}
//                                   <span>Membership was cancelled</span>
//                                 {% else %}
//                                   <span>Membership will be cancelled on {{contract.next_processing_date}}</span>
//                                 {% endif %}
//                               </li>
//                             </ul>
//                             {% endif %}
//                           </div>
//                         {% endunless %}


//                         <div class="subscription_form box_spacing" id="simplee_edit_order_info">
//                             <form>
//                               <div class="row">
//                                 <div class="col-md-6">
//                                   <div class="mb-2">
//                                     <label for="ship_firstName" class="form-label font-700 black-color">{{languages.portal_order_fname}}</label>
//                                     <input type="text" class="form-control" id="ship_firstName" value="{{ contract.ship_firstName }}">
//                                   </div>
//                                 </div>
//                                 <div class="col-md-6">
//                                   <div class="mb-2">
//                                     <label for="ship_lastName" class="form-label font-700 black-color">{{languages.portal_order_lname}}</label>
//                                     <input type="text" class="form-control" id="ship_lastName" value="{{ contract.ship_lastName }}">
//                                   </div>
//                                 </div>
//                                 <div class="col-md-12">
//                                   <div class="mb-2">
//                                     <label for="ship_company" class="form-label font-700 black-color">{{languages.portal_order_company}}</label>
//                                     <input type="text" class="form-control" id="ship_company" value="{{ contract.ship_company }}">
//                                   </div>
//                                 </div>
//                                 <div class="col-md-12">
//                                   <div class="mb-2">
//                                     <label for="ship_address1" class="form-label font-700 black-color">{{languages.portal_order_address1}}</label>
//                                     <input type="text" class="form-control" id="ship_address1" value="{{ contract.ship_address1 }}">
//                                   </div>
//                                 </div>
//                                 <div class="col-md-12">
//                                   <div class="mb-2">
//                                     <label for="Apartment" class="form-label font-700 black-color">{{languages.portal_order_address2}}</label>
//                                     <input type="text" class="form-control" id="ship_address2" value="{{ contract.ship_address2 }}">
//                                   </div>
//                                 </div>
//                                 <div class="col-md-6">
//                                   <div class="mb-2">
//                                     <label for="ship_city" class="form-label font-700 black-color">{{languages.portal_order_city}}</label>
//                                     <div class="subscription_city">
//                                       <input type="text" class="form-control ps-4" id="ship_city" value="{{ contract.ship_city }}">
//                                     </div>
//                                   </div>
//                                 </div>
//                                 <div class="col-md-6">
//                                   <div class="mb-2">
//                                     <label for="ship_zip" class="form-label font-700 black-color">{{languages.portal_order_zip}}</label>
//                                     <input type="text" class="form-control" id="ship_zip" required value="{{ contract.ship_zip }}">
//                                   </div>
//                                 </div>
//                                 <div class="col-md-6">
//                                   <div class="mb-2">
//                                     <label for="Zip" class="form-label font-700 black-color">{{languages.portal_order_country}}</label>
//                                     <select class="form-select" aria-label="Default select example" value="{{ contract.ship_country }}" id="select_countries">
//                                       {% for country in countries %}
//                                         <option value="{{ country[1] }}" {% if country[1] == contract.ship_country %} selected {% endif %}>{{country[1]}}</option>
//                                       {% endfor %}
//                                     </select>
//                                   </div>
//                                 </div>
//                                 <div class="col-md-6">
//                                   <div class="mb-2">
//                                     <label for="Zip" class="form-label font-700 black-color">{{languages.portal_order_state}}</label>
//                                     <select class="form-select" aria-label="Default select example" value="{{ ship_province }}" id="select_states">
//                                        {% for state in states %}
//                                         {% assign sindex =  forloop.index %}
//                                         <option value="{{ states[sindex] }}" data-province="{{sindex}}">{{states[sindex]}}</option>
//                                        {% endfor %}
//                                     </select>
//                                   </div>
//                                 </div>
//                               </div>
//                             </form>
//                             <div class="subscription_btn d-flex align-items-center mt-3">
//                               <a href="javascript:void(0)" class="btn-primary bg-blue sm_save_data" data-save="edit_shipping_address">
//                                 <i class="fa fa-spinner fa-spin sm_saving"></i>
//                                 <span class="sm_save">{{languages.portal_order_update}}</span></a>
//                               <a href="javascript:void(0)" class="blue-color ms-3" id="simplee_order_edit_cancel">{{languages.portal_order_cancel}}</a>
//                             </div>
//                         </div>
//                       </div>
//                       {% endunless %}

//                       <!-- prepaid order information container -->
//                       {% if contract.is_prepaid and contract.is_onetime_payment == false %}
//                         <div class="simplee-portal__order subscription_order subscription_order_prepaid bg-white border-radius mb-0" id="prepaid_order">
//                           <div class="subscription_product_heading box_spacing mb-0 border-bottom d-flex align-items-center justify-content-between">
//                             <h2 class="black-color font-700 mb-0">{{languages.portal_prepaid_title}}</h2>
//                           </div>
//                           <div class="subscription_products box_spacing">
//                             <table>
//                               <thead class="prepaid_heading">
//                                 <th>{{languages.portal_prepaid_orderdate}}</th>
//                                 <th>{{languages.protal_prepaid_status}}</th>
//                               </thead>
//                               <tbody class="prepaid_content">
//                                 {% for fulfillment in contract.fulfillmentOrders %}
//                                   <tr>
//                                     <td>{{fulfillment.fulfillAt }}</td>
//                                     <td class="prepaid_status">{{fulfillment.status | pluralizeBrand}}</td>
//                                   </tr>
//                                 {% endfor %}
//                               </tbody>
//                             </table>
//                           </div>
//                         </div>
//                       {% endif %}

//                       <!-- billing information container -->
//                       {% unless contract.is_onetime_payment %}
//                         <div class="simplee-portal__order subscription_order bg-white border-radius mb-0" id="billing">
//                           <div class="subscription_product_heading box_spacing mb-0 border-bottom">
//                             <h2 class="black-color font-700 mb-0">{{languages.portal_billing_title}}</h2>
//                           </div>
//                           <div class="subscription_products box_spacing">
//                             <div class="subscription_card_on_file mb-3">
//                               <div class="accordion" id="billingInfo">
//                                 <div class="accordion-item border-0 rounded-0">
//                                   <h2 class="accordion-header" id="headingTwo">
//                                     <button class="billing-acc accordion-button d-flex align-items-center justify-content-between collapsed p-0 rounded-0" type="button" data-toggle="collapse" data-target="#billingInfoInner" aria-expanded="false" aria-controls="billingInfoInner">
//                                       {% if contract.payment_method == "credit_card" or contract.payment_method == "shop_pay" %}
//                                         <div class="subscription_accordian_title">
//                                           <h5 class="black-color font-700 mb-1">{{languages.portal_billing_card}}</h5>
//                                           <div class="black-color font-400 mb-0 billing-card">
//                                             <img src="{{ images.img | pluralizeBrandImg : contract.cc_brand, contract.payment_method }}" class="me-1" class=" {% if contract.payment_method != "credit_card" %} shoppay_img {% endif %}" }">
//                                             <div class="white-space-wrap">
//                                               <span class="font-600">{{contract.cc_name}}</span>
//                                               {% if contract.payment_method == "credit_card" %}<span> {{contract.cc_brand | pluralizeBrand}} {{languages.portal_billing_ending}} {{contract.cc_lastDigits}}</span>
//                                               {% else %}<span> Card {{languages.portal_billing_ending}} {{contract.cc_lastDigits}}</span>{% endif %}
//                                             </div>
//                                           </div>
//                                         </div>
//                                       {% endif %}
//                                       {% if contract.payment_method == "paypal" %}
//                                       <div class="subscription_accordian_title">
//                                         <p class="black-color font-400 mb-0">
//                                           <img src="{{ images.img }}/paypal.png" class="me-1 img-paypal">
//                                           <span class="font-600">PayPal Account: </span> <span>{{contract.paypal_account}}</span>
//                                         </p>
//                                       </div>
//                                       {% endif %}
//                                     </button>
//                                   </h2>
//                                   <div id="billingInfoInner" class="accordion-collapse collapse">
//                                     <div class="accordion-body px-0">
//                                       {% if contract.payment_method != "shop_pay" %}<p class="black-color font-600">{{languages.portal_billing_instructions}}</p>
//                                       <div class="subscription_btn d-flex align-items-center">
//                                         {% if contract.billing_update_url == "" %}
//                                           <a href="javascript:void(0)" class="btn-primary bg-blue px-4 py-2 sm_save_data" data-save="updatePaymentDetailEmail">{{languages.portal_billing_send}}</a>
//                                         {% else %}
//                                           <a href="{{ contract.billing_update_url }}" class="btn-primary bg-blue px-4 py-2" target="_blank">Update Billing Information</a>
//                                         {% endif %}
//                                         <a href="javascript:void(0)" class="blue-color ms-3" data-toggle="collapse" data-target="#billingInfoInner" aria-expanded="false" aria-controls="billingInfoInner">{{languages.portal_billing_cancel}}</a>
//                                       </div>
//                                       {% endif %}
//                                     </div>
//                                   </div>
//                                 </div>
//                               </div>
//                             </div>
//                             <div class="suscription_summary">
//                               <h5 class="black-color font-700 mb-1">{{languages.portal_billing_summary}}</h5>
//                               <table class="table">
//                                 <tbody>
//                                   <tr>
//                                     <td class="p-0 border-0 black-color font-400">{{languages.portal_billing_subtotal}}</td>
//                                     <td class="p-0 border-0 black-color font-400 text-end">{{ contract.delivery_price_currency_symbol }}
//                                       {% if contract.currency_code == "JPY" %}
//                                         <span class="summary-subtotal">{{ summarySubtotal | amount_no_decimals }}</span>
//                                       {% else %}<span class="summary-subtotal">{{ summarySubtotal }}</span>{% endif %}

//                                     </td>
//                                   </tr>
//                                   {% if contract.ship_name and contract.ship_address1 or contract.ship_address2 %}
//                                     <tr>
//                                       <td class="p-0 border-0 black-color font-400">Shipping - {{contract.shipping_presentmentTitle}}</td>
//                                       <td class="p-0 border-0 black-color font-400 text-end">
//                                         {{ contract.delivery_price_currency_symbol }}
//                                         {% if contract.currency_code == "JPY" %}<span class="shipping-price">{{ shippingPrice | amount_no_decimals }}</span>
//                                         {% else %}<span class="shipping-price">{{ shippingPrice | round: 2 }}</span>{% endif %}

//                                       </td>
//                                     </tr>
//                                   {% endif %}
//                                   <tr class="subscription_total">
//                                     <td class="p-0 border-0 black-color font-600 pt-2">{{languages.portal_billing_total}}</td>
//                                     <td class="p-0 border-0 black-color font-600 pt-2 text-end">
//                                     <span class="grey-color font-400">{{contract.delivery_price_currency_symbol}}</span>
//                                       {% if contract.currency_code == "JPY" %}<span class="current-total-price">{{ current_total_price | amount_no_decimals}}</span>
//                                       {% else %}<span class="current-total-price">{{ current_total_price }}</span>{% endif %}
//                                     </td>
//                                   </tr>
//                                 </tbody>
//                               </table>
//                             </div>
//                           </div>
//                         </div>
//                       {% endunless %}

//                       <!-- active status container -->
//                       {% if contract.status == "active" %}
//                         <div class="simplee-portal__important subscription_important bg-white border-radius box_spacing d-md-flex align-items-center justify-content-between">
//                           <div class="subscription_status mb-3 mb-md-0">
//                             <h5 class="black-color font-400 mb-1">{{languages.portal_general_status}}</h5>
//                             {% if contract.on_trial == 0 %}<h2 class="black-color font-700 mb-0">{{languages.portal_general_active}}</h2>
//                             {% else %}<h2 class="black-color font-700 mb-0">On Trial (until {{contract.next_processing_date}})</h2>{% endif %}
//                           </div>
//                           <div class="subscription_status_btn d-flex align-items-center justify-content-between">
//                             {% if contract.billing_min_cycles > 0 %}
//                               <div>
//                                 {% if settings.portal_can_cancel and contract.billing_min_cycles > 0 and contract.order_count < contract.billing_min_cycles %}
//                                   <a class="font-600">Minimum of {{contract.billing_min_cycles}} orders before cancelling</a>
//                                 {% elsif settings.portal_can_cancel and contract.billing_min_cycles > 0 and contract.order_count >= contract.billing_min_cycles %}
//                                   <a href="javascript:void(0)" data-modal="cancel_subscription" class="red-color font-600 open_modal">{{languages.portal_action_cancel}}</a>
//                                 {% endif %}
//                               </div>
//                             {% else %}
//                               <div>
//                                 {% if settings.portal_can_cancel %}<a href="javascript:void(0)" data-modal="cancel_subscription" class="red-color font-600 open_modal">{{languages.portal_action_cancel}}</a>{% endif %}
//                               </div>
//                             {% endif %}
//                           </div>
//                         </div>
//                       {% endif %}
//                     </div>
//                 </div>
//               </div>
//             </div>
//           {% else %}
//             <div class="simplee-portal__verify">
//               <div>{{languages.portal_no_membership}}</div>
//             </div>
//           {% endif %}

//           {% if contract.size > 0 %}
//             <!-- Message Toast -->
//             <div id="snackbar">
//               <div class="snackbar_inner">Some text some message..</div>
//             </div>

//             <!-- modal -->
//             <div id="simplee-portal__modal" class="simplee-portal__modal overlay">
//                 <div class="popup subscription_cancel p-4">
//                     <div class="subscription_cancel_popup">
//                         <h2 class="black-color font-700" id="sm_modal_title"></h2>
//                         <p class="black-color font-400 mb-0" id="sm_modal_label"></p>
//                     </div>
//                     <div class="text-end mt-4">
//                         <a class="btn-return ms-3 font-600 close_modal" href="javascript:void(0)" id="sm_close_modal"></a>
//                         <a class="close btn-primary ms-3 font-600" href="javascript:void(0)">
//                           <i class="fa fa-spinner fa-spin sm_saving"></i>
//                           <span class="sm_save sm_save_data" id="sm_modal_save" data-save=""></span>
//                         </a>
//                     </div>
//                 </div>
//             </div>
//           {% endif %}
//       </div>
//    </div>
// </div>');
$template->parse($liquid);

$template->setCache(new Local());

echo $template->render([
    'liquid' => $liquid,
    'language'=> $data['language'],
    'shop' => $data['shop'],
    'membership' => $data['membership'],
    'other_memberships' => $data['other_memberships'],
    'customer' => $data['customer'],
    'images' => $data['images'],
    'settings' => $data['settings'],
    'reasons' => $data['reasons'],
    'countries' => $data['countries'],
    'is_loading' => false,
    'isSaving' => false,
]);
?>


<!DOCTYPE html>

<html>
    <head>

    </head>
</html>

