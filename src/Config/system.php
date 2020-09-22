<?php

return [
    [
        'key' => 'sales.paymentmethods.stripe',
        'name' => 'payment::app.system.stripe.name',
        'sort' => 1,
        'fields' => [
            [
                'name' => 'title',
                'title' => 'admin::app.admin.system.title',
                'type' => 'text',
                'validation' => 'required',
                'channel_based' => false,
                'locale_based' => true
            ],
            [
                'name' => 'description',
                'title' => 'admin::app.admin.system.description',
                'type' => 'textarea',
                'channel_based' => false,
                'locale_based' => true
            ],
            [
                'name' => 'active',
                'title' => 'admin::app.admin.system.status',
                'type' => 'boolean',
                'validation' => 'required',
                'channel_based' => false,
                'locale_based' => true
            ],
            [
                'name' => 'pk_test_key',
                'title' => 'payment::app.system.stripe.pk-test-key',
                'type' => 'text',
                'channel_based' => false,
                'locale_based' => true
            ],
            [
                'name' => 'sk_test_key',
                'title' => 'payment::app.system.stripe.sk-test-key',
                'type' => 'text',
                'channel_based' => false,
                'locale_based' => true
            ],
            [
                'name' => 'pk_key',
                'title' => 'payment::app.system.stripe.pk-key',
                'type' => 'text',
                'channel_based' => false,
                'locale_based' => true
            ],
            [
                'name' => 'sk_key',
                'title' => 'payment::app.system.stripe.sk-key',
                'type' => 'text',
                'channel_based' => false,
                'locale_based' => true
            ],
            [
                'name' => 'test_mode',
                'title' => 'payment::app.system.stripe.test-mode',
                'type' => 'boolean',
                'channel_based' => false,
                'locale_based' => true,
            ],
            [
                'name' => 'sort',
                'title' => 'admin::app.admin.system.sort_order',
                'type' => 'text',
                'validation' => 'numeric|max:100',
                'default' => 1,
            ],
        ]
    ]
];
