<template>
    <div :class="classNames" v-if="!closed">
        <div class="messagebox_buttons">
            <a v-if="!hideClose" class="close" href="" title="Nachrichtenbox schließen" @click.prevent="closed = true">
                <span>Nachrichtenbox schließen</span>
            </a>
        </div>
        <slot></slot>
        <div v-if="details.length" class="messagebox_details">
            <ul>
                <li v-for="detail in details">{{ detail }}</li>
            </ul>
        </div>
    </div>
</template>

<script>
export default {
    name: 'studip-message-box',
    props: {
        type: {
            type: String, // exception, error, success, info, warning
            default: 'info',
        },
        details: {
            type: Array,
            default: [],
        },
        hideClose: {
            type: Boolean,
            default: false,
        },
    },
    computed: {
        classNames() {
            return {
                messagebox: true,
                [`messagebox_${this.type}`]: true,
                details_hidden: this.closeDetails && this.details.length,
            };
        },
    },
    data() {
        return {
            closed: false,
        };
    },
};
</script>
