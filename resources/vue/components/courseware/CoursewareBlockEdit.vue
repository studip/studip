<template>
    <section class="cw-block-edit" @click="deactivateToolbar">
        <header><translate>Bearbeiten</translate></header>
        <div class="cw-block-features-content">
            <slot name="edit" />
            <div class="cw-button-box">
                <button class="button" @click="$emit('store'); exitHandler = false;"><translate>Speichern</translate></button>
                <button class="button" @click="$emit('close'); exitHandler = false;"><translate>Abbrechen</translate></button>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    name: 'courseware-block-edit',
    props: {
        block: Object,
    },
    data() {
        return {
            originalBlock: Object,
            exitHandler: true
        };
    },
    beforeMount() {
        this.originalBlock = this.block;
    },
    methods: {
        deactivateToolbar() {
            this.$store.dispatch('coursewareShowToolbar', false);
        },
    },
    beforeDestroy() {
        if (this.exitHandler) {
            console.log('autosave');
            this.$emit('store');
        }
    }
};
</script>
