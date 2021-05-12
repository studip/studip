<template>
    <div class="cw-blockadder-item" :class="['cw-blockadder-item-' + type]" @click="addContainer">
        <header class="cw-blockadder-item-title">
            {{ title }}
        </header>
        <p class="cw-blockadder-item-description">
            {{ description }}
        </p>
    </div>
</template>
<script>
import { mapActions } from 'vuex';
export default {
    name: 'courseware-container-adder-item',
    components: {},
    props: {
        title: String,
        description: String,
        type: String,
        colspan: String,
        firstSection: String,
    },
    methods: {
        ...mapActions({
            createContainer: 'createContainer',
            companionSuccess: 'companionSuccess',
        }),
        async addContainer() {
            let attributes = {};
            attributes["container-type"] = this.type;
            attributes.payload = {
                colspan: this.colspan,
                sections: [{ name: this.firstSection, icon: '', blocks: [] }],
            };
            await this.createContainer({ structuralElementId: this.$route.params.id, attributes: attributes });
            this.companionSuccess({
                info: this.$gettext('Container wurde erfolgreich eingefügt.'),
            });
        },
    },
    mounted() {},
};
</script>
