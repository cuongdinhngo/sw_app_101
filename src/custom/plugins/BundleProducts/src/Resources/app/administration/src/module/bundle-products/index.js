import deDE from '../../snippet/de-DE';
import enGB from '../../snippet/en-GB';
import './page/bundle-list';
import './page/bundle-create';
import './page/bundle-detail';

Shopware.Module.register('bundle-products', {
    type: 'core',
    name: 'bundle-products',
    title: 'bundle.general.mainMenuItemGeneral',
    description: 'bundle.general.descriptionTextModule',
    color: '#ff3d58',
    icon: 'regular-3d',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes: {
        'index': {
            component: 'bundle-list',
            path: 'index',
        },
        'create': {
            component: 'bundle-create',
            path: 'create',
            meta: {
                parentPath: 'bundle.products.index'
            }
        },
        'detail': {
            component: 'bundle-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'bundle.products.index'
            }
        }
    },

    navigation: [{
        label: 'bundle.general.mainMenuItemGeneral',
        color: '#ff3d58',
        path: 'bundle.products.index',
        icon: 'regular-3d',
        position: 100,
        // parent: 'sw-catalogue',
    }]
});
