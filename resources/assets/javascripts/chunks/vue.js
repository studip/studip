import Vue from 'vue';

const eventBus = new Vue();

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

registerGlobalComponents(Vue);

function createApp(...args) {
    return new Vue(...args);
}

function registerGlobalComponents() {
    const files = require.context('../../../vue/components', true, /\.vue$/i);

    files.keys().map((key) => {
        Vue.component(key.split('/').pop().split('.')[0], files(key).default);
    });
}

export { Vue, createApp, eventBus };
