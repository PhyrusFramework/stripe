
class StripeSDK {

    static client;
    static onLoadEvents = [];

    static init(key) {
        this.client = Stripe(key);

        for(let ev of StripeSDK.onLoadEvents) {
            ev();
        }
        StripeSDK.onLoadEvents = [];
    }

    static onLoad(event) {
        if (StripeSDK.client) {
            event();
            return;
        }

        StripeSDK.onLoadEvents.push(event);
    }

    static CardForm() {

        let group = StripeSDK.client.elements();

        let form = {
            form: group,
            create: () => {
                // Promise
                return new Promise((resolve, reject) => {

                    StripeSDK.client.createPaymentMethod({
                        type: 'card',
                        card: form['cardNumberElement']
                    })
                    .then(response => {
                        if (response.error) {
                            reject(response.error);
                        } else if (response.paymentMethod) {
                            resolve(response.paymentMethod);
                        } else {
                            reject('Payment Method not created.');
                        }
                    })
                    .catch(err => {
                        reject(err);
                    });

                });
            }
        }

        let types = ['Number', 'Expiry', 'Cvc'];
        for(let type of types) {
            form['mountCard' + type] = (options = {}) => {
                let element = group.create('card' + type, options);
                element.mount(options.id ? options.id : '#card' + type)
                form['card' + type + 'Element'] = element;
                
                return form;
            }
        }

        return form;
    }

    static createPayment(amount, options = {}) {

        return new Promise((resolve, reject) => {

            Ajax.post('Stripe.Payment', {amount: amount, options: options})
            .then(response => {
                let json = JSON.parse(response);
                resolve({
                    intent: json,
                    checkout: (cardId) => {
                        StripeSDK.client.confirmCardPayment(json.client_secret, {
                            payment_method: cardId
                        });
                    }
                });
            })
            .catch(err => {
                reject(err);
            });

        });

    }

}