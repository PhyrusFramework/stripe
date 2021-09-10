<?php

class StripeSubscription {

    public static function create($id, $data) {

        $ops = [
            'items' => [
                ['price' => $id]
            ]
        ];

        if (isset($data['trial_duration'])) {
            $timestamp = Time::instance()->add($data['trial_duration'], 'second')->timestamp;
            $ops['trial_end'] = $timestamp;
        }

        foreach($data as $k => $v) {
            if (in_array($k, [
                'trial_duration'
            ])) {
                continue;
            }
            $ops[$k] = $v;
        }

        return new StripeSubscription(Stripe::getClient()->subscriptions->create($ops));

    }

    public static function retrieve($id) {
        $sub = Stripe::getClient()->subscriptions->retrieve($id);
        return new StripeSubscription($sub);
    }

    private $subscription;

    function __construct($subscription) {
        $this->subscription = $subscription;

        $this->{'id'} = $subscription->id;
        $this->{'ID'} = $subscription->id;

        $times = [
            'cancel_at',
            'canceled_at',
            'created',
            'current_period_end',
            'current_period_start',
            'start_date',
            'trial_end',
            'trial_start'
        ];

        foreach($times as $time) {
            if (empty($subscription->{$time})) {
                $this->{$time} = null;
            } else {
                $this->{$time} = Time::fromTimestamp($subscription->{$time});
            }
        }

        $this->{'customer'} = $subscription->customer;
        $this->{'last_invoice'} = $subscription->latest_invoice;
        $this->{'metadata'} = $subscription->metadata;
        $this->{'status'} = $subscription->status;
    }

    public function cancel() {
        try {
        Stripe::getClient()->subscriptions->cancel($this->id);
        } catch(Exception $e) {}
    }

    public function getLastInvoice() {
        return new StripeInvoice($this->last_invoice);
    }

    public function getInvoices() {
        $list = Stripe::getClient()->invoices->all([
            'limit' => 100,
            'subscription' => $this->id
        ]);

        $invs = [];
        foreach($list as $item) {
            $invs[] = new StripeInvoice($item);
        }

        return $invs;
    }

}