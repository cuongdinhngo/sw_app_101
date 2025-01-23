import template from './sw-customer-base-info.html.twig';

Shopware.Component.override('sw-customer-base-info', {
    template,
    computed: {
        customer() {
            console.log('Customer data:', this.$parent.customer);
            return this.$parent.customer;
        },
    },

    created() {
        console.log('Customer on created hook:', this.customer);
        console.log('Customer on created hook:', this.customer.birthday);
        console.log('Customer enableBirthdayEmail:', this.customer.enableBirthdayEmail);
    },
});