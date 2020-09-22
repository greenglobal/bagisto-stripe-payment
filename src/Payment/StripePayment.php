<?php

namespace GGPHP\Payment\Payment;

use Webkul\Payment\Payment\Payment;
use GGPHP\Payment\Models\UserStripe;

/**
 * Stripe payment method class
 */
class StripePayment extends Payment
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $code = 'stripe';

    public const TYPE_CUSTOMER = 'customer';
    public const TYPE_ADMIN = 'admin';

    protected $guard;

    protected $auth;

    public function __construct()
    {
        auth()->setDefaultDriver('customer');

        $this->auth = auth()->guard('customer')->user();

        \Stripe\Stripe::setApiKey($this->getApiKey());
    }

    public function charge($card, $data)
    {
        $customer = $this->createOrRetrieveCustomer();

        try {
            $charge = \Stripe\Charge::create([
                'amount' => (int) core()->convertPrice($data['base_grand_total']) * 100,
                'currency' => $data['order_currency_code'],
                'source' => $card,
                'customer' => $customer->id,
                'receipt_email' => $data['customer_email'],
                'shipping' => [
                    'address' => [
                        'line1' => $data['shipping_address']['address1'],
                        'state' => $data['shipping_address']['state'],
                        'postal_code' => $data['shipping_address']['postcode'],
                    ],
                    'carrier' => $data['shipping_description'],
                    'name' => $data['shipping_address']['first_name'] . ' ' . $data['shipping_address']['last_name'],
                ],
                'description' => json_encode([
                    'cart_id' => $data['cart_id'],
                    'customer_id' => $data['customer_id'],
                    'total_item_count' => $data['total_item_count'],
                    'total_qty_ordered' => $data['total_qty_ordered'],
                    'base_grand_total' => $data['base_grand_total'],
                ]),
            ]);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }

        return $charge;
    }

    public function createOrRetrieveCustomer($newStatus = 1)
    {
        $user = $this->auth;

        $userStripe= UserStripe::where('user_id', $user->id)->first();
        $stripeCustomerId = $userStripe->stripe_customer_id ?? null;

        if (empty($stripeCustomerId)) {
            $customer = \Stripe\Customer::create([
                'description' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'metadata' => [
                    'phone' => $user->phone,
                ],
            ]);
            $stripeCustomerId = $customer->id;

            $userStripe = UserStripe::create([
                'stripe_customer_id' => $stripeCustomerId,
                'type' => self::TYPE_CUSTOMER,
                'status' => $newStatus
            ]);
        } else {
            // Get customer stripe ID and check if had been deleted then update user stripe_customer_id is null
            $customer = \Stripe\Customer::retrieve($stripeCustomerId);
            if (isset($customer->deleted) && $customer->deleted) {
                $userStripe->stripe_customer_id = null;
                $userStripe->save();
            }
        }

        return $customer;
    }

    public function getApiKey()
    {
        $configs = $this->getConfigs();

        if ($configs['test_mode']) {
            $key = $configs['sk_test_key'];
        } else {
            $key = $configs['sk_key'];
        }

        return $key ?: env('STRIPE_API_KEY', '');
    }

    public function getPublicKey()
    {
        $configs = $this->getConfigs();

        if ($configs['test_mode']) {
            $key = $configs['pk_test_key'];
        } else {
            $key = $configs['pk_key'];
        }

        return $key ?: env('STRIPE_PUBLIC_KEY', '');
    }

    public function getConfigs()
    {
        return [
            'pk_test_key' => $this->getConfigData('pk_test_key') ?: null,
            'sk_test_key' => $this->getConfigData('sk_test_key') ?: null,
            'pk_key' => $this->getConfigData('pk_key') ?: null,
            'sk_key' => $this->getConfigData('sk_key') ?: null,
            'test_mode' => $this->getConfigData('test_mode') ?: null,
        ];
    }

    public function getConfigData($field)
    {
        return core()->getConfigData('sales.paymentmethods.stripe.' . $field);
    }

    public function getRedirectUrl()
    {

    }

    public function createOrRetrieveAccount($token = null, $newStatus = 1)
    {
        $user = auth()->guard('admin')->user();
        $stripeAccountId = $user->stripe->stripe_account_id ?? null;

        if (empty($stripeAccountId)) {
            $params = [
                'type' => 'custom',
                'email' => $user->email,
                'requested_capabilities' => [
                    'card_payments',
                    'transfers',
                ],
            ];
            if ($token) {
                $params['account_token'] = $token;
            }
            $account = \Stripe\Account::create($params);
            $stripeAccountId = $account->id;

            $user->stripe()->create([
                'stripe_account_id' => $stripeAccountId,
                'type' => self::TYPE_ADMIN,
                'status' => $newStatus
            ]);
        } else {
            // Get account stripe ID and check if had been deleted then update user stripe_account_id is null
            $account = \Stripe\Account::retrieve($stripeAccountId);
            if (isset($account->deleted) && $account->deleted) {
                $user->stripe->stripe_account_id = null;
                $user->stripe->save();
            }
        }

        return $account;
    }
}
