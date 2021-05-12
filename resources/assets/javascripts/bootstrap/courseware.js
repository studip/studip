STUDIP.domReady(() => {
    if (document.getElementById('courseware-index-app')) {
        STUDIP.Vue.load().then(({ createApp }) => {
            import(
                /* webpackChunkName: "courseware-index-app" */
                '@/vue/courseware-index-app.js'
            ).then(({ default: mountApp }) => {
                return mountApp(STUDIP, createApp, '#courseware-index-app');
            });
        });
    }

    if (document.getElementById('courseware-dashboard-app')) {
        STUDIP.Vue.load().then(({ createApp }) => {
            import(
                /* webpackChunkName: "courseware-dashboard-app" */
                '@/vue/courseware-dashboard-app.js'
            ).then(({ default: mountApp }) => {
                return mountApp(STUDIP, createApp, '#courseware-dashboard-app');
            });
        });
    }

    if (document.getElementById('courseware-manager-app')) {
        STUDIP.Vue.load().then(({ createApp }) => {
            import(
                /* webpackChunkName: "courseware-manager-app" */
                '@/vue/courseware-manager-app.js'
            ).then(({ default: mountApp }) => {
                return mountApp(STUDIP, createApp, '#courseware-manager-app');
            });
        });
    }
});
