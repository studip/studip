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
        secondSection: String,
    },
    methods: {
        ...mapActions({
            createContainer: 'createContainer',
            companionSuccess: 'companionSuccess',
        }),
        async addContainer() {
            let attributes = {};
            attributes["container-type"] = this.type;
            let sections = [];
            if (this.type === 'list') {
                sections = [{ name: this.firstSection, icon: '', blocks: [] }];
            } else {
                sections = [{ name: this.firstSection, icon: '', blocks: [] },{ name: this.secondSection, icon: '', blocks: [] }];
            }
            attributes.payload = {
                colspan: this.colspan,
                sections: sections,
            };
            await this.createContainer({ structuralElementId: this.$route.params.id, attributes: attributes });
            this.companionSuccess({
                info: this.$gettext('Abschnitt wurde erfolgreich eingefügt.'),
            });
        },
    },
    mounted() {},
};
</script>
