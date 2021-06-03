<template>
    <div
        class="cw-manager-filing"
        :class="{ 'cw-manager-filing-active': active, 'cw-manager-filing-disabled': disabled }"
        @click="toggleFiling"
    >
        <span v-if="itemType === 'element'"><translate>Seite</translate> </span>
        <span v-if="itemType === 'container'"><translate>Abschnitt</translate> </span>
        <span v-if="itemType === 'block'"><translate>Block</translate> </span>
        <translate>an dieser Stelle einfügen</translate>
    </div>
</template>

<script>
export default {
    name: 'courseware-manager-filing',
    props: {
        parentId: String,
        parentItem: Object,
        itemType: String, // element || container || block
    },
    data() {
        return {
            active: false,
            disabled: false,
            data: {},
        };
    },
    computed: {
        filingData() {
            return this.$store.getters.filingData;
        },
    },
    methods: {
        toggleFiling() {
            if (this.disabled) {
                return false;
            }
            if (this.active) {
                this.$store.dispatch('cwManagerFilingData', {});
            } else {
                this.$store.dispatch('cwManagerFilingData', { parentId: this.parentId, itemType: this.itemType, parentItem: this.parentItem });
            }
        },
    },
    watch: {
        filingData(newValue, oldValue) {
            if (Object.keys(newValue).length !== 0) {
                if (newValue.parentId === this.parentId && newValue.itemType === this.itemType) {
                    this.active = true;
                } else {
                    this.disabled = true;
                }
            } else {
                this.active = false;
                this.disabled = false;
                if (Object.keys(oldValue).length !== 0) {
                    if (oldValue.parentId === this.parentId && oldValue.itemType === this.itemType) {
                        this.$emit('deactivated');
                    }
                }
            }
        },
    },
};
</script>
