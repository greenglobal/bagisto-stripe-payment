<?php

namespace GGPHP\Payment\Http\Controllers;

use Webkul\Shop\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use GGPHP\Payment\Payment\StripePayment;

class PaymentController extends Controller
{
    private $guard;

    private $auth;

    protected $admin;

    protected $stripePayment;

    public function __construct(StripePayment $stripePayment)
    {
        $this->_config = request('_config');

        $this->stripePayment = $stripePayment;
    }

    public function getGuard()
    {
        return $this->guard;
    }

    public function setGuard($value = null)
    {
        if (!$value) {
            $value = request()->guard;
        }

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

    public function addCardForCharge()
    {
        $this->setGuard();
        $user = $this->getUser();

        $data = request()->data;

        if (!empty($user) && $user->id != request()->id) {
            abort(403);
        }

        $this->stripePayment->setGuard($this->getGuard());

        $customer = $this->stripePayment->createOrRetrieveCustomer(0, $data);

        try {
            $card = $customer->sources->create([
                'source' => request()->stripe_token
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }

        return response()->json(['message' => 'Success', 'card' => $card], 200);
    }
}
