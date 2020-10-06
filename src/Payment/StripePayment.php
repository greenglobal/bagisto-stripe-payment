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

    private $guard;

    private $auth;


    public function __construct()
    {
        \Stripe\Stripe::setApiKey($this->getApiKey());
    }

    public function getGuard()
    {
        return $this->guard;
    }

    public function setGuard($value)
    {
        $this->guard = $value;
    }

    public function getAuth()
    {
        return $this->auth;
    }

    private function setAuth($value = null)
    {
        if (!$value) {
            $guard = $this->getGuard();

            if ($guard) {
                $value = auth()->guard($guard)->user();
            }
        }

        $this->auth = $value;
    }

    public function getUser()
    {
        $this->setAuth();

        return $this->getAuth();
    }

    public function createOrRetrieveCustomer($newStatus = 1, $data = [])
    {
        $user = $this->getUser();
        $userStripe = $this->getUserStripe();
        $stripeCustomerId = $userStripe->stripe_customer_id ?? null;

        if (empty($stripeCustomerId)) {
            $firstName = $data['first_name'] ?? ($user->first_name ?? null);
            $lastName = $data['last_name'] ?? ($user->last_name ?? null);
            $email = $data['email'] ?? ($user->email ?? null);
            $phone = $data['phone'] ?? ($user->phone ?? null);
            $customer = \Stripe\Customer::create([
                'description' => $firstName . ' ' . $lastName,
                'email' => $email,
                'metadata' => [
                    'phone' => $phone
                ],
            ]);

            if ($user) {
                $stripeCustomerId = $customer->id;
                $userStripe = UserStripe::create([
                    'user_id' => $user->id,
                    'stripe_customer_id' => $stripeCustomerId,
                    'type' => $this->getGuard(),
                    'status' => $newStatus
                ]);
            }
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

    public function getUserStripe()
    {
        $userStripe = null;
        $user = $this->getUser();
        if ($user) {
            $userStripe = UserStripe::where('user_id', $user->id)->where('type', '=', $this->getGuard())->first();
        }

        return $userStripe;
    }

    public function retrieveCustomer()
    {
        $userStripe = $this->getUserStripe();
        $stripeCustomerId = $userStripe->stripe_customer_id ?? null;
        $customer = null;
        if ($stripeCustomerId) {
            $customer = \Stripe\Customer::retrieve($stripeCustomerId);
            if (isset($customer->deleted) && $customer->deleted) {
                $userStripe->delete();
            }
        }

        return $customer;
    }

    public function cards()
    {
        $cards = [];
        $customer = $this->retrieveCustomer();
        if (empty($customer)) {
            return $this->error('Customer not found', 404);
        }

        try {
            $cardInfo = $customer->sources->toArray(true);
            $cards = $cardInfo['data'];
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }

        return $this->success($cards, 200);
    }

    public function showCard($id)
    {
        $customer = $this->retrieveCustomer();
        $cards = [];

        if (empty($customer)) {
            return $this->error('Customer not found', 404);
        }

        try {
            $card = $customer->sources->retrieve($id)->toArray(true);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }

        return $this->success($card, 200);
    }

    public function updateCard($id)
    {
        $customer = $this->retrieveCustomer();
        $card = [];

        if (empty($customer)) {
            return $this->error('Customer not found', 404);
        }

        try {
            $card = \Stripe\Customer::updateSource(
                $customer->id,
                $id,
                request()->all()
            );
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }

        return $this->success($card->toArray(true), 200);
    }

    public function charge($amount, $currency, $data = [])
    {
        $customer = $this->createOrRetrieveCustomer();

        $data = array_merge(
            [
                'amount' => $amount,
                'currency' => $currency,
                'customer' => $customer->id,
            ],
            $data
        );

        try {
            $charge = \Stripe\Charge::create($data);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }

        return $this->success($charge->toArray(true), 200);
    }

    public function error($error, $status)
    {
        return [
            'error' => $error,
            'status' => $status,
        ];
    }

    public function success($data, $status)
    {
        return [
            'data' => $data,
            'status' => $status,
        ];
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
}
