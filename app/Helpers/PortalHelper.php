<?php

if (!function_exists('getPortalLiquidH')) {
    function getPortalLiquidH()
    {
        $portalLiquid  = '{% comment %}
        ########################################################################
         We\'re giving you full access to customize the member portal! For documentation on how to use Liquid and our custom objects, visit:
         https://support.simplee.best/en/articles/6331178-customizing-the-member-portal
        ########################################################################
        {% endcomment %}

        <div class="container">
            <div class="simplee-portal__wrapper">
               <div class="simplee-portal__wrapper_inner container">
                   {% if membership.size > 0 %}

                       <div class="simplee-portal__text_inner pb-5">
                         <div class="membership_heading mb-3">
                           <h1 class="black-color font-700">{{language.portal_title_details}} <span class="grey-color font-400 d-md-inline-block">&#8212;  {{language.member_number}}: {{membership.member_number | member_count}}</span></h1>
                         </div>

                         </div>
                         <div class="membership_select d-flex align-items-center">
                           <h5 class="grey-color font-400 me-3 mb-0 mt-0 me-1">{{language.portal_title_membership}}</h5>
                          <select class="form-select form-select-sm w-auto mb-0" id="dp_membership_contracts" aria-label=".form-select-sm example">
                             {% for other_membership in other_memberships %}
                                {{ other_membership.status }}
                                 <option value="{{ other_membership.id }}" {% if other_membership.id == membership.id %} selected {% endif %}>{{ language.portal_dropdown_label }} #{{other_membership.shopify_contract_id}} (
                                   {% if other_membership.status_display == "Active - Expiring" %}<span>{{language.portal_status_display_expiring}}</span>{% endif %}
                                   {% if other_membership.status_display == "Lifetime Access" %}<span>{{language.portal_status_display_lifetime}}</span> {% endif %}
                                   {% if other_membership.status_display == "Billing Failed" %}<span>{{language.portal_status_display_billing_failed}}</span> {% endif %}
                                   {% if other_membership.status_display == "Active" %}<span>{{language.portal_general_active}}</span> {% endif %}
                                   {% if other_membership.status_display == "Expired" %}<span>{{language.portal_general_expired}}</span> {% endif %}
                                   {% if other_membership.status_display == "Cancelled" or other_membership.status_display == "Access Removed"%}<span>{{language.portal_general_cancelled}}</span> {% endif %}
                                   )
                                 </option>
                               {% endfor %}
                           </select>
                         </div>
                       </div>
                       <div class="row">
                       {% comment %}
                        #################################################
                            BEGIN NAVIGATION BAR (LEFT SIDE OF PAGE)
                        #################################################
                       {% endcomment %}
                         <div class="col-lg-3 col-md-3 d-none d-md-block">
                           <div class="simplee-portal__sidebar">
                             <div class="simplee-portal__sidebar_inner">
                               <ul>
                                 <li class="mb-3">
                                   <a href="#products" class="black-color font-600">{{language.portal_products_title}}</a>
                                 </li>
                                 {% unless membership.is_onetime_payment %}
                                   <li class="mb-3">
                                     <a href="#yourorder" class="black-color font-600 active">{{language.portal_order_title}}</a>
                                   </li>
                                 {% endunless %}
                                 {% unless membership.is_onetime_payment %}
                                   <li class="mb-3">
                                     <a href="#billing" class="black-color font-600">{{language.portal_billing_title}}</a>
                                   </li>
                                 {% endunless %}
                               </ul>
                             </div>
                           </div>
                         </div>
                         {% comment %}
                         #######################################
                            BEGIN MAIN PORTAL CONTENT
                         #######################################
                         {% endcomment %}
                       <div class="col-lg-9 col-md-9 membership_right_column">
                         <div class="simplee-portal__rightside">
                             <div class="membership_right_inner is_waiting" style="text-align: center;">{{language.portal_is_waiting}}</div>
                             <div class="membership_right_inner not_waiting">


                                {% comment %}
                                #######################################
                                   FAILED PAYMENT WARNING MESSAGE
                                #######################################
                                {% endcomment %}
                               {% if membership.failed_payment_count > 0 %}
                                 <div class="simplee-portal__important bg-white border-radius box_spacing">
                                   <h5 class="black-color font-700 mb-2">{{language.portal_warning_title}}</h5>
                                   <h5 class="mb-0 red-color font-400">{{language.portal_warning_text}}</h5>
                                 </div>
                               {% endif %}

                                {% comment %}
                                #######################################
                                    MEMBERSHIP PRODUCT
                                #######################################
                                {% endcomment %}
                               <div class="simplee-portal__products membership_products bg-white border-radius  mb-4" id="products">
                                 <div class="membership_product_heading box_spacing mb-0 border-bottom d-flex align-items-center justify-content-between">
                                   <h2 class="black-color font-700 mb-0">{{language.portal_products_title}}</h2>
                                 </div>

                                 <div class="subscirption_products_inner box_spacing">
                                   {% for lineitem in membership.lineItems %}
                                     <div class="membership_products mb-4">
                                       <div class="d-flex align-items-center">
                                         <div class="flex-shrink-0 col-md-3">
                                           {% if lineitem.shopify_variant_image %}
                                             <img src="{{ lineitem.shopify_variant_image }}" alt="product-img" />
                                           {% elsif lineitem.sh_product.product_image %}
                                             <img src="{{ lineitem.sh_product.product_image }}" alt="product-img" />
                                           {% else %}
                                             <img src="{{ images.no_img }}" alt="product">
                                           {% endif %}
                                         </div>
                                         <div class="flex-grow-1 ms-4">
                                           <div class="membership_product_content">
                                             <h4 class="blue-color font-700 mb-1">
                                               <span class="Polaris-Link">{{ lineitem.title }}</span>
                                             </h4>
                                             <a href="#">
                                               <p class="mb-0 black-color font-600 mb-1"><i class="fas fa-redo me-1"></i> {{language.portal_products_title}}</p>
                                             </a>

                                             {% if lineitem.sh_variants.size > 1 %}<p class="mb-0 black-color mb-1">{{ lineitem.shopify_variant_title }}</p>{% endif %}

                                             <p class="mb-0 black-color mb-1">{{language.protal_products_price}}: <span class="font-600"> {{ lineitem.currency_symbol }}
                                               {% if membership.currency_code == "JPY" %}<span>{{ lineitem.price | amount_no_decimals }} </span>
                                               {% else %}<span>{{ lineitem.price }}</span>{% endif %}
                                               </span>
                                             </p>
                                           </div>
                                         </div>
                                       </div>
                                     </div>
                                   {% endfor %}
                                   <div class="membership_product_subtotal d-flex align-items-center justify-content-between border-top mt-5 pt-3">
                                     <h5 class="black-color font-400 mb-0">{{language.portal_products_subtotal}}</h5>
                                     <h4 class="black-color font-700 mb-0">{{membership.delivery_price_currency_symbol}}
                                       {% if membership.currency_code == "JPY" %}<span class="sub-total-price"></span>
                                       {% else %}<span class="sub-total-price"></span>{% endif %}
                                     </h4>
                                   </div>
                                 </div>
                               </div>

                                {% comment %}
                                ###########################################
                                    UPCOMING MEMBERSHIP PAYMENT DETAILS
                                    Only show if the membership has a renewal coming up
                                ###########################################
                                {% endcomment %}
                               {% unless membership.is_onetime_payment %}
                               <div class="simplee-portal__order membership_order membership_order_next bg-white border-radius  mb-0" id="yourorder">
                                 <div class="membership_product_heading box_spacing mb-0 border-bottom d-flex align-items-center justify-content-between">
                                   <h2 class="black-color font-700 mb-0">{{language.portal_order_title}}</h2>

                                   {% if membership.shipping_address1 or membership.shipping_address2 and membership.shipping_fullname and membership.status != "cancelled" %}
                                     <div class="membership_edit">
                                       <a href="javascript:void(0)" id="simplee_order_edit_icon" class="d-flex align-items-center justify-content-center"><i class="fas fa-pen"></i></a>
                                     </div>
                                   {% endif %}
                                 </div>

                                   <div class="membership_products box_spacing" id="simplee_order_info">
                                     {% if membership.status != "cancelled" %}
                                       <ul>
                                        {% if membership.status_display == \'Active - Expiring\' %}
                                            <li class="mb-4">
                                                <p>This membership will expire on {{ membership.next_processing_date }}</p>
                                            </li>
                                        {% else %}
                                          <li class="mb-4">
                                            <h5 class="black-color font-700 mb-1">{{language.portal_order_to}}</h5>
                                            <p class="black-color font-400 mb-0">{{membership.shipping_fullname}}</p>
                                            <p class="black-color font-400 mb-0">{{ customer.email }}
                                              {% if membership.shipping_phone and customer.email  %}<span>|</span> {{membership.shipping_phone}} {% endif %}
                                            </p>
                                          </li>
                                        {% endif %}
                                        {% if membership.shipping_fullname and membership.shipping_address1 or membership.shipping_address2 %}
                                         <li class="mb-4">
                                           <h5 class="black-color font-700 mb-1">{{language.portal_order_address}}</h5>
                                           <p class="black-color font-400 mb-0">{{membership.shipping_address1}}, {{ membership.shipping_city }} {{ membership.shipping_stateprovname }} {{ membership.shipping_postalzip }}</p>
                                         </li>
                                       {% endif %}
                                       {% if membership.shipping_fullname and membership.shipping_address1 or membership.shipping_address2 %}
                                         <li class="mb-4">
                                           <h5 class="black-color font-700 mb-1">{{language.portal_order_method}}</h5>
                                           <p class="black-color font-400 mb-0">{{membership.shipping_carrier}} <span class="font-500"> {{membership.shipping_presentmentTitle}} -  {{membership.delivery_price_currency_symbol}}</span><span class="shipping-price">{{ shippingPrice | round: 2}}</span></p>
                                         </li>
                                       {% endif %}



                                       {% unless membership.status_display == \'Active - Expiring\' %}
                                           <li class="mb-4">
                                             <h5 class="black-color font-700 mb-1">{{language.portal_order_frequency}}</h5>
                                             <p class="black-color font-400 mb-0">{{membership.delivery_interval_count}} {{membership.delivery_interval | pluralize : membership.delivery_interval_count}}</p>
                                           </li>
                                       {% endunless %}

                                         <li class="mb-0 d-flex align-items-end justify-content-between">
                                           <div class="membership_nex_order">
                                             <h5 class="black-color font-700 mb-1">{{language.portal_next_renewal}}</h5>
                                             <p class="black-color font-400 mb-0">{{membership.next_order_date}}</p>
                                           </div>
                                         </li>


                                     </ul>
                                     {% else %}
                                     <ul>
                                       <li class="black-color">
                                         {% if membership.isPast %}
                                           <span>Membership was cancelled</span>
                                         {% else %}
                                           <span>Membership will be cancelled on {{membership.next_processing_date}}</span>
                                         {% endif %}
                                       </li>
                                     </ul>
                                     {% endif %}
                                   </div>


                                 {% comment %}
                                 #######################################
                                    FORM TO EDIT SHIPPING INFORMATION
                                 #######################################
                                 {% endcomment %}
                                 <div class="membership_form box_spacing" id="simplee_edit_order_info">
                                     <form>
                                       <div class="row">
                                         <div class="col-md-6">
                                           <div class="mb-2">
                                             <label for="shipping_firstname" class="form-label font-700 black-color">{{language.portal_order_fname}}</label>
                                             <input type="text" class="form-control" id="shipping_firstname" value="{{ membership.shipping_firstname }}">
                                           </div>
                                         </div>
                                         <div class="col-md-6">
                                           <div class="mb-2">
                                             <label for="shipping_lastname" class="form-label font-700 black-color">{{language.portal_order_lname}}</label>
                                             <input type="text" class="form-control" id="shipping_lastname" value="{{ membership.shipping_lastname }}">
                                           </div>
                                         </div>
                                         <div class="col-md-12">
                                           <div class="mb-2">
                                             <label for="shipping_company" class="form-label font-700 black-color">{{language.portal_order_company}}</label>
                                             <input type="text" class="form-control" id="shipping_company" value="{{ membership.shipping_company }}">
                                           </div>
                                         </div>
                                         <div class="col-md-12">
                                           <div class="mb-2">
                                             <label for="shipping_address1" class="form-label font-700 black-color">{{language.portal_order_address1}}</label>
                                             <input type="text" class="form-control" id="shipping_address1" value="{{ membership.shipping_address1 }}">
                                           </div>
                                         </div>
                                         <div class="col-md-12">
                                           <div class="mb-2">
                                             <label for="Apartment" class="form-label font-700 black-color">{{language.portal_order_address2}}</label>
                                             <input type="text" class="form-control" id="shipping_address2" value="{{ membership.shipping_address2 }}">
                                           </div>
                                         </div>
                                         <div class="col-md-6">
                                           <div class="mb-2">
                                             <label for="shipping_city" class="form-label font-700 black-color">{{language.portal_order_city}}</label>
                                             <div class="membershipping_city">
                                               <input type="text" class="form-control ps-4" id="shipping_city" value="{{ membership.shipping_city }}">
                                             </div>
                                           </div>
                                         </div>
                                         <div class="col-md-6">
                                           <div class="mb-2">
                                             <label for="shipping_postalzip" class="form-label font-700 black-color">{{language.portal_order_zip}}</label>
                                             <input type="text" class="form-control" id="shipping_postalzip" required value="{{ membership.shipping_postalzip }}">
                                           </div>
                                         </div>
                                         <div class="col-md-6">
                                           <div class="mb-2">
                                             <label for="Zip" class="form-label font-700 black-color">{{language.portal_order_country}}</label>
                                             <select class="form-select" aria-label="Default select example" value="{{ membership.shipping_country }}" id="select_countries">
                                               {% for country in countries %}
                                                 <option value="{{ country[1] }}" {% if country[1] == membership.shipping_country %} selected {% endif %}>{{country[1]}}</option>
                                               {% endfor %}
                                             </select>
                                           </div>
                                         </div>
                                         <div class="col-md-6">
                                           <div class="mb-2">
                                             <label for="Zip" class="form-label font-700 black-color">{{language.portal_order_state}}</label>
                                             <select class="form-select" aria-label="Default select example" value="{{ shipping_stateprovname }}" id="select_states">
                                                {% for state in states %}
                                                 {% assign sindex =  forloop.index %}
                                                 <option value="{{ states[sindex] }}" data-province="{{sindex}}">{{states[sindex]}}</option>
                                                {% endfor %}
                                             </select>
                                           </div>
                                         </div>
                                       </div>
                                     </form>
                                     <div class="membership_btn d-flex align-items-center mt-3">
                                       <a href="javascript:void(0)" class="btn-primary bg-blue sm_save_data" data-save="edit_shipping_address">
                                         <i class="fa fa-spinner fa-spin sm_saving"></i>
                                         <span class="sm_save">{{language.portal_order_update}}</span></a>
                                       <a href="javascript:void(0)" class="blue-color ms-3" id="simplee_order_edit_cancel">{{language.portal_order_cancel}}</a>
                                     </div>
                                 </div>
                               </div>
                               {% endunless %}

                                {% comment %}
                                #######################################
                                    BILLING INFORMATION
                                #######################################
                                {% endcomment %}
                               {% unless membership.is_onetime_payment %}
                                 <div class="simplee-portal__order membership_order bg-white border-radius mb-0" id="billing">
                                   <div class="membership_product_heading box_spacing mb-0 border-bottom">
                                     <h2 class="black-color font-700 mb-0">{{language.portal_billing_title}}</h2>
                                   </div>
                                   <div class="membership_products box_spacing">
                                     <div class="membership_card_on_file mb-3">

                                             <button style="margin-bottom: 20px;" class="billing-acc accordion-button d-flex align-items-center justify-content-between collapsed p-0 rounded-0" type="button" data-toggle="collapse" data-target="#billingInfoInner" aria-expanded="false" aria-controls="billingInfoInner">
                                               {% if membership.payment_method == "credit_card" or membership.payment_method == "shop_pay" %}
                                                 <div class="membership_accordian_title">
                                                   <h5 class="black-color font-700 mb-1">{{language.portal_billing_card}}</h5>
                                                   <div class="black-color font-400 mb-0 billing-card">
                                                    <img src="{{ images.cards | pluralizeBrandImg : membership.cc_brand, membership.payment_method }}" class="me-1{% if membership.payment_method != "credit_card" %} shoppay_img{% endif %}">
                                                     <div class="white-space-wrap">
                                                       <span class="font-600">{{membership.cc_name}}</span>
                                                       {% if membership.payment_method == "credit_card" %}
                                                        <span> {{membership.cc_brand | pluralizeBrand}} {{language.portal_billing_ending}} {{membership.cc_lastDigits}}</span>
                                                       {% else %}
                                                        <span> Card {{language.portal_billing_ending}} {{membership.cc_lastDigits}}</span>
                                                      {% endif %}
                                                     </div>
                                                   </div>
                                                 </div>
                                               {% endif %}
                                               {% if membership.payment_method == "paypal" %}
                                               <div class="membership_accordian_title">
                                                 <p class="black-color font-400 mb-0">
                                                   <img src="{{ images.cards }}/paypal.png" class="me-1 img-paypal">
                                                   <span class="font-600">PayPal Account: </span> <span>{{membership.paypal_account}}</span>
                                                 </p>
                                               </div>
                                               {% endif %}
                                             </button>

                                           <div id="billingInfoInner">
                                             <div class="px-0">
                                                <p class="black-color font-600">{{language.portal_billing_instructions}}</p>
                                                <div class="membership_btn d-flex align-items-center">
                                                  {% if membership.billing_update_url == "" %}
                                                    <a href="javascript:void(0)" class="btn-primary bg-blue px-4 py-2 sm_save_data" data-save="updatePaymentDetailEmail">{{language.portal_billing_send}}</a>
                                                  {% else %}
                                                    <a href="{{ membership.billing_update_url }}" class="btn-primary bg-blue px-4 py-2" target="_blank">Update Billing Information</a>
                                                  {% endif %}
                                                </div>

                                             </div>
                                           </div>
                                         </div>

                                     <div class="suscription_summary">
                                       <h5 class="black-color font-700 mb-1">{{language.portal_billing_summary}}</h5>
                                       <table class="table">
                                         <tbody>
                                           <tr>
                                             <td class="p-0 border-0 black-color font-400">{{language.portal_billing_subtotal}}</td>
                                             <td class="p-0 border-0 black-color font-400 text-end">{{ membership.delivery_price_currency_symbol }}
                                               {% if membership.currency_code == "JPY" %}
                                                 <span class="summary-subtotal">{{ summarySubtotal | amount_no_decimals }}</span>
                                               {% else %}<span class="summary-subtotal">{{ summarySubtotal }}</span>{% endif %}

                                             </td>
                                           </tr>
                                           {% if membership.shipping_fullname and membership.shipping_address1 or membership.shipping_address2 %}
                                             <tr>
                                               <td class="p-0 border-0 black-color font-400">Shipping - {{membership.shipping_presentmentTitle}}</td>
                                               <td class="p-0 border-0 black-color font-400 text-end">
                                                 {{ membership.delivery_price_currency_symbol }}
                                                 {% if membership.currency_code == "JPY" %}<span class="shipping-price">{{ shippingPrice | amount_no_decimals }}</span>
                                                 {% else %}<span class="shipping-price">{{ shippingPrice | round: 2 }}</span>{% endif %}

                                               </td>
                                             </tr>
                                           {% endif %}
                                           <tr class="membership_total">
                                             <td class="p-0 border-0 black-color font-600 pt-2">{{language.portal_billing_total}}</td>
                                             <td class="p-0 border-0 black-color font-600 pt-2 text-end">
                                             <span class="font-600">{{membership.delivery_price_currency_symbol}}</span>
                                               {% if membership.currency_code == "JPY" %}<span class="current-total-price">{{ current_total_price | amount_no_decimals}}</span>
                                               {% else %}<span class="current-total-price">{{ current_total_price }}</span>{% endif %}
                                             </td>
                                           </tr>
                                         </tbody>
                                       </table>
                                     </div>
                                   </div>
                                 </div>
                               {% endunless %}

                                {% comment %}
                      #######################################
                                    MEMBERSHIP STATUS & CANCELLATION
                                #######################################
                                {% endcomment %}
                                {% if membership.status == "active" %}
                                 <div class="simplee-portal__important membership_important bg-white border-radius box_spacing d-md-flex align-items-center justify-content-between">
                                   <div class="membership_status mb-3 mb-md-0">
                                     <h5 class="black-color font-400 mb-1"> {{language.portal_general_status}}</h5>
                                     {% if membership.on_trial == 0 %}
                                        <h2 class="black-color font-700 mb-0">
                                        {% if membership.status_display == \'Active - Expiring\' %} {{ language.portal_status_display_expiring }}
                                        {% else %}  {{language.portal_general_active}}
                                        {% endif %}</h2>
                                     {% else %}
                                      <h2 class="black-color font-700 mb-0">On Trial (until {{membership.next_processing_date}})</h2>
                                    {% endif %}
                                   </div>
                                   <div class="membership_status_btn d-flex align-items-center justify-content-between">
                                     {% if membership.billing_min_cycles > 0 %}
                                       <div>
                                        {% if settings.portal_can_cancel and membership.billing_min_cycles > 0 and membership.order_count < membership.billing_min_cycles %}
                                           <a class="font-600">Minimum of {{membership.billing_min_cycles}} orders before cancelling</a>
                                        {% elsif settings.portal_can_cancel and membership.billing_min_cycles > 0 and membership.order_count >= membership.billing_min_cycles  and membership.is_onetime_payment != 1 and  membership.status_display != \'Active - Expiring\' %}
                                           <a href="javascript:void(0)" data-modal="cancel_membership" class="red-color font-600 open_modal">{{language.portal_action_cancel}}</a>
                                        {% endif %}
                                       </div>
                                     {% else %}
                                       <div>

                                        {% if settings.portal_can_cancel and membership.is_onetime_payment != 1  %}
                                           <a href="javascript:void(0)" data-modal="cancel_membership" class="red-color font-600 open_modal">{{language.portal_action_cancel}}</a>
                                        {% endif %}
                                       </div>
                                     {% endif %}
                                   </div>
                                </div>
                                {% elsif  membership.status == "cancelled" %}
                                <div class="simplee-portal__important membership_important bg-white border-radius box_spacing d-md-flex align-items-center justify-content-between">
                                   <div class="membership_status mb-3 mb-md-0">
                                     <h5 class="black-color font-400 mb-1"> {{language.portal_general_status}}</h5>
                                     <h2 class="black-color font-700 mb-0">Cancelled</h2>
                                     </div>
                                      <div class="membership_status_btn d-flex align-items-center justify-content-between">
                                          <div class="fixdiv">
                                     <a href="javascript:void(0)" data-modal="resume_membership" class="green-color font-600 open_modal">{{language.portal_action_resume}}</a>
                                    </div>

                                    </div>
                                </div>
                               {% endif %}

                             </div>
                         </div>
                       </div>
                     </div>
                   {% else %}
                    {% comment %}
                    #######################################
                        MESSAGE IF NO MEMBERSHIPS FOUND
                    #######################################
                    {% endcomment %}
                     <div class="simplee-portal__verify">
                       <div>{{language.portal_no_membership}}</div>
                     </div>
                   {% endif %}

                   {% comment %}
                   ##############################################
                      ADDITIONAL DIVS FOR MODALS AND MESSAGES
                   ##############################################
                   {% endcomment %}

                   {% if membership.size > 0 %}
                     <!-- Message Toast -->
                     <div id="snackbar">
                       <div class="snackbar_inner"></div>
                     </div>

                     <!-- modal -->
                    <div id="simplee-portal__modal" class="simplee-portal__modal overlay">
                        <div class="popup membership_cancel p-4">
                            <div class="membership_cancel_popup">
                                <h2 class="black-color font-700" id="sm_modal_title"></h2>
                                {% if settings.cancellation_reason_enable == 1 and membership.status == "active" %}
                                    <div class="text-end mt-6">
                                    <label style="text-align:left">{{settings.custom_reason_message}}</label>
                                    </div>
                                    <form id="cancellation_reasons_form" class="form-group" style="padding-top:10px">
                                    <div id="error_message" style="color: red; display: none;"><b>Please check any radio button before submitting.</b></div>
                                    <input type="hidden" name="required_reason" value="{{settings.required_reason}}" id="required_reason" />
                                        {% for reason in reasons %}
                                            {% if reason.is_enabled == 1 %}
                                                <div class="form-check">
                                                    <input type="radio" class="form-check-input" name="cancellation_reason" id="reason_{{ forloop.index }}" value="{{ reason.reason }}">
                                                    <label class="form-check-label" for="reason_{{ forloop.index }}">
                                                        {{ reason.reason }}
                                                    </label>
                                                </div>
                                            {% endif %}
                                        {% endfor %}
                                        {% if settings.cancellation_reason_enable_custom == 1 %}
                                            <div class="form-check">
                                                <input type="radio" class="form-check-input"  name="cancellation_reason" id="cancellation_other_reason" value="other">
                                                <label class="form-check-label" for="cancellation_other_reason">
                                                    {{ settings.custom_options }}
                                                </label>
                                            </div>
                                            <div id="custom_reason_textbox" class="mt-2" style="display: none;padding-top:5px">
                                                <label for="custom_reason"><b>Please Specify Reason :</b></label>
                                                <input type="text" class="form-control" id="custom_reason" name="custom_reason" placeholder="Your custom reason"  maxlength="255">
                                                <div id="custom_msg" style="color: red; display: none;"><b>Please add a custom reason before submitting.</b></div>
                                            </div>
                                            </br>
                                        {% endif %}
                                    </form>
                                {% endif %}
                                <p class="black-color font-400 mb-0" id="sm_modal_label"></p>
                            </div>
                            <div class="text-end mt-4">
                                <a class="btn-return ms-3 font-600 close_modal" href="javascript:void(0)" id="sm_close_modal"></a>
                                <a class="close btn-primary ms-3 font-600" href="javascript:void(0)">
                                <i class="fa fa-spinner fa-spin sm_saving"></i>
                                <span class="sm_save sm_save_data" id="sm_modal_save" data-save=""></span>
                                </a>
                            </div>
                        </div>
                    </div>
                   {% endif %}
                    {% comment %}
                      FINISH ADDITIONAL DIVS
                    {% endcomment %}
               </div>
            </div>
         </div>';

 return $portalLiquid;
    }
}

if (!function_exists('getPortalCssH')) {
    function getPortalCssH()
    {
        $path = resource_path('css/portal-app-v2.scss');
        $portalCSS  = file_get_contents($path);
        return $portalCSS;
    }
}

if (!function_exists('getPoratlJsH')) {
    function getPoratlJsH()
    {
        $path = resource_path('js/liquid-portal.js');
        $portalJS  = file_get_contents($path);
        return $portalJS;
    }
}

?>
