<?php

class StripeCard {

    /**
     * Create a new Credit Card.
     * 
     * @param string $cardNumber
     * @param string $cardExpiry
     * @param string $cvc
     * @param array $extras
     * 
     * @return StripeCard
     */
    public static function create(string $cardNumber, string $cardExpiry, string $cvc, array $extras = []) : StripeCard {

        $client = Stripe::getClient();

        $parts = explode('/', $cardExpiry);
        if (sizeof($parts) < 2) return null;

        $pm = $client->paymentMethods->create([
            'type' => 'card',
            'card' => [
              'number' => $cardNumber,
              'exp_month' => $parts[0],
              'exp_year' => $parts[1],
              'cvc' => $cvc
            ]
        ]);

        return new StripeCard($pm);
    }

    /**
     * Created payment method
     * 
     * @var PaymentMethod $paymentMethod
     */
    private $paymentMethod;

    public function __construct($paymentMethod) {

        $this->paymentMethod = $paymentMethod;

        $this->{'id'} = $paymentMethod->id;
        $this->{'brand'} = $paymentMethod->card->brand;
        $this->{'country'} = $paymentMethod->card->country;
        $this->{'month'} = $paymentMethod->card->exp_month;
        $this->{'year'} = $paymentMethod->card->exp_year;
        $this->{'expiry'} = "$this->month/$this->year";
        $this->{'last_digits'} = $paymentMethod->card->last4;

        $this->{'metadata'} = $paymentMethod->card->metadata;
    }

    /**
     * Converts the card back to an associative array.
     * 
     * @return array
     */
    public function export() : array {
        return [
            'id' => $this->id,
            'brand' => $this->brand,
            'country' => $this->country,
            'expiry' => $this->expiry,
            'last_digits' => $this->last_digits,
            'metadata' => $this->metadata
        ];
    }

}