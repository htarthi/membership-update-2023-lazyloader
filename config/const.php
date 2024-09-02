<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Global constants
    |--------------------------------------------------------------------------
    */

    'HMAC_KEY'  => 'MRi-s7dL32W5neMvbVnp7rP5MAwS2AC6eHholWVu',

    'gtm_tag' => env('GTM_TAG', 'GTM-TGMRHPM'),

    'PREVENT_NUMBER_FOR_MEMBER' => [
        'keungshowhkfanclub.myshopify.com' => [99, 430, 99430],
    ],

    'email_categories' => [
        'failed_payment_to_customer' => [
            'subject' => ' - Your membership payment failed',
            'html_body' => getPaymentFailedMailHtml(),
            'days_ahead' => null
        ],
        'new_membership_to_customer' => [
            'subject' => ' - Your membership has begun!',
            'html_body' => getNewSubscriptioMailHtml(),
            'days_ahead' => null
        ],
        'cancelled_membership' => [
            'subject' => ' - Your membership will be cancelled!',
            'html_body' => getCancelMembershipMailHtml(),
            'days_ahead' => null
        ],
        'recurring_notify' => [
            'subject' => ' - Your membership will be recurring!',
            'html_body' => getRecurringMailHtml(),
            'days_ahead' => 3
        ],
    ],

    'THEME' => [
        'EXPRESS' => [
            '1_9_1' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<a href="/tools/memberships" class="button simplee_express_msl">My Memberships</a>',
                'ACCOUNT_PAGE_PLACE' => '{{ customer.default_address | format_address }}',
                'PRODUCT_PAGE_PLACE' => '<div class="product-form__buttons">',
                'PRODUCT_FILE' => 'snippets/product-form.liquid',
                'PRICE_SALE' => '.price-item--regular',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FILES_ACCOUNT' => 'templates/customers/account.liquid',
            ],
            '*' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<a href="/tools/memberships" class="button simplee_express_msl">My Memberships</a>',
                'ACCOUNT_PAGE_PLACE' => '{{ customer.default_address | format_address }}',
                'PRODUCT_PAGE_PLACE' => '<div class="product-form__buttons">',
                'PRODUCT_FILE' => 'snippets/product-form.liquid',
                'PRICE_SALE' => '.price-item--regular',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FILES_ACCOUNT' => 'templates/customers/account.liquid',
            ],
        ],
        'MINIMAL' => [
            '12_2_0' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<a href="/tools/memberships" class="simplee_msl_box">My Memberships</a>',
                'ACCOUNT_PAGE_PLACE' => '{{ customer.default_address | format_address }}',
                'PRODUCT_PAGE_PLACE' => '<button type="submit" name="add" id="AddToCart"',
                'PRODUCT_FILE' => 'sections/product-template.liquid',
                'PRICE_SALE' => '.product-single__price',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FILES_ACCOUNT' => 'templates/customers/account.liquid',
            ],
            '*' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<a href="/tools/memberships" class="simplee_msl_box">My Memberships</a>',
                'ACCOUNT_PAGE_PLACE' => '{{ customer.default_address | format_address }}',
                'PRODUCT_PAGE_PLACE' => '<button type="submit" name="add" id="AddToCart"',
                'PRODUCT_FILE' => 'sections/product-template.liquid',
                'PRICE_SALE' => '.product-single__price',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FILES_ACCOUNT' => 'templates/customers/account.liquid',
            ],
        ],
        'BROOKLYN' => [
            '17_2_0' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<a href="/tools/memberships" class="text-link simplee_msl_box">My Memberships</a>',
                'ACCOUNT_PAGE_PLACE' => '{{ customer.default_address | format_address }}',
                'PRODUCT_PAGE_PLACE' => '<div class="product-single__add-to-cart',
                'PRODUCT_FILE' => 'sections/product-template.liquid',
                'PRICE_SALE' => '.product-single__price',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FILES_ACCOUNT' => 'templates/customers/account.liquid',
            ],
            '*' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<a href="/tools/memberships" class="text-link simplee_msl_box">My Memberships</a>',
                'ACCOUNT_PAGE_PLACE' => '{{ customer.default_address | format_address }}',
                'PRODUCT_PAGE_PLACE' => '<div class="product-single__add-to-cart',
                'PRODUCT_FILE' => 'sections/product-template.liquid',
                'PRICE_SALE' => '.product-single__price',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FILES_ACCOUNT' => 'templates/customers/account.liquid',
            ],
        ],
        'NARRATIVE' => [
            '10_2_1' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<a href="/tools/memberships" class="btn-link simplee_msl_box">My Memberships</a>',
                'ACCOUNT_PAGE_PLACE' => '{{ customer.default_address | format_address }}',
                'PRODUCT_PAGE_PLACE' => '<button class="btn btn--to-secondary btn--full product__add-to-cart-button',
                'PRODUCT_FILE' => 'snippets/product-form.liquid',
                'PRICE_SALE' => '.product__current-price',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FILES_ACCOUNT' => 'templates/customers/account.liquid',
            ],
            '*' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<a href="/tools/memberships" class="btn-link simplee_msl_box">My Memberships</a>',
                'ACCOUNT_PAGE_PLACE' => '{{ customer.default_address | format_address }}',
                'PRODUCT_PAGE_PLACE' => '<button class="btn btn--to-secondary btn--full product__add-to-cart-button',
                'PRODUCT_FILE' => 'snippets/product-form.liquid',
                'PRICE_SALE' => '.product__current-price',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FILES_ACCOUNT' => 'templates/customers/account.liquid',
            ],
        ],
        'SUPPLY' => [
            '10_2_1' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<a href="/tools/memberships" class="simplee_msl_box">My Memberships</a>',
                'ACCOUNT_PAGE_PLACE' => '<p><a href="{{ routes.account_addresses_url }}">{{ \'customer.account.view_addresses\' | t }} ({{ customer.addresses_count }})</a></p>',
                'PRODUCT_PAGE_PLACE' => '<div class="payment-buttons payment-buttons--{{ section.settings.add_to_cart_button_size }}">',
                'PRODUCT_FILE' => 'sections/product-template.liquid',
                'PRICE_SALE' => '#productPrice-product-template',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FILES_ACCOUNT' => 'templates/customers/account.liquid',
            ],
            '*' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<a href="/tools/memberships" class="simplee_msl_box">My Memberships</a>',
                'ACCOUNT_PAGE_PLACE' => '<p><a href="{{ routes.account_addresses_url }}">{{ \'customer.account.view_addresses\' | t }} ({{ customer.addresses_count }})</a></p>',
                'PRODUCT_PAGE_PLACE' => '<div class="payment-buttons payment-buttons--{{ section.settings.add_to_cart_button_size }}">',
                'PRODUCT_FILE' => 'sections/product-template.liquid',
                'PRICE_SALE' => '#productPrice-product-template',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FILES_ACCOUNT' => 'templates/customers/account.liquid',
            ],
        ],
        'VENTURE' => [
            '12_2_0' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<a href="/tools/memberships" class="simplee_msl_box">My Memberships</a>',
                'ACCOUNT_PAGE_PLACE' => '<p><a href="{{ routes.account_addresses_url }}">{{ \'customer.account.view_addresses\' | t }} ({{ customer.addresses_count }})</a></p>',
                'PRODUCT_PAGE_PLACE' => '<div class="product-form__item product-form__item--submit">',
                'PRODUCT_FILE' => 'sections/product-template.liquid',
                'PRICE_SALE' => '.product-single__price',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FILES_ACCOUNT' => 'templates/customers/account.liquid',
            ],
            '*' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<a href="/tools/memberships" class="simplee_msl_box">My Memberships</a>',
                'ACCOUNT_PAGE_PLACE' => '<p><a href="{{ routes.account_addresses_url }}">{{ \'customer.account.view_addresses\' | t }} ({{ customer.addresses_count }})</a></p>',
                'PRODUCT_PAGE_PLACE' => '<div class="product-form__item product-form__item--submit">',
                'PRODUCT_FILE' => 'sections/product-template.liquid',
                'PRICE_SALE' => '.product-single__price',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FILES_ACCOUNT' => 'templates/customers/account.liquid',
            ],
        ],
        'DEBUT' => [
            '17_8_0' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships" class="simplee_msl_box">My Memberships</a>',
                'ACCOUNT_PAGE_PLACE' => '{{ \'layout.customer.log_out\' | t | customer_logout_link }}',
                'PRODUCT_PAGE_PLACE' => '<div class="product-form__error-message-wrapper product-form__error-message-wrapper--hidden',
                'PRODUCT_FILE' => 'sections/product-template.liquid',
                'PRICE_SALE' => '.price-item',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FILES_ACCOUNT' => 'templates/customers/account.liquid',
            ],
            '*' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships" class="simplee_msl_box">My Memberships</a>',
                'ACCOUNT_PAGE_PLACE' => '{{ \'layout.customer.log_out\' | t | customer_logout_link }}',
                'PRODUCT_PAGE_PLACE' => '<div class="product-form__error-message-wrapper product-form__error-message-wrapper--hidden',
                'PRODUCT_FILE' => 'sections/product-template.liquid',
                'PRICE_SALE' => '.price-item',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FILES_ACCOUNT' => 'templates/customers/account.liquid',
            ],
        ],
        'SIMPLE' => [
            '12_5_1' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<a href="/tools/memberships" class="simplee_msl_box">My Memberships</a>',
                'ACCOUNT_PAGE_PLACE' => '</h1>',
                'PRODUCT_PAGE_PLACE' => '<div class="product-single__cart-submit-wrapper',
                'PRODUCT_FILE' => 'sections/product-template.liquid',
                'PRICE_SALE' => '.price-item',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FILES_ACCOUNT' => 'templates/customers/account.liquid',
            ],
            '*' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<a href="/tools/memberships" class="simplee_msl_box">My Memberships</a>',
                'ACCOUNT_PAGE_PLACE' => '</h1>',
                'PRODUCT_PAGE_PLACE' => '<div class="product-single__cart-submit-wrapper',
                'PRODUCT_FILE' => 'sections/product-template.liquid',
                'PRICE_SALE' => '.product-single__price',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FILES_ACCOUNT' => 'templates/customers/account.liquid',
            ],
        ],
        'BOUNDLESS' => [
            '10_2_3' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<a href="/tools/memberships" class="simplee_msl_box">My Memberships</a>',
                'ACCOUNT_PAGE_PLACE' => '<p><a href="{{ routes.account_addresses_url }}" class="btn btn--small">{{ \'customer.account.view_addresses\' | t }} ({{ customer.addresses_count }})</a></p>',
                'PRODUCT_PAGE_PLACE' => '{% if product.available %}',
                'PRODUCT_FILE' => 'sections/product-template.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FILES_ACCOUNT' => 'templates/customers/account.liquid',
            ],
            '*' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<a href="/tools/memberships" class="simplee_msl_box">My Memberships</a>',
                'ACCOUNT_PAGE_PLACE' => '<p><a href="{{ routes.account_addresses_url }}" class="btn btn--small">{{ \'customer.account.view_addresses\' | t }} ({{ customer.addresses_count }})</a></p>',
                'PRODUCT_PAGE_PLACE' => '{% if product.available %}',
                'PRODUCT_FILE' => 'sections/product-template.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FILES_ACCOUNT' => 'templates/customers/account.liquid',
            ],
        ],
        'DAWN' => [
            '1_1_0' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '<product-form class="product-form">',
                'PRODUCT_FILE' => 'sections/main-product.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'templates/customers/account.liquid',
            ],
            '7_0_1' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '<product-form class="product-form">',
                'PRODUCT_FILE' => 'sections/main-product.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'sections/main-account.liquid',
            ],
            '8_0_0' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '<product-form class="product-form">',
                'PRODUCT_FILE' => 'snippets/buy-buttons.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'sections/main-account.liquid',
            ],
            '8_0_1' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '<product-form class="product-form">',
                'PRODUCT_FILE' => 'snippets/buy-buttons.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'sections/main-account.liquid',
            ],
            '9_0_0' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '{%- if product != blank -%}',
                'PRODUCT_FILE' => 'snippets/buy-buttons.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'sections/main-account.liquid',
            ],
            '*' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '{%- if product != blank -%}',
                'PRODUCT_FILE' => 'snippets/buy-buttons.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'sections/main-account.liquid',
            ],
    //         '*' => [
    //             'CART_FIND' => 'selling_plan.name',
    //             'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
    //               <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
    //                 <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
    //               </svg>
    //               My Membership
    //             </a>',
    //             'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
    //   <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
    //     <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
    //   </svg>
    //   {{ \'customer.log_out\' | t }}
    // </a>',
    //             'PRODUCT_PAGE_PLACE' => '<product-form class="product-form">',
    //             'PRODUCT_FILE' => 'sections/main-product.liquid',
    //             'PRICE_SALE' => '.product__price--reg',
    //             'PRICE_BADGE_SALE' => '.data-subscription-badge',
    //             'LIQUID_PLACE' => '{%- liquid',
    //             'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
    //           <div {{ block.shopify_attributes }}>',
    //             'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
    //             'FILES_ACCOUNT' => 'templates/customers/account.liquid',
    //         ],
        ],
        'CRAFT' => [
            '1_0_0' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '<product-form class="product-form">',
                'PRODUCT_FILE' => 'sections/main-product.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'templates/customers/account.liquid',
            ],

            '5_0_1' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '<product-form class="product-form">',
                'PRODUCT_FILE' => 'sections/main-product.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'sections/main-account.liquid',
            ],
            '*' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '<product-form class="product-form">',
                'PRODUCT_FILE' => 'sections/main-product.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'templates/customers/account.liquid',
            ],
        ],
         'CRAVE' => [
            '1_1_0' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '<product-form class="product-form">',
                'PRODUCT_FILE' => 'sections/main-product.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'templates/customers/account.liquid',
            ],
            '5_0_1' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '<product-form class="product-form">',
                'PRODUCT_FILE' => 'sections/main-product.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'sections/main-account.liquid',
            ],
            '*' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '<product-form class="product-form">',
                'PRODUCT_FILE' => 'sections/main-product.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'templates/customers/account.liquid',
            ],
        ],
         'SENSE' => [
            '1_1_0' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '<product-form class="product-form">',
                'PRODUCT_FILE' => 'sections/main-product.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'templates/customers/account.liquid',
            ],
            '5_0_1' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '<product-form class="product-form">',
                'PRODUCT_FILE' => 'sections/main-product.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'sections/main-account.liquid',
            ],
            '*' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '<product-form class="product-form">',
                'PRODUCT_FILE' => 'sections/main-product.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'templates/customers/account.liquid',
            ],
        ],
        'TASTE' => [
            '1_0_0' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '<product-form class="product-form">',
                'PRODUCT_FILE' => 'sections/main-product.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'templates/customers/account.liquid',
            ],
            '4_0_1' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '<product-form class="product-form">',
                'PRODUCT_FILE' => 'sections/main-product.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'sections/main-account.liquid',
            ],
            '*' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '<product-form class="product-form">',
                'PRODUCT_FILE' => 'sections/main-product.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'templates/customers/account.liquid',
            ],
        ],
        'STUDIO' => [
            '1_0_0' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '<product-form class="product-form">',
                'PRODUCT_FILE' => 'sections/main-product.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'templates/customers/account.liquid',
            ],
            '4_0_1' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '<product-form class="product-form">',
                'PRODUCT_FILE' => 'sections/main-product.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'sections/main-account.liquid',
            ],
            '*' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '<product-form class="product-form">',
                'PRODUCT_FILE' => 'sections/main-product.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'templates/customers/account.liquid',
            ],
        ],
        'REFRESH' => [
          '2_0_1' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '<product-form class="product-form">',
                'PRODUCT_FILE' => 'sections/main-product.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'sections/main-account.liquid',
            ],
            '*' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '<product-form class="product-form">',
                'PRODUCT_FILE' => 'sections/main-product.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'sections/main-account.liquid',
            ],
        ],
        'ORIGIN' => [
          '1_0_1' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '<product-form class="product-form">',
                'PRODUCT_FILE' => 'sections/main-product.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'sections/main-account.liquid',
            ],
          '9_0_0' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '{%- if product != blank -%}',
                'PRODUCT_FILE' => 'snippets/buy-buttons.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'sections/main-account.liquid',
            ],
            '*' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '{%- if product != blank -%}',
                'PRODUCT_FILE' => 'snippets/buy-buttons.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'sections/main-account.liquid',
            ],
        ],
        'PUBLISHER' => [
          '1_0_1' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '<product-form class="product-form">',
                'PRODUCT_FILE' => 'sections/main-product.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'sections/main-account.liquid',
            ],
          '9_0_0' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '{%- if product != blank -%}',
                'PRODUCT_FILE' => 'snippets/buy-buttons.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'sections/main-account.liquid',
            ],
            '*' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '{%- if product != blank -%}',
                'PRODUCT_FILE' => 'snippets/buy-buttons.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'sections/main-account.liquid',
            ],
        ],
        'RIDE' => [
          '3_0_0' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '<product-form class="product-form">',
                'PRODUCT_FILE' => 'sections/main-product.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'sections/main-account.liquid',
            ],
            '*' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '<product-form class="product-form">',
                'PRODUCT_FILE' => 'sections/main-product.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'sections/main-account.liquid',
            ],
        ],
        'COLORBLOCK' => [
          '3_0_1' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '<product-form class="product-form">',
                'PRODUCT_FILE' => 'sections/main-product.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'sections/main-account.liquid',
            ],
            '*' => [
                'CART_FIND' => 'selling_plan.name',
                'ACCOUNT_PAGE_URL' => '<br><a href="/tools/memberships">
                  <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
                  </svg>
                  My Membership
                </a>',
                'ACCOUNT_PAGE_PLACE' => '<a href="{{ routes.account_logout_url }}">
      <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 18 19">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M6 4.5a3 3 0 116 0 3 3 0 01-6 0zm3-4a4 4 0 100 8 4 4 0 000-8zm5.58 12.15c1.12.82 1.83 2.24 1.91 4.85H1.51c.08-2.6.79-4.03 1.9-4.85C4.66 11.75 6.5 11.5 9 11.5s4.35.26 5.58 1.15zM9 10.5c-2.5 0-4.65.24-6.17 1.35C1.27 12.98.5 14.93.5 18v.5h17V18c0-3.07-.77-5.02-2.33-6.15-1.52-1.1-3.67-1.35-6.17-1.35z" fill="currentColor">
      </svg>
      {{ \'customer.log_out\' | t }}
    </a>',
                'PRODUCT_PAGE_PLACE' => '<product-form class="product-form">',
                'PRODUCT_FILE' => 'sections/main-product.liquid',
                'PRICE_SALE' => '.product__price--reg',
                'PRICE_BADGE_SALE' => '.data-subscription-badge',
                'LIQUID_PLACE' => '{%- liquid',
                'FEATURE_PRODUCT_PLACE' => '            {%- when \'buy_buttons\' -%}
              <div {{ block.shopify_attributes }}>',
                'FEATURE_PRODUCT_DATA' => '{% render \'simplee-widget\', simplee_id: section.id, product:product  %}',
                'FILES_ACCOUNT' => 'sections/main-account.liquid',
            ],
        ],
        '*' => [
            'CART_FIND' => 'selling_plan.name',
            'ACCOUNT_PAGE_URL' => '<a href="/tools/memberships" class="simplee_msl_box">My Memberships</a>',
            'ACCOUNT_PAGE_PLACE' => '{{ customer.default_address | format_address }}',
            'PRODUCT_PAGE_PLACE' => '<div class="product-form__error-message-wrapper product-form__error-message-wrapper--hidden',
            'PRODUCT_FILE' => 'sections/product-template.liquid',
            'PRICE_SALE' => '.price-item--sale',
            'PRICE_BADGE_SALE' => '.price__badge--sale',
            'PRICE_FILE' => 'snippets/product-price.liquid',
            'PRICE_SALE' => '.product__price--reg',
            'PRICE_BADGE_SALE' => '.data-subscription-badge',
            'LIQUID_PLACE' => '{%- liquid',
            'FILES_ACCOUNT' => 'templates/customers/account.liquid',
        ]
    ],

    'FILES' => [
        'PRODUCT' => 'sections/product-template.liquid',
        'CART' => 'templates/cart.liquid',
        'ACCOUNT' => 'templates/customers/account.liquid',
        'THEME' => 'layout/theme.liquid'
    ],

    'SNIPPETS' => [
        'SIMPLEE' => 'simplee',
        'SIMPLEE_MEMBERSHIP' => 'simplee-memberships',
        'SIMPLEE_WIDGET' => 'simplee-widget',
        'CART' => 'simplee-cart',
    ],

    'SECTIONS' => [
        'JSON_THEME_FEATURE_PRODUCT' => 'sections/featured-product.liquid',
    ],

    'JSON_THEMES' => [
        'Dawn', 'Craft', 'Sense', 'Crave', 'Taste', 'Studio', 'Colorblock', 'Ride', 'Refresh', 'Origin', 'Publisher'
    ],

    'ASSETS' => [
        'CSS' => 'simplee',
        'JS' => 'simplee',
    ],

    'SHOPIFY_FLOW' => [
        'NEW_MEMBERSHIP' => env('SHOPIFY_FLOW_NEW_MEMBERSHIP_TRIGGER_ID','4119726f-344b-45f0-9c4e-11273717b525'),
        'MEMBERSHIP_CANCEL' => env('SHOPIFY_FLOW_MEMBERSHIP_CANCEL_TRIGGER_ID','5bb7085a-137d-4e53-bcc8-ff3b4624cc45'),
        'PAYMENT_FAIL' => env('SHOPIFY_FLOW_PAYMENT_FAIL_TRIGGER_ID','0e2a9e99-8908-41bd-9802-247ab4c165b7'),
        'PAYMENT_SUCCESS' => env('SHOPIFY_FLOW_PAYMENT_SUCCESS_TRIGGER_ID','29d49a1b-daa3-43c5-aac2-7d8d83a1a021'),
    ]

];
