<?php

autoload(['Stripe', 'Stripe*'], [
    __DIR__ . '/src/install.php',
    __DIR__ . '/src/SDK.php',
    __DIR__ . '/src/Stripe.php',
    __DIR__ . '/src/StripeCustomer.php',
    __DIR__ . '/src/StripeCard.php',
    __DIR__ . '/src/StripePayment.php',
    __DIR__ . '/src/StripeSubscription.php',
    __DIR__ . '/src/StripeInvoice.php',
    __DIR__ . '/src/StripeAjax.php'
]);