import { mapGetters } from 'vuex';

const containerMixin = {
    computed: {
        ...mapGetters(['pluginManager']),
    },
    created: function () {
        this.pluginManager.registerComponentsLocally(this);
    },
};

export default containerMixin;
