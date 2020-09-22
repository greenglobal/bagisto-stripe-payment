<?php

return [
    'stripe' => [
        'code' => 'stripe',
        'title' => 'Creadit/Debit Card',
        'description' => 'Stripe Payments',
        'class' => 'GGPHP\Payment\Payment\StripePayment',
        'active' => true,
        'pk_test_key' => env('STRIPE_PUBLIC_KEY', ''),
        'sk_test_key' => env('STRIPE_API_KEY', ''),
        'test_mode' => true,
        'sort' => 1,
    ]
];
