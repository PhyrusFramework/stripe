<?php

Ajax::add('Stripe.Payment', function($req) {
    $req->requireMethod('POST');
    $req->require('amount');

    $amount = floatval($req->amount);
    $options = $req->has('options') ? $req->options : [];

    $payment = StripePayment::create($amount, $options);

    echo JSON::stringify($payment->export());
});