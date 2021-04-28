import MyCourses from '../../../vue/components/MyCourses.vue';
import storeConfig from '../../../vue/store/MyCoursesStore.js';

STUDIP.domReady(async () => {
    if ($('.my-courses-vue-app').length === 0) {
        return;
    }

    const { createApp, store } = await STUDIP.Vue.load();

    store.registerModule('mycourses', storeConfig);

    store.commit('mycourses/setCourses', window.STUDIP.MyCoursesData['courses']);
    store.commit('mycourses/setGroups', window.STUDIP.MyCoursesData['groups']);
    store.commit('mycourses/setUserId', window.STUDIP.MyCoursesData['user_id']);
    store.commit('mycourses/setConfig', window.STUDIP.MyCoursesData['config']);

    const vm = createApp({
        components: { MyCourses }
    });
    vm.$mount('.my-courses-vue-app');
});
