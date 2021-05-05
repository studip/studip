<template>
    <div id="my_seminars">
        <table class="default collapsable mycourses" v-for="group in groups" :key="group.id">
            <caption> {{ group.name }}</caption>
            <colgroup>
                <col style="width: 7px">
                <col style="width: 25px">
                <col style="width: 70px" v-if="getConfig('sem_number') && !responsiveDisplay">
                <col>
                <col v-if="!responsiveDisplay" :style="{width: (2 * 5 + numberOfNavElements * (iconSize + 2 * 3 + 3) - 3) + 'px'}">
                <col v-if="!responsiveDisplay" style="width: 24px">
            </colgroup>
            <thead>
                <tr class="sortable">
                    <th></th>
                    <th></th>
                    <th v-if="getConfig('sem_number') && !responsiveDisplay" :class="getOrderClasses('number')">
                        <a href="#" @click.prevent="changeOrder('number')">
                            {{ $gettext('Nr.') }}
                        </a>
                    </th>
                    <th :class="getOrderClasses('name')">
                        <a href="#" @click.prevent="changeOrder('name')">
                            {{ $gettext('Name') }}
                        </a>
                    </th>
                    <th v-if="!responsiveDisplay" >{{ $gettext('Inhalt') }}</th>
                    <th v-if="!responsiveDisplay"></th>
                </tr>
            </thead>
            <tbody v-for="subgroup in group.data" :key="subgroup.id" :class="{collapsed: !isGroupOpen(subgroup)}">
                <tr class="header-row" v-if="subgroup.label !== false">
                    <th style="white-space: nowrap; text-align: left"></th>
                    <th class="toggle-indicator" :colspan="(getConfig('sem_number') && !responsiveDisplay) ? 3 : 2">
                        <a href="#" @click.prevent.stop="toggleOpenGroup(subgroup)">{{ subgroup.label }}</a>
                    </th>
                    <th v-if="!responsiveDisplay" class="dont-hide" colspan="2"></th>
                </tr>
                <tr v-for="course in getOrderedCourses(subgroup.ids)" :data-course-id="course.id" :class="getCourseClasses(course)">
                    <td :class="`gruppe${course.group}`"></td>
                    <td :class="{'subcourse-indented': isChild(course)}">
                        <span :style="{backgroundImage: `url(${course.avatar}`}" class="my-courses-avatar course-avatar-small" :title="course.name" alt=""></span>
                    </td>
                    <td v-if="getConfig('sem_number') && !responsiveDisplay"  :class="{'subcourse-indented': isChild(course)}">
                        {{ course.number }}
                    </td>
                    <td :class="{'subcourse-indented': isChild(course)}">
                        <a :href="urlFor('seminar_main.php', {auswahl: course.id})">
                            {{ getCourseName(course, getConfig('sem_number') && responsiveDisplay) }}
                            <span v-if="course.is_deputy">{{ $gettext('[Vertretung]') }}</span>

                            <span v-if="course.is_hidden">
                                {{ $gettext('[versteckt]') }}
                                <studip-tooltip-icon :text="getHiddenTooltip(course)"></studip-tooltip-icon>
                            </span>
                        </a>
                        <div v-if="responsiveDisplay" class="mycourse_elements">
                            <div class="special_nav">
                                <studip-action-menu :items="getActionMenuForCourse(course)"
                                                    :collapseAt="false"
                                                    v-on:show-color-picker="shownColorPicker = course.id"
                                ></studip-action-menu>
                            </div>

                            <my-courses-navigation :navigation="getNavigationForCourse(course)" :icon-size="iconSize"></my-courses-navigation>
                        </div>
                    </td>
                    <td v-if="!responsiveDisplay" class="course-navigation">
                        <my-courses-navigation :navigation="getNavigationForCourse(course, true)" :icon-size="iconSize"></my-courses-navigation>
                    </td>
                    <td v-if="!responsiveDisplay" class="actions">
                        <studip-action-menu :items="getActionMenuForCourse(course)"
                                            :collapseAt="2"
                        ></studip-action-menu>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import MyCoursesMixin from '../mixins/MyCoursesMixin.js';

export default {
    name: 'MyCoursesTables',
    mixins: [MyCoursesMixin],
    props: {
        iconSize: {
            type: Number,
            required: false,
            default: 16
        }
    },
    data () {
        return {
            orderBy: 'group',
            orderDir: 'asc'
        }
    },
    methods: {
        changeOrder (by) {
            if (this.orderBy === by) {
                this.orderDir = this.orderDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.orderBy = by;
                this.orderDir = 'asc';
            }
        },
        getCourseClasses (course) {
            return {
                'has-subcourses': this.isParent(course),
                subcourses: this.isChild(course),
            };
        },
        getOrderedCourses (ids) {
            const sorted = this.getCourses(ids);
            const dirFactor = this.orderDir === 'desc' ? -1 : 1;
            if (this.orderBy === 'name') {
                sorted.sort((a, b) => a.name.localeCompare(b.name) * dirFactor);
            } else if (this.orderBy === 'number') {
                sorted.sort((a, b) => a.number.localeCompare(b.number) * dirFactor);
            }

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
        getOrderClasses (by) {
            if (by !== this.orderBy) {
                return [];
            }
            return this.orderDir === 'asc' ? ['sortasc'] : ['sortdesc'];
        }
    }
}
</script>

<style lang="scss">
@use '../../assets/stylesheets/mixins/colors.scss' as *;

table.mycourses {
    tbody td {
        vertical-align: top;

        &.actions,
        &.course-navigation {
            vertical-align: middle;
        }
    }

    .special_nav {
        float: right;
    }

    tr.has-subcourses td {
        border-bottom: 1px solid $dark-gray-color-75;
    }
    tr.subcourses {
        background-color: $dark-gray-color-5;

        td.subcourse-indented {
            padding-left: 20px;
        }
    }
}
.my-courses-avatar.course-avatar-small {
    background-position: center;
    background-repeat: no-repeat;
    background-size: cover;
    display: inline-block;
    height: 25px;
    width: 25px;
}
</style>
