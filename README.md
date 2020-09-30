# [bagisto-stripe-payment](https://github.com/greenglobal/bagisto-stripe-payment)

## Overview

This extension used for implement form create card, provider helper function for payment with the Stripe services.

## Requirements
- [Bagisto](https://github.com/bagisto/bagisto)
- [stripe/stripe-php](https://packagist.org/packages/stripe/stripe-php)

## Installation
1. Unzip all the files to **packages/GGPHP/Payment**.
2. Open `config/app.php` and add **GGPHP\Payment\Providers\PaymentServiceProvider::class**.
3. Open `composer.json` of root project and add **""GGPHP\\Payment\\": "packages/GGPHP/Payment/src""**.
4. Go to `packages/GGPHP/Payment`, run the following command
```bash
cd packages/GGPHP/Payment
yarn
yarn run prod
```
5. Run the following command
```php
composer dump-autoload

php artisan migrate

php artisan vendor:publish --force

php artisan route:cache
```
## Configurable
1. Go to `https://<your-site>/admin/configuration/sales/paymentmethods`
2. Enter the values of the stripe's keys and Save

## Usage
- Implement form Strcreate card in view
```php
@section('content-wrapper')
  // $guard accept value is customer or admin
  @php $guard = 'customer'; @endphp
  @include('ggphp-payment::payment.payment-stripe', ['guard' => $guard])
@endsection
```
