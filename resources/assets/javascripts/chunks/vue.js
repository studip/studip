import Vue from 'vue';
import Vuex from 'vuex';
import Router from "vue-router";
import eventBus from '../lib/event-bus.js';
import GetTextPlugin from 'vue-gettext';
import { getLocale, getVueConfig } from '../lib/gettext.js';
import PortalVue from 'portal-vue';
import BaseComponents from '../../../vue/base-components.js';
import BaseDirectives from "../../../vue/base-directives.js";

Vue.mixin({
    methods: {
        globalEmit(...args) {
            eventBus.emit(...args);
        },
        globalOn(...args) {
            eventBus.on(...args);
        },
    },
});

Vue.use(GetTextPlugin, getVueConfig());
eventBus.on('studip:set-locale', (locale) => {
    Vue.config.language = locale;
})

registerGlobalComponents();
registerGlobalDirectives();

Vue.use(Vuex);
const store = new Vuex.Store({});

Vue.use(Router);

Vue.use(PortalVue);

function createApp(options, ...args) {
    return new Vue({ store, ...options }, ...args);
}

function registerGlobalComponents() {
    for (const [name, component] of Object.entries(BaseComponents)) {
        Vue.component(name, component);
    }
}

function registerGlobalDirectives() {
    for (const [name, directive] of Object.entries(BaseDirectives)) {
        Vue.directive(name, directive);
    }
}

export { Vue, createApp, eventBus, store };
