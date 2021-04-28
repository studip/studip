<template>
    <ul class="my-courses-navigation" v-if="navigationLength > 0">
        <li v-for="nav in navigation" class="my-courses-navigation-item" :class="nav.class">
            <a v-if="nav" :href="nav.url" v-bind="nav.attr">
                <studip-icon :shape="nav.icon.shape" :role="nav.icon.role"></studip-icon>
            </a>
            <span v-else class="empty-slot"></span>
        </li>
    </ul>
</template>

<script>
export default {
    name: 'my-courses-navigation',
    props: {
        navigation: Object,
        iconSize: {
            type: Number,
            required: false,
            default: 24,
        },
    },
    computed: {
        navigationLength () {
            return Object.keys(this.navigation).length;
        }
    }
}
</script>

<style lang="scss">
@use '../../assets/stylesheets/mixins.scss';
.my-courses-navigation {
    list-style: none;
    margin: 0;
    margin-bottom: -10px;
    padding: 0;

    display: flex;
    flex-wrap: wrap;
}
.my-courses-navigation-item {
    $icon-size: 24px;
    $icon-padding: 3px;

    margin: 0 3px 10px 0;

    a {
        display: inline-block;
        padding: $icon-padding;
    }

    &:last-child {
        margin-right: 0;
    }

    img {
        vertical-align: bottom;
        width: $icon-size;
        height: $icon-size;
    }

    .empty-slot {
        display: inline-block;
        width: ($icon-size + 2 * $icon-padding);
    }
}
.my-courses-navigation-important {
    background-color: mixins.$red;
}
</style>
