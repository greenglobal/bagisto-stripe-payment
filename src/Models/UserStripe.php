<?php

namespace GGPHP\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Kewi\Admin\Models\ConsumerProxy;
use GGPHP\Payment\Contracts\UserStripe as UserStripeContract;

class UserStripe extends Model implements UserStripeContract
{
    protected $table = 'users_stripe';

    protected $fillable = ['user_id', 'stripe_customer_id', 'stripe_card_id', 'status',
        'type', 'stripe_account_id', 'stripe_person_id', 'stripe_external_account_id'];

    public function consumer() {
        return $this->belongsTo(ConsumerProxy::modelClass(), 'user_id');
    }
}
