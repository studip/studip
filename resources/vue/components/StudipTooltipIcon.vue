<template>
    <span data-tooltip class="tooltip tooltip-icon" :class="cssClass" :title="title">
        <span class="tooltip-content" v-if="isHtml" v-html="text"></span>
        <studip-icon shape="info-circle" role="inactive" :size="size"></studip-icon>
    </span>
</template>

<script>
    export default {
        name: 'studip-tooltip-icon',
        props: {
            text: String,
            isHtml: {
                type: Boolean,
                required: false,
                default: false
            },
            isImportant: {
                type: Boolean,
                required: false,
                default: false
            },
            size: {
                type: Number,
                default: 16
            }
        },
        computed: {
            cssClass () {
                return this.isImportant ? 'tooltip-important' : '';
            },
            title () {
                return !this.isHtml ? this.text : '';
            }
        }
    }
</script>

<style lang="scss" scoped>
.tooltip.tooltip-icon::before {
    display: none;
}
</style>
