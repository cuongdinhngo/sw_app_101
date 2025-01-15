import template from './swag-example-detail.html.twig';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('swag-example-detail', {
    template,
    inject:[
        'repositoryFactory'
    ],
    mixins: [
        Mixin.getByName('notification')
    ],
    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },
    data() {
        return {
            shop: null,
            isLoading: false,
            processSuccess: false,
            repository: null,
            countryRepository: null,
            countries: null,
        };
    },
    computed: {
        options() {
            return [
                {value: '1', name: this.$tc('swag-example.detail.activeText')},
                {value: '0', name: this.$tc('swag-example.detail.disabledText')}
            ];
        }
    },
    created() {
        this.createdComponent();
    },
    methods: {
        createdComponent() {
            this.repository = this.repositoryFactory.create('swag_shop_finder');
            console.log('created repository ...');
            console.log(this.repository);
            this.getShop();
            this.countryRepository = this.repositoryFactory.create('country');
            this.countryRepository.search(new Criteria(), Shopware.Context.api).then((countries) => {
                this.countries = countries;
            });
        },

        getShop() {
            console.log(Shopware.Context.api)
            console.log("route ....");
            console.log(this.$route);
            console.log(this.$route.params);
            this.repository.get(this.$route.params.id, Shopware.Context.api).then((entity) => {
                console.log('get shop by id');
                console.log(entity);
                this.shop = entity;
            });
        },

        onClickSave() {
            this.isLoading = true;
            this.repository.save(this.shop, Shopware.Context.api).then(() => {
                this.getShop();
                this.isLoading = false;
                this.processSuccess = true;
            }).catch((exception) => {
                this.isLoading = false;
                this.createNotificationError({
                    title: this.$tc('swag-example.detail.errorTitle'),
                    message: exception
                })
            });
        },

        saveFinish() {
            this.processSuccess = false;
        }
    }
});
