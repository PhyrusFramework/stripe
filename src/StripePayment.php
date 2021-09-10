<?php

class StripePayment {

    public static function create($amount, array $options = []) : StripePayment {

        $cur = empty($options['currency']) ? Config::get('stripe.defaultCurrency', 'USD') : $options['currency'];

        $params = [
            'amount' => $amount * 100,
            'currency' => $cur,
            'payment_method_types' => ['card'],
            'metadata' => empty($options['metadata']) ? [] : $options['metadata']
        ];

        if (isset($options['customer'])) {
            $customer = $options['customer'];

            if (is_string($customer)) {
                $params['customer'] = $customer;
            } else {
                $params['customer'] = $customer->id;
            }
        }

        $pi = Stripe::getClient()->paymentIntents->create($params);

        return new StripePayment($pi);

    }

    private $paymentIntent;

    public function __construct($paymentIntent) {

        $this->paymentIntent = $paymentIntent;

        $this->{'id'} = $paymentIntent->id;
        $this->{'amount'} = $paymentIntent->amount / 100;
        $this->{'client_secret'} = $paymentIntent->client_secret;
        $this->{'currency'} = $paymentIntent->currency;
        $this->{'metadata'} = $paymentIntent->metadata;

        $this->{'ID'} = $paymentIntent->id;

    }

    public function export() {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'client_secret' => $this->client_secret,
            'currency' => $this->currency,
            'metadata' => $this->metadata
        ];
    }

    public function pay(string $paymentMethod) {
        Stripe::getClient()->paymentIntents->confirm($this->id, [
            'payment_method' => $paymentMethod
        ]);
    }

}