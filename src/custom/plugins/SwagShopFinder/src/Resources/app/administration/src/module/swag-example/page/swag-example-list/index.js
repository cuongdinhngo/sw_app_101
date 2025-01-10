import template from './swag-example-list.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

Component.register('swag-example-list', {
    template,
    inject:[
        'repositoryFactory'
    ],
    data() {
        return {
            repository: null,
            bundles: null
        };
    },
    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },
    computed: {
        columns() {
            return this.getColumns();
        }
    },
    created() {
        this.createdComponent();
    },
    methods: {
        createdComponent() {
            this.repository = this.repositoryFactory.create('swag_example');
            this.repository.search(new Criteria(), Shopware.Context.api).then((result) => {
                this.bundles = result;
            });
        },

        getColumns() {
            return [
                {
                    property: 'name',
                    label: this.$tc('swag-example.list.columnName'),
                    routerLink: 'swag.example.detail',
                    inlineEdit: 'string',
                    allowResize: true,
                    primary: true
                },
                {
                    property: 'discount',
                    label: this.$tc('swag-example.list.columnDiscount'),
                    inlineEdit: 'number',
                    allowResize: true
                },
                {
                    property: 'discountType',
                    label: this.$tc('swag-example.list.columnDiscountType'),
                    allowResize: true
                }
            ];
        }
    }
});
