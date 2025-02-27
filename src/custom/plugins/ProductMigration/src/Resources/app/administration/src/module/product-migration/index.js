import './page/form-upload-file';
import enGB from '../../snippet/en-GB';

console.log('product-migratipn-page ....');

Shopware.Module.register('product-migration', {
    type: 'core',
    name: 'Product Migration',
    title: 'product-migration.general.mainMenuItemGeneral',
    description: 'product-migration.general.descriptionTextModule',
    color: '#ff3d58',
    // icon: 'default-shopping-paper-bag-product',

    snippets: {
        'en-GB': enGB
    },

    routes: {
        index: {
            components: {
                default: 'form-upload-file',
            },
            path: 'index',
        },
        // detail: {
        //     components: {
        //         default: 'swag-example-detail',
        //     },
        //     path: 'detail/:id',
        //     meta: {
        //         parentPath: 'swag.example.index'
        //     }
        // },
        // create: {
        //     components: {
        //         default: 'swag-example-create',
        //     },
        //     path: 'create',
        //     meta: {
        //         parentPath: 'swag.example.index'
        //     }
        // }
    },

    navigation: [{
        label: 'product-migration.general.mainMenuItemGeneral',
        color: '#ff3d58',
        path: 'product.migration.index',
        icon: 'regular-home',
        position: 100,
    }]
});
