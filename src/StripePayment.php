<?php

class StripePayment {

    /**
     * Retrieve a payment by its id
     * 
     * @param string $id
     * 
     * @return StripePayment
     */
    public static function retrieve(string $id) : StripePayment {
        $inv = Stripe::getClient()->paymentIntents->retrieve($id);
        return new StripePayment($inv);
    }

    /**
     * Create a new payment.
     * 
     * @param float amount
     * @param array $options
     * 
     * @return StripePayment
     */
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

    /**
     * Original payment intent from Stripe
     * 
     * @var mixed $paymentIntent
     */
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

    /**
     * Get main payment data as an array
     */
    public function export() {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'client_secret' => $this->client_secret,
            'currency' => $this->currency,
            'metadata' => $this->metadata
        ];
    }

    /**
     * Carry out this payment with a payment method.
     * 
     * @param string $paymentMethod (ID)
     */
    public function pay(string $paymentMethod) {
        Stripe::getClient()->paymentIntents->confirm($this->id, [
            'payment_method' => $paymentMethod
        ]);
    }

    /**
     * Get invoice for this payment.
     * 
     * @return StripeInvoice
     */
    public function getInvoice() : ?StripeInvoice {
        if ($this->paymentIntent->invoice == null) {
            return null;
        }

        return StripeInvoice::retrieve($this->paymentIntent->invoice);
    }

}