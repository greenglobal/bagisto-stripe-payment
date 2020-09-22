<?php

namespace GGPHP\Payment\Http\Controllers;

use Webkul\Shop\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use GGPHP\Payment\Payment\StripePayment;

class PaymentController extends Controller
{
    protected $guard;

    protected $auth;

    protected $admin;

    protected $stripePayment;

    public function __construct(StripePayment $stripePayment)
    {
        $this->_config = request('_config');

        $this->auth = auth()->guard('customer')->user();

        $this->admin = auth()->guard('admin')->user();

        $this->stripePayment = $stripePayment;
    }

    public function addCard($id)
    {
        $user = $this->auth;

        if ($user->id != $id) {
            abort(403);
        }

        Validator::make(request()->all(), [
            'stripe_token' => 'required|string',
        ])->validate();

        \DB::beginTransaction();
        try {
            // Create or retrieve customer
            $customer = $this->stripePayment->createOrRetrieveCustomer();

            // Create card
            try {
                $card = $customer->sources->create([
                    'source' => request()->stripe_token
                ]);
                $customer->default_source = $card->id;
                $customer->save();
                $user->refresh();
                $user->stripe->stripe_card_id = $card->id;
                $user->stripe->status = 1;
                $user->stripe->save();
            } catch (\Exception $e) {
                return response()->json(['message' => $e->getMessage()], 401);
            }

            \DB::commit();
            $isCommited = true;

            return response()->json(['message' => 'Success', 'card' => $card], 200);
        } catch (\Exception $e) {
            if (!isset($isCommited)) {
                \DB::rollBack();
            }

            return response()->json(['message' => $e->getMessage()], 401);
        }
    }

    public function getCard($id)
    {
        $user = $this->auth;

        if ($user->id != $id) {
            abort(403);
        }

        $stripe = $user->stripe;
        $customerId = $stripe->stripe_customer_id ?? null;
        $cardId = $stripe->stripe_card_id ?? null;
        $status = $stripe->status ?? null;
        try {
            if ($customerId && $cardId && $status == 1) {
                return \Stripe\Customer::retrieveSource(
                    $customerId,
                    $cardId,
                    []
                );
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    public function addCardForCharge($id)
    {
        $user = $this->auth;

        if ($user->id != $id) {
            abort(403);
        }

        $customer = $this->stripePayment->createOrRetrieveCustomer(0);

        try {
            $card = $customer->sources->create([
                'source' => request()->stripe_token
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }

        return response()->json(['message' => 'Success', 'card' => $card], 200);
    }

    public function getStripeAccount($id)
    {
        $user = $this->admin;

        if ($user->id != $id) {
            abort(403);
        }

        $this->stripePayment->createOrRetrieveAccount();
        $stripe = $user->stripe;
        $accountId = $stripe->stripe_account_id ?? null;
        $status = $stripe->status ?? null;
        try {
            if ($accountId && $status == 1) {
                return \Stripe\Account::retrieve(
                    $accountId,
                    []
                );
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    public function addStripeCard($id)
    {
        $user = $this->admin;

        if ($user->id != $id) {
            abort(403);
        }

        Validator::make(request()->all(), [
            'card_token' => 'required|string',
        ])->validate();

        \DB::beginTransaction();
        try {
            // Create or retrieve customer
            $account = $this->stripePayment->createOrRetrieveAccount();

            // Create/Update card
            $card = \Stripe\Account::update(
                $account->id,
                [
                    'external_account' => request()->card_token
                ]
            );
            $user->stripe->stripe_card_id = $card->id;
            $user->stripe->status = 1;
            $user->stripe->save();

            \DB::commit();
            $isCommited = true;

            return response()->json(['message' => 'Success', 'data' => $account], 200);
        } catch (\Exception $e) {
            if (! isset($isCommited)) {
                \DB::rollBack();
            }

            return response()->json(['message' => $e->getMessage()], 401);
        }
    }

    public function getAccountLink($id)
    {
        $user = $this->admin;

        if ($user->id != $id) {
            abort(403);
        }

        try {
            // Create or retrieve customer
            $account = $this->stripePayment->createOrRetrieveAccount();
            $url = env('APP_URL', 'https://www.boosters.club');
            if (substr($url, -1) === '/') {
                $url = substr($url, 0, -1);
            }

            // Create account link
            try {
                $link = \Stripe\AccountLink::create([
                    'account' => $account->id,
                    'refresh_url' => $url . '/affiliates/card',
                    'return_url' => $url . '/affiliates/card',
                    'type' => 'custom_account_verification',
                ]);
            } catch (\Exception $e) {
                return response()->json(['message' => $e->getMessage()], 401);
            }

            return response()->json(['message' => 'Success', 'data' => $link], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }
}
