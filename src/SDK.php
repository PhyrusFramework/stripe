<?php

class StripeSDK {

    /**
     * Include Stripe JS SDK into this page.
     */
    public static function use() {
        Head::add(function() {?>
        <script src="<?= Path::toRelative(__DIR__) . '/assets/Stripe.js' ?>"></script>
        <script src="https://js.stripe.com/v3/" async onload="StripeSDK.init('<?= Config::get('stripe.public') ?>')"></script>
        <?php });
    }
    
    /**
     * Initialize a Credit Card form in the current page.
     * 
     * @param mixed $options
     */
    public static function initCardForm($options = []) { 

        Footer::add(function() use($options) {
        
            $el = $options['elements'] ?? [];

            $el = Arr($el)->force([
                'cardNumber' => [
                    'showIcon' => true
                ],
                'cardExpiry' => [],
                'cardCvc' => []
            ]);

            $name = $options['name'] ?? 'Card'; ?>
<script>
StripeSDK.onLoad(function(){

    window['<?= $name ?>'] = StripeSDK.CardForm();

    <?php foreach($el as $k => $ops) { ?>
    <?= $name ?>.mount<?= ucfirst($k) ?>({
        <?php 
        if (isset($ops['style'])) {
            echo 'style: { '; 
                
            $st = $ops['style'];
            $list = ['base', 'invalid', 'empty', 'complete'];

            foreach($list as $item) {
                if (!isset($st[$item])) {
                    continue;
                }
                echo "$item: ";
                Javascript::define_value($st[$item]);
                echo ', ';
            }
            echo "},\n";
        }
        if (isset($ops['placeholder'])) {
            echo "placeholder: '" . $ops['placeholder'] . "',\n";
        }
        if (isset($ops['showIcon']) && $ops['showIcon']) {
            echo 'showIcon: true,' . "\n";
        }
        if (isset($ops['id'])) {
            echo 'id: "' . $ops['id'] . '",' . "\n";
        }
        ?>
    });
    <?php } ?>
    
});
</script>
            <?php
        });
    }

    /**
     * Embed a Credit Card Widget form in the current papge.
     */
    public static function CardWidget() {?>
<div id="card-widget">
    
    <div class="card-decobox">
        <div class="card-decobox-interior"></div>
    </div>

    <div id="cardNumber"></div>

    <div class="card-form-bottom">
        <div id="cardName"></div>
        <div id="cardExpiry"></div>
        <div id="cardCvc"></div>
    </div>
</div> <?php

        Footer::add(function() { ?>
<style>
#card-widget {
    background: linear-gradient(to bottom right, #007ebb, #1ba1d4, #007ebb, #007ebb);
    padding: 30px;
    border-radius: 10px;
}
#card-widget .card-decobox {
    margin-top: 30px;
    width: 60px;
    height: 40px;
    border-radius: 5px;
    background-color: #cdcdcd;
    padding-top: 5px;
}
#card-widget .card-decobox-interior {
    width: 40px;
    height: 25px;
    border-radius: 5px;
    background-color: #e4e4e4;
}
#card-widget #cardNumber {
    margin-top: 20px;
}
#card-widget .card-form-bottom {
    display: flex;
    align-items: center;
    margin: 40px 0;
}
#card-widget .card-form-bottom > * {
    flex: 1;
}
#card-widget .card-form-bottom > #cardName {
    flex: 2;
}
</style>
        <?php });

        self::initCardForm([
            'elements' => [
                'cardNumber' => [
                    'showIcon' => true,
                    'style' => [
                        'base' => [
                            'color' => 'white',
                            'fontSize' => '24px',
                            '::placeholder' => [
                                'color' => 'lightgray'
                            ]
                        ]
                    ]
                ],
                'cardExpiry' => [
                    'style' => [
                        'base' => [
                            'color' => 'white',
                            '::placeholder' => [
                                'color' => 'lightgray'
                            ]
                        ]
                    ]
                ],
                'cardCvc' => [
                    'style' => [
                        'base' => [
                            'color' => 'white',
                            '::placeholder' => [
                                'color' => 'lightgray'
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }

    /**
     * Embed a Credit Card form in the current page.
     */
    public static function CardForm() {?>
    <div id="card-form">
    
        <div id="cardNumber"></div>
    
        <div class="card-form-bottom">
            <div id="cardExpiry"></div>
            <div id="cardCvc"></div>
        </div>
    </div> <?php

        Footer::add(function() { ?>
<style>
#card-form #cardNumber {
    margin-top: 20px;
    background: white;
    border-radius: 5px;
    border: solid 1px lightgray;
    padding: 12px 12px;
}
#card-form .card-form-bottom {
    display: flex;
    align-items: center;
    margin: 20px -15px 0 -15px;
}
#card-form .card-form-bottom > * {
    flex: 1;
    background: white;
    border-radius: 5px;
    border: solid 1px lightgray;
    padding: 10px 12px;
    margin: 0 15px;
}
#card-form .card-form-bottom > #cardName {
    flex: 2;
}
</style>

        <?php });
    
        self::initCardForm([
            'elements' => [
                'cardNumber' => [
                    'showIcon' => true,
                    'style' => [
                        'base' => [
                            '::placeholder' => [
                                'color' => 'gray'
                            ]
                        ]
                    ]
                ],
                'cardExpiry' => [
                    'style' => [
                        'base' => [
                            '::placeholder' => [
                                'color' => 'gray'
                            ]
                        ]
                    ]
                ],
                'cardCvc' => [
                    'style' => [
                        'base' => [
                            '::placeholder' => [
                                'color' => 'gray'
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }

}