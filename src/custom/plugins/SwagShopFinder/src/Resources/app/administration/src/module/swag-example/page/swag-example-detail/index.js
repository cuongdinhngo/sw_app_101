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
            countries: null,
        };
    },
    computed: {
        options() {
            return [
                {value: true, name: this.$tc('swag-example.detail.activeText')},
                {value: false, name: this.$tc('swag-example.detail.disabledText')}
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
            this.criteria = new Criteria();
            this.criteria.addSorting(Criteria.sort('name', 'ASC'));
            this.countryRepository.search(new Criteria(), Shopware.Context.api).then((countries) => {
                this.countries = countries.map((country) => ({
                    value: country.id,
                    label: country.name,
                }));
            });
        },

        getShop() {
            this.repository.get(this.$route.params.id, Shopware.Context.api).then((entity) => {
                console.log('get shop by id');
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
