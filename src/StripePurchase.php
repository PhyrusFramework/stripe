<?php

class StripePurchase {

    private $customer;

    private $invoice;

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
    static function create(StripeCustomer $customer) : StripePurchase {

        return new StripePurchase($customer);
    }

    function __construct(StripeCustomer $customer) {
        $this->customer = $customer;
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
    public function getInvoice($finalizePurchase = false) : ?StripeInvoice {
        if ($this->invoice == null) {

            if (!$finalizePurchase)
                return null;

            $inv = Stripe::getClient()->invoices->create([
                'customer' => $this->customer->ID
            ]);
    
            $invoice = new StripeInvoice($inv);
            $this->invoice = $invoice;
            return $invoice;
        } else if ($this->invoice->url == null) {
            $this->invoice = StripeInvoice::retrieve($this->invoice->ID);
        }

        return $this->invoice;
    }

    /**
     * Add item to this purchase
     * 
     * @param string priceId
     * @param int quantity
     * 
     * @return StripePurchase
     */
    public function addItem(string $priceId, int $quantity = 1) : StripePurchase {

        $data = [
            'customer' => $this->customer->ID,
            'price' => $priceId
        ];

        if ($quantity > 1) {
            $data['amount'] = $quantity;
        }

        Stripe::getClient()->invoiceItems->create($data);
        return $this;
    }

    /**
     * 
     */
    public function pay() {

        if ($this->invoice == null) {
            $inv = Stripe::getClient()->invoices->create([
                'customer' => $this->customer->ID
            ]);
    
            $invoice = new StripeInvoice($inv);
            $this->invoice = $invoice;
        }

        $this->invoice->pay();

        return $this;
    }

}