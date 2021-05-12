<template>
    <div
        class="cw-block-adder-area"
        :class="{ 'cw-block-adder-active': adderActive, 'cw-block-adder-disabled': adderDisable }"
        @click="selectBlockAdder"
    >
        <translate>Block hinzufügen</translate>
    </div>
</template>

<script>
export default {
    name: 'courseware-block-adder-area',
    props: {
        container: Object,
        section: Number,
    },
    data() {
        return {
            adderActive: false,
        };
    },
    computed: {
        adderDisable() {
            return Object.keys(this.$store.getters.blockAdder).length !== 0 && !this.adderActive;
        },
        adderStorage() {
            return this.$store.getters.blockAdder;
        },
    },
    methods: {
        selectBlockAdder() {
            if (this.adderDisable) {
                return false;
            }
            if (this.adderActive) {
                this.adderActive = false;
                this.$store.dispatch('coursewareBlockAdder', {});
            } else {
                this.adderActive = true;
                this.$store.dispatch('coursewareBlockAdder', { container: this.container, section: this.section });
                this.$store.dispatch('coursewareShowToolbar', true);
            }
        },
    },
    watch: {
        adderStorage(newValue, oldValue) {
            if (Object.keys(newValue).length === 0) {
                this.adderActive = false;
                this.$emit('updateContainerContent', oldValue)
            }
        },
    },
};
</script>
