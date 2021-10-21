<?php

class StripeSubscription {

    /**
     * Retrieve a Subscription by its id
     * 
     * @param string Stripe subscription ID
     * 
     * @return StripeSubscription
     */
    public static function retrieve(string $id) : StripeSubscription {
        $sub = Stripe::getClient()->subscriptions->retrieve($id);
        return new StripeSubscription($sub);
    }

    /**
     * Create a new subscription
     * 
     * @param string Stripe price ID
     * @param array $data
     * 
     * @return StripeSubscription
     */
    public static function create(string $priceId, array $data) : StripeSubscription {

        $ops = [
            'items' => [
                ['price' => $priceId]
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

    /**
     * @var object Stripe subscription object.
     */
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

    /**
     * Cancel subscription
     */
    public function cancel() {
        try {
            Stripe::getClient()->subscriptions->cancel($this->id);
            return true;
        } catch(Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Get Subscription Customer object
     * 
     * @return StripeCustomer
     */
    public function getCustomer() : StripeCustomer {
        return StripeCustomer::retrieve($this->customer);
    }

    /**
     * Get last invoice of this Subscription
     * 
     * @return StripeInvoice
     */
    public function getLastInvoice() {
        return new StripeInvoice($this->last_invoice);
    }

    /**
     * Get invoices for this Subscription.
     * 
     * @param string invoice status
     * 
     * @return StripeInvoice[]
     */
    public function getInvoices(string $status = 'all') : array {
        $filter = [
            'limit' => 100,
            'subscription' => $this->id,
            'customer' => $this->customer
        ];

        if ($status != 'all') {
            $filter['status'] = $status;
        }

        $list = Stripe::getClient()->invoices->all($filter);

        $invs = [];
        foreach($list as $item) {
            $invs[] = new StripeInvoice($item);
        }

        return $invs;
    }

}