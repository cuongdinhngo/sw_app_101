const { Component } = Shopware;

//ToDo: make a new component 'swag-example-create' which extends 'swag-example-detail'

//ToDo: make a 'getBundle' method, which creates a new object instead of fetching one
//ToDo: make a 'onClickSave' method, which saves your new object and after that uses 'this.$router.push' to redirect to your detail page
//ToDo: add 'catch' to your chain to create an error notification

console.log("swag-example-create ....")

Component.extend('swag-example-create', 'swag-example-detail', {
    methods: {
        getShop() {
            console.log('Shop initialized:', this.shop);
            if (!this.shop) {
                console.log('[swag-example-create] getShop ....');
                this.shop = this.repository.create(Shopware.Context.api);
            }
        },

        onClickSave() {
            this.isLoading = true;

            this.repository.save(this.shop, Shopware.Context.api).then(() => {
                this.isLoading = false;
                this.$router.push({name: 'swag.example.detail', params: {id: this.shop.id}})
            }).catch((exception) => {
                this.isLoading = false;
                this.createNotificationError({
                    title: this.$tc('swag-example.detail.errorTitle'),
                    message: exception
                })
            });
        }
    }
});
