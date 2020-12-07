import Vue from 'vue';
import Vuex from 'vuex';

const eventBus = new Vue();

Vue.mixin({
    methods: {
        globalEmit(...args) {
            eventBus.$emit(...args);
        },
        globalOn(...args) {
            eventBus.$on(...args);
        },
    },
});

registerGlobalComponents(Vue);

Vue.use(Vuex);
const store = new Vuex.Store({});

function createApp(options, ...args) {
    return new Vue({ store, ...options }, ...args);
}

function registerGlobalComponents() {
    const files = require.context('../../../vue/components', true, /\.vue$/i);

    files.keys().map((key) => {
        Vue.component(key.split('/').pop().split('.')[0], files(key).default);
    });
}

export { Vue, createApp, eventBus, store };
