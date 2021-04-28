<template>
    <div class="my-courses my-courses--tiles">
        <template v-for="group in groups">
            <div class="group-label">{{ group.name }}</div>
            <article class="studip" v-for="subgroup in group.data" :key="subgroup.id" :class="getGroupCssClasses(subgroup)">
                <header v-if="subgroup.label">
                    <h1>
                        <a href="#" @click.prevent.stop="toggleOpenGroup(subgroup)">{{ subgroup.label }}</a>
                    </h1>
                </header>
                <section class="studip-grid">
                    <template v-for="course in getOrderedCourses(subgroup.ids)">
                        <div class="course-group-label" v-if="isParent(course)">
                            {{ getCourseName(course, getConfig('sem_number')) }}
                        </div>

                        <article class="studip-grid-element" :data-course-id="course.id" :class="getCourseCssClasses(course)">
                            <header class="tiles-grid-element-header">
                                <span class="tiles-grid-element-options">
                                    <studip-action-menu :items="getActionMenuForCourse(course, true)"
                                                        :collapseAt="0"
                                                        class="tiles-action-menu"
                                                        v-on:show-color-picker="shownColorPicker = course.id"
                                    ></studip-action-menu>
                                </span>

                                <a v-if="!course.is_studygroup" data-dialog :href="urlFor('dispatch.php/course/details/index/' + course.id)" :title="$gettext('Veranstaltungsdetails')">
                                    <img :src="course.avatar" class="tiles-grid-element-header-image" />
                                </a>
                                <span v-else>
                                    <img :src="course.avatar" class="tiles-grid-element-header-image" />
                                </span>

                                <a :href="urlFor('seminar_main.php', {auswahl: course.id})" class="tiles-grid-element-header-content" :title="getCourseName(course, getConfig('sem_number'))">
                                    {{ getCourseName(course, getConfig('sem_number')) }}
                                    <span v-if="course.is_deputy">{{ $gettext('[Vertretung]') }}</span>

                                    <span v-if="course.is_hidden">
                                        {{ $gettext('[versteckt]') }}
                                        <studip-tooltip-icon :text="getHiddenTooltip(course)"></studip-tooltip-icon>
                                    </span>
                                </a>
                            </header>
                            <footer class="tiles-grid-element-footer">
                                <my-courses-navigation :navigation="getNavigationForCourse(course)"></my-courses-navigation>
                            </footer>

                            <my-courses-color-picker v-if="showColorPickerForCourse(course)" :course="course" v-on:color-picked="changeColor"></my-courses-color-picker>
                        </article>
                    </template>
                </section>
            </article>
        </template>
    </div>
</template>


<script>
import MyCoursesMixin from '../mixins/MyCoursesMixin.js';
import MyCoursesColorPicker from './MyCoursesColorPicker.vue';

export default {
    name: 'my-courses-tiles',
    mixins: [MyCoursesMixin],
    components: {MyCoursesColorPicker},
    data () {
        return {
            shownColorPicker: null,
        };
    },
    methods: {
        getGroupCssClasses(group) {
            if (group.label === false) {
                return ['my-courses--group-hidden'];
            }

            let classes = ['toggle'];
            if (this.isGroupOpen(group)) {
                classes.push('open');
            }

            return classes;
        },
        getCourseCssClasses(course) {
            let classes = [`my-courses-group-${course.group}`];

            if (this.isParent(course)) {
                classes.push('has-subcourses');
            }

            if (this.isChild(course)) {
                classes.push('subcourses');
            }

            if (this.showColorPickerForCourse(course)) {
                classes.push('has-color-picker');
            }

            return classes;
        },
        getOrderedCourses (ids) {
            const sorted = this.getCourses(ids);
            sorted.sort((a, b) => {
                // Sort courses with subcourses at the end
                if (this.isParent(a)) {
                    return 1;
                }
                if (this.isParent(b)) {
                    return -1;
                }

                // Sort by number and name
                return (a.group - b.group) || a.number.localeCompare(b.number) || a.name.localeCompare(b.name);
            });

            // Ensure parent / child relation
            let courses = [];
            sorted.forEach(course => {
                if (!this.isChild(course)) {
                    courses.push(course);
                }
                if (this.isParent(course)) {
                    this.getCourses(course.children).forEach(c => {
                        courses.push(c);
                    });
                }
            });

            return courses;
        },
        showColorPickerForCourse(course) {
            return this.shownColorPicker === course.id;
        },
        changeColor(course, index) {
            STUDIP.jsonapi.PATCH(`course-memberships/${course.id}_${this.userid}`, {
                data: {
                    data: {
                        type: 'course-memberships',
                        attributes: {
                            group: index
                        }
                    }
                }
            }).done(() => {
                course.group = index;
            }).always(() => {
                this.shownColorPicker = null;
            });
        },
    },
    computed: {
    }
}
</script>

<style lang="scss" scoped>
@use '../../assets/stylesheets/mixins.scss';
@use '../../assets/stylesheets/scss/breakpoints.scss' as *;
@use '../../assets/stylesheets/scss/variables.scss';
@import '../../assets/stylesheets/scss/visibility.scss'; // Needs to be imported (breakpoint variables are missing)


.studip-grid {
    $padding: 10px;
    $avatar-size: 60px;
    $header-size: $avatar-size;
    $element-height: (100px + $header-size);

    &:not(:last-child) {
        margin-bottom: 2rem;
    }

    .studip-grid-element {
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        position: relative; // For color picker

        border: 2px;
        border-style: solid;
        border-left-width: 15px;

        padding: $padding;
    }

    .tiles-grid-element-header {
        flex: 0 $header-size;
    }

    .tiles-grid-element-header-content {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        max-height: $header-size;
        overflow: hidden;
    }

    .tiles-grid-element-header-image {
        float: left;
        display: block;

        margin-right: $padding;

        width: $avatar-size;
        height: $avatar-size;
    }

    .tiles-grid-element-options {
        float: right;
    }

    .tiles-grid-element-footer {
        flex: 0 0 auto;
        &:not(:empty) {
            padding-top: 27px;
        }
    }

    .course-group-label {
        grid-column: 1 / -1;
        margin-bottom: -1em;
    }
}

.group-label,
.course-group-label {
    color: mixins.$base-gray;
}

.group-label {
    font-size: variables.$font-size-h1;

    &:not(:first-child) {
        margin-top: 1em;
    }
}
.course-group-label {
    font-size: variables.$font-size-h2;
}

article.studip.my-courses--group-hidden {
    border: 0;
    padding: 0;
    > header {
        display: none;
    }
}

// Border below according to selected group
$group-colors: (
    0: mixins.$group-color-0,
    1: mixins.$group-color-1,
    2: mixins.$group-color-2,
    3: mixins.$group-color-3,
    4: mixins.$group-color-4,
    5: mixins.$group-color-5,
    6: mixins.$group-color-6,
    7: mixins.$group-color-7,
    8: mixins.$group-color-8,
);
@for $i from 0 through 8 {
    .studip-grid-element.my-courses-group-#{$i} {
        border-color: map-get($group-colors, $i);
    }
}

// Definitions for color picker
.my-courses-color-picker {
    $gap: 0.5ex;

    display: grid;
    grid-template-rows: 1fr 1fr 1fr;
    grid-template-columns: 1fr 1fr 1fr;

    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 2;

    background: mixins.$white;
    grid-gap: $gap;
    padding: $gap;
}
</style>
