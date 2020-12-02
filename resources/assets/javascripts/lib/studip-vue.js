let globalComponentsRegistered = false;
function registerGlobalComponents (Vue) {
    if (globalComponentsRegistered) {
        return;
    }

    const files = require.context('../../../vue/components', true, /\.vue$/i);

    files.keys().map(key => {
        Vue.component(
            key
            .split('/')
            .pop()
            .split('.')[0],
            files(key).default
        )
    });

    globalComponentsRegistered = true;
};

const StudipVue = {
    Vue: null,
    Bus: null,

    register (globalComponents = false) {
        return STUDIP.loadChunk('vue').then(Vue => {
            if (this.Vue === null) {
                Vue.mixin({
                    methods: {
                        globalEmit (...args) {
                            StudipVue.emit(...args);
                        },
                        globalOn (...args) {
                            StudipVue.on(...args);
                        },
                    },
                });

                StudipVue.Vue = Vue;
                StudipVue.Bus = new Vue();

                window.Vue = Vue; // TODO: remove
            }
            return Vue;
        }).then(Vue => {
            if (globalComponents) {
                registerGlobalComponents(Vue);
            }
            return Vue;
        });
    },
    async createApp (element, data = {}, additionalOptions = {}, globalComponents = true) {
        return await StudipVue.register(globalComponents).then(Vue => {
            const config = Object.assign({
                el: element,
                data: data
            }, additionalOptions);
            return new Vue(config);
        });
    },
    emit (eventName, ...args) {
        StudipVue.register().then(() => {
            StudipVue.Bus.$emit(eventName, ...args);
        })
    },
    on (eventName, ...args) {
        StudipVue.register().then(() => {
            StudipVue.Bus.$on(eventName, ...args);
        });
    }
};

export default StudipVue;
