<template>
    <ul class="my-courses-navigation" v-if="navigationLength > 0">
        <li v-for="nav in navigation" class="my-courses-navigation-item" :class="nav.class">
            <a v-if="nav" :href="nav.url" v-bind="nav.attr">
                <studip-icon :shape="nav.icon.shape" :role="nav.icon.role" :size="iconSize"></studip-icon>
            </a>
            <span v-else class="empty-slot" :style="{width: `${iconSize}px`}"></span>
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
$icon-padding: 3px;

.my-courses-navigation {
    list-style: none;
    margin: 0;
    margin-bottom: -10px;
    padding: 0;

    display: flex;
    flex-wrap: wrap;
}
.my-courses-navigation-item {
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
    }

    .empty-slot {
        display: inline-block;
        padding-left: $icon-padding;
        padding-right: $icon-padding;
    }
}
.my-courses-navigation-important {
    $border-width: 1px;
    border: $border-width solid mixins.$red;

    a {
        padding: $icon-padding - $border-width;
    }
    html.high-contrast-mode-activated &  {
        a {
            border: 1px dashed mixins.$black;
        }

        img,
        svg {
            filter: grayscale(100%) invert(1);
        }
    }
}
</style>
