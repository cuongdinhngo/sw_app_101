import template from './sw-customer-base-form.html.twig';

console.log('override >>> sw-customer-base-form ....');

Shopware.Component.override('sw-customer-base-form', {
    template,
    computed: {
        customer() {
            return this.$parent.customer;
        },
        options() {
            return [
                { value: true, name: this.$tc('birthdayEmail.optionYes') },
                { value: false, name: this.$tc('birthdayEmail.optionNo') },
            ];
        }
    },
});
