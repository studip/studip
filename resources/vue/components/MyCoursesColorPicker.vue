<template>
    <ul class="my-courses-color-picker">
        <li v-for="(i, index) in color_count" :id="i" :class="getCSSClasses(index)">
            <a @click="selectColor(index)" :title="getTitle(i, index)">
                {{ getTitle(i) }}
            </a>
        </li>
    </ul>
</template>

<script>
export default {
    name: "my-courses-color-picker",
    props: {
        course: {
            type: Object,
            required: true
        },
        color_count: {
            required: false,
            default: 9
        },
    },
    methods: {
        getCSSClasses (index) {
            let classes = [];
            classes.push(`gruppe${index}`);

            if (this.course.group === index) {
                classes.push('color-selected');
            }

            return classes;
        },
        getTitle (i, index) {
            let title = this.$gettext('Gruppe') + ' ' + i;
            if (this.course.group === index) {
                title += ' ('  + this.$gettext('ausgewählt') + ')';
            }
            return title;
        },
        selectColor (index) {
            this.$emit('color-picked', this.course, index);
        },
    },
    mounted () {
        // Detect safari
        if (!/^((?!chrome|android).)*safari/i.test(navigator.userAgent)) {
            return;
        }

        // Force a double redraw in css since safari won't display the
        // colorpicker otherwise
        setTimeout(() => {
            this.$el.style.position = 'static';
            setTimeout(() => {
                this.$el.style.position = '';
            }, 0);
        }, 0);
    }
}
</script>

<style lang="scss">
@use '../../assets/stylesheets/mixins.scss';

.my-courses-color-picker {
    list-style: none;
    margin: 0;
    padding: 0;

    // Hide text in color groups
    li {
        text-indent: 100%;
        overflow: hidden;
        white-space: nowrap;

        position: relative;
    }

    a {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;

        cursor: pointer;
    }

    .color-selected {
        @include mixins.background-icon(accept, info, 32px);
        background-position: center;
        background-repeat: no-repeat;
    }
}
</style>
