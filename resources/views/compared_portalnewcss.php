html {
    scroll-behavior: smooth;
}
#portal {
    font-family: 'Open Sans', sans-serif;
    margin: 0;
    line-height: 1.5;
    height: auto;
    scroll-behavior: smooth;
    background-color: #f5f5f5;
    padding: 30px 0;
}

#portal button,
#portal input,
#portal textarea,
#portal select,
#portal .disclosure__toggle {
    font-family: 'Open Sans', sans-serif;
}

#portal .container {
    max-width: 1320px;
    width: 100%;
    padding-right: 0.75rem;
    padding-left: 0.75rem;
    margin-right: auto;
    margin-left: auto;
}

#portal .row {
    --bs-gutter-x: 24px;
    --bs-gutter-y: 0;
    display: flex;
    flex-wrap: wrap;
    margin-right: calc(var(--bs-gutter-x)/ -2);
    margin-left: calc(var(--bs-gutter-x)/ -2);
}

#portal .row>* {
    padding-right: calc(var(--bs-gutter-x)/ 2);
    padding-left: calc(var(--bs-gutter-x)/ 2);
    margin-top: var(--bs-gutter-y);
    width: 100%;
}

#portal h1,
#portal h2,
#portal h3,
#portal h4,
#portal h5,
#portal h6,
#portal p {
    letter-spacing: initial;
    text-transform: unset;
    font-family: 'Open Sans', sans-serif;
}

#portal .simplee-portal__wrapper .d-flex {
    display: flex;
}

#portal .simplee-portal__wrapper .align-items-center {
    align-items: center;
}

#portal .align-items-end {
    align-items: flex-end;
}

#portal .simplee-portal__wrapper .justify-content-between {
    justify-content: space-between;
}

#portal .justify-content-center {
    justify-content: center;
}

#portal .simplee-portal__wrapper .mb-0 {
    margin-bottom: 0;
}

#portal .simplee-portal__wrapper .mt-0 {
    margin-top: 0;
}

#portal .simplee-portal__wrapper .mb-1 {
    margin-bottom: .25rem;
}

#portal .simplee-portal__wrapper .me-1 {
    margin-right: .25rem;
}

#portal .simplee-portal__wrapper .mb-2 {
    margin-bottom: .5rem;
}

#portal .simplee-portal__wrapper .mb-3 {
    margin-bottom: 16px;
}

#portal .simplee-portal__wrapper .me-3 {
    margin-right: 16px;
}

#portal .simplee-portal__wrapper .mt-3 {
    margin-top: 16px;
}

#portal .simplee-portal__wrapper .ms-3 {
    margin-left: 16px;
}

#portal .simplee-portal__wrapper .ms-4 {
    margin-left: 24px;
}

#portal .simplee-portal__wrapper .mb-4 {
    margin-bottom: 24px;
}

#portal .simplee-portal__wrapper .mt-4 {
    margin-top: 24px;
}

#portal .simplee-portal__wrapper .mt-5 {
    margin-top: 3rem;
}

#portal .simplee-portal__wrapper .mb-5 {
    margin-bottom: 3rem;
}

#portal .simplee-portal__wrapper .pt-3 {
    padding-top: 16px;
}

#portal .simplee-portal__wrapper .pt-2 {
    padding-top: .5rem;
}

#portal .simplee-portal__wrapper .pe-4 {
    padding-right: 24px;
}

#portal .simplee-portal__wrapper .py-4 {
    padding-top: 24px;
    padding-bottom: 24px;
}

#portal .simplee-portal__wrapper .pb-5 {
    padding-bottom: 3rem;
}

#portal .simplee-portal__wrapper .ps-3 {
    padding-left: 16px;
}

#portal .simplee-portal__wrapper .p-4 {
    padding: 24px;
}

#portal .simplee-portal__wrapper .py-1 {
    padding-top: .25rem;
    padding-bottom: .25rem;
}

#portal .simplee-portal__wrapper .px-1 {
    padding-right: .25rem;
    padding-left: .25rem;
}

#portal .simplee-portal__wrapper .d-none {
    display: none;
}

#portal .simplee-portal__wrapper .d-block {
    display: block;
}

#portal .simplee-portal__wrapper .d-inline-block {
    display: inline-block;
}

#portal .simplee-portal__wrapper .border-top {
    border-top: 1px solid #dee2e6;
}

#portal .simplee-portal__wrapper .border-bottom {
    border-bottom: 1px solid #dee2e6;
}

#portal .simplee-portal__wrapper .form-select-sm {
    padding: 4px 36px 4px 8px;
    font-size: .875rem;
    font-weight: 400;
    color: #212529;
    background-color: #fff;
    border: 1px solid #ced4da;
    border-radius: .25rem;
}

#portal .simplee-portal__wrapper .w-auto {
    width: auto;
}

#portal .simplee-portal__wrapper img,
#portal .simplee-portal__wrapper svg {
    vertical-align: middle;
}

#portal .accordion-button {
    position: relative;
    width: 100%;
    font-size: 16px;
    color: #212529;
    text-align: left;
    background-color: #fff;
    border: 0;
    padding: 0;
    border-radius: 0;
    overflow-anchor: none;
    transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out, border-radius .15s ease;
}

#portal .table {
    width: 100%;
    caption-side: bottom;
    border-collapse: collapse;
    margin-bottom: 16px;
}

#portal .simplee-portal__wrapper .text-end {
    text-align: right;
}

#portal [type=button]:not(:disabled),
#portal [type=reset]:not(:disabled),
#portal [type=submit]:not(:disabled),
#portal button:not(:disabled) {
    cursor: pointer;
}

#portal .simplee-portal__wrapper .btn {
    line-height: 1.5;
}

#portal .simplee-portal__wrapper .fade:not(.show) {
    opacity: 0;
}

#portal .modal {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1060;
    display: none;
    width: 100%;
    height: 100%;
    overflow: hidden;
    outline: 0;
}

#portal .modal-open .modal {
    overflow-x: hidden;
    overflow-y: auto;
}

#portal .modal.fade .modal-dialog {
    transition: transform .3s ease-out;
}

#portal .modal-dialog {
    position: relative;
    width: auto;
    margin: .5rem;
    pointer-events: none;
}

#portal .modal-content {
    position: relative;
    display: flex;
    flex-direction: column;
    width: 100%;
    pointer-events: auto;
    background-color: #fff;
    background-clip: padding-box;
    border-radius: 10px;
    outline: 0;
}

#portal .modal-backdrop.fade {
    opacity: 0;
}

#portal .modal-backdrop.show {
    opacity: .5;
}

#portal .modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1040;
    width: 100vw;
    height: 100vh;
    background-color: #000;
}

#portal .fade {
    transition: opacity .15s linear;
}

#portal .membership_products label {
    font-size: 16px;
}


/* variable css */

#portal .font-300 {
    font-weight: 300;
}

#portal .font-400 {
    font-weight: 400;
}

#portal .font-600 {
    font-weight: 600;
}

#portal .font-700 {
    font-weight: 700;
}
#portal .blue-color {
    color: #1a76e6;
    cursor: pointer;
}

#portal .black-color {
    color: #000;
}

#portal .red-color {
    color: #f71111;
}

#portal .green-color {
    color: #166612;
}

#portal .grey-color {
    color: #818080;
}

#portal .bg-white {
    background-color: #fff;
}

#portal .bg-blue {
    background-color: #1a76e6;
}

#portal h1 {
    font-size: 26px;
}

#portal h2 {
    font-size: 22px;
}

#portal h4 {
    font-size: 16px;
}

#portal h5 {
    font-size: 14px;
}

#portal p,
#portal span {
    font-size: 14px;
    margin-top: 0;
}

#portal ul {
    padding: 0;
    margin: 0;
}

#portal li {
    list-style: none;
}

#portal a {
    text-decoration: none;
}

#portal .box_spacing {
    padding: 16px 39px;
    margin-bottom: 30px;
}

#portal .border-radius {
    border-radius: 20px;
}

#portal .btn:focus {
    box-shadow: none;
    outline: none;
}


/* custom css */

#portal .form-select:focus {
    box-shadow: none;
    outline: none;
}

#portal .membership_select .form-select {
    border: none;
    font-size: 11px;
    appearance: auto;
    -webkit-appearance: auto;
    background-image: none !important;
}

#portal .membership_select .form-select:focus {
    border: none;
}

#portal .simplee-portal__sidebar_inner ul li a {
    letter-spacing: 0.03em;
    font-size: 15px;
}

#portal .simplee-portal__sidebar_inner ul li a.active,
#portal .simplee-portal__sidebar_inner ul li a:hover {
    color: #1a76e6;
}

#portal .simplee-portal__text_inner span {
    font-size: 16px;
}

#portal .membership_edit a {
    width: 30px;
    height: 30px;
    border: 1px solid #9190906e;
    border-radius: 50%;
}

#portal .membership_edit a i {
    font-size: 14px;
    color: #818080;
}

#portal .membership_product_content .fa-redo {
    font-size: 14px;
    transform: rotate(-90deg);
}

#portal .accordion-button:focus,
#portal .accordion-button:not(.collapsed) {
    box-shadow: none;
    border: none;
    background-color: transparent;
}

#portal .collapse:not(.show) {
    display: none;
}

#portal .btn-primary {
    font-size: 15px;
    letter-spacing: 0.06em;
    border-radius: 7px;
    padding: 11px 29px;
    color: #fff;
}

#portal .accordion-button:not(.collapsed)::after {
    background-image: none;
    content: '\f078';
    transform: none;
}

#portal .membership_total td {
    font-size: 16px ;
}

#portal .btn-secondary {
    border-radius: 14px;
    background-color: #60bb46;
    letter-spacing: 0.75px;
    padding: 11px 21px;
    font-size: 15px ;
    color: #fff;
}

#portal .btn-secondary:hover {
    background-color: #60bb46;
}

#portal .form-control {
    border: 1px solid #9190906e;
    display: block;
    width: 100%;
    padding: 6px 12px;
    font-size: 16px;
    font-weight: 400;
    line-height: 1.5;
    color: #212529;
    background-color: #fff;
    background-clip: padding-box;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    border-radius: .25rem;
}

#portal .form-control:focus {
    box-shadow: none;
    outline: none;
}

#portal .membership_city {
    position: relative;
}

#portal .membership_city .fa-search {
    position: absolute;
    top: 11px;
    left: 8px;
}

#portal .membership_city .fa-times {
    position: absolute;
    top: 9px;
    right: 10px;
    width: 20px;
    height: 20px;
    background-color: #f7f5f5;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

/*.membership_form,
.membership_product_edit {
    display: none;
}*/

/*.membership_form.active,
.membership_product_edit.active {
    display: block;
}

.membership_order .membership_products.deactive,
.subscirption_products_inner.deactive {
    display: none;
}*/

#portal .membership_product_edit_inner .fa-times {
    position: unset;
}

#portal .membership_quantity span {
    cursor: pointer;
}

#portal .quntity_main {
    border: 1px solid #ddd;
    border-radius: 5px;
}

#portal .membership_quantity .minus,
#portal .membership_quantity .plus {
    width: 20px;
    height: 20px;
    background: #f2f2f2;
    border-radius: 50%;
    display: inline-block;
    vertical-align: middle;
    text-align: center;
    line-height: 14px;
    font-size: 24px;
}

#portal .membership_quantity input {
    width: 60px;
    text-align: center;
    font-size: 18px;
    border: none;
    border-radius: 4px;
    display: inline-block;
    vertical-align: middle;
}

#portal .modal-content {
    border-radius: 10px;
}

#portal .membership_cancel .btn-primary {
    background-color: #f71111;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    padding: 11px 23px;
}

// .simplee-portal__sidebar {
//     position: sticky;
// }
// .simplee-portal__sidebar.stickysidebar {
//     top: 5%;
// }
#portal .suscription_summary .table td {
    font-size: 14px;
    padding: 0;
    border: none;
}

#portal .suscription_summary .table td span {
    //font-size: 10px;
}

#portal label {
    font-size: 14px;
    margin-bottom: .5rem;
    display: inline-block;
}

#portal .membership_cancel a {
    letter-spacing: 0.75px;
}

#portal .form-select {
    display: block;
    width: 100%;
    padding: 6px 36px 6px 12px;
    font-size: 16px;
    font-weight: 400;
    line-height: 1.5;
    color: #212529;
    background-color: #fff;
    /*background-image: url(../images/download.svg);*/
    background-repeat: no-repeat;
    background-position: right .75rem center;
    background-size: 16px 12px;
    border: 1px solid #ced4da;
    border-radius: .25rem;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}
#portal .simplee-portal__verify{
    text-align: center;
}
#portal .quntity_main button {
    border: none;
    background: inherit;
}
#portal .membership_products  img{
    max-width: 100%;
}

/* START:: modal css*/
#portal .button {
    font-size: 1em;
    padding: 10px;
    color: #000;
    border-radius: 20px/50px;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease-out;
}

#portal .overlay {
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.7);
    transition: opacity 500ms;
    visibility: hidden;
    opacity: 0;
    z-index: 12;
    display: flex;
    align-items: center;
    justify-content: center;
}

#portal .overlay:target {
    visibility: visible;
    opacity: 1;
}

#portal .popup {
    position: relative;
    display: flex;
    flex-direction: column;
    width: 100%;
    pointer-events: auto;
    background-color: #fff;
    background-clip: padding-box;
    border-radius: 10px;
    outline: 0;
    position: relative;
    transition: all 5s ease-in-out;
}

#portal .popup .content {
    max-height: 30%;
    overflow: auto;
}

#portal .membership_cancel {
    max-width: 650px;
    margin: 0 auto;
}
#portal .open-modal{
    overflow: hidden;
}
/*END :: modal css*/
#portal .inactiveLink {
    pointer-events: none;
    cursor: default;
}
#portal p.v-toast__text {
    color: #ffffff;
}
#portal .img-paypal{
    width: 100px;
}
#portal .billing-card img {
    width: 20%;
}
#portal .billing-card {
    display: flex;
    align-items: center;
}
#portal .white-space-wrap{
    white-space: nowrap;
}
#portal .prepaid_status.scheduled {
    background-color: #a7eae2;
}
#portal .prepaid_status.unfulfilled {
    background-color: #fff398;
}
#portal .prepaid_status.fulfilled {
    background-color: #bbf3ae;
}
#portal .prepaid_status {
    border-radius: 50px;
    color: #000;
}
#portal .membership_order_prepaid th, #portal .membership_order_prepaid td {
    border: none;
    display: inline-block;
    width: 20%;
    text-align: center;
    margin-bottom: 10px;
    padding: 5px 14px;
}
#portal .membership_order_prepaid th:first-child, #portal .membership_order_prepaid td:first-child {
    text-align: left;
    padding-left: 0;
}

@media (min-width: 992px) {
    #portal .col-lg-3 {
        flex: 0 0 auto;
        width: 25%;
    }
    #portal .col-lg-9 {
        flex: 0 0 auto;
        width: 75%;
    }
}

@media (min-width: 768px) {
    #portal .col-md-3 {
        flex: 0 0 auto;
        width: 25%;
    }
    #portal .col-md-9 {
        flex: 0 0 auto;
        width: 75%;
    }
    #portal .col-md-6 {
        flex: 0 0 auto;
        width: 50%;
    }
    #portal .col-md-12 {
        flex: 0 0 auto;
        width: 100%;
    }
    #portal .d-md-block {
        display: block!important;
    }
    #portal .d-md-inline-block {
        display: inline-block!important;
    }
    #portal .d-md-flex {
        display: flex!important;
    }
    #portal .mb-md-0 {
        margin-bottom: 0!important;
    }
}

@media (min-width: 576px) {
    #portal .modal-dialog {
        max-width: 650px;
        margin: 0 auto;
        top: 40%;
    }
}

@media (max-width:767px) {
    #portal h1 {
        font-size: 20px;
    }
    #portal h2 {
        font-size: 21px;
    }
    #portal h5,
    #portal .membership_form label {
        font-size: 14px;
    }
    #portal p,
    #portal span {
        font-size: 14px;
    }
    #portal .membership_right_column {
        padding: 0;
    }
    #portal .border-radius {
        border-radius: 0;
    }
    #portal .btn-secondary,
    #portal .btn-primary {
        font-size: 14px !important;
        padding: 9px 19px !important;
    }
    #portal .subscirption_products_inner .membership_products img {
        width: 80px;
    }
    #portal .membership_edit a {
        width: 25px;
        height: 25px;
    }
    #portal .membership_edit a i {
        font-size: 12px;
    }
    #portal .membership_total td,
    #portal .membership_status_btn a {
        font-size: 17px;
    }
    #portal .modal-dialog {
        top: 40%;
    }
    #portal .box_spacing {
        padding: 14px 21px;
    }
    #portal .membership_product_content h4,
    #portal .membership_single_edit h4 {
        color: #000;
    }
}

@media (min-width:767px) and (max-width:1024px) {
    #portal .simplee-portal__sidebar_inner ul li a {
        font-size: 14px;
    }
    #portal h2 {
        font-size: 20px;
    }
    #portal p,
    #portal span {
        font-size: 13px;
    }
    #portal .subscirption_products_inner .membership_products img {
        width: 110px;
    }
}

@media (min-width:1024px) and (max-width:1600px) {
    #portal h2 {
        font-size: 22px;
    }
}
p.v-toast__text {
    color: #ffffff !important;
}
body.open-modal .overlay {
    visibility: visible !important;
    opacity: 1 !important;
}
 #portal .simplee-portal__wrapper .shoppay_img{
          width: 10%;
      }
      @-moz-keyframes throbber-loader {
        0% {
          background: #dde2e7;
        }
        10% {
          background: #6b9dc8;
        }
        40% {
          background: #dde2e7;
        }
      }
      @-webkit-keyframes throbber-loader {
        0% {
          background: #dde2e7;
        }
        10% {
          background: #6b9dc8;
        }
        40% {
          background: #dde2e7;
        }
      }
      @keyframes throbber-loader {
        0% {
          background: #dde2e7;
        }
        10% {
          background: #6b9dc8;
        }
        40% {
          background: #dde2e7;
        }
      }
      /* :not(:required) hides these rules from IE9 and below */
      .throbber-loader:not(:required) {
        -moz-animation: throbber-loader 2000ms 300ms infinite ease-out;
        -webkit-animation: throbber-loader 2000ms 300ms infinite ease-out;
        animation: throbber-loader 2000ms 300ms infinite ease-out;
        background: #dde2e7;
        display: inline-block;
        position: relative;
        text-indent: -9999px;
        width: 0.9em;
        height: 1.5em;
        margin: 0 1.6em;
      }
      .throbber-loader:not(:required):before, .throbber-loader:not(:required):after {
        background: #dde2e7;
        content: '\x200B';
        display: inline-block;
        width: 0.9em;
        height: 1.5em;
        position: absolute;
        top: 0;
      }
      .throbber-loader:not(:required):before {
        -moz-animation: throbber-loader 2000ms 150ms infinite ease-out;
        -webkit-animation: throbber-loader 2000ms 150ms infinite ease-out;
        animation: throbber-loader 2000ms 150ms infinite ease-out;
        left: -1.6em;
      }
      .throbber-loader:not(:required):after {
        -moz-animation: throbber-loader 2000ms 450ms infinite ease-out;
        -webkit-animation: throbber-loader 2000ms 450ms infinite ease-out;
        animation: throbber-loader 2000ms 450ms infinite ease-out;
        right: -1.6em;
      }
      .is_waiting{
        display: none;
      }
      #simplee_edit_order_info{
        display: none;
      }
      /* The snackbar - position it at the bottom and in the middle of the screen */
      #snackbar {
        visibility: hidden; /* Hidden by default. Visible on click */
      }
      .snackbar_inner{
        min-width: 250px;
        color: #fff;
        text-align: center;
        border-radius: 2px;
        padding: 16px;
        z-index: 9;
      }

      .error > .snackbar_inner{
        background-color: #d72626; /* Red background color */
      }

      .info > .snackbar_inner{
        background-color: #333; /* Red background color */
      }

      /* Show the snackbar when clicking on a button (class added with JavaScript) */
      #snackbar.show {
          visibility: visible;
          -webkit-animation: fadein 0.5s, fadeout 0.5s 2.5s;
          animation: fadein 0.5s, fadeout 0.5s 2.5s;
          display: flex !important;
          align-items: flex-end;
          justify-content: center;
          width: 100%;
          height: 100vh;
          position: fixed;
          left: 0;
          bottom: 20px;
      }

      /* Animations to fade the snackbar in and out */
      @-webkit-keyframes fadein {
        from {bottom: 0; opacity: 0;}
        to {bottom: 30px; opacity: 1;}
      }

      @keyframes fadein {
        from {bottom: 0; opacity: 0;}
        to {bottom: 30px; opacity: 1;}
      }

      @-webkit-keyframes fadeout {
        from {bottom: 30px; opacity: 1;}
        to {bottom: 0; opacity: 0;}
      }

      @keyframes fadeout {
        from {bottom: 30px; opacity: 1;}
        to {bottom: 0; opacity: 0;}
      }
      #PageContainer {
        transform: unset;
        -ms-transform: unset;
        -webkit-transform: unset;
      }
      .sm_saving{
        display: none;
      }
.cancel_reason_row{
   margin: 30px 0 0 0;
}
#other_cancel_reason{
  display: none !important;;
}

.simplee-portal__wrapper_inner {
    margin-bottom: 20px;
}

form#cancellation_reasons_form { margin-bottom: 10px; margin-left: 20px; }