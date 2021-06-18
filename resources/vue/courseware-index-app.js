import CoursewareModule from './store/courseware/courseware.module';
import CoursewareStructuralElement from './components/courseware/CoursewareStructuralElement.vue';
import IndexApp from './components/courseware/IndexApp.vue';
import PluginManager from './components/courseware/plugin-manager.js';
import Vue from 'vue';
import VueRouter from 'vue-router';
import Vuex from 'vuex';
import axios from 'axios';
import { mapResourceModules } from '@elan-ev/reststate-vuex';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css'

Vue.component('v-select', vSelect);

const mountApp = (STUDIP, createApp, element) => {
    const getHttpClient = () =>
        axios.create({
            baseURL: STUDIP.URLHelper.getURL(`jsonapi.php/v1`, {}, true),
            headers: {
                'Content-Type': 'application/vnd.api+json',
            },
        });

    // get id of parent structural element
    let elem_id = null;
    let entry_id = null;
    let entry_type = null;
    let oer_title = null;
    let licenses = null;
    let elem;

    if ((elem = document.getElementById(element.substring(1))) !== undefined) {
        if (elem.attributes !== undefined) {
            if (elem.attributes['entry-element-id'] !== undefined) {
                elem_id = elem.attributes['entry-element-id'].value;
            }

            if (elem.attributes['entry-type'] !== undefined) {
                entry_type = elem.attributes['entry-type'].value;
            }

            if (elem.attributes['entry-id'] !== undefined) {
                entry_id = elem.attributes['entry-id'].value;
            }

            if (elem.attributes['oer-title'] !== undefined) {
                oer_title = elem.attributes['oer-title'].value;
            }
            // we need a route for License SORM
            if (elem.attributes['licenses'] !== undefined) {
                licenses = JSON.parse(elem.attributes['licenses'].value);
            }
        }
    }

    const routes = [
        {
            path: '/',
            redirect: '/structural_element/' + elem_id,
        },
        {
            path: '/structural_element/:id',
            name: 'CoursewareStructuralElement',
            component: CoursewareStructuralElement,
        },
    ];

    const base = `${STUDIP.ABSOLUTE_URI_STUDIP}dispatch.php/course/courseware/?cid=${STUDIP.URLHelper.parameters.cid}`;
    const router = new VueRouter({
        base,
        routes,
    });

    const httpClient = getHttpClient();

    const store = new Vuex.Store({
        modules: {
            courseware: CoursewareModule,
            ...mapResourceModules({
                names: [
                    'courses',
                    'course-memberships',
                    'courseware-blocks',
                    'courseware-block-comments',
                    'courseware-block-feedback',
                    'courseware-containers',
                    'courseware-instances',
                    'courseware-structural-elements',
                    'courseware-user-data-fields',
                    'courseware-user-progresses',
                    'files',
                    'file-refs',
                    'folders',
                    'status-groups',
                    'users',
                    'institutes',
                    'semesters',
                    'sem-classes',
                    'sem-types',
                    'terms-of-use'
                ],
                httpClient,
            }),
        },
    });

    store.dispatch('setUrlHelper', STUDIP.URLHelper);
    store.dispatch('setUserId', STUDIP.USER_ID);
    store.dispatch('users/loadById', {id: STUDIP.USER_ID});
    store.dispatch('setHttpClient', httpClient);

    store.dispatch('coursewareContext', {
        id: entry_id,
        type: entry_type,
    });

    store.dispatch('coursewareCurrentElement', elem_id);

    store.dispatch('oerTitle', oer_title);
    store.dispatch('licenses', licenses);

    const pluginManager = new PluginManager();
    store.dispatch('setPluginManager', pluginManager);
    STUDIP.eventBus.emit('courseware:init-plugin-manager', pluginManager);

    const app = createApp({
        render: (h) => h(IndexApp),
        router,
        store,
    });

    app.$mount(element);

    return app;
};

export default mountApp;
