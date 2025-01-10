// <plugin root>/src/Resources/app/administration/src/module/swag-example/index.js
import './page/swag-example-list';
// import './page/swag-example-detail';
// import './page/swag-example-create';
import deDE from '../../snippet/de-DE';
import enGB from '../../snippet/en-GB';

Shopware.Module.register('swag-example', {
    type: 'plugin',
    name: 'Shop Finder',
    title: 'swag-example.general.mainMenuItemGeneral',
    description: 'swag-example.general.descriptionTextModule',
    color: '#ff3d58',
    icon: 'default-shopping-paper-bag-product',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes: {
        // list: {
        //     component: 'swag-example-list',
        //     path: 'list'
        // },
        // detail: {
        //     component: 'swag-example-detail',
        //     path: 'detail/:id',
        //     meta: {
        //         parentPath: 'swag.example.list'
        //     }
        // },
        // create: {
        //     component: 'swag-example-create',
        //     path: 'create',
        //     meta: {
        //         parentPath: 'swag.example.list'
        //     }
        // }
        index: {
            component: 'swag-example-list',
            path: 'index'
        }
    },

    navigation: [{
        label: 'swag-example.general.mainMenuItemGeneral',
        color: '#ff3d58',
        path: 'swag.example.index',
        icon: 'default-shopping-paper-bag-product',
        position: 100
    }]
});
