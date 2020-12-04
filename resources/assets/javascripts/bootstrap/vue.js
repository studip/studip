/**
 * The following block of code is used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */
STUDIP.ready(() => {
    $('[data-vue-app]').each(function () {
        const instance = $(this).data().vueAppInstance;
        if ($(this).is('[data-vue-app-created]')) {
            return;
        }

        const config = Object.assign({}, $(this).data().vueApp, {
            id: false,
            components: []
        });

        let data = {};
        if (config.id && window.STUDIP.AppData && window.STUDIP.AppData.hasOwnProperty(config.id)) {
            data = window.STUDIP.AppData[config.id];
        }

        let components = {};
        config.components.forEach(component => {
            components[component] = () => import(`../../../vue/components/${component}.vue`);
        });

        STUDIP.Vue.load().then(({createApp}) => {
            createApp({
                el: this,
                data,
                components
            });
        });

        $(this).attr('data-vue-app-created', '');
    });
});
