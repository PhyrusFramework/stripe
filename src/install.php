<?php

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