import Vue from 'vue';
import Vuex from 'vuex';
import BaseComponents from '../../../vue/components/base-components.js';

const eventBus = new Vue();

Vue.mixin({
    methods: {
        globalEmit(...args) {
            eventBus.$emit(...args);
        },
        globalOn(...args) {
            eventBus.$on(...args);
        },
        t: aString => aString.toLocaleString(),
    },
});

registerGlobalComponents(Vue);

Vue.use(Vuex);
const store = new Vuex.Store({});

function createApp(options, ...args) {
    return new Vue({ store, ...options }, ...args);
}

function registerGlobalComponents() {
    for (const [name, component] of Object.entries(BaseComponents)) {
        Vue.component(name, component);
    }
}

export { Vue, createApp, eventBus, store };
