const configMapping = {
    display_type: value => {
        return {
            MY_COURSES_TILED_DISPLAY: value === 'tiles',
        }
    },
    responsive_type: value => {
        return {
            MY_COURSES_TILED_DISPLAY_RESPONSIVE: value === 'tiles',
        }
    },
    navigation_show_only_new: value => {
        return {
            MY_COURSES_SHOW_NEW_ICONS_ONLY: value,
        };
    },
    open_groups: value => {
        return {
            MY_COURSES_OPEN_GROUPS: value,
        };
    },

};

export default {
    namespaced: true,

    state: () => ({
        courses: {},
        groups: {},
        userid: null,
        config: {},
    }),

    getters: {
        isGroupOpen: (state) => (group) => {
            if (state.config.group_by === 'sem_number') {
                return true;
            }
            return state.config.open_groups.includes(group.id);
        },
        getConfig: (state) => (key) => {
            return state.config[key];
        },
    },

    mutations: {
        setCourses (state, courses) {
            state.courses = courses;
        },
        setGroups (state, groups) {
            state.groups = groups;
        },
        setUserId (state, userid) {
            state.userid = userid;
        },
        setConfig (state, config) {
            state.config = config;
        },
    },

    actions: {
        updateConfigValue({ commit, state }, { key, value }) {
            commit('setConfig', { ...state.config, [key]: value });

            // do we have to store this on the server?
            if (!configMapping[key]) {
                return Promise.resolve(null);
            }

            const configValue = configMapping[key](value);
            const configKey = Object.keys(configValue)[0];
            const documentId = `${state.userid}_${configKey}`;

            const data = {
                id: documentId,
                type: 'config-values',
                attributes: { value: configValue[configKey] }
            };

            return STUDIP.jsonapi.PATCH(`config-values/${documentId}`, { data: { data } })
        },
        toggleOpenGroup ({ state, dispatch }, group) {
            let open_groups = [ ...state.config.open_groups ];
            if (open_groups.includes(group.id)) {
                open_groups = open_groups.filter(item => item != group.id);
            } else {
                open_groups.push(group.id);
            }
            return dispatch('updateConfigValue', { key: 'open_groups', value: open_groups });
        }
    }
}
