import template from './bundle-list.html.twig';

const {Component} = Shopware;
const {Criteria} = Shopware.Data;

Component.register('bundle-list', {
    template,
    inject: [
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
            this.repository = this.repositoryFactory.create('bundle');
            this.repository.search(new Criteria(), Shopware.Context.api).then((result) => {
                console.log(result);
                this.bundles = result;
            })
        },
        getColumns() {
            return [
                {
                    property: 'name',
                    label: this.$tc('bundle.list.columnName'),
                    routerLink: 'bundle.products.detail',
                    inlineEdit: 'string',
                    allowResize: true,
                    primary: true
                },
                {
                    property: 'discount',
                    label: this.$tc('bundle.list.columnDiscount'),
                    inlineEdit: 'number',
                    allowResize: true
                },
                {
                    property: 'discountType',
                    label: this.$tc('bundle.list.columnDiscountType'),
                    allowResize: true
                }
            ];
        }
    }
});