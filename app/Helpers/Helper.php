<?php

use App\Models\ExchangeRate;
use App\Models\Shop;
use App\Models\SsSetting;
use App\Models\SsCustomer;
use App\Models\SsLanguage;
use App\Models\User;
use DougSisk\CountryState\CountryState;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Intl\Currencies;

if (!function_exists('get_shopID_H')) {

    /**
     * @return mixed
     *
     */
    function get_shopID_H()
    {
        $user = Auth::user();
        $shop = Shop::where('user_id', $user->id)->first();
        return $shop['id'];
    }
}

if (!function_exists('check_decimal_H')) {

    /**
     * @return mixed
     *
     */
    function check_decimal_H($number)
    {
        return preg_replace('/[^0-9 .]/s', '', $number);
    }
}

if (!function_exists('gidToShopifyId')) {
    /**
     * @return mixed
     *
     */
    function gidToShopifyId($gid)
    {
        $parts = explode('/', $gid);
        return end($parts);
    }
}

if (!function_exists('getShopH')) {

    /**
     * @return mixed
     * @return mixed
     */
    function getShopH()
    {
        $user = Auth::user();
        // logger('========> User'.$user);


        //logger(json_encode($user));
        $shop = Shop::where('user_id', $user['id'])->first();
        return $shop;
    }
}

if (!function_exists('triggerCURL')) {

    /**
     * @param $shopName
     * @param $planName
     * @param $shopURL
     */
    function triggerCURL($data, $header, $url)
    {
        logger('=============== START :: triggerCURL ===============');
        try {

            $raw = json_encode($data);

            // start Curl //

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $raw);
            // Create header
            $headers = array();
            $headers[] = $header;
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);

            //             print result for debug

            $error = curl_errno($ch);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
                $info = curl_getinfo($ch);
                \Log::info($info);
            } else {
                // logger(json_encode($result));
            }
            curl_close($ch);
        } catch (\Exception $e) {
            logger('=============== ERROR :: triggerCURL ===============');
            logger($e->getMessage());
        }
    }
}

if (!function_exists('sendMailH')) {

    /**
     * @return mixed
     * @return mixed
     */
    function sendMailH($subject, $html, $from, $to, $fromname, $shopID, $customerID, $planData = [], $isNotify = false)
    {
        try {
            $customer = SsCustomer::find($customerID);
            $shop = Shop::find($shopID);
            $newHtml = changeEmailVariableH($customer, $html, $shop, $planData);
            $subject = changeEmailVariableH($customer, $subject, $shop, $planData);

            $data = array('data' => $newHtml);
            $setting = SsSetting::where('shop_id', $shopID)->first();

            $reply_to = '';
            $mailgun_method = $setting->mailgun_method;
            if (!$isNotify) {
                if ($mailgun_method == 'Basic') {
                    if (isset($setting->email_from_email) && !empty($setting->email_from_email)) {
                        $from = $setting->email_from_email;
                    }
                    $domain = env('MAILGUN_DOMAIN');
                    Config::set('services.mailgun.domain', $domain);
                } elseif ($mailgun_method == 'Safe') {
                    $reply_to = $from;
                    $from = 'no-reply@mg.simplee.best';
                    $domain = env('MAILGUN_DOMAIN');
                    Config::set('services.mailgun.domain', $domain);
                } elseif ($mailgun_method == 'Advanced') {
                    if ($setting->mailgun_verified) {
                        Config::set('services.mailgun.domain', $setting->mailgun_domain);
                        // config(['services.mailgun.domain' => $setting->mailgun_domain]);
                    }
                }
            } else {
                // Sending to merchant
                // logger("Notifying merchant");
                $reply_to = $from;
            }

            $to = str_replace(' ', '', $to);
            // logger("Shop ::::" . $shop->myshopify_domain);
            // logger("Mail Domain :::" . Config::get('services.mailgun.domain'));
            // logger('Sending mail to ::: ' . $to);

            $res = Mail::send('mail.mail', $data, function ($message) use ($subject, $newHtml, $from, $to, $fromname, $reply_to) {
                $message->from($from, $fromname);
                $message->to($to);
                $message->subject($subject);
                ($reply_to != '') ? $message->replyTo($reply_to) : '';
            });
            // logger(json_encode($res));
            return 'success';
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}

if (!function_exists('changeEmailVariableH')) {
    /**
     * @return mixed
     */
    function changeEmailVariableH($customer, $html, $shop, $planData)
    {
        if ($customer) {
            $lang = SsLanguage::select('date_format')->where('shop_id', $shop->id)->first();

            $newHtml = str_replace('[FIRST_NAME]', $customer->first_name, $html);
            $newHtml = str_replace('[LAST_NAME]', $customer->last_name, $newHtml);
            $newHtml = str_replace('[STORE_NAME]', $shop->name, $newHtml);
            $newHtml = str_replace('[STORE_URL]', 'https://' . $shop->domain, $newHtml);
            $newHtml = str_replace('[EMAIL]', $customer->email, $newHtml);

            if (@$planData['next_billing_date']) {
                $next_billing_date = date($lang->date_format, strtotime($planData['next_billing_date']));
                $newHtml = str_replace('[NEXT_BILLING_DATE]', $next_billing_date . ' UTC', $newHtml);
            }

            $newHtml = (@$planData['membership_plan']) ? str_replace('[MEMBERSHIP_PLAN]', $planData['membership_plan'], $newHtml) : $newHtml;

            if (@$planData['renewal_date']) {
                // $renewal_date = date($lang->date_format, strtotime($planData['renewal_date']));

                $renewal_date = DateTime::createFromFormat("d/m/Y", $planData['renewal_date']);
                $renewal_date = $renewal_date->format($lang->date_format);

                $newHtml = str_replace('[RENEWAL_DATE]', $renewal_date, $newHtml);
            }
        } else {
            $newHtml = str_replace('[STORE_NAME]', $shop->name, $html);
            $newHtml = str_replace('[STORE_URL]', 'https://' . $shop->domain, $newHtml);

            $newHtml = str_replace('[FIRST_NAME]', 'Jane', $newHtml);
            $newHtml = str_replace('[LAST_NAME]', 'Smith', $newHtml);
            $newHtml = str_replace('[CARD_TYPE]', 'Visa', $newHtml);
            $newHtml = str_replace('[EXPIRY_DATE]', '01/29', $newHtml);
        }

        return $newHtml;
    }
}

if (!function_exists('getPaymentFailedMailHtml')) {

    /**
     * @return mixed
     * @return mixed
     */
    function getPaymentFailedMailHtml()
    {
        $html = '<html lang="en">
            <head>
              <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
              <meta name="viewport" content="width=device-width">
            <style>
            body {
            margin: 0;
            }
            h1 a:hover {
            font-size: 30px; color: #333;
            }
            h1 a:active {
            font-size: 30px; color: #333;
            }
            h1 a:visited {
            font-size: 30px; color: #333;
            }
            a:hover {
            text-decoration: none;
            }
            a:active {
            text-decoration: none;
            }
            a:visited {
            text-decoration: none;
            }
            .button__text:hover {
            color: #fff; text-decoration: none;
            }
            .button__text:active {
            color: #fff; text-decoration: none;
            }
            .button__text:visited {
            color: #fff; text-decoration: none;
            }
            a:hover {
            color: #1990C6;
            }
            a:active {
            color: #1990C6;
            }
            a:visited {
            color: #1990C6;
            }
             .simplee-mail td{
                font-family: -apple-system, BlinkMacSystemFont, \'Roboto\', \'Oxygen\', \'Ubuntu\',\'Cantarell\', \'Fira Sans\', \'Droid Sans\', \'Helvetica Neue\', sans-serif;";
              }
            @media (max-width: 600px) {
              .container {
                width: 94% !important;
              }
              .main-action-cell {
                float: none !important; margin-right: 0 !important;
              }
              .secondary-action-cell {
                text-align: center; width: 100%;
              }
              .header {
                margin-top: 20px !important; margin-bottom: 2px !important;
              }
              .shop-name__cell {
                display: block;
              }
              .order-number__cell {
                display: block; text-align: left !important; margin-top: 20px;
              }
              .button {
                width: 100%;
              }
              .or {
                margin-right: 0 !important;
              }
              .apple-wallet-button {
                text-align: center;
              }
              .customer-info__item {
                display: block; width: 100% !important;
              }
              .spacer {
                display: none;
              }
              .subtotal-spacer {
                display: none;
              }
            }
            </style>
            </head>
            <body style="margin: 0px; zoom: 0%;">
            <table class="body simplee-mail" style="height: 100% !important; width: 100% !important; border-spacing: 0; border-collapse: collapse;">
            <tbody>
            <tr>
            <td>
            <table class="header row" style="width: 100%; border-spacing: 0; border-collapse: collapse; margin: 40px 0 20px;">
            <tbody>
            <tr>
            <td class="header__cell"><center>
            <table class="container" style="width: 560px; text-align: left; border-spacing: 0; border-collapse: collapse; margin: 0 auto;">
            <tbody>
            <tr>
            <td>
            <table class="row" style="width: 100%; border-spacing: 0; border-collapse: collapse;">
            <tbody>
            <tr>
            <td class="shop-name__cell">
            <h1 class="shop-name__text" style="font-weight: normal; font-size: 30px; color: #333; margin: 0;"><a style="font-size: 30px; color: #333; text-decoration: none;" href="https://simplee-memberships-dev-1.myshopify.com">[STORE_NAME]</a></h1>
            </td>
            </tr>
            </tbody>
            </table>
            </td>
            </tr>
            </tbody>
            </table>
            </center></td>
            </tr>
            </tbody>
            </table>
            <table class="row content" style="width: 100%; border-spacing: 0; border-collapse: collapse;">
            <tbody>
            <tr>
            <td class="content__cell" style="padding-bottom: 40px; border: 0;"><center>
            <table class="container" style="width: 560px; text-align: left; border-spacing: 0; border-collapse: collapse; margin: 0 auto;">
            <tbody>
            <tr>
            <td>
            <h2 style="font-weight: normal; font-size: 24px; margin: 0 0 10px;">Your membership payment failed</h2>
            <p style="color: #777; line-height: 150%; font-size: 16px; margin: 0;">Hello - we recently tried to process your membership payment, but it was unsuccessful.<br /><br />This sometimes happens if your payment method is experiencing a temporary issue. Don\'t worry - we\'ll try again tomorrow. If your payment method has recently changed, please login to your account to manager this membership, and update your payment information.</p>
            <table class="row actions" style="width: 100%; border-spacing: 0; border-collapse: collapse; margin-top: 20px;">
            <tbody>
            <tr>
            <td class="empty-line" style="line-height: 0em;">&nbsp;</td>
            </tr>
            <tr>
            <td class="actions__cell">
            <table class="button main-action-cell" style="border-spacing: 0; border-collapse: collapse; float: left; margin-right: 15px;">
            <tbody>
            <tr>
            <td class="button__cell" style="border-radius: 4px;" align="center" bgcolor="#1990C6"><a class="button__text" style="font-size: 16px; text-decoration: none; display: block; color: #fff; padding: 20px 25px;" href="[STORE_URL]/account">Login to your account</a></td>
            </tr>
            </tbody>
            </table>
            <table class="link secondary-action-cell" style="border-spacing: 0; border-collapse: collapse; margin-top: 19px;">
            <tbody>
            <tr>
            <td class="link__cell">or <a style="font-size: 16px; text-decoration: none; color: #1990c6;" href="[STORE_URL]">Visit our store</a></td>
            </tr>
            </tbody>
            </table>
            </td>
            </tr>
            </tbody>
            </table>
            </td>
            </tr>
            </tbody>
            </table>
            </center></td>
            </tr>
            </tbody>
            </table>
            <table class="row footer" style="width: 100%; border-spacing: 0; border-collapse: collapse; border-top-width: 1px; border-top-color: #e5e5e5; border-top-style: solid;">
            <tbody>
            <tr>
            <td class="footer__cell" style="padding: 35px 0;"><center>
            <table class="container" style="width: 560px; text-align: left; border-spacing: 0; border-collapse: collapse; margin: 0 auto;">
            <tbody>
            <tr>
            <td>
            <p class="disclaimer__subtext" style="color: #999; line-height: 150%; font-size: 14px; margin: 0;">If you have any questions, reply to this email or contact us on our website.</p>
            </td>
            </tr>
            </tbody>
            </table>
            </center></td>
            </tr>
            </tbody>
            </table>
            </td>
            </tr>
            </tbody>
            </table>
            </body>
            </html>';

        return $html;
    }
}

if (!function_exists('getRestrictedContentHtml')) {
    /**
     * @return mixed
     */
    function getRestrictedContentHtml()
    {
        return '<!DOCTYPE html>
       <html>
       <head>
       <style>
       .simplee-locked-header {
           margin: auto;
           width: 50%;
           text-align: center;
       }
       .simplee-locked-details {
           margin: auto;
           width: 50%;
           text-align: center;
       }
       </style>
       </head>
       <body>
       <div class="simplee-locked-header">
       <h1>Members Only</h1>
       </div>
       <div class="simplee-locked-details">This content is restricted to members only. If you are a member, please <a href="/account">login</a> now. If you are not a member, please purchase a membership to view this page.</div>
       </body>
       </html>';
    }
}

if (!function_exists('getNewSubscriptioMailHtml')) {

    /**
     * @return mixed
     * @return mixed
     */
    function getNewSubscriptioMailHtml()
    {
        $html = '<html lang="en">
        <head>
          <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
          <meta name="viewport" content="width=device-width">
        <style>
        body {
        margin: 0;
        }
        h1 a:hover {
        font-size: 30px; color: #333;
        }
        h1 a:active {
        font-size: 30px; color: #333;
        }
        h1 a:visited {
        font-size: 30px; color: #333;
        }
        a:hover {
        text-decoration: none;
        }
        a:active {
        text-decoration: none;
        }
        a:visited {
        text-decoration: none;
        }
        .button__text:hover {
        color: #fff; text-decoration: none;
        }
        .button__text:active {
        color: #fff; text-decoration: none;
        }
        .button__text:visited {
        color: #fff; text-decoration: none;
        }
        a:hover {
        color: #1990C6;
        }
        a:active {
        color: #1990C6;
        }
        a:visited {
        color: #1990C6;
        }
        .simplee-mail td{
            font-family: -apple-system, BlinkMacSystemFont, \'Roboto\', \'Oxygen\', \'Ubuntu\',\'Cantarell\', \'Fira Sans\', \'Droid Sans\', \'Helvetica Neue\', sans-serif;";
        }
        @media (max-width: 600px) {
          .container {
            width: 94% !important;
          }
          .main-action-cell {
            float: none !important; margin-right: 0 !important;
          }
          .secondary-action-cell {
            text-align: center; width: 100%;
          }
          .header {
            margin-top: 20px !important; margin-bottom: 2px !important;
          }
          .shop-name__cell {
            display: block;
          }
          .order-number__cell {
            display: block; text-align: left !important; margin-top: 20px;
          }
          .button {
            width: 100%;
          }
          .or {
            margin-right: 0 !important;
          }
          .apple-wallet-button {
            text-align: center;
          }
          .customer-info__item {
            display: block; width: 100% !important;
          }
          .spacer {
            display: none;
          }
          .subtotal-spacer {
            display: none;
          }
        }
        </style>
        </head>
        <body style="margin: 0px; zoom: 0%;">
        <table class="body simplee-mail" style="height: 100% !important; width: 100% !important; border-spacing: 0; border-collapse: collapse;">
        <tbody>
        <tr>
        <td>
        <table class="header row" style="width: 100%; border-spacing: 0; border-collapse: collapse; margin: 40px 0 20px;">
        <tbody>
        <tr>
        <td class="header__cell"><center>
        <table class="container" style="width: 560px; text-align: left; border-spacing: 0; border-collapse: collapse; margin: 0 auto;">
        <tbody>
        <tr>
        <td>
        <table class="row" style="width: 100%; border-spacing: 0; border-collapse: collapse;">
        <tbody>
        <tr>
        <td class="shop-name__cell">
        <h1 class="shop-name__text" style="font-weight: normal; font-size: 30px; color: #333; margin: 0;"><a style="font-size: 30px; color: #333; text-decoration: none;" href="https://simplee-memberships-dev-1.myshopify.com">[STORE_NAME]</a></h1>
        </td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        </tbody>
        </table>
        </center></td>
        </tr>
        </tbody>
        </table>
        <table class="row content" style="width: 100%; border-spacing: 0; border-collapse: collapse;">
        <tbody>
        <tr>
        <td class="content__cell" style=" padding-bottom: 40px; border: 0;"><center>
        <table class="container" style="width: 560px; text-align: left; border-spacing: 0; border-collapse: collapse; margin: 0 auto;">
        <tbody>
        <tr>
        <td>
        <h2 style="font-weight: normal; font-size: 24px; margin: 0 0 10px;">Your membership just started!</h2>
        <p style="color: #777; line-height: 150%; font-size: 16px; margin: 0;">We wanted to let you know that we\'ve received your order, and your new membership has begun.<br /><br />If you need to make any updates to this membership, you can always login to your account using the link below. If you don\'t already have an account with our store, simply create a new account using this email address, and you will be able to manage your membership.</p>
        <table class="row actions" style="width: 100%; border-spacing: 0; border-collapse: collapse; margin-top: 20px;">
        <tbody>
        <tr>
        <td class="empty-line" style="line-height: 0em;">&nbsp;</td>
        </tr>
        <tr>
        <td class="actions__cell">
        <table class="button main-action-cell" style="border-spacing: 0; border-collapse: collapse; float: left; margin-right: 15px;">
        <tbody>
        <tr>
        <td class="button__cell" style="border-radius: 4px;" align="center" bgcolor="#1990C6"><a class="button__text" style="font-size: 16px; text-decoration: none; display: block; color: #fff; padding: 20px 25px;" href="[STORE_URL]/account">Login to your account</a></td>
        </tr>
        </tbody>
        </table>
        <table class="link secondary-action-cell" style="border-spacing: 0; border-collapse: collapse; margin-top: 19px;">
        <tbody>
        <tr>
        <td class="link__cell">or <a style="font-size: 16px; text-decoration: none; color: #1990c6;" href="[STORE_URL]">Visit our store</a></td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        </tbody>
        </table>
        </center></td>
        </tr>
        </tbody>
        </table>
        <table class="row footer" style="width: 100%; border-spacing: 0; border-collapse: collapse; border-top-width: 1px; border-top-color: #e5e5e5; border-top-style: solid;">
        <tbody>
        <tr>
        <td class="footer__cell" style="padding: 35px 0;"><center>
        <table class="container" style="width: 560px; text-align: left; border-spacing: 0; border-collapse: collapse; margin: 0 auto;">
        <tbody>
        <tr>
        <td>
        <p class="disclaimer__subtext" style="color: #999; line-height: 150%; font-size: 14px; margin: 0;">If you have any questions, reply to this email or contact us on our website.</p>
        </td>
        </tr>
        </tbody>
        </table>
        </center></td>
        </tr>
        </tbody>
        </table>
        </td>
        </tr>
        </tbody>
        </table>
        </body>
        </html>';
        return $html;
    }
}

if (!function_exists('getRecurringMailHtml')) {

    /**
     * @return mixed
     * @return mixed
     */
    function getRecurringMailHtml()
    {
        return '
    <html lang="en">
    <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
      <meta name="viewport" content="width=device-width">
    <style>
      body {
      margin: 0;
      }
      h1 a:hover {
      font-size: 30px; color: #333;
      }
      h1 a:active {
      font-size: 30px; color: #333;
      }
      h1 a:visited {
      font-size: 30px; color: #333;
      }
      a:hover {
      text-decoration: none;
      }
      a:active {
      text-decoration: none;
      }
      a:visited {
      text-decoration: none;
      }
      .button__text:hover {
      color: #fff; text-decoration: none;
      }
      .button__text:active {
      color: #fff; text-decoration: none;
      }
      .button__text:visited {
      color: #fff; text-decoration: none;
      }
      a:hover {
      color: #1990C6;
      }
      a:active {
      color: #1990C6;
      }
      a:visited {
      color: #1990C6;
      }
      .simplee-mail td{
          font-family: -apple-system, BlinkMacSystemFont, \'Roboto\', \'Oxygen\', \'Ubuntu\',\'Cantarell\', \'Fira Sans\', \'Droid Sans\', \'Helvetica Neue\', sans-serif;";
      }
      @media (max-width: 600px) {
        .container {
          width: 94% !important;
        }
        .main-action-cell {
          float: none !important; margin-right: 0 !important;
        }
        .secondary-action-cell {
          text-align: center; width: 100%;
        }
        .header {
          margin-top: 20px !important; margin-bottom: 2px !important;
        }
        .shop-name__cell {
          display: block;
        }
        .order-number__cell {
          display: block; text-align: left !important; margin-top: 20px;
        }
        .button {
          width: 100%;
        }
        .or {
          margin-right: 0 !important;
        }
        .apple-wallet-button {
          text-align: center;
        }
        .customer-info__item {
          display: block; width: 100% !important;
        }
        .spacer {
          display: none;
        }
        .subtotal-spacer {
          display: none;
        }
      }
    </style>
    </head>
    <body style="margin: 0px; zoom: 0%;">
      <table class="body simplee-mail" style="height: 100% !important; width: 100% !important; border-spacing: 0; border-collapse: collapse;">
      <tbody>
      <tr>
      <td>
      <table class="header row" style="width: 100%; border-spacing: 0; border-collapse: collapse; margin: 40px 0 20px;">
      <tbody>
      <tr>
      <td class="header__cell"><center>
      <table class="container" style="width: 560px; text-align: left; border-spacing: 0; border-collapse: collapse; margin: 0 auto;">
      <tbody>
      <tr>
      <td>
      <table class="row" style="width: 100%; border-spacing: 0; border-collapse: collapse;">
      <tbody>
      <tr>
      <td class="shop-name__cell">
      <h1 class="shop-name__text" style="font-weight: normal; font-size: 30px; color: #333; margin: 0;"><a style="font-size: 30px; color: #333; text-decoration: none;" href="[STORE_URL]">[STORE_NAME]</a></h1>
      </td>
      </tr>
      </tbody>
      </table>
      </td>
      </tr>
      </tbody>
      </table>
      </center></td>
      </tr>
      </tbody>
      </table>
      <table class="row content" style="width: 100%; border-spacing: 0; border-collapse: collapse;">
      <tbody>
      <tr>
      <td class="content__cell" style=" padding-bottom: 40px; border: 0;"><center>
      <table class="container" style="width: 560px; text-align: left; border-spacing: 0; border-collapse: collapse; margin: 0 auto;">
      <tbody>
      <tr>
      <td>
      <h2 style="font-weight: normal; font-size: 24px; margin: 0 0 10px;">Notice of upcoming membership renewal</h2>
      <p style="color: #777; line-height: 150%; font-size: 16px; margin: 0;">Hello! This is a friendly reminder that your membership with our store will be renewed on [RENEWAL_DATE]. We\'re so happy to have you as a member, thank you for your continued support.</p>
      <p style="color: #777; line-height: 150%; font-size: 16px; margin: 20px 0px;">If your billing information has changed since your last renewal, please login to your account, look for the "My Membership" link, and choose to update your billing information before the planned billing date.</p>
      <table class="row actions" style="width: 100%; border-spacing: 0; border-collapse: collapse; margin-top: 20px;">
      <tbody>
      <tr>
      <td class="empty-line" style="line-height: 0em;">&nbsp;</td>
      </tr>
      <tr>
      <td class="actions__cell">
      <table class="button main-action-cell" style="border-spacing: 0; border-collapse: collapse; float: left; margin-right: 15px;">
      <tbody>
      <tr>
      <td class="button__cell" style="border-radius: 4px;" align="center" bgcolor="#1990C6"><a class="button__text" style="font-size: 16px; text-decoration: none; display: block; color: #fff; padding: 20px 25px;" href="[STORE_URL]/account">Login to your account</a></td>
      </tr>
      </tbody>
      </table>
      <table class="link secondary-action-cell" style="border-spacing: 0; border-collapse: collapse; margin-top: 19px;">
      <tbody>
      <tr>
      <td class="link__cell">or <a style="font-size: 16px; text-decoration: none; color: #1990c6;" href="[STORE_URL]">Visit our store</a></td>
      </tr>
      </tbody>
      </table>
      </td>
      </tr>
      </tbody>
      </table>
      </td>
      </tr>
      </tbody>
      </table>
      </center></td>
      </tr>
      </tbody>
      </table>
      <table class="row footer" style="width: 100%; border-spacing: 0; border-collapse: collapse; border-top-width: 1px; border-top-color: #e5e5e5; border-top-style: solid;">
      <tbody>
      <tr>
      <td class="footer__cell" style="padding: 35px 0;"><center>
      <table class="container" style="width: 560px; text-align: left; border-spacing: 0; border-collapse: collapse; margin: 0 auto;">
      <tbody>
      <tr>
      <td>
      <p class="disclaimer__subtext" style="color: #999; line-height: 150%; font-size: 14px; margin: 0;">If you have any questions, reply to this email or contact us on our website.</p>
      </td>
      </tr>
      </tbody>
      </table>
      </center></td>
      </tr>
      </tbody>
      </table>
      </td>
      </tr>
      </tbody>
      </table>
    </body>
    </html>
    ';
    }
}

if (!function_exists('getCancelMembershipMailHtml')) {

    /**
     * @return mixed
     * @return mixed
     */
    function getCancelMembershipMailHtml()
    {
        $html = '<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta name="viewport" content="width=device-width">
<style>
body {
margin: 0;
}
h1 a:hover {
font-size: 30px; color: #333;
}
h1 a:active {
font-size: 30px; color: #333;
}
h1 a:visited {
font-size: 30px; color: #333;
}
a:hover {
text-decoration: none;
}
a:active {
text-decoration: none;
}
a:visited {
text-decoration: none;
}
.button__text:hover {
color: #fff; text-decoration: none;
}
.button__text:active {
color: #fff; text-decoration: none;
}
.button__text:visited {
color: #fff; text-decoration: none;
}
a:hover {
color: #1990C6;
}
a:active {
color: #1990C6;
}
a:visited {
color: #1990C6;
}
@media (max-width: 600px) {
  .container {
    width: 94% !important;
  }
  .main-action-cell {
    float: none !important; margin-right: 0 !important;
  }
  .secondary-action-cell {
    text-align: center; width: 100%;
  }
  .header {
    margin-top: 20px !important; margin-bottom: 2px !important;
  }
  .shop-name__cell {
    display: block;
  }
  .order-number__cell {
    display: block; text-align: left !important; margin-top: 20px;
  }
  .button {
    width: 100%;
  }
  .or {
    margin-right: 0 !important;
  }
  .apple-wallet-button {
    text-align: center;
  }
  .customer-info__item {
    display: block; width: 100% !important;
  }
  .spacer {
    display: none;
  }
  .subtotal-spacer {
    display: none;
  }
}
</style>
</head>
<body style="margin: 0px; zoom: 0%;">
  <table class="body" style="height: 100% !important; width: 100% !important; border-spacing: 0; border-collapse: collapse;">
      <tbody><tr>
        <td style="font-family: -apple-system, BlinkMacSystemFont, &quot;Roboto&quot;, &quot;Oxygen&quot;, &quot;Ubuntu&quot;,&quot;Cantarell&quot;, &quot;Fira Sans&quot;, &quot;Droid Sans&quot;, &quot;Helvetica Neue&quot;, sans-serif;">
          <table class="header row" style="width: 100%; border-spacing: 0; border-collapse: collapse; margin: 40px 0 20px;">
              <tbody><tr>
                  <td class="header__cell" style="font-family: -apple-system, BlinkMacSystemFont, &quot;Roboto&quot;, &quot;Oxygen&quot;, &quot;Ubuntu&quot;,&quot;Cantarell&quot;, &quot;Fira Sans&quot;, &quot;Droid Sans&quot;, &quot;Helvetica Neue&quot;, sans-serif;">
                    <center>

                      <table class="container" style="width: 560px; text-align: left; border-spacing: 0; border-collapse: collapse; margin: 0 auto;">
                        <tbody><tr>
                          <td style="font-family: -apple-system, BlinkMacSystemFont, &quot;Roboto&quot;, &quot;Oxygen&quot;, &quot;Ubuntu&quot;,&quot;Cantarell&quot;, &quot;Fira Sans&quot;, &quot;Droid Sans&quot;, &quot;Helvetica Neue&quot;, sans-serif;">

                            <table class="row" style="width: 100%; border-spacing: 0; border-collapse: collapse;">
                              <tbody><tr>
                                <td class="shop-name__cell" style="font-family: -apple-system, BlinkMacSystemFont, &quot;Roboto&quot;, &quot;Oxygen&quot;, &quot;Ubuntu&quot;,&quot;Cantarell&quot;, &quot;Fira Sans&quot;, &quot;Droid Sans&quot;, &quot;Helvetica Neue&quot;, sans-serif;">
                                  <h1 class="shop-name__text" style="font-weight: normal; font-size: 30px; color: #333; margin: 0;">
                                    <a href="https://simplee-memberships-dev-1.myshopify.com" style="font-size: 30px; color: #333; text-decoration: none;">[STORE_NAME]</a>
                                  </h1>
                                </td>

                              </tr>
                            </tbody>
                          </table>

                          </td>
                        </tr>
                      </tbody>
                    </table>

                  </center>
                </td>
              </tr>
            </tbody>
          </table>

          <table class="row content" style="width: 100%; border-spacing: 0; border-collapse: collapse;">
            <tbody><tr>
              <td class="content__cell" style="font-family: -apple-system, BlinkMacSystemFont, &quot;Roboto&quot;, &quot;Oxygen&quot;, &quot;Ubuntu&quot;,&quot;Cantarell&quot;, &quot;Fira Sans&quot;, &quot;Droid Sans&quot;, &quot;Helvetica Neue&quot;, sans-serif; padding-bottom: 40px; border: 0;">
                <center>
                  <table class="container" style="width: 560px; text-align: left; border-spacing: 0; border-collapse: collapse; margin: 0 auto;">
                    <tbody><tr>
                      <td style="font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Oxygen", "Ubuntu", "Cantarell", "Fira Sans", "Droid Sans", "Helvetica Neue", sans-serif;">

                        <h2 style="font-weight: normal; font-size: 24px; margin: 0 0 10px;">Your membership will be cancelled</h2>
                        <p style="color: #777; line-height: 150%; font-size: 16px; margin: 0;">We wanted to confirm that your existing membership renewal has been cancelled.<br><br>
                          You will continue to have access to your member benefits until your next renewal date on [RENEWAL_DATE].</p>

                        <table class="row actions" style="width: 100%; border-spacing: 0; border-collapse: collapse; margin-top: 20px;">
                          <tbody>
                            <tr>
                            <td class="empty-line" style="font-family: -apple-system, BlinkMacSystemFont, &quot;Roboto&quot;, &quot;Oxygen&quot;, &quot;Ubuntu&quot;,&quot;Cantarell&quot;, &quot;Fira Sans&quot;, &quot;Droid Sans&quot;, &quot;Helvetica Neue&quot;, sans-serif; line-height: 0em;">&nbsp;</td>
                            </tr>
                            <tr>
                              <td class="actions__cell" style="font-family: -apple-system, BlinkMacSystemFont, &quot;Roboto&quot;, &quot;Oxygen&quot;, &quot;Ubuntu&quot;,&quot;Cantarell&quot;, &quot;Fira Sans&quot;, &quot;Droid Sans&quot;, &quot;Helvetica Neue&quot;, sans-serif;">
                                <table class="button main-action-cell" style="border-spacing: 0; border-collapse: collapse; float: left; margin-right: 15px;">
                                  <tbody>
                                    <tr>
                                      <td class="button__cell" style="font-family: -apple-system, BlinkMacSystemFont, &quot;Roboto&quot;, &quot;Oxygen&quot;, &quot;Ubuntu&quot;,&quot;Cantarell&quot;, &quot;Fira Sans&quot;, &quot;Droid Sans&quot;, &quot;Helvetica Neue&quot;, sans-serif; border-radius: 4px;" align="center" bgcolor="#1990C6"><a href="[STORE_URL]/account" class="button__text" style="font-size: 16px; text-decoration: none; display: block; color: #fff; padding: 20px 25px;">Login to your account</a></td>
                                    </tr>
                                  </tbody>
                                </table>

                                <table class="link secondary-action-cell" style="border-spacing: 0; border-collapse: collapse; margin-top: 19px;">
                                  <tbody>
                                    <tr>
                                      <td class="link__cell" style="font-family: -apple-system, BlinkMacSystemFont, &quot;Roboto&quot;, &quot;Oxygen&quot;, &quot;Ubuntu&quot;,&quot;Cantarell&quot;, &quot;Fira Sans&quot;, &quot;Droid Sans&quot;, &quot;Helvetica Neue&quot;, sans-serif;">or <a href="[STORE_URL]" style="font-size: 16px; text-decoration: none; color: #1990C6;">Visit our store</a>
                                      </td>
                                    </tr>
                                  </tbody>
                                </table>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </center>
            </td>
          </tr>
        </tbody>
      </table>
      <table class="row footer" style="width: 100%; border-spacing: 0; border-collapse: collapse; border-top-width: 1px; border-top-color: #e5e5e5; border-top-style: solid;">
        <tbody>
          <tr>
            <td class="footer__cell" style="font-family: -apple-system, BlinkMacSystemFont, &quot;Roboto&quot;, &quot;Oxygen&quot;, &quot;Ubuntu&quot;,&quot;Cantarell&quot;, &quot;Fira Sans&quot;, &quot;Droid Sans&quot;, &quot;Helvetica Neue&quot;, sans-serif; padding: 35px 0;">
              <center>
                <table class="container" style="width: 560px; text-align: left; border-spacing: 0; border-collapse: collapse; margin: 0 auto;">
                  <tbody>
                    <tr>
                      <td style="font-family: -apple-system, BlinkMacSystemFont, &quot;Roboto&quot;, &quot;Oxygen&quot;, &quot;Ubuntu&quot;,&quot;Cantarell&quot;, &quot;Fira Sans&quot;, &quot;Droid Sans&quot;, &quot;Helvetica Neue&quot;, sans-serif;">
                        <p class="disclaimer__subtext" style="color: #999; line-height: 150%; font-size: 14px; margin: 0;">If you have any questions, reply to this email or contact us on our website.</p>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </center>
            </td>
          </tr>
        </tbody>
      </table>
    </td>
  </tr>
</tbody>
</table>
</body>
</html>
';
        return $html;
    }
}

if (!function_exists('currencyH')) {

    /**
     * @return mixed
     * @return mixed
     */
    function currencyH($c)
    {
        return Currencies::getSymbol($c);
    }
}

if (!function_exists('noImagePathH')) {
    /**
     * @return mixed
     */
    function noImagePathH()
    {
        return asset('images/static/no-image-box.png');
    }
}

if (!function_exists('calculateCurrency')) {
    /**
     * currency converter
     */
    function calculateCurrency($fromCurrency, $toCurrency, $amount)
    {
        try {
            $db_rates = ExchangeRate::latest()->first();
            $rates = json_decode($db_rates->conversion_rates);

            $calculated = round((($amount * $rates->$toCurrency) / $rates->$fromCurrency), 4);
            return $calculated;
        } catch (\Exception $e) {
            logger('=========== calculateCurrency ===========');
            logger($e);
        }
    }
}

if (!function_exists('installThemeH')) {
    /**
     * @return mixed
     */
    function installThemeH($theme_id, $user_id)
    {
        addSnippetH($theme_id, $user_id, true);
        addCSSAsset($theme_id, $user_id);
    }
}

//simplee.liquid snippet
if (!function_exists('addSnippetH')) {
    /**
     * @param $theme_id
     */
    function addSnippetH($theme_id, $user_id, $is_asset, $snippets = ['simplee', 'simplee_membership'])
    {
        try {
            \Log::info('-----------------------START :: addSnippet ==> ' . $user_id . '-----------------------');
            $user = User::find($user_id);

            //             foreach ($snippets as $skey => $svalue) {
            //                 // skip the simplee snippet part..
            //                 // if($svalue == 'simplee') {
            //                 //     continue;
            //                 // }

            //                 $isUpload = 0;
            //                 if ($svalue == 'simplee') {
            //                     $isUpload = 1;
            //                     $type = 'add';
            //                     if ($type == 'add') {

            //                         if ($is_asset) {
            //                             $simpleeAsset = getSimpleeSnippetCode();
            //                             $value = <<<EOF
            // $simpleeAsset
            // EOF;
            //                         } else {
            //                             $value = <<<EOF
            // {% if customer %}
            //   <script type="text/javascript">
            //     sessionStorage.setItem("X-shopify-customer-ID",{{ customer.id }});
            //   </script>
            // {% endif %}
            // EOF;
            //                         }
            //                     }
            //                 } else if ($svalue == 'simplee_membership' && $is_asset) {
            //                     $isUpload = 1;
            //                     $fileData = getSimpleeMembershipSnippetCode();
            //                     $value = <<<EOF
            //                     $fileData
            // EOF;
            //                 }

            //                 logger('========>theme id');
            //                 logger($theme_id);
            //                 if ($isUpload) {
            //                     $config = ($svalue == 'simplee') ? config('const.SNIPPETS.SIMPLEE') : config('const.SNIPPETS.SIMPLEE_MEMBERSHIP');
            //                     $parameter['asset']['key'] = 'snippets/' . $config . '.liquid';
            //                     $parameter['asset']['value'] = $value;
            //                     $asset = $user->api()->rest('PUT', 'admin/api/' .  env('SHOPIFY_API_VERSION') . '/themes/' . $theme_id . '/assets.json', $parameter);

            //                     logger(json_encode($asset));
            //                 }
            //             }

            updateThemeLiquidH('simplee', $theme_id, $user_id, $snippets);
        } catch (\Exception $e) {
            \Log::info('-----------------------ERROR :: addSnippet -----------------------');
            \Log::info(json_encode($e));
        }
    }
}

if (!function_exists('getSimpleeSnippetCode')) {
    function getSimpleeSnippetCode()
    {
        try {
            \Log::info('-----------------------START :: getSimpleeMembershipSnippetCode -----------------------');
            $string = '{% if customer %}
  <script type="text/javascript">
    sessionStorage.setItem("X-shopify-customer-ID",{{ customer.id }});
  </script>

    {%- liquid
      assign sm_discounts = shop.metafields.simplee.memberships.discounts
    %}

      {% for discount in sm_discounts   %}
          {% if customer.tags contains discount.tag %}
               <script type="text/javascript">
                 let isDiscount = sessionStorage.getItem("X-sm-discount-applied");

                 if(isDiscount == null || isDiscount == false){
                    var xhttp = new XMLHttpRequest();
                    xhttp.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {
                          sessionStorage.setItem("X-sm-discount-applied", true);
                        }
                    };

                    var host = window.location.protocol + "//" + window.location.host;
                    var code =  "{{ discount.code }}";
                    let endPoint = host + \'/discount/\' + encodeURIComponent(code);
                    xhttp.open("GET", endPoint, true);
                    xhttp.send();
                 }else{
                   let isMsgOnCart = {{ discount.display_cart }};
                   let isMsgOnAcc = {{ discount.display_login }};

                   if( isMsgOnCart || isMsgOnAcc ){
                     let pathName = window.location.pathname;
                     if(pathName == \'/cart\' || pathName == \'/account\'){
                        const newDiv = document.createElement("div");
                        newDiv.setAttribute("id", "simplee-cart-message");
                        newDiv.setAttribute("class", "simplee-cart-message");
                        newDiv.appendChild(document.createTextNode("{{ discount.message }}"));

                        if((isMsgOnCart && pathName == \'/cart\') || ( isMsgOnAcc && pathName == \'/account\' )){
                          setTimeout(function(){
                              let referenceNode = document.getElementsByTagName("h1")[0];
                              insertAfter(newDiv, referenceNode);
                          }, 500);
                        }
                     }
                   }
                 }

                 function insertAfter(newNode, referenceNode) {
                    referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
                 }
              </script>
          {% endif %}
      {% endfor %}
{% endif %}
{{ \'simplee.css\' | asset_url | stylesheet_tag }}
{{ \'simplee.js\' | asset_url | script_tag }}
            ';
            return $string;
        } catch (\Exception $e) {
            \Log::info('-----------------------ERROR :: getSimpleeMembershipSnippetCode -----------------------');
            \Log::info(json_encode($e));
        }
    }
}

if (!function_exists('getSimpleeMembershipSnippetCode')) {
    function getSimpleeMembershipSnippetCode()
    {
        try {
            \Log::info('-----------------------START :: getSimpleeMembershipSnippetCode -----------------------');
            $string = '{%- comment -%}
            Simplee Memberships Rules Parser
            Version: 1.2 - Ignore rules when in the theme customizer
            For questions visit https://support.simplee.best
          {%- endcomment -%}

          {% liquid
            unless content_for_header contains \'Shopify.designMode\'
              assign sm_config = shop.metafields.simplee.memberships.config

              assign sm_show_content = true
              assign sm_show_cart = true
              assign continue_loop = true
              if sm_config.active
                assign sm_page = request.page_type

                assign sm_rules = shop.metafields.simplee.memberships.rules
                if template
                  assign sm_active_tag = \'\'
                  for tag in customer.tags
                    if sm_config.active_tags contains tag
                      if sm_active_tag == \'\'
                        assign sm_active_tag = tag
                      else
                        assign sm_active_tag = sm_active_tag | append: \',\'
                        assign sm_active_tag = sm_active_tag | append: tag
                      endif
                    endif
                  endfor
                  assign customer_tags = sm_active_tag | split: \',\'
                endif

                echo simplee-memberships

                case sm_page
                  when \'page\'
                    for rule in sm_rules
                      assign rule_tags = rule.tags | split: \',\'
                      if continue_loop
                        if rule.type == \'all_pages\'
                          assign sm_show_content = false
                          for customer_tag in customer_tags
                            if rule_tags contains customer_tag
                              assign sm_show_content = true
                              assign continue_loop = false
                            endif
                          endfor
                        elsif rule.type == \'page\' and rule.id contains page.id
                          assign sm_show_content = false
                          for customer_tag in customer_tags
                            if rule_tags contains customer_tag
                              assign sm_show_content = true
                              assign continue_loop = false
                            endif
                          endfor

                        elsif rule.type == \'show_page_specific_template\'
                          if page.template_suffix == rule.id[0]
                            assign sm_show_content = false
                            for customer_tag in customer_tags
                              if rule_tags contains customer_tag
                                assign sm_show_content = true
                                assign continue_loop = false
                              endif
                            endfor
                          endif
                        endif
                      endif
                    endfor

                  when \'product\'
                    for rule in sm_rules
                      assign rule_tags = rule.tags | split: \',\'
                      if continue_loop
                        echo rule.type
                        if rule.type == \'all_products\'
                          assign sm_show_content = false
                          for customer_tag in customer_tags
                            if rule_tags contains customer_tag
                              assign sm_show_content = true
                              assign continue_loop = false
                            endif
                          endfor
                        elsif rule.type == \'product\' and rule.id contains product.id
                          assign sm_show_content = false
                          for customer_tag in customer_tags
                            if rule_tags contains customer_tag
                              assign sm_show_content = true
                              assign continue_loop = false
                            endif
                          endfor
                        elsif rule.type == \'collection\'
                          for collection in product.collections
                            if rule.id contains collection.id
                              assign sm_show_content = false
                              for customer_tag in customer_tags
                                if rule_tags contains customer_tag
                                  assign sm_show_content = true
                                  assign continue_loop = false
                                endif
                              endfor
                            endif
                          endfor

                        elsif rule.type == \'show_product_specific_tag\'
                          if product.tags contains rule.id[0]
                            assign sm_show_content = false
                            for customer_tag in customer_tags
                              if rule_tags contains customer_tag
                                assign sm_show_content = true
                                assign continue_loop = false
                              endif
                            endfor
                          endif
                        elsif rule.type == \'show_product_specific_vendor\'
                          if product.vendor == rule.id[0]
                            assign sm_show_content = false
                            for customer_tag in customer_tags
                              if rule_tags contains customer_tag
                                assign sm_show_content = true
                                assign continue_loop = false
                              endif
                            endfor
                          endif

                        elsif rule.type == \'show_collection_specific_template\'
                          for collection in product.collections
                            if collection.template_suffix == rule.id[0]
                              assign sm_show_content = false
                              for customer_tag in customer_tags
                                if rule_tags contains customer_tag
                                  assign sm_show_content = true
                                  assign continue_loop = false
                                endif
                              endfor
                            endif
                          endfor
                        endif
                      endif
                    endfor

                  when \'article\'
                    for rule in sm_rules
                      assign rule_tags = rule.tags | split: \',\'
                      if continue_loop
                        if rule.type == \'article\' and rule.id contains article.id
                          assign sm_show_content = false
                          for customer_tag in customer_tags
                            if rule_tags contains customer_tag
                              assign sm_show_content = true
                              assign continue_loop = false
                            endif
                          endfor
                        elsif rule.type == \'all_blogs\'
                          assign sm_show_content = false
                          for customer_tag in customer_tags
                            if rule_tags contains customer_tag
                              assign sm_show_content = true
                              assign continue_loop = false
                            endif
                          endfor
                        elsif rule.type == \'blog\'
                          if rule.id contains blog.id
                            assign sm_show_content = false
                            for customer_tag in customer_tags
                              if rule_tags contains customer_tag
                                assign sm_show_content = true
                                assign continue_loop = false
                              endif
                            endfor
                          endif
                        elsif rule.type == \'show_blog_post_specific_template\'
                          if article.template_suffix == rule.id[0]
                            assign sm_show_content = false
                            for customer_tag in customer_tags
                              if rule_tags contains customer_tag
                                assign sm_show_content = true
                                assign continue_loop = false
                              endif
                            endfor
                          endif
                        elsif rule.type == \'show_blog_post_specific_tag\'
                          if article.tags contains rule.id[0]
                            assign sm_show_content = false
                            for customer_tag in customer_tags
                              if rule_tags contains customer_tag
                                assign sm_show_content = true
                                assign continue_loop = false
                              endif
                            endfor
                          endif
                        endif
                      endif
                    endfor

                  when \'collection\'
                    for rule in sm_rules
                      if continue_loop
                        assign rule_tags = rule.tags | split: \',\'

                        if rule.type == \'collection\' and rule.id contains collection.id
                          assign sm_show_content = false
                          for customer_tag in customer_tags
                            if rule_tags contains customer_tag
                              assign sm_show_content = true
                              assign continue_loop = false
                            endif
                          endfor
                        elsif rule.type == \'all_products\'
                          assign sm_show_content = false
                          for customer_tag in customer_tags
                            if rule_tags contains customer_tag
                              assign sm_show_content = true
                              assign continue_loop = false
                            endif
                          endfor
                        elsif rule.type == \'show_collection_specific_template\'

                            if collection.template_suffix == rule.id[0]
                              assign sm_show_content = false
                              for customer_tag in customer_tags
                                if rule_tags contains customer_tag
                                  assign sm_show_content = true
                                  assign continue_loop = false
                                endif
                              endfor
                            endif

                        endif
                      endif
                    endfor

                  when \'list-collections\'
                    for rule in sm_rules
                      if continue_loop
                        assign rule_tags = rule.tags | split: \',\'
                        if rule.type == \'all_products\'
                          assign sm_show_content = false
                          for customer_tag in customer_tags
                            if rule_tags contains customer_tag
                              assign sm_show_content = true
                              assign continue_loop = false
                            endif
                          endfor
                        endif
                      endif
                    endfor

                  when \'blog\'
                    for rule in sm_rules
                      if continue_loop
                        assign rule_tags = rule.tags | split: \',\'
                        if rule.type == \'all_blogs\'
                          assign sm_show_content = false
                          for customer_tag in customer_tags
                            if rule_tags contains customer_tag
                              assign sm_show_content = true
                              assign continue_loop = false
                            endif
                          endfor
                        elsif rule.type == \'blog\' and rule.id contains blog.id
                          assign sm_show_content = false
                          for customer_tag in customer_tags
                            if rule_tags contains customer_tag
                              assign sm_show_content = true
                              assign continue_loop = false
                            endif
                          endfor
                        endif
                      endif
                    endfor
                endcase

                case simplee-memberships
                  when \'cart\'
                    assign sm_show_cart = false
                    for rule in sm_rules
                      if continue_loop
                        assign rule_tags = rule.tags | split: \',\'
                        if rule.type == \'cart\'
                          assign sm_show_cart = false
                          for customer_tag in customer_tags
                            if rule_tags contains customer_tag
                              assign sm_show_cart = false
                              assign continue_loop = false
                            endif
                          endfor
                        endif
                      endif
                    endfor

                  when \'prices\'
                    assign sm_show_price = true
                    for rule in sm_rules
                      if continue_loop
                        assign rule_tags = rule.tags | split: \',\'
                        if rule.type == \'prices\'
                          assign sm_show_price = false
                          for customer_tag in customer_tags
                            if rule_tags contains customer_tag
                              assign sm_show_price = true
                              assign continue_loop = false
                            endif
                          endfor
                        endif
                      endif
                    endfor
                endcase
              endif
            endunless
          %}



            ';
            return $string;
        } catch (\Exception $e) {
            \Log::info('-----------------------ERROR :: getSimpleeMembershipSnippetCode -----------------------');
            \Log::info(json_encode($e));
        }
    }
}

//simplee-widget.liquid snippet
if (!function_exists('addSimpleeWidgetSnippetH')) {
    /**
     * @param $theme_id
     * @param $user_id
     * @param $version
     */
    function addSimpleeWidgetSnippetH($theme_id, $user_id, $version, $themeName = '')
    {
        try {
            \Log::info('-----------------------START :: addSimpleeWidgetSnippetH -----------------------');
            $user = User::where('id', $user_id)->first();

            $value = ($version == 'default') ? simpleeNewDefaultWidgetTextH($themeName) : simpleeWidgetTextH();

            $parameter['asset']['key'] = 'snippets/' . config('const.SNIPPETS.SIMPLEE_WIDGET') . '.liquid';
            $parameter['asset']['value'] = $value;
            $asset = $user->api()->rest('PUT', 'admin/themes/' . $theme_id . '/assets.json', $parameter);

            //            updateThemeLiquidH('simplee-widget', $theme_id);
        } catch (\Exception $e) {
            \Log::info('-----------------------ERROR :: addSimpleeWidgetSnippetH -----------------------');
            \Log::info(json_encode($e));
        }
    }
}

//simplee-cart.liquid snippet
if (!function_exists('addSimpleeCartWidgetSnippetH')) {
    /**
     * @param $theme_id
     */
    function addSimpleeCartWidgetSnippetH($theme_id, $user_id)
    {
        try {
            \Log::info('-----------------------START :: addSimpleeCartWidgetSnippetH -----------------------');
            $user = User::where('id', $user_id)->first();

            $value = <<<EOF
{%- comment -%} Simplee Storefront Widget - Cart Page - Version 0.1 {%- endcomment -%}
{%- comment -%} For questions visit http://support.simplee.best  {%- endcomment -%}

{% unless item.selling_plan_allocation == nil  %}
  <span class="simplee-selling-plan-details cart__option cart__option--single" data-simplee-item-key="{{item.key}}">
    Subscription: {{item.selling_plan_allocation.selling_plan.name}}
  </span>
{% endunless %}
EOF;

            $parameter['asset']['key'] = 'snippets/' . config('const.SNIPPETS.CART') . '.liquid';
            $parameter['asset']['value'] = $value;
            $asset = $user->api()->rest('PUT', 'admin/themes/' . $theme_id . '/assets.json', $parameter);
        } catch (\Exception $e) {
            \Log::info('-----------------------ERROR :: addSimpleeCartWidgetSnippetH -----------------------');
            \Log::info(json_encode($e));
        }
    }
}
//update theme.liquid
if (!function_exists('updateThemeLiquidH')) {
    /**
     * @param $snippet_name
     * @param $theme_id
     */
    function updateThemeLiquidH($snippet_name, $theme_id, $user_id, $snippets)
    {
        try {
            \Log::info('-----------------------START :: updateThemeLiquidH ==> ' . $user_id . '-----------------------');
            $user = User::find($user_id);

            $asset = getLiquidAssetH($theme_id, $user_id, config('const.FILES.THEME'));
            if ($asset != '') {
                // add after <body>
                logger('================> Checking for snnipets');
                logger($snippets);
                if (in_array("simplee_membership", $snippets)) {
                    logger("==================> simplee membership found...");
                    if (!strpos($asset, "{% if sm_show_content %}")) {
                        logger("==================>wrapping content_for_layout ....");
                        $asset = str_replace('{{ content_for_layout }}', " {% assign sm_show_content = true %} {% include 'simplee-memberships' %}
                                              {% if sm_show_content %}
                                                    {{ content_for_layout }}
                                                {% else %}
                                                    {%  echo shop.metafields.simplee.restricted %}
                                                {% endif %}", $asset);
                    }
                }

                if (!strpos($asset, "{% render '$snippet_name' %}")) {
                    if ($snippet_name != 'simplee') {
                        $asset = str_replace('</head>', "{% render '$snippet_name' %}</head>", $asset);
                    }
                }

                $parameter['asset']['key'] = config('const.FILES.THEME');
                $parameter['asset']['value'] = $asset;
                logger('============>>>> parameter');
                logger(json_encode($parameter));
                logger('admin/api/' .  env('SHOPIFY_API_VERSION') . '/themes/' . $theme_id . '/assets.json');
                $result = $user->api()->rest('PUT', 'admin/api/' .  env('SHOPIFY_API_VERSION') . '/themes/' . $theme_id . '/assets.json', $parameter);
                logger('==========> update theme');
                logger(json_encode($result));
            }
        } catch (\Exception $e) {
            \Log::info('-----------------------ERROR :: updateThemeLiquidH -----------------------');
            \Log::info(json_encode($e));
        }
    }
}

//update cart.liquid
if (!function_exists('updateCartLiquidH')) {
    /**
     * @param $snippet_name
     * @param $theme_id
     */
    function updateCartLiquidH($snippet_name, $theme_id, $user_id)
    {
        try {
            \Log::info('----------------------- START :: updateCartLiquidH -----------------------');
            $user = User::find($user_id);

            $asset = getLiquidAssetH($theme_id, $user_id, config('const.FILES.CART'));

            // logger($asset);
            //            if ($asset != '') {
            //                if (!strpos($asset, config('const.FILES.CART_FIND'))) {
            //                    if (!strpos($asset, "{% render '$snippet_name' %}")) {
            //                        $asset = str_replace('</tbody>', "{% render '$snippet_name' %}</tbody>", $asset);
            //                    }
            //                }
            //
            //                $parameter['asset']['key'] =  config('const.FILES.CART');
            //                $parameter['asset']['value'] = $asset;
            //                $result = $user->api()->rest('PUT', 'admin/themes/'.$theme_id.'/assets.json', $parameter);
            //            }
            return true;
        } catch (\Exception $e) {
            \Log::info('-----------------------ERROR :: updateCartLiquidH -----------------------');
            \Log::info(json_encode($e));
            return false;
        }
    }
}

//simplee css
if (!function_exists('addCSSAsset')) {
    /**
     * @param $theme_id
     * @param $user_id
     * @param $version
     */
    function addCSSAsset($theme_id, $user_id, $version, $themeName)
    {
        try {
            \Log::info('-----------------------START :: addCSSAsset -----------------------');
            $user = User::where('id', $user_id)->first();
            $cssCode = ($version == 'default') ? getDefaultCSSCode($themeName) : getCSSCode();

            $parameter['asset']['key'] = 'assets/' . config('const.ASSETS.CSS') . '.css';
            $parameter['asset']['value'] = $cssCode;
            $asset = $user->api()->rest('PUT', 'admin/themes/' . $theme_id . '/assets.json', $parameter);

            \Log::info(json_encode($asset));
        } catch (\Exception $e) {
            \Log::info('-----------------------ERROR :: addCSSAsset -----------------------');
            \Log::info(json_encode($e));
        }
    }
}

// simplee default css code
if (!function_exists('getDefaultCSSCode')) {
    /**
     * @return string
     */
    function getDefaultCSSCode($themeName)
    {
        // Asset Simplee CSS
        $cssCode = '/* Simplee Storefront Widget - CSS - Version 0.1
   For questions visit http://support.simplee.best */

.simplee-defaultwidget__color-button-background{
    background-color:#FAFAFA;
    border-radius: 11px;
}
.simplee-defaultwidget__color-button-text{
    color:#000;
}
.simplee-defaultwidget__button_wrapper input[type="radio"]:checked + .simplee-defaultwidget__color-text-primary{
    background-color:#1473E6;
    color:#fff;
}
.simplee-defaultwidget__button_wrapper input[type="radio"]:checked + .simplee-defaultwidget__color-text-primary svg path{
    fill:#fff;
}
.simplee-defaultwidget__purchase-options{
    font-size:16px !important;
    color: #000000;
    font-weight:600;
  margin-bottom:0;
}
.simplee-defaultwidget__button_wrapper input[type="radio"] {
  display:none;
}
.simplee-defaultwidget {
    padding: 10px 0px;
}';

        if ($themeName == 'Express' || $themeName == 'Narrative') {
            $cssCode .= '.simplee-defaultwidget__button_wrapper label {
      cursor: pointer;
      padding: 25px 10px;
      font-size: 16px;
      color: #000;
      font-weight: 600;
      display: flex;
      align-items:center;
      width: 25rem;
      height: 3rem;
    }';
        } else {
            $cssCode .= '.simplee-defaultwidget__button_wrapper label {
      cursor: pointer;
      padding: 10px 10px;
      font-size: 16px;
      color: #000;
      font-weight: 600;
      display: flex;
      align-items:center;
      width: 20rem;
      height: 3rem;
    }';
        }

        $cssCode .= '.simplee-defaultwidget__button_wrapper label span{
        width: auto;
    line-height: 0;
    margin-right: 15px;
}
.simplee-defaultwidget__button_wrapper{
    margin-bottom:13px;
    display: flex;
}


/* checkbox css */
.simplee-defaultwidget__checkbox input {
    display: none;
}
.simplee-defaultwidget__checkbox-wrapper, .simplee-defaultwidget__label{
     display: flex;
     align-items: center;
   margin-bottom: 10px;
}
.simplee-defaultwidget__checkbox label {
    display: block;
    margin: 0;
    padding-left: 30px;
    font-size: 16px;
    position: relative;
    color: #000;
    cursor: pointer;
}

.simplee-defaultwidget__checkbox input[type="radio"]:checked + label.simplee-defaultwidget__radio::before{
   background-color:#1473E6;
    border-color:#1473E6;
}
.simplee-defaultwidget__checkbox input[type="radio"]:checked + label.simplee-defaultwidget__radio::after{
    border-color:#fff;
}
.simplee-defaultwidget__checkbox label.simplee-defaultwidget__radio::before {
    content: "";
    width: 20px;
    height: 20px;
    border: 1px solid #707070;
    border-radius: 50%;
    position: absolute;
    left: 0;
}
.simplee-defaultwidget__checkbox label.simplee-defaultwidget__radio::after {
    content: "";
    width: 7px;
    height: 11px;
    border: 3px solid #142bff;
    position: absolute;
    top: 9px;
    left: 7px;
    transform: translateY(-50%) rotate(45deg);
    border-left: none;
    border-top: none;
    visibility: hidden;
    border-radius: 1px;
}
.simplee-defaultwidget__checkbox input[type="radio"]:checked + label.simplee-defaultwidget__radio::after{
    visibility: visible;
}
.simplee-defaultwidget__info svg{
    margin-left:8px;
}
.simplee-defaultwidget__info{
    position:relative;
}
.simplee-defaultwidget__tooltipText {
    background-color: #B2AEAE;
    position: absolute;
    bottom: 23px;
    left: -65px;
    padding: 4px 7px;
    border-radius: 0;
    display:none;
    width: 163px;

}
.simplee-defaultwidget__tooltipText p{
    font-size: 12px;
   color: #fff;
  font-style:italic;
}
.simplee-defaultwidget__tooltipText h6{
    color:#fff;
    margin-bottom:0;
  font-size:12px;
}
.simplee-defaultwidget__labelinfo p{
    margin-bottom:10px;
}
.simplee-defaultwidget__tooltipText::after {
    content: "";
    border-width: 5px;
    border-style: solid;
    border-color: #B2AEAE transparent transparent transparent;
    position: absolute;
    top: 100%;
    left: 40%;
    margin-left: 5%;
}
.simplee-defaultwidget__info:hover .simplee-defaultwidget__tooltipText {
   display:block;
}
.simplee-defaultwidget__hr{
    border-left: 1px solid #70707040;
}
.simplee-defaultwidget__labelinfo{
        left: 22px;
    bottom: auto;
    top: -118px;
   z-index: 999;
      width: 228px;
}
.simplee-defaultwidget__labelinfo::after{
    border-color: transparent #B2AEAE transparent transparent;
    top: 50%;
    left: -21px;

}
.simplee-defaultwidget__options {
    padding: 15px 0;
}

.simplee-defaultwidget__close, .simplee-defaultwidget__labelinfo .simplee-defaultwidget__purchase-options{
    display:none;
}
@media (max-width: 767px){
  .simplee-defaultwidget__labelinfo{
       position: unset;
    width:100%;
        background-color: #fff;
            padding: 6px 10px 10px 14px;
  }
  .simplee-defaultwidget__info-wrapper {
    display: none;
    position: fixed;
    z-index: 1;
       padding: 0 22px;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
  }
  .simplee-defaultwidget__info-wrapper.active{
    display:flex;
    align-items:center;
  }
  .simplee-defaultwidget__labelinfo h6, .simplee-defaultwidget__labelinfo p{
    color:#000;
  }
  .simplee-defaultwidget__close svg {
    width: 12px;
  }
  .simplee-defaultwidget__close {
    text-align: right;
    margin-bottom: 0;
  }
  .simplee-defaultwidget__mobile_info .simplee-defaultwidget__purchase-options{
    margin-bottom:10px;
  }
  .simplee-defaultwidget__labelinfo::after{
    display:none;
  }
  .simplee-defaultwidget__close, .simplee-defaultwidget__labelinfo .simplee-defaultwidget__purchase-options{
    display:block;
  }
}
/* Styles specific to themes */
.simplee_express_msl {
    margin:10px 0px;
}
.simplee_msl_box {
    border: 1px solid currentColor;
    padding: 15px 30px;
    display: inline-flex;
    margin: 10px 0px;
}
.simplee-widget__description {
    margin-top: 20px;
    color: #333232;
}
.price--subscription .price__badge--subscription {
    display: flex;
}
.price_badge_debut--subscription{
   color: var(--color-bg);
    border-color: var(--color-sale-text) !important;
    background-color: var(--color-sale-text) !important;
}
.price__badge--subscription {
    margin-left: 10px;
    align-self: center;
    text-align: center;
    font-size: 0.5em;
    line-height: 1em;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    background-color: var(--color-bg);
    border: 1px solid var(--color-text);
    border-radius: 2px;
    padding: 0.2rem 0.5rem;
}';

        if ($themeName == 'Narrative') {
            $cssCode .= '
        span.price__per-delivery {
            margin-left: 1rem;
            font-size: 1rem;
            font-weight: 400;
        }
    ';
        } else if ($themeName == 'Brooklyn') {
            $cssCode .= '
        span.price__per-delivery {
            font-size: .8rem;
            font-weight: 400;
            color: #a56600;
            display: block;
        }
    ';
        } else if ($themeName == 'Express') {
            $cssCode .= ' .price__per-delivery_express{
      font-size: 1.2rem !important;
        font-weight: 400 !important;
        display: flex !important;
        position: absolute !important;
        left: 0 !important;
        color: var(--color-headings-and-links) !important;
    }
    .price__pricing-group{
      position: relative;
    }
    .price__badge{
      font-size: 1.2rem;
    }
    span.price__badge-express--subscription + .price__per-delivery {
        font-size: 1.2rem;
    }';
        } else {
            $cssCode .= '
        span.price__per-delivery {
            font-size: .8rem;
            font-weight: 400;
            display: flex;
        }
    ';
        }

        $cssCode .= 'span.price__badge-express--subscription.price__badge--sale {
    border: 1px solid;
    font-size: 1.2rem;
    padding: 0.2rem;
}';
        if ($themeName == 'Simple') {
            $cssCode .= '
        .product-single__prices {
            display: flex;
        }
    ';
        }
        if ($themeName == 'Venture' || $themeName == 'Narrative') {
            $cssCode .= '
        .simplee-properties .line-item-property__field input[type=radio i]+label, .simplee-properties .line-item-property__field input[type=checkbox]+label {
            margin-bottom: 0;
        }
    ';
        }

        $cssCode .= '.price_badge_brooklyn--subscription.product-single__price.on-sale {
    font-size: 0.8rem;
}
.badge--simple-subscription{
  width: 100% !important;
}
.price_each--simple{
  color: #333333;
}

.simplee-properties .line-item-property__field span {
    padding: 0 10px;
}
.simplee-properties .line-item-property__field label {
    font-weight: 600;
}
.simplee-properties .line-item-property__field {
    align-items: center;
}
.simplee-properties .line-item-property__field input#checkbox {
    margin-left: 10px;
}
.simplee-properties .line-item-property__field input[type="radio" i] + label {
  font-weight: normal;
}
.sm-error{
    color: red;
}
.simplee-properties .line-item-property__field span {
    display: block;
    padding: 5px 0;
    margin-bottom: 15px;
}
.simplee-properties .memberships_options p {
    margin-top: 13px;
}
.simplee-properties .memberships_options {
    align-items: flex-start;
}
.simplee-properties .line-item-property__field.chkbox {
    margin: 0;
}
.line-item__chkbox{
    margin-bottom: 20px;
}
.line-item__chkbox span {
    padding: 10px;
}
.plan_name {
    display: flex;
    width: 66%;
}
.plan_price {
    text-align: right;
    font-size: 13px;
    width: 34%;
    color: #000;
}
.simplee-defaultwidget .simplee-defaultwidget__checkbox-wrapper, .simplee-defaultwidget__label{
        margin-bottom: 19px;
}
.simplee-properties input:not([type=\'checkbox\']), .simplee-properties select, .simplee-properties textarea {
    width: 100%;
    min-height: 35px;
    background-color: transparent;
    border: 1px solid #000;
    border-radius: 3px;
}
.simplee-properties .line-item-property__field.chkbox label {
    padding-left: 10px;
}
.simplee-properties .line-item-property__field.chkbox input {
    min-height: auto;
}
.sm-error:empty {
    display: none;
}
.simplee-cart-message {
 	width: 100%;
  	border: 2px solid green;
  	border-radius: 10px;
  	text-align: center;
  	color: black;
  	background-color:#e4f3cf;
  	padding: 20px;
  	margin:10px 0px;
}
';
        return $cssCode;
    }
}

// simplee css code
if (!function_exists('getCSSCode')) {
    /**
     * @return string
     */
    function getCSSCode()
    {
        // Asset Simplee CSS
        $cssCode = '/* Simplee Storefront Widget - CSS - Version 0.1
   For questions visit http://support.simplee.best */

.simplee-widget__input input {
    margin-right: 10px;
}
.simplee-widget__input-main, .simplee-widget__input-inner{
    display: flex;
    align-items: center;
}
.simplee-widget__input{
    padding: 15px 10px;
}
.simplee-widget__input-content {
    padding: 0 20px;
}
.simplee-widget__Label{
    margin-bottom: 10px;
}
.simplee-widget__wrapper-inner{
    border: 1px solid #dddddd;
}
.simplee-widget__hr{
    border-bottom: 1px solid #dddddd;
}
.simplee-widget__hidden{
    display: none;
}
.simplee-widget__visible{
    display: block;
}

.fieldset--nested {
    display: block;
    animation: fadeInFromNone 100ms ease-in-out;
    margin-left: 1.8em;
    margin-bottom: 1em;
    padding: 0;
}
fieldset.simplee-widget{
    border: none;
    margin: 0px;
    padding: 27.5px 0px;
}
.simplee-widget fieldset{
    border:none;
    margin-bottom:0;
}
.fieldset.simplee-widget .fieldset__legend {
    font-size: 0.73333em;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    padding: 10px 0;
}
.simplee-widget input[type="radio"]:checked::before {
    background-image: radial-gradient(#000 50%, #fff 60%);
}
.simplee-widget input[type="radio"]::before {
    content: "";
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    border: 0.05em solid #000;
    box-sizing: border-box;
}
.simplee-widget .simplee-widget__input-inner {
    margin-bottom: 10px;
}
.simplee-widget input[type="radio"]:focus{
    outline:none;
}
.simplee-widget input[type="radio"] {
    color: #000;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    position: relative;
    padding: 0;
    border: 0;
    width: 45px;
    height: 45px;
    min-height: 45px;
    cursor: pointer;
    zoom: 0.35;
    margin-right: 25px;
}
.simplee-widget select {
    width: 100% !important;
}


/* Styles specific to themes */
.simplee_express_msl {
    margin:10px 0px;
}
.simplee_msl_box {
    border: 1px solid currentColor;
    padding: 15px 30px;
    display: inline-flex;
    margin: 10px 0px;
}
.simplee-widget__description {
    margin-top: 20px;
    color: #333232;
}
.price--subscription .price__badge--subscription {
    display: flex;
}
.price_badge_debut--subscription{
   color: var(--color-bg);
    border-color: var(--color-sale-text) !important;
    background-color: var(--color-sale-text) !important;
}
.price__badge--subscription {
    margin-left: 10px;
    align-self: center;
    text-align: center;
    font-size: 0.5em;
    line-height: 1em;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    background-color: var(--color-bg);
    border: 1px solid var(--color-text);
    border-radius: 2px;
    padding: 0.2rem 0.5rem;
}
.price_badge_minimal--subscription, .price_badge_narrative--subscription{
  font-size: 16.8px;
}
.price_badge_minimal--subscription + .product-single__sale-price {
  color: #241f1f;
}
.price_badge_brooklyn--subscription{
  font-size: 11.8px;
}
.simplee_price-item-express--sale{
    margin: 0 1rem;
    font-weight: var(--font-body-weight-normal);
}
.price__badge-express--subscription {
    align-self: center;
    text-align: center;
    font-size: .8rem;
    line-height: 1em;
    font-weight: var(--font-body-weight-bold);
    text-transform: uppercase;
    letter-spacing: .1em;
    background-color: var(--color-background);
    border: 1px solid transparent;
    border-radius: .25rem;
    padding: .6rem .7rem;
    color: var(--color-sale-price);
    border-color: var(--color-sale-price) !important
}
.simplee_price-item-supply--sale{
    margin: 0 1rem;
}
.price__pricing-group + .price__per-delivery {
    font-size: 1.3rem;
}

.simplee-properties .line-item-property__field span {
    padding: 0 10px;
}
.simplee-properties .line-item-property__field label {
    padding: 0 10px;
    font-weight: 600;
}
.simplee-properties .line-item-property__field {
    display: flex;
    align-items: center;
}
.simplee-properties .line-item-property__field input#checkbox {
    margin-left: 10px;
}
.simplee-properties .line-item-property__field input[type="radio" i] + label {
  font-weight: normal;
}
.sm-error{
    color: red;
}
.simplee-properties .line-item-property__field span {
    display: block;
    padding: 5px 0;
    margin-bottom: 15px;
}
.simplee-properties .memberships_options p {
    margin-top: 13px;
}
.simplee-properties .memberships_options {
    align-items: flex-start;
}
.simplee-properties .line-item-property__field.chkbox {
    margin: 0;
}
.line-item__chkbox{
    margin-bottom: 20px;
}
.line-item__chkbox span {
    padding: 10px;
}
.simplee-cart-message {
 	width: 100%;
  	border: 2px solid green;
  	border-radius: 10px;
  	text-align: center;
  	color: black;
  	background-color:#e4f3cf;
  	padding: 20px;
  	margin:10px 0px;
}
';

        if ($themeName == 'Venture' || $themeName == 'Narrative') {
            $cssCode .= '
        .simplee-properties .line-item-property__field input[type=radio i]+label, .simplee-properties .line-item-property__field input[type=checkbox]+label {
            margin-bottom: 0;
        }
    ';
        }

        return $cssCode;
    }
}

//simplee js
if (!function_exists('addJSAsset')) {
    /**
     * @param $theme_id
     * @param $user_id
     * @param $constants
     * @param $theme_name
     * @param $version
     */
    function addJSAsset($theme_id, $user_id, $constants, $theme_name, $version)
    {
        try {
            \Log::info('-----------------------START :: addJSAsset -----------------------');
            $user = User::where('id', $user_id)->first();
            $cssCode = ($version == 'default') ? getSimpleeDefaultJSCode($constants, $theme_name) : getJSCode($constants, $theme_name);

            $parameter['asset']['key'] = 'assets/' . config('const.ASSETS.JS') . '.js';
            $parameter['asset']['value'] = $cssCode;
            $asset = $user->api()->rest('PUT', 'admin/themes/' . $theme_id . '/assets.json', $parameter);

            \Log::info(json_encode($asset));
        } catch (\Exception $e) {
            \Log::info('-----------------------ERROR :: addJSAsset -----------------------');
            \Log::info(json_encode($e));
        }
    }
}

//simplee js
if (!function_exists('getSimpleeDefaultJSCode')) {
    /**
     * @param $constants
     * @param $theme_name
     * @return string
     */
    function getSimpleeDefaultJSCode($constants, $theme_name)
    {
        try {
            \Log::info('-----------------------START :: getSimpleeDefaultJSCode -----------------------');
            $jsonThemes = config('const.JSON_THEMES');

            // Asset Simplee JS
            $jsCode = '
                // Simplee Storefront Widget - Script - Version 0.1
// For questions visit http://support.simplee.best

var simpleeSelectors = {};
var simpleeClasses = {};

var simpleeWidget = (function () {
    function simpleeWidget() {
        simpleeSelectors = {
          sellingPlanGroupContainer: ".simplee-widget__planGroup--container",
          sellingPlanOptionContainer: "#simplee-defaultwidget__options_grid",
          sellingPlanOptionContainerDelivery_chks: "#simplee-defaultwidget__options_delivery_chks",
          sellingPlanOptionContainerBill_chks: "#simplee-defaultwidget__options_bill_chks",
          sellingPlanOptions: ".simplee-widget__sellingPlan-options",
          widget: ".simplee-defaultwidget",
          sellingPlanIdInput: ".simplee-selling-plan-id-input",
          productForm: \'form[action="/cart/add"]\',
          variantIdInput: \'[name="id"]\',
          variantSelector: ["#shappify-variant-id", ".single-option-selector", "select[name=id]", "input[name=id]"],
          pageTemplate: ".simplee-page-template",
          productJson: ".simplee-product-json",
          moneyFormat: ".simplee-money-format",
          sellingPlanOptionName: ".simplee-widget_sellingPlan_option_name",
          perDeliveryPrice: ".price-item",
          perPriceBadge: ".data-subscription-badge",
        };

    simpleeClasses = {
        hidden: "simplee-widget__hidden",
           visible: "simplee-widget__visible",
        };

        this.products = {};
        this.variants = {};
        this.sellingPlanGroups = {};
        this.pageTemplate = "";
        this.productId = {};
    }

simpleeWidget.prototype = Object.assign({}, simpleeWidget.prototype, {
    init: function () {
        this._handleRequired();';
            if (in_array($theme_name, $jsonThemes)) {
                $jsCode .= '
			this._addFormAttr();
			';
            }
            $jsCode .= '},

_handleRequired: function(){
       let forms = document.getElementsByTagName(\'form\');
        let base = this;
        for(i=0; i<forms.length; i++){
          let action =  forms[i].action;
          if(action.includes("/cart/add")){
//              remove novalidate attribute
              forms[i].removeAttribute("novalidate");

               forms[i].addEventListener("submit", function(e) {

                event.preventDefault();
                event.stopPropagation();
                event.stopImmediatePropagation(); // Prevents form from submitting

                if(base._validate()){';
            if (!in_array($theme_name, $jsonThemes)) {
                $jsCode .= '
                        this.submit();
                    ';
            }

            $jsCode .= '}

                  });
                }
            }
        },

    _validate: function(){
        let okayOrNot = true;
                let isErr = false;
                let requireds = document.getElementsByClassName(\'required\');
                for(j=0; j<requireds.length; j++){
                  let fieldType = requireds[j].type;
                  let fieldClass = requireds[j].id;
                  let fieldVal = \'\';

                  if(fieldType == \'text\' || fieldType == \'textarea\' || fieldType == \'checkbox\'){
                    if(fieldType == \'checkbox\'){
                     const cb = requireds[j].checked;
                      fieldVal = (cb) ? \'Yes\' : \'\';
                    }else{
                      fieldVal = requireds[j].value;
                    }
                    if(fieldVal == \'\'){
                      isErr = true;
                      document.getElementsByClassName(fieldClass)[0].innerHTML = \'This field is required.\';
                    } else{
                      document.getElementsByClassName(fieldClass)[0].innerHTML = \'\';
                    }
                  }
                }

                if(!isErr){
                  return true;
                }
                return false;
    },';

            if (in_array($theme_name, $jsonThemes)) {
                $jsCode .= '
		_addFormAttr: function(){
	      	  let sectionID = document.getElementsByTagName(\'section\')[0].getAttribute(\'id\');
	          let filterId = sectionID.replace(/\D/g, \'\');



	          let simpleeProperties = document.getElementsByClassName(\'simplee-properties\');
	          if(simpleeProperties.length > 0){
	            let attrValue = \'product-form-template--\'+filterId+\'__main\';
	            for(i=0; i<simpleeProperties.length; i++){
	            	let container = simpleeProperties[i];
	              	let tags = [\'input\', \'textarea\', \'select\'];

	                for(k=0; k<tags.length; k++){
						let inputes = container.getElementsByTagName(tags[k]);
	                    for(j=0; j<inputes.length; j++){
	                        inputes[j].setAttribute(\'form\', attrValue);
	                    }
	                }
	            }
	          }
	      }';
            }
            $jsCode .= '
    })
    return simpleeWidget;
    })();

        document.addEventListener("DOMContentLoaded", function () {
            window.Simplee = window.Simplee || {};
            window.Simplee.simpleeWidget = new simpleeWidget();
            window.Simplee.simpleeWidget.init();
        });
            ';
            return $jsCode;
        } catch (\Exception $e) {
            \Log::info('-----------------------ERROR :: getSimpleeDefaultJSCode -----------------------');
            \Log::info(json_encode($e));
        }
    }
}


// simplee default js code
if (!function_exists('getDefaultJSCode')) {
    /**
     * @param $constants
     * @param $theme_name
     * @return string
     */
    function getDefaultJSCode($constants, $theme_name)
    {
        // Asset Simplee JS
        $jsCode = '// Simplee Storefront Widget - Script - Version 0.1
// For questions visit http://support.simplee.best

var simpleeSelectors = {};
var simpleeClasses = {};

var simpleeWidget = (function () {
    function simpleeWidget() {
        simpleeSelectors = {
          sellingPlanGroupContainer: ".simplee-widget__planGroup--container",
          sellingPlanOptionContainer: "#simplee-defaultwidget__options_grid",
          sellingPlanOptionContainerDelivery_chks: "#simplee-defaultwidget__options_delivery_chks",
          sellingPlanOptionContainerBill_chks: "#simplee-defaultwidget__options_bill_chks",
          sellingPlanOptions: ".simplee-widget__sellingPlan-options",
          widget: ".simplee-defaultwidget",
          sellingPlanIdInput: ".simplee-selling-plan-id-input",
          productForm: \'form[action="/cart/add"]\',
          variantIdInput: \'[name="id"]\',
          variantSelector: ["#shappify-variant-id", ".single-option-selector", "select[name=id]", "input[name=id]"],
          pageTemplate: ".simplee-page-template",
          productJson: ".simplee-product-json",
          moneyFormat: ".simplee-money-format",
          sellingPlanOptionName: ".simplee-widget_sellingPlan_option_name",
          perDeliveryPrice: "' . $constants['PRICE_SALE'] . '",
          perPriceBadge: "' . $constants['PRICE_BADGE_SALE'] . '",
        };

    simpleeClasses = {
        hidden: "simplee-widget__hidden",
           visible: "simplee-widget__visible",
        };

        this.products = {};
        this.variants = {};
        this.sellingPlanGroups = {};
        this.pageTemplate = "";
        this.productId = {};
    }

simpleeWidget.prototype = Object.assign({}, simpleeWidget.prototype, {
    init: function () {
        this._parsePageTemplate();
        this._parseProductJson();
        this._addVariantChangeListener();
        this._handleRequired();
    },

    handleSellingPlanGroupChange: function (event = null) {
        let groupRadioEl = null;
        if (event == null)
          groupRadioEl = document.querySelector(`input[name=simplee-sellingPlanGroup-radio]:checked`);
        else
          groupRadioEl = event.target;

        var groupId = groupRadioEl.value;
        let groupOptionWidget = groupRadioEl.parentNode.nextElementSibling;
        let planGroupContainer = document.querySelectorAll(simpleeSelectors.sellingPlanGroupContainer);
        var widget = groupRadioEl.closest(simpleeSelectors.widget);

          if (groupId === "once") {
              let oncePrice = groupRadioEl.getAttribute("data-price");
              this._setSellingPlanIdInput(widget, "", "", oncePrice);
              let oldEl = widget.querySelector(simpleeSelectors.sellingPlanOptionContainer);
              oldEl.innerHTML = "";
              return;
          }
          this._SetGroupChilds(widget, groupId);
    },

    handleSellingPlanChange: function (event) {
        var planRadioEl = event.target;
        var widget = planRadioEl.closest(simpleeSelectors.widget);

        groupId = widget.querySelector(`input[name=simplee-sellingPlanGroup-radio]:checked`).value;
        var sellingPlan = this._getActiveSellingPlanId(widget, groupId);
        localStorage.setItem("selectedOption", planRadioEl.value);
        this._setSellingPlanIdInput(widget, sellingPlan, groupId, "");
    },
    handleMultiOptions: function (event){
        var planRadioEl = event.target;
        var widget = planRadioEl.closest(simpleeSelectors.widget);

        var selectedPlanValue = event.target.value; // i.e. Deliver every-1 Month
        var selectedOptionName = event.target.parentNode.getAttribute("data-value");

        groupId = widget.querySelector(`input[name=simplee-sellingPlanGroup-radio]:checked`).value;

        this._SetMultiOptionChilds(widget, groupId, selectedOptionName, selectedPlanValue);
    },

    _SetMultiOptionChilds(widget, groupId, selectedOptionName, selectedPlanValue){
        var multiOptions = selectedOptionName.split(","); // i.e. array ("Deliver every", "Bill every")

        var selectedOption = selectedPlanValue.replace(multiOptions[0] + "-", ""); // selected option value i.e. 1 month
        var sellingPlanGroup = this._getSelectedSellingPlanGroup(groupId);
        var planOptions = sellingPlanGroup.options;
        let currentOption = planOptions.find(option => option.name == selectedOptionName)

        var optionValues = currentOption.values;

        let optionArr = [];
        optionValues.forEach(function(el){
            let ElArr = el.split(",");
            if(typeof optionArr[ElArr[0]] === "undefined") {
                optionArr[ElArr[0]] = [];
            }
             optionArr[ElArr[0]].push(ElArr[1]);
        });

        let localSOption = localStorage.getItem("selectedOption");
        let secondOption = optionArr[selectedOption];
        let chkSecondHtmlWrapper = "";
        let j = 0;
        let checked = "";
        let idName = selectedOption;
        for (var key in secondOption) {
         let sendIdName = idName + \',\' + secondOption[key];
          let sPlan = this._getPlanDescription(groupId, sendIdName);
          if(sPlan[0] == sendIdName){
            if( localSOption == sendIdName ){
              checked = \'checked\';
            }
          }else{
            if( j == 0 ){
              checked = \'checked\';
            }else{
              checked = "";
            }
          }

          if(checked == \'checked\'){
            localStorage.setItem("selectedOption", sendIdName);
          }

          chkSecondHtmlWrapper = this._setOptionOneHtml(chkSecondHtmlWrapper, secondOption[key], groupId, sendIdName, checked, \'\', selectedOptionName, \'handleSellingPlanChange\', sPlan[1]);
          j++;
        }
        let chkSecondWrapper = widget.querySelector(simpleeSelectors.sellingPlanOptionContainerBill_chks);
        chkSecondWrapper.innerHTML = chkSecondHtmlWrapper;

        var sellingPlan = this._getActiveSellingPlanId(widget, groupId);

        this._setSellingPlanIdInput(widget, sellingPlan, groupId, "");
    },
    _SetGroupChilds(widget, groupId){
        var sellingPlanGroup = this._getSelectedSellingPlanGroup(groupId);

        var groupOptions = sellingPlanGroup.options;

        let displayOptions = [];

        var selectedSellingPlan = document.getElementById("simplee-selling-plan-id").value;
        var selectedOption = localStorage.getItem("selectedOption");
        let base = this;
        let htmlWrapper = \'\';
        let sPlan = [];
        groupOptions.forEach(function(groupOptionsEl, groupOptionsElIndex){
          let optionName = groupOptionsEl.name;
          let optionNameArr = optionName.split(",");

          let optionValues = groupOptionsEl.values;
          let checked = \'\';

            htmlWrapper += base._setOptionWrapperHtml(htmlWrapper, optionNameArr[0], \'simplee-defaultwidget__options_delivery_chks\', \'first\');
            if( optionNameArr.length == 2 ){
               htmlWrapper = base._setOptionWrapperHtml(htmlWrapper, optionNameArr[1], \'simplee-defaultwidget__options_bill_chks\', \'second\');
            }

            let oldEl = widget.querySelector(simpleeSelectors.sellingPlanOptionContainer);
            oldEl.innerHTML = htmlWrapper;

            if(optionNameArr.length == 1){
              //            for single option
              let chkHtmlWrapper = "";
              optionValues.forEach(function(optionValueEl, optionValueElIndex){

                sPlan = base._getPlanDescription(groupId, optionValueEl);

                if( selectedSellingPlan == \'\' && optionValueElIndex == 0  ){
                    checked = \'checked\';
                }
                if(sPlan[0] == selectedSellingPlan){
                  if( selectedOption == optionValueEl ){
                    checked = \'checked\';
                  }
                }else{
                  if( optionValueElIndex == 0 ){
                    checked = \'checked\';
                  }else{
                    checked = \'\';
                  }
                }

                if(checked == \'checked\'){
                    localStorage.setItem("selectedOption", optionValueEl);
                }
//                 checked = (optionValueElIndex == 0) ? \'checked\' : \'\';
                chkHtmlWrapper = base._setOptionOneHtml(chkHtmlWrapper, optionValueEl, groupId, optionValueEl, checked, \'\', optionName, \'handleSellingPlanChange\', sPlan[1]);
                //               end of optionValues
              });
              let chkWrapper = widget.querySelector(simpleeSelectors.sellingPlanOptionContainerDelivery_chks);
              chkWrapper.innerHTML = chkHtmlWrapper;
            }else{
              //           for multiple options
              let optionArr = [];
                optionValues.forEach(function(el){
                  sPlan = base._getPlanDescription(groupId, el);
                  let ElArr = el.split(",");
                  if(typeof optionArr[ElArr[0]] === "undefined") {
                    optionArr[ElArr[0]] = [];
                  }
                  optionArr[ElArr[0]].push(ElArr[1]);
                });

              // first option
                let chkHtmlWrapper = \'\';
                let i = 0;
                let secondOption = [];
                let idName = \'\';

                for (var key in optionArr) {
                 if(sPlan[0] == selectedSellingPlan){
                    let selectFirst = selectedOption.split(\',\');
                    if( selectFirst[0] == key ){
                      checked = \'checked\';
                      idName = key;
                      secondOption = optionArr[key];
                    }
                  }else{
                    if( i == 0 ){
                      secondOption = optionArr[key];
                      idName = key;
                      checked = \'checked\';
                    }else{
                      checked = \'\';
                    }
                  }

                  let keyvalue = optionNameArr[0] + \'-\' + key;
                  let optName = "simplee-sellingPlan-"+ optionNameArr[0] +"-Option-" + groupId;


                  chkHtmlWrapper = base._setOptionOneHtml(chkHtmlWrapper, key, groupId, keyvalue, checked, optName, optionName, \'handleMultiOptions\', \'\');
                  i++;
                }
                let chkWrapper = widget.querySelector(simpleeSelectors.sellingPlanOptionContainerDelivery_chks);

                chkWrapper.innerHTML = chkHtmlWrapper;

              // second option
                let chkSecondHtmlWrapper = \'\';
                let j = 0;
                for (var key in secondOption) {
                  let sendIdName = idName + \',\' + secondOption[key];
                  sPlan = base._getPlanDescription(groupId, sendIdName);
                  if(sPlan[0] == selectedSellingPlan){
                    if( selectedOption == sendIdName ){
                      checked = \'checked\';
                    }else{
                      if( j == 0 ){
                        checked = \'checked\';
                      }
                    }
                  }else{
                    if( j == 0 ){
                      checked = \'checked\';
                    }else{
                      checked = \'\';
                    }
                  }

                  if(checked == \'checked\'){
                      localStorage.setItem("selectedOption", sendIdName);
                  }

                  chkSecondHtmlWrapper = base._setOptionOneHtml(chkSecondHtmlWrapper, secondOption[key], groupId, sendIdName, checked, \'\', optionName, \'handleSellingPlanChange\', sPlan[1]);
                  j++;
                }
                let chkSecondWrapper = widget.querySelector(simpleeSelectors.sellingPlanOptionContainerBill_chks);
                chkSecondWrapper.innerHTML = chkSecondHtmlWrapper;
            }
        });

        var sellingPlan = this._getActiveSellingPlanId(widget, groupId);
        this._setSellingPlanIdInput(widget, sellingPlan, groupId, "");
    },
    _setOptionOneHtml: function(chkHtmlWrapper, displayName, groupId, idName, checked, optName = \'\', optionName, changeAction, description){
      // add checkboxes
      let formatDisplName = idName.replace(/ /g, \'-\');
      formatDisplName = formatDisplName.replace(/,/g, \'-\');
      let id = "sellingPlan-Option-product-template-" + groupId + "-" + formatDisplName;

      let name = ( optName == \'\' ) ? \'simplee-sellingPlan-Option-\' + groupId : optName;
      chkHtmlWrapper += \'<div class="simplee-defaultwidget__checkbox-wrapper" data-value="\'+ optionName +\'">\';
      chkHtmlWrapper += \'<input type="radio"\' +
                        \'name="\'+ name +\'"\' +
                        \'id="\'+ id +\'"\' +
                        \'value="\'+ idName + \'" \'+ checked + \' \' +
                        \'onchange="window.Simplee.simpleeWidget.\'+ changeAction +\'(event)" />\';
      chkHtmlWrapper += \'<label for="\'+ id +\'" class="simplee-defaultwidget__radio"> \'+ displayName +\' </label>\';

      if(typeof description != \'undefined\' && description != \'\'){
        chkHtmlWrapper += \'<div class="simplee-defaultwidget__info">\';
        chkHtmlWrapper += \'<svg width="9" height="9" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"\' +
                          \'viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">\';
        chkHtmlWrapper += \'<g>\';
        chkHtmlWrapper += \'<path d="M256,0C114.833,0,0,114.833,0,256s114.833,256,256,256s256-114.833,256-256S397.167,0,256,0z M245.333,426.667\' +
              \'c-17.646,0-32-14.354-32-32s14.354-32,32-32c17.646,0,32,14.354,32,32S262.979,426.667,245.333,426.667z M277.333,296.542v34.125\' +
              \'c0,5.896-4.771,10.667-10.667,10.667H224c-5.896,0-10.667-4.771-10.667-10.667v-53.333c0-23.521,19.146-42.667,42.667-42.667\' +
              \'s42.667-19.146,42.667-42.667S279.521,149.333,256,149.333S213.333,168.479,213.333,192v10.667\' +
              \'c0,5.896-4.771,10.667-10.667,10.667H160c-5.896,0-10.667-4.771-10.667-10.667V192c0-58.813,47.854-106.667,106.667-106.667\' +
              \'S362.667,133.188,362.667,192C362.667,243.188,326.604,286.563,277.333,296.542z" fill="#CECECED8"/>\';
        chkHtmlWrapper += \'</g>\'

        chkHtmlWrapper += \'</svg>\';
        chkHtmlWrapper += \'<div class="simplee-defaultwidget__tooltipText"><p>\'+ description +\'</p></div>\';
        chkHtmlWrapper += \'</div>\';
      }

      chkHtmlWrapper += \'</div>\';
      chkHtmlWrapper += \'</div>\';
      return chkHtmlWrapper;
    },

    _setOptionWrapperHtml: function(htmlWrapper, displayName, optionChkId, optionPosition){
        htmlWrapper += (optionPosition == "first") ? \'<div class="grid__item medium-up--two-quarters">\' : \'<div class="grid__item medium-up--two-quarters simplee-defaultwidget__hr">\';
        htmlWrapper += \'<div class="simplee-defaultwidget__bill">\';
        htmlWrapper += \'<div class="simplee-defaultwidget__time-wrapper">\';

        //                        add label
        htmlWrapper += \'<div class="simplee-defaultwidget__label">\';
        htmlWrapper += \'<h5 class="simplee-defaultwidget__purchase-options">\'+ displayName +\':</h5>\';
        htmlWrapper += \'</div>\';

        //                                      checkbox wrapper
        htmlWrapper += \'<div class="simplee-defaultwidget__checkbox" id="\'+ optionChkId +\'">\';
        htmlWrapper += \'</div>\';

        htmlWrapper += \'</div>\';
        htmlWrapper += \'</div>\';
        htmlWrapper += \'</div>\';
        return htmlWrapper;
    },

    _setSellingPlanIdInput: function (widget, sellingPlan, groupId, oncePrice) {
        var sellingPlanIdInput = widget.querySelector(simpleeSelectors.sellingPlanIdInput);
        var variantId = this._getVariantId(widget);
        sellingPlanIdInput.value = (typeof sellingPlan.id != \'undefined\') ? sellingPlan.id : "";
        if (/.*(product).*/.test(this.pageTemplate)) {
            this._updateHistoryState(variantId, sellingPlan);
        }

        this._updateVariantPriceBadge(variantId, sellingPlan, groupId, oncePrice);
    },
    _updateVariantPriceBadge(variantId, sellingPlan, groupId, oncePrice){
        var variants = this.products[this.productId].variants;
        var regularPrice = this.products[this.productId].compare_at_price;
        var currentVariant =  variants.find(function(vari) {
           return vari.id == variantId
        });

        var sellingPlanGroup = this._getSelectedSellingPlanGroup(groupId);
        var sellingPlanAllocation = sellingPlan
        ? this._getVariantSellingPlan(currentVariant, sellingPlan)
        : false;

        if (!currentVariant) {
          return;
        }

        //         update price

      var priceEl = document.querySelector(simpleeSelectors.perDeliveryPrice);

      var productType = document.getElementById("simplee_sale_product").value;

      var isPrepaid = (sellingPlanAllocation.price != sellingPlanAllocation.per_delivery_price);

      var regularFormattedPrice = this._formatSimpleeMoney(sellingPlanAllocation.compare_at_price);

      var calculatePrice = (isPrepaid) ? sellingPlanAllocation.price : sellingPlanAllocation.per_delivery_price;
      var price = ( oncePrice == "" ) ? calculatePrice : oncePrice;

      var formattedPrice = this._formatSimpleeMoney(price);
      var priceDiff = (sellingPlanAllocation.compare_at_price - calculatePrice);
      var formattedBadgePrice = this._formatSimpleeMoney(priceDiff);';

        if ($theme_name == 'Venture') {
            //     $jsCode .= '
            //     if(productType == "regular" && oncePrice == "") {
            //       if( priceDiff != 0 ){
            //         let html = "<div class=\'product-tag\'> Subscription · save "+ formattedBadgePrice +"</div>" + formattedPrice + " " + "<s>" + regularFormattedPrice + "</s>";

            //         html += (isPrepaid) ? \'<span class="price__per-delivery" id="per_delivery">\'+ this._formatSimpleeMoney(sellingPlanAllocation.per_delivery_price) +\'/each</span>\' : \'\';
            //         priceEl.innerHTML = html;
            //       }else{
            //          priceEl.innerHTML = this._formatSimpleeMoney(price);
            //       }
            //     } else{
            //       document.querySelector(".product-tag").innerHTML = (oncePrice == "" && priceDiff != 0) ? "Subscription · save " + formattedBadgePrice : "SALE";

            //       let prepay = (isPrepaid) ? \'<span class="price__per-delivery" id="per_delivery">\'+ this._formatSimpleeMoney(sellingPlanAllocation.per_delivery_price) +\'/each</span>\' : \'\';
            //       const prepayEach = document.querySelector(\'#ProductSaleTag-product-template\');

            //       if(prepayEach.childElementCount > 1 ){
            //         let d_nested = document.getElementById("per_delivery");
            //         let throwawayNode = prepayEach.removeChild(d_nested);
            //       };

            //       document.getElementById("ProductSaleTag-product-template").innerHTML += prepay;


            //       priceEl.innerHTML = formattedPrice;
            //       if(oncePrice == "" && priceDiff != 0){
            //         document.querySelector(\'.product-single__price--compare\').innerHTML = regularFormattedPrice;
            //       }

            //     }
            // ';
            $jsCode .= '
                 if(productType == "regular" && oncePrice == "") {
                    document.getElementsByClassName(\'product-single__price\')[0].innerHTML = formattedPrice;
                  }else{
                    document.getElementsByClassName(\'product-single__price\')[0].innerHTML = formattedPrice;
                    document.getElementById(\'ComparePrice-product-template\').style.display = \'none\';
                  }
            ';
        } elseif ($theme_name == 'Express') {
            //     $jsCode .= 'if(productType == "regular" && oncePrice == "") {
            //             let html = "";
            //             if( priceDiff != 0 ){
            //               html += "<s>" + regularFormattedPrice + "</s><span class=\'price-item--sale simplee_price-item-express--sale\'>" + formattedPrice + " " + "</span><span class=\'price__badge-express--subscription price__badge--sale\' aria-hidden=\'true\'> Subscription · save " + formattedBadgePrice + "</span>";
            //             }else{
            //                  html += "<span class=\'price-item--sale simplee_price-item-express--sale\'>" + formattedPrice + " " + "</span>";
            //             }
            //               html += (isPrepaid) ? \'<span class="price__per-delivery">\'+ this._formatSimpleeMoney(sellingPlanAllocation.per_delivery_price) +\'/each</span>\' : \'\';
            //               priceEl.innerHTML = html;
            //             } else if( ( productType == "regular" && oncePrice != "" ) || (productType == "regular" && priceDiff == 0)){
            //                 priceEl.innerHTML = this._formatSimpleeMoney(oncePrice);

            //             }else{
            //                let html = formattedPrice;
            //                html += (isPrepaid) ? \'<span class="price__per-delivery price__per-delivery_express">\'+ this._formatSimpleeMoney(sellingPlanAllocation.per_delivery_price) +\'/each</span>\' : \'\';
            //               document.querySelector(".price-item--sale").innerHTML = html;
            //               let allRegulatEl= document.querySelectorAll(".price-item--regular");

            //               for( var i=0; i<allRegulatEl.length; i++ ){
            //                 allRegulatEl[i].innerHTML = (oncePrice == "" && priceDiff != 0) ? regularFormattedPrice : this._formatSimpleeMoney(regularPrice);
            //               }
            //             document.querySelector(".price__badge--sale").innerHTML = (oncePrice == "" && priceDiff != 0) ? "Subscription · save " + formattedBadgePrice : "SALE";
            // }';
            $jsCode .= '
                if(productType == "regular" && oncePrice == "") {
                    document.getElementsByClassName(\'price-item--regular\')[0].innerHTML = formattedPrice;
                }else{
                    document.getElementsByClassName(\'price__sale\')[0].innerHTML = formattedPrice;
                }
            ';
        } elseif ($theme_name == 'Minimal') {
            // $jsCode .= '
            //     if(productType == "regular" && oncePrice == "") {
            //        let html = \'\';
            //        html += "<div class=\'price_badge_minimal--subscription\'> Subscription · save "+ formattedBadgePrice +"</div>" + formattedPrice + " " + "<s id=\'ComparePrice\' class=\'product-single__sale-price\'>" + regularFormattedPrice + "</s>";
            //        html += (isPrepaid) ? \'<span class="price__per-delivery">\'+ this._formatSimpleeMoney(sellingPlanAllocation.per_delivery_price) +\'/each</span>\' : \'\';
            //        priceEl.innerHTML = html;
            //     } else if((productType == "sale" && oncePrice == "") || (productType == "regular" && priceDiff == 0)){
            //       let html = \'\';
            //       html += (priceDiff != 0) ? "<div class=\'price_badge_minimal--subscription\'> Subscription · save "+ formattedBadgePrice +"</div>" + formattedPrice : formattedPrice;
            //       html += (isPrepaid) ? \'<span class="price__per-delivery">\'+ this._formatSimpleeMoney(sellingPlanAllocation.per_delivery_price) +\'/each</span>\' : \'\';
            //       priceEl.innerHTML = html;
            //       document.querySelector(".product-single__sale-price").innerHTML = (priceDiff != 0) ? this._formatSimpleeMoney(sellingPlanAllocation.compare_at_price) : this._formatSimpleeMoney(regularPrice);
            //     }else{
            //       priceEl.innerHTML = formattedPrice;
            //     }
            // ';
            $jsCode .= '
                document.getElementsByClassName(\'product-single__price\')[0].innerHTML = formattedPrice;
                if(productType == "regular" && oncePrice == "") {
                }else{
                    document.getElementsByClassName(\'product-single__sale-price\')[0].style.display = \'none\';
                }
            ';
        } elseif ($theme_name == 'Supply') {
            //     $jsCode .= '
            //     if(productType == "regular" && oncePrice == "" && priceDiff != 0) {
            //       let html = \'\';
            //       html += "<s>" + regularFormattedPrice + "</s><span class=\'price-item--sale simplee_price-item-supply--sale\'>" + formattedPrice + " " + "</span><span class=\'sale-tag large\' aria-hidden=\'true\'> Subscription · save " + formattedBadgePrice + "</span>";
            //       html += (isPrepaid) ? \'<span class="price__per-delivery">\'+ this._formatSimpleeMoney(sellingPlanAllocation.per_delivery_price) +\'/each</span>\' : \'\';
            //       priceEl.innerHTML = html;
            //     } else if( productType == "regular" && oncePrice != "" ){
            //       priceEl.innerHTML = this._formatSimpleeMoney(oncePrice);
            //     }else{
            //       let html = \'\';
            //       html += (productType == "sale" && oncePrice == "" && priceDiff != 0) ? "<s>" + regularFormattedPrice + "</s><span class=\'price-item--sale simplee_price-item-supply--sale\'>" + formattedPrice + "</span>" : "<span class=\'price-item--sale\'>" + formattedPrice + "</span>";
            //       html += (isPrepaid) ? \'<span class="price__per-delivery">\'+ this._formatSimpleeMoney(sellingPlanAllocation.per_delivery_price) +\'/each</span>\' : \'\';
            //       priceEl.innerHTML = html;
            //       document.querySelector(".sale-tag").innerHTML = (oncePrice == "" && priceDiff != 0) ? "Subscription · save " + formattedBadgePrice : "SALE";
            //     }
            // ';
            $jsCode .= '
                document.getElementById(\'productPrice-product-template\').innerHTML = formattedPrice;
            ';
        } elseif ($theme_name == 'Narrative') {
            //     $jsCode .= '
            //     if(productType == "regular" && oncePrice == "") {
            //     let html = \'\';

            //     if(priceDiff != 0){
            //          html = "<div class=\'price_badge_narrative--subscription\'> Subscription · save " + formattedBadgePrice
            //          html += (isPrepaid) ? \'<span class="price__per-delivery">\'+ this._formatSimpleeMoney(sellingPlanAllocation.per_delivery_price) +\'/each</span>\' : \'\';
            //          html += "</div>" + formattedPrice + " " + "<s id=\'ComparePrice\' class=\'product-single__sale-price\'>" + regularFormattedPrice + "</s>";
            //     }else{
            //         html = formattedPrice;
            //     }
            //         priceEl.innerHTML = html;
            //     } else if(productType == "sale" && oncePrice == ""){
            //        let html = \'\';
            //       if(priceDiff != 0){
            //          html = "<div class=\'price_badge_narrative--subscription\'> Subscription · save " + formattedBadgePrice
            //          html += (isPrepaid) ? \'<span class="price__per-delivery">\'+ this._formatSimpleeMoney(sellingPlanAllocation.per_delivery_price) +\'/each</span>\' : \'\';
            //          html += "</div>" + formattedPrice;
            //       }else{
            //           html = formattedPrice;
            //       }
            //       priceEl.innerHTML = html;
            //       document.querySelector(\'.product__compare-price\').innerHTML = (priceDiff != 0) ? regularFormattedPrice : this._formatSimpleeMoney(regularPrice);

            //     }else{
            //       priceEl.innerHTML = (priceDiff != 0 && oncePrice == "") ? this._formatSimpleeMoney(sellingPlanAllocation.compare_at_price) : formattedPrice;

            //     }
            // ';
            $jsCode .= '
                document.getElementsByClassName(\'product__current-price\')[0].innerHTML = formattedPrice;
                if(productType == "regular" && oncePrice == "") {
                }else{
                    document.getElementsByClassName(\'product__compare-price\')[0].style.display = \'none\';
                }
            ';
        } elseif ($theme_name == 'Brooklyn') {
            //     $jsCode .= '
            //     if(productType == "regular" && oncePrice == "") {
            //     let html =  "<s id=\'ComparePrice\'>" + regularFormattedPrice + "</s> <span class=\'product-single__price on-sale\'>" + formattedPrice + "</span> " + "<div class=\'price_badge_brooklyn--subscription product-single__price on-sale\'> Subscription · save "+ formattedBadgePrice +"</div>";
            //     html += (isPrepaid) ? \'<span class="price__per-delivery">\'+ this._formatSimpleeMoney(sellingPlanAllocation.per_delivery_price) +\'/each</span>\' : \'\';
            //     priceEl.innerHTML = html;
            //   } else if(productType == "sale" && oncePrice == ""){
            //     let html =   (priceDiff != 0) ? formattedPrice + "<div class=\'price_badge_brooklyn--subscription product-single__price on-sale\'> Subscription · save "+ formattedBadgePrice +"</div>" : formattedPrice;
            //     html += (isPrepaid) ? \'<span class="price__per-delivery">\'+ this._formatSimpleeMoney(sellingPlanAllocation.per_delivery_price) +\'/each</span>\' : \'\';
            //     priceEl.innerHTML = html;
            //     document.querySelector("#ComparePrice").innerHTML = (priceDiff != 0) ? regularFormattedPrice : this._formatSimpleeMoney(regularPrice);
            //   }else{
            //     priceEl.innerHTML = formattedPrice;
            //   }
            // ';
            $jsCode .= '
                document.getElementsByClassName(\'product-single__price\')[0].innerHTML = formattedPrice;
                  if(productType == "regular" && oncePrice == "") {
                }else{
                    document.getElementById(\'ComparePrice\').style.display = \'none\';
                }
            ';
        } else if ($theme_name == 'Simple') {
            //   $jsCode .= '
            //   if(productType == "regular" && oncePrice == "") {
            //   let html =  (priceDiff != 0) ? "<span class=\'product-single__price--on-sale\'>" + formattedPrice + "</span> <s id=\'ComparePrice\'>" + regularFormattedPrice + "</s>" : "<span class=\'product-single__price\'>" + formattedPrice + "</span>";
            //   html += (isPrepaid) ? \'<span class="price__per-delivery price_each--simple">\'+ this._formatSimpleeMoney(sellingPlanAllocation.per_delivery_price) +\'/each</span>\' : \'\';
            //   priceEl.innerHTML = html;

            //   const badgeEach = document.getElementsByClassName(\'product-single__featured-image-wrapper\');
            //     for( var i=0; i<badgeEach.length; i++ ){
            //       let mydivframe = badgeEach[i];
            //         let rn = document.getElementById("badge badge--sale" + i);
            //         (rn) ? rn.remove() : \'\';
            //         mydivframe.innerHTML += (priceDiff != 0) ? "<span class=\'badge badge--sale\' id=\'badge badge--sale"+ i +"\'><span class=\'badge--simple-subscription\'> Subscription · save "+ formattedBadgePrice +"</span></span>" : "";

            //     }
            // } else if(productType == "sale" && oncePrice == ""){
            //   let html =   (priceDiff != 0) ? formattedPrice  : formattedPrice;
            //   html += (isPrepaid) ? \'<span class="price__per-delivery price_each--simple">\'+ this._formatSimpleeMoney(sellingPlanAllocation.per_delivery_price) +\'/each</span>\' : \'\';
            //   priceEl.innerHTML = html;
            //   document.querySelector("#ComparePrice").innerHTML = (priceDiff != 0) ? regularFormattedPrice : this._formatSimpleeMoney(regularPrice);

            //   let badges = document.getElementsByClassName("badge--sale");
            //   for( var i=0; i<badges.length; i++ ){
            //     badges[i].innerHTML = (priceDiff != 0) ? "<span class=\'badge--simple-subscription\'> Subscription · save "+ formattedBadgePrice +"</span>" : "<span>Sale</span>";
            //   }
            // }else{
            //   if(productType == "sale"){
            //     document.querySelector("#ComparePrice").innerHTML = this._formatSimpleeMoney(regularPrice);
            //     document.querySelector(".badge--sale").innerHTML = "<span>Sale</span>";
            //   }else{
            //      let rn = document.getElementById("badge badge--sale");
            //       (rn) ? rn.remove() : \'\';
            //   }

            //   priceEl.innerHTML = this._formatSimpleeMoney(currentVariant.price);
            // }
            // ';
            $jsCode .= '
                if(productType == "regular" && oncePrice == "") {
                  document.getElementsByClassName(\'product-single__price\')[0].innerHTML = formattedPrice;
                }else{
                  document.getElementsByClassName(\'product-single__prices\')[0].innerHTML = formattedPrice;
                }
            ';
        } elseif ($theme_name == 'Boundless') {
            //     $jsCode .= '
            //     if(productType == "regular" && oncePrice == "") {
            //         let html = "";
            //         if( priceDiff != 0 ){
            //             html += "<s id=\'ComparePrice\'>" + regularFormattedPrice + "</s> <span class=\'product__price--sale\'>";
            //             html += "<span class=\'txt--emphasis\'>now</span> <span class=\'js-price\'>" + formattedPrice;
            //             html += (isPrepaid) ? \'<span class="price__per-delivery">\'+ this._formatSimpleeMoney(sellingPlanAllocation.per_delivery_price) +\'/each</span>\' : \'\';
            //             html += "<div class=\'price_badge_brooklyn--subscription product-single__price on-sale\'> Subscription · save "+formattedBadgePrice+"</div></span></span>";
            //         }else{
            //             html += formattedPrice;
            //         }

            //         priceEl.innerHTML = html;

            //       } else if(productType == "sale" && oncePrice == ""){
            //         let html = (isPrepaid) ? \'<span class="price__per-delivery">\'+ this._formatSimpleeMoney(sellingPlanAllocation.per_delivery_price) +\'/each</span>\' : \'\';
            //         html += (priceDiff != 0) ? "<div class=\'price_badge_brooklyn--subscription product-single__price on-sale\'> Subscription · save "+ formattedBadgePrice +"</div>" : \'\';

            //         document.querySelector(".js-price").innerHTML = (priceDiff != 0) ? formattedPrice + html : formattedPrice;
            //         document.querySelector(".product__price--reg").innerHTML = (priceDiff != 0) ? regularFormattedPrice : this._formatSimpleeMoney(regularPrice);
            //       }else{
            //         if(productType == "sale"){
            //           document.querySelector(".js-price").innerHTML = formattedPrice;
            //           document.querySelector(".product__price--reg").innerHTML = this._formatSimpleeMoney(regularPrice);
            //         }else{
            //           document.querySelector(".js-price").innerHTML = this._formatSimpleeMoney(currentVariant.price);
            //         }
            //       }
            // ';
            $jsCode .= '
                if(productType == "regular" && oncePrice == "") {
                 document.getElementsByClassName(\'product__price--reg\')[0].innerHTML = formattedPrice;
               }else{
                 document.getElementsByClassName(\'product__price\')[0].innerHTML = formattedPrice;
               }
            ';
        } else {
            $jsCode .= 'if(productType == "regular" && oncePrice == "") {
                 document.getElementsByClassName(\'price-item--regular\')[0].innerHTML = formattedPrice;
              }else{
                 document.getElementsByClassName(\'price__sale\')[0].innerHTML = formattedPrice;
              }';
        }
        $jsCode .= '},
    _getVariantSellingPlan: function(variant, sellingPlan) {
        if( typeof variant == \'undefined\') {
          return false;
        }
        var result = variant.selling_plan_allocations.find(function(
          variantSellingPlan
        ) {
          return variantSellingPlan.selling_plan_id === sellingPlan.id;
        });

        if (result) {
          return result;
        } else {
          return false;
        }
    },
    _getVariantFromOptions: function() {
      var selectedValues = [];
      var variants = this.products[this.productId].variants;
      var found = variants.find(function(variant) {
        return selectedValues.every(function(values) {
          return variant[values.index] === values.value;
        });
      });

      return found;
    },
    _addVariantChangeListener: function () {
        var selectors = document.querySelectorAll(simpleeSelectors.variantSelector.join())
            selectors.forEach(function (select) {
            if (select) {
                select.addEventListener("change", function (event) {
                    var productForm = event.target.closest(simpleeSelectors.productForm);
                    var widget = productForm.querySelector(simpleeSelectors.widget);

                    // NOTE: Variant change event needs to propagate to `input[name=id]`, so wait for that to happen...
                    setTimeout(function () {
                       var dest = document.querySelector(`input[name=simplee-sellingPlanGroup-radio]:checked`);
                        dest.dispatchEvent(new Event("change"));
                    }.bind(this), 100)
                    }.bind(this));
                }
        }.bind(this));
        },
    _parsePageTemplate: function () {
        var pageTemplateInputEl = document.querySelector(simpleeSelectors.pageTemplate);
        if (pageTemplateInputEl === null) {
            return;
        }
        this.pageTemplate = pageTemplateInputEl.value;
    },
    _getPlanDescription: function(groupId, optionName) {
      var selectedGroup = this._getSelectedSellingPlanGroup(groupId);
      var sPlan = selectedGroup.selling_plans.find(function(plan) {
        return plan.options.find(planOption => planOption.value == optionName);
      });

      let id = \'\',
        description = \'\';
      if( typeof sPlan != \'undefined\' ){
        id = sPlan.id,
        description = (typeof sPlan.description != \'undefined\' && sPlan.description != null) ? sPlan.description  : \'\';
      }else{
        id = \'\',
        description = \'\';
      }
      return [id, description];
    },
    _parseProductJson: function () {
        var productJsonElements = document.querySelectorAll(simpleeSelectors.productJson);
        productJsonElements.forEach(function (element) {
            var productJson = JSON.parse(element.innerHTML);
            this.productId = element.dataset.simpleeProductId;
            this.products[element.dataset.simpleeProductId] = productJson;

            productJson.selling_plan_groups.forEach(function (sellingPlanGroup) {
                this.sellingPlanGroups[sellingPlanGroup.id] = sellingPlanGroup;
            }.bind(this));

                productJson.variants.forEach(function (variant) {
                this.variants[variant.id] = variant;
            }.bind(this));
            }.bind(this));
        },

    _getVariantId: function (widget) {
        var productForm = widget.closest(simpleeSelectors.productForm);
        if (!productForm) {
            console.error("Error - no product form");
            return null;
        }
        var variantIdInput = productForm.querySelector(simpleeSelectors.variantIdInput);

        return variantIdInput.value;
    },
    _getActiveSellingPlanId: function (widget, groupId) {
        var activePlanInputEl = widget.querySelector(
                `input[name="simplee-sellingPlan-Option-${groupId}"]:checked`,
            );

        if (!activePlanInputEl) {
            console.error(`Error - no plan for plangroup ${groupId}.`);
        }
        var activePlanValue = activePlanInputEl.value;
        planName = activePlanInputEl.parentNode.getAttribute("data-value");
        var selectedGroup = this._getSelectedSellingPlanGroup(groupId);
        var selectedPlanOptions = this._getCurrentSellingPlanOptions(activePlanInputEl, selectedGroup);

        var sellingPlan = selectedGroup.selling_plans.find(function(plan) {
          return plan.options.find(planOption => planOption.value == activePlanValue);
        });
        return sellingPlan;
    },
    _getCurrentSellingPlanOptions: function(activePlanInputEl, selectedGroup) {
        planName = activePlanInputEl.parentNode.getAttribute("data-value");
        planOptions = selectedGroup.options;

        let currentOption = planOptions.find(option => option.name == planName)
            return currentOption;
        },
    _getSelectedSellingPlanGroup: function(selectedGroupId){
        sellingPlanG = this.products[this.productId].selling_plan_groups;
        return found = sellingPlanG.find(planG => planG.id == selectedGroupId);
       },
    _updateHistoryState: function(variantId, sellingPlan) {
        if (!history.replaceState || !variantId) {
            return;
        }

        var newurl =
            window.location.protocol +
            "//" +
            window.location.host +
            window.location.pathname +
            "?";

        if (sellingPlan) {
            newurl += "selling_plan=" + sellingPlan.id + "&";
        }

        newurl += "variant=" + variantId;
        window.history.replaceState({ path: newurl }, "", newurl);
      },
    _formatSimpleeMoney: function (cents, format) {
      moneyFormat = document.querySelector(simpleeSelectors.moneyFormat).getAttribute("data-simplee-money-format");
      if (typeof cents === "string") {
        cents = cents.replace(".", "");
      }
      var value = "";
      var placeholderRegex = /\{\{\s*(\w+)\s*\}\}/;
      var formatString = format || moneyFormat;

      function formatWithDelimiters(number, precision, thousands, decimal) {
        thousands = thousands || ",";
        decimal = decimal || ".";

        if (isNaN(number) || number === null) {
          return 0;
        }

        number = (number / 100.0).toFixed(precision);

        var parts = number.split(".");
        var dollarsAmount = parts[0].replace(
          /(\d)(?=(\d\d\d)+(?!\d))/g,
          "$1" + thousands
        );
        var centsAmount = parts[1] ? decimal + parts[1] : "";

        return dollarsAmount + centsAmount;
      }

    switch (formatString.match(placeholderRegex)[1]) {
      case "amount":
        value = formatWithDelimiters(cents, 2);
        break;
      case "amount_no_decimals":
        value = formatWithDelimiters(cents, 0);
        break;
      case "amount_with_comma_separator":
        value = formatWithDelimiters(cents, 2, ".", ",");
        break;
      case "amount_no_decimals_with_comma_separator":
        value = formatWithDelimiters(cents, 0, ".", ",");
        break;
      case "amount_no_decimals_with_space_separator":
        value = formatWithDelimiters(cents, 0, " ");
        break;
      case "amount_with_apostrophe_separator":
        value = formatWithDelimiters(cents, 2, "\'");
        break;
    }

    return formatString.replace(placeholderRegex, value);
  },
   _handleRequired: function(){
       let forms = document.getElementsByTagName(\'form\');
        for(i=0; i<forms.length; i++){
          let action =  forms[i].action;
          if(action.includes("/cart/add")){
              forms[i].addEventListener("submit", function(e) {
                event.preventDefault();
                event.stopPropagation();
                event.stopImmediatePropagation(); // Prevents form from submitting
                let okayOrNot = true;
                let isErr = false;
                let requireds = document.getElementsByClassName(\'required\');
                for(j=0; j<requireds.length; j++){
                  let fieldType = requireds[j].type;
                  let fieldClass = requireds[j].id;
                  let fieldVal = \'\';


                  if(fieldType == \'text\' || fieldType == \'textarea\' || fieldType == \'checkbox\'){
                    if(fieldType == \'checkbox\'){
                     const cb = requireds[j].checked;
                      fieldVal = (cb) ? \'Yes\' : \'\';
                    }else{
                      fieldVal = requireds[j].value;
                    }
                    if(fieldVal == \'\'){
                      isErr = true;
                      document.getElementsByClassName(fieldClass)[0].innerHTML = \'This field is required.\';
                    } else{
                      document.getElementsByClassName(fieldClass)[0].innerHTML = \'\';
                    }
                  }
                }

                if(!isErr){
                  this.submit();
                }

            });
          }
        }
    }
})

    return simpleeWidget;
})();

document.addEventListener("DOMContentLoaded", function () {
    window.Simplee = window.Simplee || {};
    window.Simplee.simpleeWidget = new simpleeWidget();
    window.Simplee.simpleeWidget.init();

    var dest = document.querySelector(`input[name=simplee-sellingPlanGroup-radio]:checked`);
    (dest) ? dest.dispatchEvent(new Event("change")) : \'\';
});
';
        return $jsCode;
    }
}

// simplee js code
if (!function_exists('getJSCode')) {
    /**
     * @param $constants
     * @param $theme_name
     * @return string
     */
    function getJSCode($constants, $theme_name)
    {
        // Asset Simplee JS
        $jsCode = '// Simplee Storefront Widget - Script - Version 0.1
// For questions visit http://support.simplee.best

var simpleeSelectors = {};
var simpleeClasses = {};

var simpleeWidget = (function () {
    function simpleeWidget() {
        simpleeSelectors = {
          sellingPlanGroupContainer: ".simplee-widget__planGroup--container",
          sellingPlanOptions: ".simplee-widget__sellingPlan-options",
          widget: ".simplee-widget",
          sellingPlanIdInput: ".simplee-selling-plan-id-input",
          productForm: \'form[action="/cart/add"]\',
          variantIdInput: \'[name="id"]\',
          variantSelector: ["#shappify-variant-id", ".single-option-selector", "select[name=id]", "input[name=id]"],
          pageTemplate: ".simplee-page-template",
          productJson: ".simplee-product-json",
          moneyFormat: ".simplee-money-format",
          sellingPlanOptionName: ".simplee-widget_sellingPlan_option_name",
          perDeliveryPrice: "' . $constants['PRICE_SALE'] . '",
          perPriceBadge: "' . $constants['PRICE_BADGE_SALE'] . '",
        };

    simpleeClasses = {
        hidden: "simplee-widget__hidden",
           visible: "simplee-widget__visible",
        };

        this.products = {};
        this.variants = {};
        this.sellingPlanGroups = {};
        this.pageTemplate = "";
        this.productId = {};
    }

simpleeWidget.prototype = Object.assign({}, simpleeWidget.prototype, {
    init: function () {
        this._parsePageTemplate();
        this._parseProductJson();
        this._addVariantChangeListener();
    },

    handleSellingPlanGroupChange: function (event) {
        let groupRadioEl = event.target;
          var groupId = groupRadioEl.value;
          let groupOptionWidget = groupRadioEl.parentNode.nextElementSibling;
          let planGroupContainer = document.querySelectorAll(simpleeSelectors.sellingPlanGroupContainer);
          var widget = groupRadioEl.closest(simpleeSelectors.widget);

          planGroupContainer.forEach(function(plansContainer){
            let hideSelector = plansContainer.querySelectorAll(simpleeSelectors.sellingPlanOptions);
            if(hideSelector.length){
                hideSelector[0].classList.add(simpleeClasses.hidden);
            }
          });
          (groupOptionWidget) ? groupOptionWidget.classList.remove("simplee-widget__hidden") : "";

          if (groupId === "once") {
              let oncePrice = groupRadioEl.getAttribute("data-price");
              this._setSellingPlanIdInput(widget, "", "", oncePrice);
              return;
          }

          var sellingPlan = this._getActiveSellingPlanId(widget, groupId);
          this._setSellingPlanIdInput(widget, sellingPlan, groupId, "");
    },

    handleSellingPlanChange: function (event) {
        var planRadioEl = event.target;
        var widget = planRadioEl.closest(simpleeSelectors.widget);

        groupId = widget.querySelector(`input[name=simplee-sellingPlanGroup-radio]:checked`).value;
        var sellingPlan = this._getActiveSellingPlanId(widget, groupId);

        this._setSellingPlanIdInput(widget, sellingPlan, groupId, "");
    },
    handleMultiOptions: function (event){
        var planRadioEl = event.target;
        var widget = planRadioEl.closest(simpleeSelectors.widget);

        var selectedPlanValue = event.target.value; // i.e. Deliver every-1 Month
        var selectedOptionName = event.target.getAttribute("data-option");

        groupId = widget.querySelector(`input[name=simplee-sellingPlanGroup-radio]:checked`).value;

        this._SetMultiOptionChilds(widget, groupId, selectedOptionName, selectedPlanValue);
    },

    _SetMultiOptionChilds(widget, groupId, selectedOptionName, selectedPlanValue){
        var multiOptions = selectedOptionName.split(","); // i.e. array ("Deliver every", "Bill every")

        var selectedOption = selectedPlanValue.replace(multiOptions[0] + "-", ""); // selected option value i.e. 1 month
        var sellingPlanGroup = this._getSelectedSellingPlanGroup(groupId);
        var planOptions = sellingPlanGroup.options;
        let currentOption = planOptions.find(option => option.name == selectedOptionName)

        var optionValues = currentOption.values;

        let optionArr = [];
        optionValues.forEach(function(el){
            let ElArr = el.split(",");
            if(typeof optionArr[ElArr[0]] === "undefined") {
                optionArr[ElArr[0]] = [];
            }
             optionArr[ElArr[0]].push(ElArr[1]);
        });

        let secondOption = optionArr[selectedOption];

        let newOptionHtml = "";
        secondOption.forEach(function(el, index){
          let formatEl = el.replace(" ", "-").toLowerCase();
          let checked = ( index == 0 ) ? "checked" : "";
            newOptionHtml += \'<div class="simplee-widget__input-inner">\';
            var input = \'<input type="radio" name="simplee-sellingPlan-Option-\'+ groupId +\'" value="\'+ selectedOption + \',\' + el +\'" data-option="\'+ selectedOptionName +\'" id="sellingPlan-Option-\'+ multiOptions[1] +\'-product-template-\'+ groupId + \'-\' + formatEl +\'" class="simplee-widget__input-inner" onchange="window.Simplee.simpleeWidget.handleSellingPlanChange(event)" \'+ checked +\'>\';
            var label = \'<label for="sellingPlan-Option-\'+ multiOptions[1] +\'-product-template-\'+ groupId + \'-\' + formatEl+\'">\'+ el +\'</label>\';
            newOptionHtml += input;
            newOptionHtml += label;
            newOptionHtml += \'</div>\';
        });

        let oldEl = document.getElementById(multiOptions[1]);
        oldEl.innerHTML = newOptionHtml;

        var sellingPlan = this._getActiveSellingPlanId(widget, groupId);

        this._setSellingPlanIdInput(widget, sellingPlan, groupId, "");
    },

    _setSellingPlanIdInput: function (widget, sellingPlan, groupId, oncePrice) {
        var sellingPlanIdInput = widget.querySelector(simpleeSelectors.sellingPlanIdInput);
        var variantId = this._getVariantId(widget);
        sellingPlanIdInput.value = (typeof sellingPlan.id != \'undefined\') ? sellingPlan.id : "";
        if (/.*(product).*/.test(this.pageTemplate)) {
            this._updateHistoryState(variantId, sellingPlan);
        }

        this._updateVariantPriceBadge(variantId, sellingPlan, groupId, oncePrice);
    },
    _updateVariantPriceBadge(variantId, sellingPlan, groupId, oncePrice){
        var variants = this.products[this.productId].variants;
        var currentVariant =  variants.find(function(vari) {
           return vari.id == variantId
        });

        var sellingPlanGroup = this._getSelectedSellingPlanGroup(groupId);
        var sellingPlanAllocation = sellingPlan
        ? this._getVariantSellingPlan(currentVariant, sellingPlan)
        : false;

        if (!currentVariant) {
          return;
        }

        //         update price
            var priceEl = document.querySelector(simpleeSelectors.perDeliveryPrice);
            var price = ( oncePrice == "" ) ? sellingPlanAllocation.per_delivery_price : oncePrice;
            var productType = document.getElementById("simplee_sale_product").value;

            var regularFormattedPrice = this._formatSimpleeMoney(sellingPlanAllocation.compare_at_price);
            var formattedPrice = this._formatSimpleeMoney(price);
            var priceDiff = (sellingPlanAllocation.compare_at_price - sellingPlanAllocation.per_delivery_price);
            var formattedBadgePrice = this._formatSimpleeMoney(priceDiff);
        ';

        if ($theme_name == 'Debut') {
            $jsCode .= '
             if(productType == "regular" && oncePrice == "") {
                 priceEl.innerHTML = "<span class=\'price-item--sale\'>" + formattedPrice + " " + "</span><s>" + regularFormattedPrice + "</s><span class=\'price_badge_debut--subscription price__badge--subscription\' aria-hidden=\'true\'> Subscription · save " + formattedBadgePrice + "</span>";
             } else if( productType == "regular" && oncePrice != "" ){
               priceEl.innerHTML = this._formatSimpleeMoney(oncePrice);
             }else{
               document.querySelector(".price-item--sale").innerHTML = formattedPrice;
               document.querySelector(".price__badge--sale").innerHTML = (oncePrice == "") ? "Subscription · save " + formattedBadgePrice : "SALE";
             }
            ';
        }
        if ($theme_name == 'Venture') {
            $jsCode .= '
                if(productType == "regular" && oncePrice == "") {
                    priceEl.innerHTML = "<div class=\'product-tag\'> Subscription · save "+ formattedBadgePrice +"</div>" + formattedPrice + " " + "<s>" + regularFormattedPrice + "</s>";
                } else{
                    document.querySelector(".product-tag").innerHTML = (oncePrice == "") ? "Subscription · save " + formattedBadgePrice : "SALE";
                    priceEl.innerHTML = formattedPrice;
                }
            ';
        }
        if ($theme_name == 'Minimal') {
            $jsCode .= '
                if(productType == "regular" && oncePrice == "") {
                    priceEl.innerHTML = "<div class=\'price_badge_minimal--subscription\'> Subscription · save "+ formattedBadgePrice +"</div>" + formattedPrice + " " + "<s id=\'ComparePrice\' class=\'product-single__sale-price\'>" + regularFormattedPrice + "</s>";
                } else if(productType == "sale" && oncePrice == ""){
                  document.querySelector(".product-single__price").innerHTML = "<div class=\'price_badge_minimal--subscription\'> Subscription · save "+ formattedBadgePrice +"</div>" + formattedPrice;
                }else{
                    document.querySelector(".product-single__price").innerHTML = formattedPrice;
                }
            ';
        }
        if ($theme_name == 'Express') {
            $jsCode .= '
                if(productType == "regular" && oncePrice == "") {
                    priceEl.innerHTML = "<s>" + regularFormattedPrice + "</s><span class=\'price-item--sale simplee_price-item-express--sale\'>" + formattedPrice + " " + "</span><span class=\'price__badge-express--subscription price__badge--sale\' aria-hidden=\'true\'> Subscription · save " + formattedBadgePrice + "</span>";
                } else if( productType == "regular" && oncePrice != "" ){
                    priceEl.innerHTML = this._formatSimpleeMoney(oncePrice);
                }else{
                    document.querySelector(".price-item--sale").innerHTML = formattedPrice;
                    document.querySelector(".price__badge--sale").innerHTML = (oncePrice == "") ? "Subscription · save " + formattedBadgePrice : "SALE";
                }
            ';
        }
        if ($theme_name == 'Supply') {
            $jsCode .= '
                if(productType == "regular" && oncePrice == "") {
                  priceEl.innerHTML = "<s>" + regularFormattedPrice + "</s><span class=\'price-item--sale simplee_price-item-supply--sale\'>" + formattedPrice + " " + "</span><span class=\'sale-tag large\' aria-hidden=\'true\'> Subscription · save " + formattedBadgePrice + "</span>";
                } else if( productType == "regular" && oncePrice != "" ){
                  priceEl.innerHTML = this._formatSimpleeMoney(oncePrice);
                }else{
                  priceEl.innerHTML = (productType == "sale" && oncePrice == "") ? "<s>" + regularFormattedPrice + "</s><span class=\'price-item--sale simplee_price-item-supply--sale\'>" + formattedPrice + "</span>" : "<span class=\'price-item--sale\'>" + formattedPrice + "</span>";
                  document.querySelector(".sale-tag").innerHTML = (oncePrice == "") ? "Subscription · save " + formattedBadgePrice : "SALE";
                }
            ';
        }
        if ($theme_name == 'Narrative') {
            $jsCode .= '
                if(productType == "regular" && oncePrice == "") {
                    priceEl.innerHTML = "<div class=\'price_badge_narrative--subscription\'> Subscription · save "+ formattedBadgePrice +"</div>" + formattedPrice + " " + "<s id=\'ComparePrice\' class=\'product-single__sale-price\'>" + regularFormattedPrice + "</s>";
                } else if(productType == "sale" && oncePrice == ""){
                    document.querySelector(".product__current-price").innerHTML = "<div class=\'price_badge_narrative--subscription\'> Subscription · save "+ formattedBadgePrice +"</div>" + formattedPrice;
                }else{
                    document.querySelector(".product__current-price").innerHTML = formattedPrice;
                }
            ';
        }
        if ($theme_name == 'Brooklyn') {
            $jsCode .= '
                if(productType == "regular" && oncePrice == "") {
                  priceEl.innerHTML = "<s id=\'ComparePrice\'>" + regularFormattedPrice + "</s> <span class=\'product-single__price on-sale\'>" + formattedPrice + "</span> " + "<div class=\'price_badge_brooklyn--subscription product-single__price on-sale\'> Subscription · save "+ formattedBadgePrice +"</div>";
                } else if(productType == "sale" && oncePrice == ""){
                  document.querySelector(".product-single__price").innerHTML = formattedPrice + "<div class=\'price_badge_brooklyn--subscription product-single__price on-sale\'> Subscription · save "+ formattedBadgePrice +"</div>";
                }else{
                  document.querySelector(".product-single__price").innerHTML = formattedPrice;
                }
            ';
        }

        $jsCode .= '
    },
    _getVariantSellingPlan: function(variant, sellingPlan) {
        var result = variant.selling_plan_allocations.find(function(
          variantSellingPlan
        ) {
          return variantSellingPlan.selling_plan_id === sellingPlan.id;
        });

        if (result) {
          return result;
        } else {
          return false;
        }
    },
    _getVariantFromOptions: function() {
      var selectedValues = [];
      var variants = this.products[this.productId].variants;
      var found = variants.find(function(variant) {
        return selectedValues.every(function(values) {
          return variant[values.index] === values.value;
        });
      });

      return found;
    },
    _addVariantChangeListener: function () {
        var selectors = document.querySelectorAll(simpleeSelectors.variantSelector.join())
            selectors.forEach(function (select) {
            if (select) {
                select.addEventListener("change", function (event) {
                    var productForm = event.target.closest(simpleeSelectors.productForm);
                    var widget = productForm.querySelector(simpleeSelectors.widget);

                    // NOTE: Variant change event needs to propagate to `input[name=id]`, so wait for that to happen...
                    setTimeout(function () {
                        this._renderPrices(widget);
                        this._renderGroupDiscountSummary(widget);
                    }.bind(this), 100)
                    }.bind(this));
                }
        }.bind(this));
        },
    _parsePageTemplate: function () {
        var pageTemplateInputEl = document.querySelector(simpleeSelectors.pageTemplate);
        if (pageTemplateInputEl === null) {
            return;
        }
        this.pageTemplate = pageTemplateInputEl.value;
    },

    _parseProductJson: function () {
        var productJsonElements = document.querySelectorAll(simpleeSelectors.productJson);
        productJsonElements.forEach(function (element) {
            var productJson = JSON.parse(element.innerHTML);
            this.productId = element.dataset.simpleeProductId;
            this.products[element.dataset.simpleeProductId] = productJson;

            productJson.selling_plan_groups.forEach(function (sellingPlanGroup) {
                this.sellingPlanGroups[sellingPlanGroup.id] = sellingPlanGroup;
            }.bind(this));

                productJson.variants.forEach(function (variant) {
                this.variants[variant.id] = variant;
            }.bind(this));
            }.bind(this));
        },

    _getVariantId: function (widget) {
        var productForm = widget.closest(simpleeSelectors.productForm);
        if (!productForm) {
            console.error("Error - no product form");
            return null;
        }
        var variantIdInput = productForm.querySelector(simpleeSelectors.variantIdInput);

        return variantIdInput.value;
    },
    _getActiveSellingPlanId: function (widget, groupId) {
        var activePlanInputEl = widget.querySelector(
                `input[name="simplee-sellingPlan-Option-${groupId}"]:checked`,
            );

        var activePlanValue = activePlanInputEl.value;

        if (!activePlanInputEl) {
            console.error(`Error - no plan for plangroup ${groupId}.`);
        }

        planName = activePlanInputEl.parentNode.parentNode.getAttribute("data-value");
        var selectedGroup = this._getSelectedSellingPlanGroup(groupId);
        var selectedPlanOptions = this._getCurrentSellingPlanOptions(activePlanInputEl, selectedGroup);
        var sellingPlan = selectedGroup.selling_plans.find(function(plan) {
          return plan.options.find(planOption => planOption.value == activePlanValue);
        });
        return sellingPlan;
//         return sellingPlan.id;
    },
    _getCurrentSellingPlanOptions: function(activePlanInputEl, selectedGroup) {
        planName = activePlanInputEl.parentNode.parentNode.getAttribute("data-value");
        planOptions = selectedGroup.options;

        let currentOption = planOptions.find(option => option.name == planName)
            return currentOption;
        },
    _getSelectedSellingPlanGroup: function(selectedGroupId){
        sellingPlanG = this.products[this.productId].selling_plan_groups;
        return found = sellingPlanG.find(planG => planG.id == selectedGroupId);
       },
    _updateHistoryState: function(variantId, sellingPlan) {
        if (!history.replaceState || !variantId) {
            return;
        }

        var newurl =
            window.location.protocol +
            "//" +
            window.location.host +
            window.location.pathname +
            "?";

        if (sellingPlan) {
            newurl += "selling_plan=" + sellingPlan.id + "&";
        }

        newurl += "variant=" + variantId;
        window.history.replaceState({ path: newurl }, "", newurl);

        document.getElementsByClassName("simplee-widget__description")[0].innerHTML = ( typeof sellingPlan.description != "undefined" ) ? sellingPlan.description : "";

      },
    _formatSimpleeMoney: function (cents, format) {
      moneyFormat = document.querySelector(simpleeSelectors.moneyFormat).getAttribute("data-simplee-money-format");
      if (typeof cents === "string") {
        cents = cents.replace(".", "");
      }
      var value = "";
      var placeholderRegex = /\{\{\s*(\w+)\s*\}\}/;
      var formatString = format || moneyFormat;

      function formatWithDelimiters(number, precision, thousands, decimal) {
        thousands = thousands || ",";
        decimal = decimal || ".";

        if (isNaN(number) || number === null) {
          return 0;
        }

        number = (number / 100.0).toFixed(precision);

        var parts = number.split(".");
        var dollarsAmount = parts[0].replace(
          /(\d)(?=(\d\d\d)+(?!\d))/g,
          "$1" + thousands
        );
        var centsAmount = parts[1] ? decimal + parts[1] : "";

        return dollarsAmount + centsAmount;
      }

    switch (formatString.match(placeholderRegex)[1]) {
      case "amount":
        value = formatWithDelimiters(cents, 2);
        break;
      case "amount_no_decimals":
        value = formatWithDelimiters(cents, 0);
        break;
      case "amount_with_comma_separator":
        value = formatWithDelimiters(cents, 2, ".", ",");
        break;
      case "amount_no_decimals_with_comma_separator":
        value = formatWithDelimiters(cents, 0, ".", ",");
        break;
      case "amount_no_decimals_with_space_separator":
        value = formatWithDelimiters(cents, 0, " ");
        break;
      case "amount_with_apostrophe_separator":
        value = formatWithDelimiters(cents, 2, "\'");
        break;
    }

    return formatString.replace(placeholderRegex, value);
  }

})

    return simpleeWidget;
})();

document.addEventListener("DOMContentLoaded", function () {
    window.Simplee = window.Simplee || {};
    window.Simplee.simpleeWidget = new simpleeWidget();
    window.Simplee.simpleeWidget.init();

    var dest = document.querySelector(`input[name=simplee-sellingPlanGroup-radio]:checked`);
    dest.dispatchEvent(new Event("change"));
});
';
        return $jsCode;
    }
}

if (!function_exists('paginateH')) {
    /**
     * @return array
     */
    function paginateH($entity)
    {
        return [
            'from' => $entity->firstItem(),
            'to' => $entity->lastItem(),
            'total' => $entity->total(),
            'count' => $entity->count(),
            'per_page' => $entity->perPage(),
            'current_page' => $entity->currentPage(),
            'total_pages' => $entity->lastPage(),
            'prev_page_url' => $entity->previousPageUrl(),
            'next_page_url' => $entity->nextPageUrl(),
        ];
    }
}

if (!function_exists('countryH')) {
    /**
     * @return array
     */
    function countryH()
    {
        $newsletter = app(CountryState::class);
        $countries = $newsletter->getCountries();
        asort($countries);
        return $countries;
    }
}

if (!function_exists('stateFromCountryH')) {
    /**
     * @return array
     */
    function stateFromCountryH($country)
    {
        $newsletter = app(CountryState::class);
        return $newsletter->getStates($country);
    }
}

if (!function_exists('installWidgetH')) {
    /**
     * @return array
     */
    function installWidgetH($themeData, $user_id, $version)
    {
        try {
            \Log::info('-----------------------START :: installWidgetH -----------------------');
            $constants = getConstantH($themeData);
            newAddSnippetH($themeData['id'], $user_id, true);

            // addSnippetH($themeData['id'], $user_id, true);
            // addSimpleeWidgetSnippetH($themeData['id'], $user_id, $version, $themeData['name']);
            // addSimpleeCartWidgetSnippetH($themeData['id'], $user_id);
            // addCSSAsset($themeData['id'], $user_id, $version, $themeData['name']);
            // addJSAsset($themeData['id'], $user_id, $constants, $themeData['name'], $version);
            \Log::info('-----------------------END :: installWidgetH -----------------------');
        } catch (\Exception $e) {
            \Log::info('-----------------------ERROR :: installWidgetH -----------------------');
            \Log::info(json_encode($e));
        }
    }
}


if (!function_exists('newUpdateThemeLiquidH')) {
    /**
     * @param $snippet_name
     * @param $theme_id
     */
    function newUpdateThemeLiquidH($snippet_name, $theme_id, $user_id, $snippets)
    {
        try {
            \Log::info('-----------------------START :: updateThemeLiquidH ==> ' . $user_id . '-----------------------');
            $user = User::find($user_id);

            $asset = getLiquidAssetH($theme_id, $user_id, config('const.FILES.THEME'));
            if ($asset != '') {
                // add after <body>

                if (in_array("simplee_membership", $snippets)) {
                    if (!strpos($asset, "{% if sm_show_content %}")) {
                        $asset = str_replace('{{ content_for_layout }}', " {% assign sm_show_content = true %} {% include 'simplee-memberships' %}
                                              {% if sm_show_content %}
                                                    {{ content_for_layout }}
                                                {% else %}
                                                    {%  echo shop.metafields.simplee.restricted %}
                                                {% endif %}", $asset);
                    }
                }

                // if (!strpos($asset, "{% render '$snippet_name' %}")) {
                //     $asset = str_replace('</head>', "{% render '$snippet_name' %}</head>", $asset);
                // }

                $parameter['asset']['key'] = config('const.FILES.THEME');
                $parameter['asset']['value'] = $asset;
                $result = $user->api()->rest('PUT', 'admin/themes/' . $theme_id . '/assets.json', $parameter);
            }
        } catch (\Exception $e) {
            \Log::info('-----------------------ERROR :: updateThemeLiquidH -----------------------');
            \Log::info(json_encode($e));
        }
    }
}


if (!function_exists('newAddSnippetH')) {
    /**
     * @param $theme_id
     */
    function newAddSnippetH($theme_id, $user_id, $is_asset, $snippets = ['simplee', 'simplee_membership'])
    {
        try {
            \Log::info('-----------------------START :: addSnippet ==> ' . $user_id . '-----------------------');
            $user = User::find($user_id);

            $isUpload = 1;
            $fileData = getSimpleeMembershipSnippetCode();
            $value = <<<EOF
                    $fileData
EOF;


            if ($isUpload) {
                $config = config('const.SNIPPETS.SIMPLEE_MEMBERSHIP');
                $parameter['asset']['key'] = 'snippets/' . $config . '.liquid';
                $parameter['asset']['value'] = $value;
                $asset = $user->api()->rest('PUT', 'admin/themes/' . $theme_id . '/assets.json', $parameter);

                logger(json_encode($asset));
            }


            newUpdateThemeLiquidH('simplee', $theme_id, $user_id, $snippets);
        } catch (\Exception $e) {
            \Log::info('-----------------------ERROR :: addSnippet -----------------------');
            \Log::info(json_encode($e));
        }
    }
}

if (!function_exists('simpleeWidgetTextH')) {
    /**
     * @return array
     */
    function simpleeWidgetTextH()
    {
        return <<<EOF
{%- comment -%} Simplee Storefront Widget - Product Page - Version 0.1 {%- endcomment -%}
{%- comment -%} For questions visit http://support.simplee.best  {%- endcomment -%}

{% if product.selling_plan_groups.size > 0 %}
{%- liquid
 if product.requires_selling_plan or product.selected_selling_plan_allocation
    assign current_selling_plan_allocation = product.selected_or_first_available_selling_plan_allocation
    assign current_variant = product.selected_or_first_available_selling_plan_allocation.variant
  else
    assign current_variant = product.selected_or_first_available_variant
    assign current_selling_plan_allocation = nil
  endif
-%}

<fieldset class="fieldset simplee-widget" role="{%- if product.requires_selling_plan == false or product.selling_plan_groups.size > 1 -%} radiogroup {%- else -%} group {%- endif -%}">
    <div class="simplee-widget__wrapper">

    <legend class="simplee-widget__Label">
      {%- if product.requires_selling_plan and product.selling_plan_groups.size == 1 -%}
        {{ product.selling_plan_groups.first.name }}
      {%- else -%}
        Membership Length
      <!-- {{ 'products.product.purchase_options' | t }} -->
    {%- endif -%}
    </legend>

    <div class="simplee-widget__wrapper-inner">

  <!--     one time purchase -->
      {% unless product.requires_selling_plan == true %}
         <div class="simplee-widget__planGroup--container simplee-widget__input simplee-widget__hr">
            <div class="simplee-widget__input-main">
                <input
                       type="radio" name="simplee-sellingPlanGroup-radio" value="once"
                       class="simplee-widget__input-inner"
                       id="sellingPlan--{{section.id}}--onetimePurchase" data-price="{{product.price}}"
                       onchange="window.Simplee.simpleeWidget.handleSellingPlanGroupChange(event)"
                       {%- unless current_selling_plan_allocation -%} checked {%- endunless -%}/>
                <label for="sellingPlan--{{section.id}}--onetimePurchase">
                  One-time Purchase
<!--                   {{ 'products.product.one_time_purchase' | t }} -->
              </label>
            </div>
         </div>
      {% endunless %}

       <!-- selling plan group radio -->
      {% for group in product.selling_plan_groups %}
       <div class="simplee-widget__planGroup--container simplee-widget__input simplee-widget__hr">
          <div class="simplee-widget__input-main">
            <input type="radio" name="simplee-sellingPlanGroup-radio" value="{{group.id}}"
                   id="sellingPlan-Group-{{section.id}}-{{group.id}}"
                   class="simplee-widget__input-inner"
                   onchange="window.Simplee.simpleeWidget.handleSellingPlanGroupChange(event)"
                   {% if group.id == current_selling_plan_allocation.selling_plan.group_id %}checked{% endif %}/>
            <label for="sellingPlan-Group-{{section.id}}-{{group.id}}">{{- group.name -}}</label>
          </div>
         {% for option in group.options %}
            {% assign forloopIndex = forloop.index0 %}
            {% assign optionNameArr = option.name | split: "," %}
            <fieldset class="fieldset simplee-widget__input-content simplee-widget__sellingPlan-options
                             {% unless current_selling_plan_allocation.selling_plan.group_id == group.id %} simplee-widget__hidden {% endunless %}" data-value="{{option.name}}" data-option-type="{% if optionNameArr.size == 1 %}single{% else %}multiple{% endif %}">


              {% if optionNameArr.size == 1  %}
<!--                    one option -->

                     <legend class="fieldset__legend simplee-widget_sellingPlan_option_name">
                        {{ optionNameArr }}
                    </legend>

                    {% for value in option.values %}
                      <div class="simplee-widget__input-inner">
                        <input
                               type="radio" name="simplee-sellingPlan-Option-{{ group.id }}" value="{{value}}"
                               id="sellingPlan-Option-{{section.id}}-{{group.id}}-{{ forloopIndex }}-{{ value | handleize }}"
                               class="simplee-widget__input-inner"
                               onchange="window.Simplee.simpleeWidget.handleSellingPlanChange(event)"
                               {%- if option.selected_value == nil and forloop.first -%} checked {%- endif -%}
                               {%- if value == option.selected_value -%} checked {%- endif -%}
                               />
                        <label for="sellingPlan-Option-{{section.id}}-{{group.id}}-{{ forloopIndex }}-{{ value | handleize }}">
                          {{- value -}}
                        </label>
                      </div>
                    {% endfor %}

              {% else %}
<!--                 more than one option -->
                   {% assign option1arr = "" %}
                   {% assign option2arr = "" %}
                   {% assign option1Selected = "" %}

                    {% for value in option.values %}
                        {% assign valueArray = value | split: "," %}
                        {% assign valueFirst = valueArray | first %}
                        {% assign valueLast = valueArray | last %}

                        {% assign option1arr = option1arr | append: valueFirst %}
                        {% assign option1arr = option1arr | append: "," %}

                        {% assign arr1First = option1arr | split: "," | first %}
                        {%- if option.selected_value == nil -%}
                          {% if arr1First == valueFirst %}
                            {% assign option1Selected = arr1First %}
                            {% assign option2arr = option2arr | append: valueLast %}
                            {% assign option2arr = option2arr | append: "," %}
+                        {% else %}
                            {% assign selectedOption =  option.selected_value | split: "," %}
                                {% if valueFirst == selectedOption[0] %}
                                    {% assign option1Selected = valueFirst %}
                                    {% assign option2arr = option2arr | append: valueLast %}
                                    {% assign option2arr = option2arr | append: "," %}
                                {% endif %}
                        {% endif %}
                    {% endfor %}

                    {% for optionName in optionNameArr %}
                      <legend class="fieldset__legend simplee-widget_sellingPlan_option_name">
                          {{ optionName }}
                      </legend>

                      {% assign forloopIndex = forloop.index %}
                      {% if forloopIndex == 1 %}
                        {% assign optionarr = option1arr | split: "," %}
                        {% assign optionarr = optionarr | uniq %}
                      {% else %}
                        {% assign optionarr = option2arr | split: "," %}
                      {% endif %}

                      <div id="{{optionName}}">
                        {% for opt1 in optionarr %}
                            <div class="simplee-widget__input-inner">
                               <input
                                     type="radio"
                                      {% if forloopIndex == 1 %}
                                            name="simplee-{{optionName}}-sellingPlan-Option-{{ group.id }}"
                                            value="{{optionName}}-{{opt1}}"
                                      {% else %}
                                            name="simplee-sellingPlan-Option-{{ group.id }}"
                                            value="{{option1Selected}},{{opt1}}"
                                      {% endif %}
                                     data-option="{{optionNameArr | join: ","}}"
                                     id="sellingPlan-Option-{{optionName}}-{{section.id}}-{{group.id}}-{{ forloopIndex }}-{{ opt1 | handleize }}"
                                     class="simplee-widget__input-inner"
                                     {% if forloopIndex == 1 %}
                                        onchange="window.Simplee.simpleeWidget.handleMultiOptions(event)"
                                        {%- if option.selected_value == nil and forloop.first -%}
                                            checked
                                        {% else %}
                                            {% assign selectedOption =  option.selected_value | split: "," %}
                                            {%- if opt1 == selectedOption[0] -%} checked {%- endif -%}
                                        {%- endif -%}
                                     {% endif %}

                                     {% if forloopIndex == 2 %}
                                        onchange="window.Simplee.simpleeWidget.handleSellingPlanChange(event)"
                                        {%- if option.selected_value == nil and forloop.first -%}
                                            checked
                                        {% else %}
                                            {% assign selectedOption =  option.selected_value | split: "," %}
                                            {%- if opt1 == selectedOption[1] -%} checked {%- endif -%}
                                        {%- endif -%}
                                     {% endif %}

                                     {%- if option.selected_value == nil and forloop.first -%} checked {%- endif -%}
                                     {%- if opt1 == option.selected_value -%} checked {%- endif -%}
                               />
                              <label for="sellingPlan-Option-{{optionName}}-{{section.id}}-{{group.id}}-{{ forloopIndex }}-{{ opt1 | handleize }}">
                                 {{- opt1 -}}
                              </label>
                            </div>
                        {% endfor %}
                       </div>
                  {% endfor %}

              {% endif %}
           </fieldset>
         {% endfor %}
        </div>
      {% endfor %}
    </div>
     <div class="simplee-widget__description">
        {{ current_selling_plan_allocation.selling_plan.description }}
      </div>
  </div>
<input
    type="hidden"
    name="selling_plan"
    class="simplee-selling-plan-id-input"
    value="{{ current_selling_plan_allocation.selling_plan.id }}"
  />
  <input
    type="hidden"
    name="simplee_sale_product"
    class="simplee-selling-sale-product"
         id="simplee_sale_product"
    value="{%- if product.compare_at_price > product.price -%} sale {%- else -%} regular {%- endif -%}"
  />
  <script
    type="application/json"
    class="simplee-product-json"
    data-simplee-product-id="{{ product.id }}"
  >
    {{ product | json }}
  </script>
</fieldset>

<script
  type="application/json"
        class="simplee-money-format"
  data-simplee-money-format="{{shop.money_format}}"
></script>

<input
  type="hidden"
  class="simplee-page-template"
  value="{{ template }}"
/>
{% endif %}
{% echo product.metafields.simplee.questions %}
EOF;
    }
}

if (!function_exists('simpleeNewDefaultWidgetTextH')) {
    /**
     * @return array
     */
    function simpleeNewDefaultWidgetTextH($themeName)
    {
        $jsonThemes = config('const.JSON_THEMES');

        $simpleeWidgetHtml = '{%- comment -%}

Simplee Memberships Storefront Widget
Version: 1.2 - Added support for trials and one-time payments, and limited when widget will load to Simplee products only
For questions visit https://support.simplee.best

{%- endcomment -%}

{%- liquid

    assign widget_metafields = shop.metafields.simplee
    assign simplee_config = shop.metafields.simplee.memberships.config
    assign simplee_settings = shop.metafields.simplee.memberships.settings
    assign selling_plan_count = product.selling_plan_groups[0].selling_plans | size

%}

{% if product.selling_plan_groups.size > 0 and simplee_config["active_products"] contains product.id %}


{%  if selling_plan_count > 1 or simplee_settings["show_widget"] %}
    <div class="simplee-defaultwidget">
      <div class="simplee-defaultwidget__wrapper">
        <div class="simplee-defaultwidget__options">
            <div class="simplee-defaultwidget__options_grid" id="simplee-defaultwidget__options_grid">
                <div class="simplee-defaultwidget__bill">
                  <div class="simplee-defaultwidget__time-wrapper">
                    <div class="simplee-defaultwidget__label">
                      <h5 class="simplee-defaultwidget__purchase-options">
                        {% if widget_metafields["widget_heading_text"] %}
                            {% echo widget_metafields["widget_heading_text"] %}
                        {% else %}
                        Membership Length:
                        {% endif %}
                      </h5>
                    </div>
                    <div class="simplee-defaultwidget__checkbox" id="simplee-defaultwidget__options_delivery_chks">
                    {% for group in product.selling_plan_groups %}
                        {% assign gindex =  forloop.first %}
                        {% for plan in group.selling_plans %}
                            {% assign pindex =  forloop.first %}
                          <div class="simplee-defaultwidget__checkbox-wrapper" data-value="Membership Length">
                            <span name="plan_name" class="plan_name">';

        if (in_array($themeName, $jsonThemes)) {
            $simpleeWidgetHtml .= '<input type="radio" name="selling_plan" id="sellingPlan-{{- plan.id -}}" value="{{ plan.id }}" form="product-form-{{ simplee_id }}" {%- if gindex and pindex -%} checked {%- endif -%}>';
        } else {
            $simpleeWidgetHtml .= '<input type="radio" name="selling_plan" id="sellingPlan-{{- plan.id -}}" value="{{ plan.id }}" {%- if gindex and pindex -%} checked {%- endif -%}>';
        }

        $simpleeWidgetHtml .= '<label for="sellingPlan-{{- plan.id -}}" class="simplee-defaultwidget__radio">
                              {{ plan.name }}
                            </label>
                            {% if plan.description != \'\' %}
                            <div class="simplee-defaultwidget__info">
                              <svg width="12" height="12" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
                                <g>
                                  <path d="M256,0C114.833,0,0,114.833,0,256s114.833,256,256,256s256-114.833,256-256S397.167,0,256,0z M245.333,426.667c-17.646,0-32-14.354-32-32s14.354-32,32-32c17.646,0,32,14.354,32,32S262.979,426.667,245.333,426.667z M277.333,296.542v34.125c0,5.896-4.771,10.667-10.667,10.667H224c-5.896,0-10.667-4.771-10.667-10.667v-53.333c0-23.521,19.146-42.667,42.667-42.667s42.667-19.146,42.667-42.667S279.521,149.333,256,149.333S213.333,168.479,213.333,192v10.667c0,5.896-4.771,10.667-10.667,10.667H160c-5.896,0-10.667-4.771-10.667-10.667V192c0-58.813,47.854-106.667,106.667-106.667S362.667,133.188,362.667,192C362.667,243.188,326.604,286.563,277.333,296.542z" fill="#CECECED8"></path>
                                </g>
                              </svg>
                              <div class="simplee-defaultwidget__tooltipText"><p>{{ plan.description }}</p></div>
                            </div>
                            {% endif %}
                            </span>
                            <span name="plan_price" class="plan_price">
                              	{% if plan.price_adjustments[0].order_count == nil %}
                                	{{ plan.price_adjustments[0].value | money }} / RENEWAL
                              	{% elsif plan.price_adjustments[1].value == 0 %}
                              		{{ plan.price_adjustments[0].value | money }}
                              	{% else %}
                              		{% if plan.price_adjustments[0].value == 0 %}
                              			Free
                              		{% else %}
                              			{{ plan.price_adjustments[0].value | money }}
                              		{% endif %}
                              		 for {{ plan.price_adjustments[0].order_count }} {% if plan.price_adjustments[0].order_count == 1 %}order{% else %}orders{% endif %}
                              	{% endif %}
                            </span>
                          </div>
                        {% endfor %}
                      {% endfor %}
                    </div>
                  </div>
                </div>
          </div>
        </div>
      </div>
    </div>

    {% style %}
      .simplee-defaultwidget__checkbox input[type=radio]:checked+label.simplee-defaultwidget__radio::before {
          background-color: {{ widget_metafields["widget_active_bg"] }};
          border-color: {{ widget_metafields["widget_active_bg"] }};
      }
      .simplee-defaultwidget__checkbox input[type=radio]:checked+label.simplee-defaultwidget__radio::after {
          border-color: {{ widget_metafields["widget_active_text"] }};
      }
    {% endstyle %}
    {% else %}';

        if (in_array($themeName, $jsonThemes)) {
            $simpleeWidgetHtml .= '<input type="hidden" name="selling_plan" value="{{ product.selling_plan_groups[0].selling_plans[0].id }}" form="product-form-{{ simplee_id }}">';
        } else {
            $simpleeWidgetHtml .= '<input type="hidden" name="selling_plan" value="{{ product.selling_plan_groups[0].selling_plans[0].id }}">';
        }

        $simpleeWidgetHtml .= '{% endif %}
    {% echo product.metafields.simplee.questions %}
 {% endif %}';
        return $simpleeWidgetHtml;
    }
}

if (!function_exists('simpleeDefaultWidgetTextH')) {
    /**
     * @return array
     */
    function simpleeDefaultWidgetTextH()
    {
        return <<<EOF
{%- comment -%} Simplee Storefront Widget - Product Page - Version 0.1 {%- endcomment -%}
{%- comment -%} For questions visit http://support.simplee.best  {%- endcomment -%}

{% if product.selling_plan_groups.size > 0 %}
{%- liquid
 if product.requires_selling_plan or product.selected_selling_plan_allocation
    assign current_selling_plan_allocation = product.selected_or_first_available_selling_plan_allocation
    assign current_variant = product.selected_or_first_available_selling_plan_allocation.variant
  else
    assign current_variant = product.selected_or_first_available_variant
    assign current_selling_plan_allocation = nil
  endif

  assign widget_metafields = shop.metafields.simplee

  if widget_metafields['widget_default_selection'] == "One-Time Purchase"
    assign current_plan_is_onetime = true
  else
    assign current_plan_is_onetime = false
  endif
%}

<div class="simplee-defaultwidget">
  <div class="simplee-defaultwidget__wrapper">
    <div class="simplee-defaultwidget__label">
      <p class="simplee-defaultwidget__purchase-options">{{ widget_metafields['widget_heading_text'] | default: "Membership Length" }}</p>
    </div>
    <div class="simplee-defaultwidget__button">
      <!--     one time purchase -->
      {% unless product.requires_selling_plan == true %}
      <div class="simplee-defaultwidget__button_wrapper" >
        <input
               type="radio"
               name="simplee-sellingPlanGroup-radio"
               id="sellingPlan--{{section.id}}--onetimePurchase" data-price="{{product.price}}"
               value="once"
               onchange="window.Simplee.simpleeWidget.handleSellingPlanGroupChange(event)"
               {%- if current_plan_is_onetime == true -%} checked {%- endif -%}/>

        <label class="simplee-defaultwidget__color-button-background simplee-defaultwidget__color-button-text simplee-defaultwidget__color-text-primary" for="sellingPlan--{{section.id}}--onetimePurchase">
          <span>
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M10 18.5C14.6944 18.5 18.5 14.6944 18.5 10C18.5 5.30558 14.6944 1.5 10 1.5C5.30558 1.5 1.5 5.30558 1.5 10C1.5 14.6944 5.30558 18.5 10 18.5ZM10 20C15.5228 20 20 15.5228 20 10C20 4.47715 15.5228 0 10 0C4.47715 0 0 4.47715 0 10C0 15.5228 4.47715 20 10 20Z" fill="black"/>
            <path d="M10.5386 15H9.51025V8.38574C9.51025 7.83561 9.52718 7.3151 9.56104 6.82422C9.47217 6.91309 9.37272 7.00618 9.2627 7.10352C9.15267 7.20085 8.64909 7.61344 7.75195 8.34131L7.19336 7.61768L9.6499 5.71973H10.5386V15Z" fill="black"/>
          </svg>
          </span>
          {{ widget_metafields['widget_one_time_text'] | default: "One-Time Purchase" }}
        </label>
       </div>
      {% endunless %}

      <!-- selling plan group radio -->
      {% for group in product.selling_plan_groups %}
      <div class="simplee-defaultwidget__button_wrapper">
        <input
               type="radio"
               id="sellingPlan-Group-{{section.id}}-{{group.id}}"
               name="simplee-sellingPlanGroup-radio"
               value="{{group.id}}"
               onchange="window.Simplee.simpleeWidget.handleSellingPlanGroupChange(event)"
               {%- if forloop.index == 1 and product.requires_selling_plan == true -%}checked{%- endif -%}
               {%- if forloop.index == 1 and product.requires_selling_plan == false and current_plan_is_onetime == false -%}checked{%- endif -%}
               />

         <label class="simplee-defaultwidget__color-button-background simplee-defaultwidget__color-button-text simplee-defaultwidget__color-text-primary" for="sellingPlan-Group-{{section.id}}-{{group.id}}">
           <span>
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18.7734 9.20313C18.332 9.20313 17.9766 9.55859 17.9766 10C17.9766 14.3984 14.3984 17.9766 10 17.9766C5.60156 17.9766 2.02344 14.3984 2.02344 10C2.02344 5.60156 5.60156 2.02344 10 2.02344C12.4023 2.02344 14.6289 3.08203 16.1367 4.90234H13.3828C12.9414 4.90234 12.5859 5.25781 12.5859 5.69922C12.5859 6.14062 12.9414 6.49609 13.3828 6.49609H17.8594C18.3008 6.49609 18.6563 6.14062 18.6563 5.69922V1.22656C18.6563 0.785156 18.3008 0.429688 17.8594 0.429688C17.418 0.429688 17.0625 0.785156 17.0625 1.22656V3.53125C15.2617 1.56641 12.7266 0.429688 10 0.429688C4.72266 0.429688 0.429688 4.72266 0.429688 10C0.429688 15.2773 4.72266 19.5703 10 19.5703C15.2773 19.5703 19.5703 15.2773 19.5703 10C19.5703 9.55859 19.2148 9.20313 18.7734 9.20313Z" fill="black"/>
                </svg>
          </span>{{- group.name -}}</label>
      </div>
      {% endfor %}
  </div>
</div>

<!-- Sync with meta fields -->
{% style %}
  .simplee-defaultwidget__button_wrapper input[type="radio"]:checked + .simplee-defaultwidget__color-text-primary{
    color: {{ widget_metafields['widget_active_text'] | default: '#fff' }};
    background-color: {{ widget_metafields['widget_active_bg'] | default: '#1473e6' }};
  }
  .simplee-defaultwidget__button_wrapper label {
    color: {{ widget_metafields['widget_inactive_text'] | default: '#000' }};
    background-color: {{ widget_metafields['widget_inactive_bg'] | default: '#fafafa' }};
  }
  .simplee-defaultwidget__button_wrapper input[type="radio"]:checked + .simplee-defaultwidget__color-text-primary svg path{
    fill: {{ widget_metafields['widget_active_text'] | default: '#fff' }};
  }
  .simplee-defaultwidget__button_wrapper input[type="radio"] + .simplee-defaultwidget__color-text-primary svg path{
    fill: {{ widget_metafields['widget_inactive_text'] | default: '#000' }};
  }
{% endstyle %}

<script>
var current_plan_is_onetime = '{{current_plan_is_onetime}}';
if ( !current_plan_is_onetime ) {
  window.Simplee.simpleeWidget.handleSellingPlanGroupChange()
}
</script>
<!-- End -->

<!--   START :: group options  -->
<div class="simplee-defaultwidget__options">
  <div class="grid" id="simplee-defaultwidget__options_grid">
  </div>
</div>

<!--   END :: group options  -->

 <input
    type="hidden"
    name="selling_plan"
    class="simplee-selling-plan-id-input"
    id="simplee-selling-plan-id"
    value="{{ current_selling_plan_allocation.selling_plan.id }}"
  />
  <input
    type="hidden"
    name="simplee_sale_product"
    class="simplee-selling-sale-product"
         id="simplee_sale_product"
    value="{%- if product.compare_at_price > product.price -%} sale {%- else -%} regular {%- endif -%}"
  />
  <script
    type="application/json"
    class="simplee-product-json"
    data-simplee-product-id="{{ product.id }}"
  >
    {{ product | json }}
  </script>
</fieldset>

<script
  type="application/json"
        class="simplee-money-format"
  data-simplee-money-format="{{shop.money_format}}"
></script>

<input
  type="hidden"
  class="simplee-page-template"
  value="{{ template }}"
/>
 {% endif %}
 {% echo product.metafields.simplee.questions %}
EOF;
    }
}

if (!function_exists('updateFilesH')) {
    /**
     * @return array
     */
    function updateFilesH($themeData, $user_id)
    {
        $jsonThemes = config('const.JSON_THEMES');
        $constants = getConstantH($themeData);

        //! Temp remove update priduct liquid theme... (Need to confirm...)
        // //        update product-template.liquid
        //         $r = updateProductLiquidH($themeData, $user_id, $constants);

        //         if(!$r){
        //             $res['success'] = false;
        //             $res['msg'] = 'Failed when updating product liquid';
        //             return $res;
        //         }
        //        update cart-template.liquid
        $r = updateCartLiquidH(config('const.SNIPPETS.CART'), $themeData['id'], $user_id, $constants);

        if (!$r) {
            $res['success'] = false;
            $res['msg'] = 'Failed when updating cart liquid';
            return $res;
        }
        //        update customer/account.liquid
        // updateCustomerLiquidH($themeData, $user_id, $constants);
        if (!$r) {
            $res['success'] = false;
            $res['msg'] = 'Failed when updating customer liquid';
            return $res;
        }
        //        update price.liquid
        //            updatePriceLiquidH($themeData, $user_id, $constants);


        // update featured-product.liquid for json theme only
        $themeName = $themeData['name'];
        // if (in_array($themeName, $jsonThemes)) {
        //     $r = updateFeatureProductForJsonThemeH($themeData, $user_id, $constants);

        //     if (!$r) {
        //         $res['success'] = false;
        //         $res['msg'] = 'Failed when updating featured-product liquid';
        //         return $res;
        //     }
        // }

        $res['success'] = true;
        $res['msg'] = 'Theme was updated';
        return $res;
    }
}

if (!function_exists('updatePriceLiquidH')) {
    /**
     * @return array
     */
    function updatePriceLiquidH($themeData, $user_id, $constants)
    {
        logger('======= updatePriceLiquidH ========');
        $user = User::find($user_id);

        $asset = getLiquidAssetH($themeData['id'], $user_id, $constants['PRICE_FILE']);
        if ($asset != '') {
            if (!strpos($asset, $constants['PRICE_SUB_BADGE'])) {
                $asset = str_replace($constants['PRICE_SUB_BADGE_PLACE'], $constants['PRICE_SUB_BADGE'] . ' ' . $constants['PRICE_SUB_BADGE_PLACE'], $asset);
            }

            $parameter['asset']['key'] = $constants['PRICE_FILE'];
            $parameter['asset']['value'] = $asset;
            $result = $user->api()->rest('PUT', 'admin/themes/' . $themeData['id'] . '/assets.json', $parameter);
        }
    }
}

if (!function_exists('updateProductLiquidH')) {
    /**
     * @return array
     */
    function updateProductLiquidH($themeData, $user_id, $constants)
    {
        try {
            $user = User::find($user_id);

            $asset = getLiquidAssetH($themeData['id'], $user_id, $constants['PRODUCT_FILE']);
            if ($asset != '') {
                logger("================= Product Asset ===============");
                logger(strpos($asset, "{% render '" . config('const.SNIPPETS.SIMPLEE_WIDGET') . "' %}"));
                if (!strpos($asset, "{% render '" . config('const.SNIPPETS.SIMPLEE_WIDGET') . "', simplee_id: section.id  %}")) {
                    logger("================= Product Asset not exist ===============");
                    $asset = lastReplace($constants['PRODUCT_PAGE_PLACE'], "{% render '" . config('const.SNIPPETS.SIMPLEE_WIDGET') . "', simplee_id: section.id  %}" . ' ' . $constants['PRODUCT_PAGE_PLACE'], $asset);
                    // $asset = str_replace($constants['PRODUCT_PAGE_PLACE'], "{% render '" . config('const.SNIPPETS.SIMPLEE_WIDGET') . "' %}" . ' ' . $constants['PRODUCT_PAGE_PLACE'], $asset);
                }

                logger("================= Product asset done ==============");
                // logger($asset);
                $parameter['asset']['key'] = $constants['PRODUCT_FILE'];
                $parameter['asset']['value'] = $asset;
                $result = $user->api()->rest('PUT', 'admin/themes/' . $themeData['id'] . '/assets.json', $parameter);
                if ($result['errors']) {
                    return false;
                }
            }
            return true;
        } catch (\Exception $e) {
            logger("=============== ERROR :: updateProductLiquidH ==============");
            logger($e);
            return false;
        }
    }
}

if (!function_exists('updateCustomerLiquidH')) {
    /**
     * @return array
     */
    function updateCustomerLiquidH($themeData, $user_id, $constants)
    {
        try {
            logger('============= START:: updateCustomerLiquidH =============');
            $user = User::find($user_id);

            $asset = getLiquidAssetH($themeData['id'], $user_id, $constants['FILES_ACCOUNT']);
            if ($asset != '') {
                if (!strpos($asset, $constants['ACCOUNT_PAGE_URL'])) {
                    $asset = str_replace($constants['ACCOUNT_PAGE_PLACE'], $constants['ACCOUNT_PAGE_PLACE'] . ' ' . $constants['ACCOUNT_PAGE_URL'], $asset);
                }

                $parameter['asset']['key'] = $constants['FILES_ACCOUNT'];
                $parameter['asset']['value'] = $asset;
                $result = $user->api()->rest('PUT', 'admin/themes/' . $themeData['id'] . '/assets.json', $parameter);
            }
            return true;
        } catch (\Exception $e) {
            logger("=============== ERROR :: updateCustomerLiquidH ==============");
            logger($e);
            return false;
        }
    }
}

if (!function_exists('updateFeatureProductForJsonThemeH')) {
    /**
     * @return array
     */
    function updateFeatureProductForJsonThemeH($themeData, $user_id, $constants)
    {
        try {
            logger('============= START:: updateFeatureProductForJsonThemeH =============');
            $user = User::find($user_id);

            $asset = getLiquidAssetH($themeData['id'], $user_id, config('const.SECTIONS.JSON_THEME_FEATURE_PRODUCT'));
            if ($asset != '') {
                logger('=============================================================');
                if (!strpos($asset, $constants['FEATURE_PRODUCT_DATA'])) {
                    $asset = str_replace($constants['FEATURE_PRODUCT_PLACE'], $constants['FEATURE_PRODUCT_PLACE'] . ' ' . $constants['FEATURE_PRODUCT_DATA'], $asset);
                    $parameter['asset']['key'] = config('const.SECTIONS.JSON_THEME_FEATURE_PRODUCT');
                    $parameter['asset']['value'] = $asset;
                    $result = $user->api()->rest('PUT', 'admin/themes/' . $themeData['id'] . '/assets.json', $parameter);
                }
            }
            return true;
        } catch (\Exception $e) {
            logger("=============== ERROR :: updateFeatureProductForJsonThemeH ==============");
            logger($e);
            return false;
        }
    }
}

if (!function_exists('getConstantH')) {
    /**
     * @return array
     */
    function getConstantH($theme)
    {

        $name = strtoupper(str_replace('-', '_', $theme['name']));
        $version = str_replace('.', '_', $theme['version']);
        $const = 'const.THEME';

        $constants = (config("$const.$name.$version")) ? config("$const.$name.$version") : config("$const.$name.*");
        return ($constants) ?: '';
        //        return ($constants) ?: (config("$const.*"));
    }
}

if (!function_exists('lastReplace')) {
    /**
     * @return array
     */
    function lastReplace($search, $replace, $subject)
    {
        logger('Search :: ' . $search);
        $pos = (string)strrpos($subject, $search);

        logger(gettype($pos));
        logger("POS :: " . $pos);
        if ($pos !== false) {
            logger('Last replace pos not exist ');
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }
        return $subject;
    }
}

if (!function_exists('getLiquidAssetH')) {
    /**
     * @return string
     */
    function getLiquidAssetH($theme_id, $user_id, $file)
    {
        $user = User::find($user_id);

        $asset = $user->api()->rest(
            'GET',
            'admin/themes/' . $theme_id . '/assets.json',
            ["asset[key]" => $file]
        );
        return (@$asset['body']->container['asset']['value']) ? $asset['body']->container['asset']['value'] : '';
    }
}

if (!function_exists('getShopMetaFields')) {
    /**
     * @return object
     */
    function getShopMetaFields()
    {
        $user = Auth::user();
        $parameter['namespace'] = 'simplee';
        $response = $user->api()->rest('GET', 'admin/api/' . env('SHOPIFY_API_VERSION') . '/metafields.json', $parameter);

        $metafields = [];
        if (!$response['errors']) {
            $metafields = $response['body']->container['metafields'];
        }
        return $metafields;
    }
}

if (!function_exists('updateFilesForAutomaticDiscount')) {
    function updateFilesForAutomaticDiscount($user, $theme_id, $data)
    {
        try {
            $simpleeCSS = getLiquidAssetH($theme_id, $user->id, 'assets/simplee.css');

            $parameter['asset']['key'] = 'assets/simplee-old.css';
            $parameter['asset']['value'] = $simpleeCSS;
            $asset = $user->api()->rest('PUT', 'admin/themes/' . $theme_id . '/assets.json', $parameter);

            addCSSAsset($theme_id, $user->id, 'default', $data['name']);
            addSnippetH($theme_id, $user->id, true, ['simplee']);
        } catch (\Exception $e) {
            logger("========== ERROR :: updateFilesForAutomaticDiscount ==========");
            logger($e);
        }
    }
}
