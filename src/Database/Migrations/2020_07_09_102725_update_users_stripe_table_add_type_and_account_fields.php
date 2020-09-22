<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersStripeTableAddTypeAndAccountFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_stripe', function (Blueprint $table) {
            $table->string('type');
            $table->string('stripe_account_id')->nullable();
            $table->string('stripe_person_id')->nullable();
            $table->string('stripe_external_account_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_stripe', function (Blueprint $table) {
            $table->dropColumn(['type']);
            $table->dropColumn(['stripe_account_id']);
            $table->dropColumn(['stripe_person_id']);
            $table->dropColumn(['stripe_external_account_id']);
        });
    }
}
