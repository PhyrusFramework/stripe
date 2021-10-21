<?php

class StripeInvoice {

    /**
     * Retrive Stripe invoice by its ID
     * 
     * @param string $id
     * 
     * @return StripeInvoice
     */
    static function retrieve(string $id) : StripeInvoice {
        $inv = Stripe::getClient()->invoices->retrieve($id);
        return new StripeInvoice($inv);
    }

    /**
     * @var object Stripe invoice data
     */
    private $invoice;

    function __construct($invoice) {

        $this->invoice = $invoice;

        $this->{'id'} = $invoice->id;
        $this->{'ID'} = $invoice->id;

        $attrs = [
            'attempted',
            'attempt_count',
            'billing_reason',
            'charge',
            'collection_method',
            'currency',
            'custom_fields',
            'default_payment_method',
            'default_source',
            'default_tax_rates',
            'description',
            'discount',
            'discounts',
            'ending_balance',
            'footer',
            'last_finalization_error',
            'metadata',
            'number',
            'on_behalf_of',
            'paid',
            'payment_intent',
            'payment_settings',
            'quote',
            'receipt_number',
            'starting_balance',
            'statement_descriptor',
            'status',
            'status_transitions',
            'subscription',
            'tax',
            'total_discount_amounts',
            'total_tax_amounts',
            'transfer_data'
        ];

        foreach($attrs as $a) {
            $this->{$a} = $invoice->{$a};
        }

        $this->{'account'} = new Generic ([
            'country' => $invoice->account_country,
            'name' => $invoice->account_name
        ]);

        $this->{'amount'} = new Generic([
            'due' => $invoice->amount_due / 100,
            'paid' => $invoice->amount_paid / 100,
            'remaining' => $invoice->amount_remaining / 100
        ]);

        $this->{'total'} = $invoice->total / 100;
        $this->{'subtotal'} = $invoice->subtotal / 100;

        $this->{'customer'} = new Generic([
            'id' => $invoice->customer,
            'address' => $invoice->customer_address,
            'email' => $invoice->customer_email,
            'name' => $invoice->customer_name,
            'phone' => $invoice->customer_phone,
            'shipping' => $invoice->customer_shipping,
            'tax_ids' => $invoice->customer_tax_ids
        ]);

        $this->{'url'} = $invoice->hosted_invoice_url;
        $this->{'pdf'} = $invoice->invoice_pdf;

        $this->{'lines'} = $invoice->lines;

        $times = [
            'created',
            'due_date',
            'period_end',
            'period_start'
        ];

        foreach($times as $time) {
            if (is_int($invoice->{$time}))
                $this->{$time} = Time::fromTimestamp($invoice->{$time});
            else
                $this->{$time} = null;
        }

        if (is_int($invoice->next_payment_attempt))
            $this->{'next_payment'} = Time::fromTimestamp($invoice->next_payment_attempt);
        else
            $this->{'next_payment'} = null;

    }

    /**
     * Get Customer of this invoice.
     * 
     * @return StripeCustomer
     */
    public function getCustomer() : StripeCustomer {
        return StripeCustomer::retrieve($this->customer->id);
    }

    /**
     * Get Subscription attached to this invoice, if there is.
     * 
     * @return StripeSubscription
     */
    public function getSubscription() : ?StripeSubscription {
        if (empty($this->subscription)) {
            return null;
        }
        return StripeSubscription::retrieve($this->subscription);
    }

    /**
     * Is this invoice fully paid?
     * 
     * @return bool
     */
    public function isPaid() {
        return $this->amount->remaining == 0;
    }

    /**
     * Attempt to pay this invoice again.
     */
    public function pay() {
        Stripe::getClient()->invoices->pay($this->id);
    }

}