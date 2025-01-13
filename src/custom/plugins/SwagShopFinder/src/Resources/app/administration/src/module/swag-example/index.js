// <plugin root>/src/Resources/app/administration/src/module/swag-example/index.js
import './page/swag-example-list';
// import './page/swag-example-detail';
// import './page/swag-example-create';
import deDE from '../../snippet/de-DE';
import enGB from '../../snippet/en-GB';

Shopware.Module.register('swag-example', {
    type: 'core',
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
        // index: {
        //     index: {
        //         components: {
        //             default: 'swag-example-list',
        //         },
        //         path: 'index',
        //     },
        // }
        index: {
            components: {
                default: 'swag-example-list',
            },
            path: 'index',
        },
    },

    navigation: [{
        label: 'swag-example.general.mainMenuItemGeneral',
        color: '#ff3d58',
        path: 'swag.example.index',
        icon: 'default-shopping-paper-bag-product',
        position: 100,
        // parent: 'sw-settings',
    }]
});
