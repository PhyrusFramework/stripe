<?php

class StripeCustomer {

    public static function create(array $data = []) : StripeCustomer {
        $customer = Stripe::getClient()->customers->create($data);
        return new StripeCustomer($customer);
    }

    public static function retrieve(string $id) : StripeCustomer {
        $customer = Stripe::getClient()->customers->retrieve($id);
        return new StripeCustomer($customer);
    }

    function __construct($customer) {
        foreach($this->props() as $prop) {
            $this->{$prop} = $customer->{$prop};
        }

        $this->{'ID'} = $this->id;
    }

    private function props() {
        return [
            'id',
            'object',
            'address',
            'balance',
            'created',
            'currency',
            'default_source',
            'delinquent',
            'description',
            'discount',
            'email',
            'invoice_prefix',
            'invoice_settings',
            'livemode',
            'metadata',
            'name',
            'next_invoice_sequence',
            'phone',
            'preferred_locales',
            'shipping',
            'tax_exempt'
        ];
    }

    public function export() {
        $obj = [];
        foreach($this->props() as $prop) {
            $obj[$prop] = $this->{$prop};
        }
        return $obj;
    }

    public function addCard(string $number, string $expiration, string $cvc) {

        $card = StripeCard::create($number, $expiration, $cvc);
        $this->attachCard($card);
        return $pm;
    }

    public function attachCard($card, $setAsDefault = false) {

        Stripe::getClient()->paymentMethods->attach(
            is_string($card) ? $card : $card->id,
            ['customer' => $this->id]
        );

        if ($setAsDefault) {
            $this->setDefaultCard($card);
        }
    }

    public function setDefaultCard($card) {

        Stripe::getClient()->customers->update($this->id, [
            'invoice_settings' => [
              'default_payment_method' => is_string($card) ? $card : $card->id
            ]
        ]);

    }

    public function getCards() {
        return Stripe::getClient()->paymentMethods->all([
            'customer' => $this->id,
            'type' => 'card',
            'limit' => 50 // Default is 10
        ]);
    }

    public function removeCard($cardId) {

        Stripe::getClient()->paymentMethods->detach(
            $cardId,
            []
        );
    }

    public function subscribe($id, $options = []) {

        $ops = Arr::instance($options)->merge([
            'customer' => $this->id
        ]);

        return StripeSubscription::create($id, $ops->getArray());

    }

    public function purchase($items) {

        $products = [];

        if (is_array($items)) {
            foreach($items as $item) {
                $products[] = ['price' => $item];
            }
        } else {
            $products[] = ['price' => $items];
        }

        return Stripe::getClient()->invoiceItems->create([
            'customer' => $this->id,
            'items' => $products
        ]);

    }

    public function payment($amount, $options = []) : StripePayment {

        $ops = Arr::instance($options)->merge([
            'customer' => $this->id
        ])->getArray();

        return StripePayment::create($amount, $ops);
    }

    public function pay(StripePayment $payment, $paymentMethod = null) {

        $pm = $paymentMethod;

        if (empty($paymentMethod)) {
            $paymentMethod = $this->invoice_settings['default_payment_method'];

            if (empty($paymentMethod)) {
                return;
            }
        }

        Stripe::getClient()->paymentIntents->confirm($payment->id, [
            'payment_method' => $pm
        ]);
    }

    public function delete() {
        try {
            Stripe::getClient()->customers->delete($this->id);
        } catch(Exception $e) { }

    }

}