const { Component } = Shopware;

Component.extend('bundle-create', 'bundle-detail', {
    data() {
        return {
            bundle: {
                name: '',
                discount: null,
                discountType: '',
                products: [],
            }
        };
    },
    methods: {
        getBundle() {
            this.bundle = this.repository.create(Shopware.Context.api);
            // console.log(this.bundle.products);
        },

        onClickSave() {
            this.isLoading = true;
            console.log(this.bundle);
            this.repository.save(this.bundle, Shopware.Context.api).then(() => {
                this.isLoading = false;
                // this.processSuccess = true;
                this.$router.push({ name: 'bundle.products.detail', params: { id: this.bundle.id } });
            }).catch((exception) => {
                this.createNotificationError({
                    title: this.$tc('bundle.detail.errorTitle'),
                    message: exception
                });
                this.isLoading = false;
            });
        }
    }
});