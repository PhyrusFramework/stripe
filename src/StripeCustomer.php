<?php

class StripeCustomer {

    /**
     * Create a Customer
     * 
     * @param array $date
     * 
     * @return StripeCustomer
     */
    public static function create(array $data = []) : StripeCustomer {
        $customer = Stripe::getClient()->customers->create($data);
        return new StripeCustomer($customer);
    }

    /**
     * Retrieve an existing Stripe user.
     * 
     * @param string $id
     * 
     * @return StripeCustomer
     */
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

    /**
     * Get the Stripe Customer properties.
     * 
     * @return array
     */
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

    /**
     * Export an array with the Customer data.
     * 
     * @return array
     */
    public function export() {
        $obj = [];
        foreach($this->props() as $prop) {
            $obj[$prop] = $this->{$prop};
        }
        return $obj;
    }

    /**
     * Add credit card to this customer.
     * 
     * @param string $number
     * @param string $expiration xx/xx
     * @param string $cvc
     * 
     * @return StripeCard
     */
    public function addCard(string $number, string $expiration, string $cvc) : StripeCard {

        $card = StripeCard::create($number, $expiration, $cvc);
        $this->attachCard($card);
        return $card;
    }

    /**
     * Attach Credit Card to Customer
     * 
     * @param StripeCard $card
     * @param bool $setAsDefault
     */
    public function attachCard(StripeCard $card, $setAsDefault = false) {

        Stripe::getClient()->paymentMethods->attach(
            is_string($card) ? $card : $card->id,
            ['customer' => $this->id]
        );

        if ($setAsDefault) {
            $this->setDefaultCard($card);
        }
    }

    /**
     * Set default Credit Card for this user.
     * 
     * @param mixed $card
     */
    public function setDefaultCard($card) {

        Stripe::getClient()->customers->update($this->id, [
            'invoice_settings' => [
              'default_payment_method' => is_string($card) ? $card : $card->id
            ]
        ]);

    }

    /**
     * Get customer credit cards.
     * 
     * @return array
     */
    public function getCards() {
        return Stripe::getClient()->paymentMethods->all([
            'customer' => $this->id,
            'type' => 'card',
            'limit' => 50 // Default is 10
        ]);
    }

    /**
     * Remove Customer credit card
     * 
     * @param string $cardId
     */
    public function removeCard(string $cardId) {

        Stripe::getClient()->paymentMethods->detach(
            $cardId,
            []
        );
    }

    /**
     * Subscribe Customer to a subscription.
     * 
     * @param string $id
     * @param mixed $options
     * 
     * @return StripeSubscription
     */
    public function subscribe(string $id, $options = []) {

        $ops = Arr::instance($options)->merge([
            'customer' => $this->id
        ]);

        return StripeSubscription::create($id, $ops->getArray());
    }

    /**
     * Customer buys items
     * 
     * @param array $items
     */
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

    /**
     * Generate a new payment for this Customer
     * 
     * @param float $amount
     * @param mixed $options
     * 
     * @return StripePayment
     */
    public function payment($amount, $options = []) : StripePayment {

        $ops = Arr::instance($options)->merge([
            'customer' => $this->id
        ])->getArray();

        return StripePayment::create($amount, $ops);
    }

    /**
     * Commit payment
     * 
     * @param StripePayment
     * @param string? $paymentMethod
     */
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

    /**
     * Delete this Customer
     */
    public function delete() {
        try {
            Stripe::getClient()->customers->delete($this->id);
        } catch(Exception $e) { }

    }

}