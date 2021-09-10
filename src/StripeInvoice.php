<?php

class StripeInvoice {

    static function retrieve(string $id) : StripeInvoice {
        $inv = Stripe::getClient()->invoices->retrieve($id);
        return new StripeInvoice($inv);
    }

    private $invoice;

    function __construct($invoice) {

        $this->invoice = $invoice;

        $this->{'id'} = $invoice->id;
        $this->{'ID'} = $invoice->ID;

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
            'subtotal',
            'tax',
            'total',
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
            'due' => $invoice->amount_due,
            'paid' => $invoice->amount_paid,
            'remaining' => $invoice->amount_remaining
        ]);

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

        $this->{'lines'} = $invoice->lines['data'];

        $times = [
            'created',
            'due_date',
            'period_end',
            'period_start'
        ];

        DebugConsole::log('CREATED', $invoice->created);

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

    public function getCustomer() {
        return StripeCustomer::retrieve($this->customer->id);
    }

    public function getSubscription() {
        return StripeSubscription::retrieve($this->subscription);
    }

}