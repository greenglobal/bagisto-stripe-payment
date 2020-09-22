<?php

namespace Kewi\Payment\Models;

use Kewi\Payment\Models\OrderStripeProxy;
use Webkul\Sales\Models\Order as WebkulOrder;
use GGPHP\Payment\Payment\StripePayment;

class Order extends WebkulOrder
{
    public function stripe()
    {
        return $this->hasOne(OrderStripeProxy::modelClass(), 'order_id');
    }

    public function getCard()
    {
        new StripePayment;
        $user = auth()->guard('customer')->user();
        $cardId = $this->stripe->stripe_card_id ?? null;
        $customerId = $user->stripe->stripe_customer_id ?? null;
        $card = [];

        if ($customerId && $cardId) {
            try {
                $card = \Stripe\Customer::retrieveSource(
                    $customerId,
                    $cardId,
                    []
                );
                $card->toArray();
            } catch (\Exception $e) {
                //
            }
        }

        return $card;
    }
}
