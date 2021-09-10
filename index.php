<?php

autoload(['Stripe', 'Stripe*'], [
    __DIR__ . '/src/SDK.php',
    __DIR__ . '/src/Stripe.php',
    __DIR__ . '/src/StripeCustomer.php',
    __DIR__ . '/src/StripeCard.php',
    __DIR__ . '/src/StripePayment.php',
    __DIR__ . '/src/StripeSubscription.php',
    __DIR__ . '/src/StripeInvoice.php',
    __DIR__ . '/src/StripeAjax.php'
]);

if (Config::get('development_mode')) {

    if (Config::get('stripe') == null) {

        Config::save('stripe', [
            'version' => '2020-08-27',
            'public' => 'pk_test_51JAIpcCZzIppxy5jdhFjko66lOuuG2sEOKkFc6I0V5brQ2kvkVVeWrXW3Znjaq1LqRQfueQLdTkBIemEOzNxP2ZH00FqBJvWeo',
            'private' => 'sk_test_51JAIpcCZzIppxy5jhnZrRUYzlrxpnBgIcGUoNxesWyJRE4NgfGfdVHCOmtQQ9hjMue9PgSIiqqw02efnE5I1PWv100FzruwT5N',
            'defaultCurrency' => 'USD'
        ]);

    }

}