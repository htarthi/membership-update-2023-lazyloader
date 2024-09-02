<?php
$customer = $data['customer'];
$contracts = ( gettype($customer) == 'object' ) ? $customer['Contracts'] : [];
$shop = $data['shop'];
?>
    <!DOCTYPE html>
<html lang="en">
<head>
    <!-- <title>Bootstrap Example</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1"> -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<!-- <script src="{{ asset('js/portal.js') }}" defer></script> -->
    <style>
        .main-subscription {
            padding: 10px !important;
            box-shadow: 0 0 10px 10px #e8e6e6 !important;
            margin-top: 10px !important;
        }
        .header-text{
            color: #807a72;
        }
    </style>
</head>
<body>

@if( gettype($customer) == 'object' )
    <div class="container">
        <h2>{{$customer['first_name']}} {{$customer['last_name']}}</h2>
        <h6>
            <span>{{$customer['email']}}</span>
            @if($customer['phone'] != '' && $customer['email']) <span>/</span>@endif
            <span>{{$customer['phone']}}</span>
        </h6>

        <h3>Subscriptions</h3>
        <div class="row">
            @if(count($contracts) > 0)
                @foreach( $contracts as $contract )
                    <div class="row main-subscription" style="padding: 10px !important;box-shadow: 0 0 10px 10px #e8e6e6 !important;margin-top: 10px !important;">
                        <!-- first row -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6 header-text"><h5>Status: </h5></div>
                                    <div class="col-md-6">{{$contract['status']}}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6 header-text"><h5>Ship to: </h5></div>
                                    <div class="col-md-6"><span class="col-md-6">{{$contract['ship_address1']}}</span> <a style="cursor:pointer;" id="edit-ship_add" data-toggle="modal" data-target="#ship_add_model" data-add="{{$contract['ship_address1']}}" data-shop="{{$shop['domain']}}" data-custid="{{$customer['id']}}" data-id="{{$contract['id']}}" data-type="edit_shipping_address1">Edit</a><br><span class="col-md-12">{{$contract['ship_phone']}}</span></div>
                                </div>
                            </div>
                        </div>
                        <!-- second row  -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6 header-text"><h5>Contract ID: </h5></div>
                                    <div class="col-md-6">{{$contract['shopify_contract_id']}}</div>
                                </div>
                            </div>
                        </div>
                        <!-- third row  -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6 header-text"><h5>Created on: </h5></div>
                                    <div class="col-md-6">{{$contract['created_at']}}</div>
                                </div>
                            </div>
                        </div>
                        <!-- fourth row  -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6 header-text"><h5>Currency:  </h5></div>
                                    <div class="col-md-6">$ USD</div>
                                </div>
                            </div>
                        </div>

                        <!-- fifth row -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6 header-text"><h5>Next Order Date: </h5></div>
                                    <div class="col-md-6">{{$contract['next_order_date']}} <a style="cursor:pointer;" class="contract_status" id="skip_next_order" data-shop="{{$shop['domain']}}" data-custid="{{$customer['id']}}" data-id="{{$contract['id']}}" data-type="skip_next_order">Skip Next Order</a></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6 header-text"><h5>Fixed date: </h5></div>
                                    <div class="col-md-6">Monthly on the 15th</div>
                                </div>
                            </div>
                        </div>
                        <!-- sixth row -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6 header-text"><h5>Deliver Every: </h5></div>
                                    <div class="col-md-6">{{$contract['delivery_interval_count']}} {{$contract['delivery_interval']}}(s)</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    @if( $contract['billing_min_cycles'] && $contract['billing_min_cycles'] != null )
                                        <div class="col-md-6 header-text"><h5>Minimum orders </h5></div>
                                        <div class="col-md-6">{{$contract['billing_min_cycles']}} Orders ({{$contract['order_count']}} complete)</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <!-- seventh row -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6 header-text"><h5>Bill Every: </h5></div>
                                    <div class="col-md-6">{{$contract['billing_interval_count']}} {{$contract['billing_interval']}}(s)</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    @if( $contract['billing_max_cycles'] && $contract['billing_max_cycles'] != null )
                                        <div class="col-md-6 header-text"><h5>Maximum orders </h5></div>
                                        <div class="col-md-6">{{$contract['billing_max_cycles']}} Orders ({{$contract['order_count']}} complete)</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <!-- lineitems -->
                        <?php $lineitems = $contract['line_items']; ?>
                        @if(count($lineitems) > 0)
                            <table class="table">
                                <tbody>
                                @foreach( $lineitems as $lineitem )
                                    <tr>
                                        <td scope="row"><img src="{{$lineitem['product_image']}}" style="width: 100px;"></td>
                                        <td><p>{{$lineitem['product_name']}}<br>{{$lineitem['variant_name']}}</br>{{$lineitem['sku']}}</p></td>
                                        <td>{{$lineitem['currency_symbol']}} {{$lineitem['final_amount']}}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                    @endif
                    <!-- buttons -->
                        <div class="row" style="padding: 15px;">
                            <div class="col-md-11">
                                <button type="button" class="btn btn-danger contract_status" data-shop="{{$shop['domain']}}" data-custid="{{$customer['id']}}" data-id="{{$contract['id']}}" data-type="cancelled">Cancel</button>

                                @if( $contract['status'] == 'active' )
                                    <button type="button" class="btn btn-warning contract_status" data-shop="{{$shop['domain']}}" data-custid="{{$customer['id']}}" data-id="{{$contract['id']}}" data-type="paused">Pause</button>
                                @else
                                    <button type="button" class="btn btn-warning contract_status" data-shop="{{$shop['domain']}}" data-custid="{{$customer['id']}}" data-id="{{$contract['id']}}" data-type="resumed">Resume</button>
                                @endif
                                <button type="button" class="btn btn-success">Add Product</button>
                                <button type="button" class="btn btn-primary">Edit Details</button>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-primary" style="background: linear-gradient(to bottom, #6371c7, #5563c1);border-color: #3f4eae;">Save</button>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div>Subscription not found...</div>
            @endif
        </div>
    </div>
@else
    <div class="container">
        <div>Subscriber not found...</div>
    </div>
@endif
<div class="modal" id="ship_add_model" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Shipping Address</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <label>Address</label>
                <input type="text" class=form-control name="ship_address1" id="new_ship_add">
            </div>
            <div class="modal-footer">
                <button type="button" id="ship_add_save" class="btn btn-primary contract_status" data-shop="{{$shop['domain']}}" data-custid="" data-id="" data-type="edit_shipping_address1">Save changes</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
</body>
</html>
<?php
