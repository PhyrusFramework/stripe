<?php

class StripePurchase {

    /**
     * Retrieve a purchase by its ID
     * 
     * @param string $id
     * 
     * @return StripePurchase
     */
    static function retrieve(string $id) : StripePurchase {
        $data = Stripe::getClient()->invoiceItems->retrieve($id);
        return new StripePurchase($data);
    }

    /**
     * Create a new purchase by a customer.
     * 
     * @param StripeCustomer $customer
     * @param string $productId
     * @param int $quantity = 1
     * 
     * @return StripePurchase
     */
    static function create(StripeCustomer $customer, string $productId, int $quantity = 1) : StripePurchase {

        $data = [
            'customer' => $customer->id,
            'price' => $productId
        ];

        if ($quantity > 1) {
            $data['quantity'] = $quantity;
        }

        return new StripePurchase(Stripe::getClient()->invoiceItems->create($data));
    }

    /**
     * @var object Stripe purchase data
     */
    private $data;

    function __construct($data) {

        $this->data = $data;

        $this->{'id'} = $data->id;
        $this->{'ID'} = $data->ID;

        $this->{'amount'} = $data->amount / 100;
        $this->{'currency'} = $data->currency;
        $this->{'date'} = Time::fromTimestamp($data->date);
        $this->{'description'} = $data->description;
        $this->{'quantity'} = $data->quantity;
        $this->{'unit_amount'} = $data->unit_amount / 100;
        $this->{'product'} = $data->price->product;
    }

    /**
     * Get Customer object for this purchase
     * 
     * @return StripePurchase
     */
    public function getCustomer() : StripeCustomer {
        return StripeCustomer::retrieve($this->data->customer);
    }

    /**
     * Get invoice for this purchase
     * 
     * @return StripeInvoice
     */
    public function getInvoice() : ?StripeInvoice {
        if ($this->data->invoice == null) {
            return null;
        }

        return StripeInvoice::retrieve($this->data->invoice);
    }

}