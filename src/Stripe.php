<?php

class Stripe {

    /**
     * @var StripeClient
     */
    private static $client;

    /**
     * Get Stripe API client.
     */
    public static function getClient() {
        if (self::$client == null) {
            new Stripe();
        }

        return self::$client;
    }

    function __construct() {
        \Stripe\Stripe::setApiKey(Config::get('stripe.private'));
        
        self::$client = new \Stripe\StripeClient(
            Config::get('stripe.private')
        );
    }

}