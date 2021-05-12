import DashboardApp from './components/courseware/DashboardApp.vue';

const mountApp = (STUDIP, createApp, element) => {
    const app = createApp({
        render: (h) => h(DashboardApp),
    });

    app.$mount(element);

    return app;
};

export default mountApp;
