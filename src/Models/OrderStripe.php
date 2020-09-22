<?php

namespace Kewi\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Sales\Models\OrderProxy;
use Kewi\Payment\Contracts\OrderStripe as OrderStripeContract;

class OrderStripe extends Model implements OrderStripeContract
{
    protected $table = 'orders_stripe';

    protected $fillable = ['order_id', 'stripe_card_id', 'stripe_payment_id', 'status'];

    public function order() {
        return $this->belongsTo(OrderProxy::modelClass(), 'order_id');
    }
}
