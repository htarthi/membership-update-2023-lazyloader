<!DOCTYPE html>
<html>
<head>
    <!-- Meta tag -->
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Memberships</title>
    <!-- style custom css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/app.css') }}" />
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap.css') }}">
    <!-- Polaris css cdn -->
    <link rel="stylesheet" href="https://unpkg.com/@shopify/polaris@5.1.0/dist/styles.css" />
    <!-- font-awesome link -->
    <script src="https://kit.fontawesome.com/7945acc650.js" crossorigin="anonymous"></script>
    <!-- mouseflow Tracking Code for https://memberships.simplee.best -->
    <script type="text/javascript">
        window._mfq = window._mfq || [];
        (function() {
            var mf = document.createElement("script");
            mf.type = "text/javascript";
            mf.defer = true;
            mf.src = "//cdn.mouseflow.com/projects/0e7fa64c-5bc6-44a3-bc91-8bb7b11131bb.js";
            document.getElementsByTagName("head")[0].appendChild(mf);
        })();
    </script>
    @if (config('shopify-app.appbridge_enabled'))
        <script src="https://unpkg.com/@shopify/app-bridge"></script>
        <script>
            var AppBridge = window['app-bridge'];
            var createApp = AppBridge.default;
            window.shopify_app_bridge = createApp({
                apiKey: '{{ config('shopify-app.api_key') }}',
                shopOrigin: '{{ Auth::user()->name }}',
                forceRedirect: true,
            });
            let smuname = '{{ Auth::user()->name }}';
        </script>
    @endif
</head>

<body>
    <div class="main">
        <div class="main-inner">
            <div class="plan-main">
                <main class="Polaris-Frame__Main" id="AppFrameMain" data-has-global-ribbon="false">
                    <a id="AppFrameMainContent" tabindex="-1"></a>
                    <div class="Polaris-Frame__Content">
                        <div class="Polaris-Page">
                            <div class="Polaris-Page__Content">
                                <div class="Polaris-Layout">
                                    <div class="plan-main-inner">
                                        <div class="plan-top-header">
                                            <h2 class="Polaris-DisplayText Polaris-DisplayText--sizeMedium">Choose the
                                                plan
                                                that’s right for you</h2>
                                        </div>

                                        <div class="plan-bottom-box">
                                            <div class="row justify-content-center">
                                                <div class="col-lg-4 col-md-4 col-sm-12" style="height: 100%">
                                                    <div class="plan-box-main">
                                                        <div class="plan-top-bar">
                                                            <h2
                                                                class="Polaris-DisplayText Polaris-DisplayText--sizeMedium">
                                                                {{ $plan['data'][0]['name'] }}</h2>
                                                        </div>
                                                        <div class="plan-bottom-part">
                                                            <div class="plan-bottom-bar">
                                                                @if ($plan['data'][0]['price'] > 0)
                                                                    <h2
                                                                        class="Polaris-DisplayText Polaris-DisplayText--sizeMedium">
                                                                        ${{ $plan['data'][0]['price'] }}</h2>
                                                                    <h5>PER MONTH</h5>
                                                                @else
                                                                    <h2
                                                                        class="Polaris-DisplayText Polaris-DisplayText--sizeMedium">
                                                                        FREE</h2>
                                                                    <h5>ONLY PAY MEMBER FEES</h5>
                                                                @endif

                                                            </div>

                                                            <div class="plan-mid-bar">
                                                                <h5>+
                                                                    ${{ number_format($plan['data'][0]['transaction_fee'] * 100, 2) }}
                                                                    PER MEMBER</h5>
                                                            </div>

                                                            <div class="plan-discription">
                                                                <ul>
                                                                    <li>
                                                                        <div class="check-icon">
                                                                            <i class="fas fa-check"></i>
                                                                        </div>

                                                                        <span class="text-uppercase">WORKS WITH SHOPIFY
                                                                            PAYMENTS, AUTHORIZE.NET, PAYPAL EXPRESS
                                                                        </span>
                                                                    </li>
                                                                    <li>
                                                                        <div class="check-icon">
                                                                            <i class="fas fa-check"></i>
                                                                        </div>
                                                                        <span class="text-uppercase">SELL
                                                                            MEMBERSHIPS</span>
                                                                    </li>

                                                                    <li>
                                                                        <div class="check-icon">
                                                                            <i class="fas fa-check"></i>
                                                                        </div>
                                                                        <span class="text-uppercase">ADD CUSTOMER +
                                                                            ORDER TAGS</span>
                                                                    </li>

                                                                    <li>
                                                                        <div class="check-icon">
                                                                            <i class="fas fa-check"></i>
                                                                        </div>
                                                                        <span class="text-uppercase"> SHOW / HIDE
                                                                            STOREFRONT CONTENT</span>
                                                                    </li>

                                                                    <li>
                                                                        <div class="check-icon">
                                                                            <i class="fas fa-check"></i>
                                                                        </div>
                                                                        <span class="text-uppercase">DUNNING
                                                                            MANAGEMENT</span>
                                                                    </li>
                                                                </ul>
                                                            </div>

                                                            <div class="plan-btn-main">
                                                                @if ($plan['active_plan_id'] == 1 || $plan['active_plan_id'] == 2 || !$plan['active_plan_id'])
                                                                    <a class="Polaris-Button billing-plan"
                                                                        data-plan="1">
                                                                        <span class="Polaris-Button__Content">
                                                                            <span class="Polaris-Button__Text">Start
                                                                                Free Trial</span>
                                                                        </span>
                                                                    </a>
                                                                @else
                                                                    <button type="button" class="Polaris-Button"
                                                                        style="cursor: none;">
                                                                        <span class="Polaris-Button__Content">
                                                                            <span class="Polaris-Button__Text">Current
                                                                                Plan</span>
                                                                        </span>
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-4 col-md-4 col-sm-12" style="height: 100%">
                                                    <div class="plan-box-main">
                                                        <div class="plan-top-bar">
                                                            <h2
                                                                class="Polaris-DisplayText Polaris-DisplayText--sizeMedium">
                                                                {{ $plan['data'][1]['name'] }}</h2>
                                                        </div>
                                                        <div class="plan-bottom-part">
                                                            <div class="plan-bottom-bar">
                                                                <h2
                                                                    class="Polaris-DisplayText Polaris-DisplayText--sizeMedium">
                                                                    ${{ $plan['data'][1]['price'] }}</h2>
                                                                <h5>PER MONTH</h5>
                                                            </div>

                                                            <div class="plan-mid-bar">
                                                                <h5>+
                                                                    ${{ number_format($plan['data'][1]['transaction_fee'] * 100, 2) }}
                                                                    PER MEMBER</h5>
                                                            </div>

                                                            <div class="plan-discription">
                                                                <ul>
                                                                    <li>
                                                                        <div class="check-icon">
                                                                            <i class="fas fa-check"></i>
                                                                        </div>
                                                                        <span class="text-uppercase"> SAME FEATURES AS
                                                                            STARTER PLAN
                                                                        </span>
                                                                    </li>
                                                                    <li>
                                                                        <div class="check-icon">
                                                                            <i class="fas fa-check"></i>
                                                                        </div>
                                                                        <span class="text-uppercase">BEST FOR STORES
                                                                            WITH MORE THAN 200 MEMBERS</span>
                                                                    </li>
                                                                </ul>
                                                            </div>

                                                            <div class="plan-btn-main">
                                                                @if ($plan['active_plan_id'] == 1 || $plan['active_plan_id'] == 2 || !$plan['active_plan_id'])
                                                                    <a class="Polaris-Button billing-plan"
                                                                        data-plan="2">
                                                                        <span class="Polaris-Button__Content">
                                                                            <span class="Polaris-Button__Text">Start
                                                                                Free Trial</span>
                                                                        </span>
                                                                    </a>
                                                                @else
                                                                    <button type="button" class="Polaris-Button"
                                                                        style="cursor: none;">
                                                                        <span class="Polaris-Button__Content">
                                                                            <span class="Polaris-Button__Text">Current
                                                                                Plan</span>
                                                                        </span>
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-lg-4 col-md-4 col-sm-12" style="height: 100%">
                                                    <div class="plan-box-main">
                                                        <div class="plan-top-bar">
                                                            <h2
                                                                class="Polaris-DisplayText Polaris-DisplayText--sizeMedium">
                                                                {{ $plan['data'][2]['name'] }}</h2>
                                                        </div>

                                                        <div class="plan-bottom-part">
                                                            <div class="plan-bottom-bar">
                                                                <h2
                                                                    class="Polaris-DisplayText Polaris-DisplayText--sizeMedium">
                                                                    ${{ $plan['data'][2]['price'] }}</h2>
                                                                <h5>PER MONTH</h5>
                                                            </div>

                                                            <div class="plan-mid-bar">
                                                                <h5>+
                                                                    ${{ number_format($plan['data'][2]['transaction_fee'] * 100, 2) }}
                                                                    PER MEMBER</h5>
                                                            </div>

                                                            <div class="plan-discription">
                                                                <ul>
                                                                    <li>
                                                                        <div class="check-icon">
                                                                            <i class="fas fa-check"></i>
                                                                        </div>
                                                                        <span class="text-uppercase"> SAME FEATURES AS
                                                                            GROWTH PLAN</span>
                                                                    </li>

                                                                    <li>
                                                                        <div class="check-icon">
                                                                            <i class="fas fa-check"></i>
                                                                        </div>
                                                                        <span class="text-uppercase">BEST FOR STORES
                                                                            WITH MORE THAN 2,500 MEMBERS</span>
                                                                    </li>
                                                                </ul>
                                                            </div>

                                                            <div class="plan-btn-main">
                                                                @if ($plan['active_plan_id'] == 0 || $plan['active_plan_id'] == 1 || !$plan['active_plan_id'])
                                                                    <a class="Polaris-Button billing-plan"
                                                                        data-plan="3">
                                                                        <span class="Polaris-Button__Content">
                                                                            <span class="Polaris-Button__Text">Start
                                                                                Free Trial</span>
                                                                        </span>
                                                                    </a>
                                                                @else
                                                                    <button type="button" class="Polaris-Button"
                                                                        style="cursor: none;">
                                                                        <span class="Polaris-Button__Content">
                                                                            <span class="Polaris-Button__Text">Current
                                                                                Plan</span>
                                                                        </span>
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="plan-info">
                                    <h3>IMPORTANT:<span> YOU WILL BE ASKED TO ACCEPT A CAPPED MONTHLY AMOUNT. THIS IS
                                            REQUIRED FOR US TO CHARGE ADDITIONAL MEMBER FEES</span>
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <!-- jQuery and bootstrap css -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <!-- jQuery and bootstrap css end-->
    <script type="text/javascript">
        var simplee_membership = {
            init: function() {
                this.addEvent();
                this.changePlan();
            },
            addEvent: function() {
                let url = '{{ route('event') }}';
                var user = @json($user->id);

                $.ajax({
                    url: url,
                    type: "post",
                    data: {
                        '_method': 'post',
                        '_token': "{{ csrf_token() }}",
                        'category': 'Install',
                        'description': 'Billing page loaded',
                        'user_id': user
                    },
                    success: function(data) {
                    },
                });
            },
            changePlan: function() {
                $('.billing-plan').on("click", function() {
                    let plan_id = $(this).attr('data-plan');
                    let url = '/pbilling/' + plan_id + '/' + smuname;
                    $.ajax({
                        url: url,
                        type: "get",
                        success: function(data) {
                            window.top.location.href = data.data.confirmation_url;
                        },
                    });
                });

            },
        };

        $(document).ready(function() {
            simplee_membership.init();
        });
    </script>
</body>

</html>
