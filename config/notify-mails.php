<?php

return [
    'notify_from_email' => env('NOTIFY_FROM_EMAIL', 'no-reply@mg.simplee.best'),
    'notify_from_name' => env('NOTIFY_FROM_NAME', 'Simplee Memberships'),
    'failed_payment_to_email' => env('FAILED_PAYMENT_TO_MAIL', 'support@simplee.best'),
    'notify_new' => [
        'subject' => 'New membership for [FIRST_NAME] [LAST_NAME]',
        'body' => '
            A new membership was purchased - congrats! <br><br>
            Name: [FIRST_NAME] [LAST_NAME] <br>
            Email: [EMAIL] <br>
            Next Billing Date: [NEXT_BILLING_DATE] <br>
            Membership Plan: [MEMBERSHIP_PLAN] <br>
            <br>
        '
    ],
    'notify_cancel' => [
        'subject' => 'Cancelled membership for [FIRST_NAME] [LAST_NAME]',
        'body' => '
            A member cancelled their next renewal. <br><br>
            Name: [FIRST_NAME] [LAST_NAME] <br>
            Email: [EMAIL] <br>
            Next Billing Date: [NEXT_BILLING_DATE] <br>
            Membership Plan: [MEMBERSHIP_PLAN] <br>
            <br>
        '
    ],
    'notify_revoke' => [
        'subject' => 'Revoked membership for [FIRST_NAME] [LAST_NAME]',
        'body' => '
            A member’s access was revoked. <br><br>
            Name: [FIRST_NAME] [LAST_NAME] <br>
            Email: [EMAIL] <br>
            Membership Plan: [MEMBERSHIP_PLAN] <br>
            <br>
        '
    ],
    'notify_paymentfailed' => [
        'subject' => 'Failed payment for [FIRST_NAME] [LAST_NAME]',
        'body' => '
            A member’s payment just failed. <br><br>
            Name: [FIRST_NAME] [LAST_NAME] <br>
            Email: [EMAIL] <br>
            Membership Plan: [MEMBERSHIP_PLAN] <br>
            <br>
        '
    ]
];
