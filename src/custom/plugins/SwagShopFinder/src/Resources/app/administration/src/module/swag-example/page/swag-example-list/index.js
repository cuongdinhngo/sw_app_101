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
            shops: null
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
            this.repository = this.repositoryFactory.create('swag_shop_finder');
            this.repository.search(new Criteria(), Shopware.Context.api).then((result) => {
                this.shops = result;
            }).catch((error) => {
                console.error('Error fetching shops:', error);
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
                    property: 'street',
                    label: this.$tc('swag-example.list.columnStreet'),
                    inlineEdit: 'string',
                    allowResize: true
                },
                {
                    property: 'city',
                    label: this.$tc('swag-example.list.columnCity'),
                    allowResize: true
                }
            ];
        }
    }
});
