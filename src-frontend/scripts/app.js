import { createApp } from 'vue';
import { default as router } from './plugins/router.js';
import { default as axios } from './plugins/axios.js';
import { default as api } from './plugins/api.js';
import { default as utils } from './plugins/utils.js';
import { default as localStorage } from './plugins/localStorage.js';

const homeDocsApp = {
    data: function () {
        return ({
        });
    }
};

const localStorageBasilOptions = {
    namespace: 'homedocs',
    storages: ['local', 'cookie', 'session', 'memory'],
    storage: 'local',
    expireDays: 3650
};

createApp(homeDocsApp).use(router).use(localStorage, localStorageBasilOptions).use(axios, {}).use(api).use(utils).mount('#app');
