import Responsive from '../../assets/javascripts/lib/responsive.js';

import { mapState, mapActions, mapGetters } from 'vuex';
import MyCoursesNavigation from '../components/MyCoursesNavigation.vue';

export default {
    components: { MyCoursesNavigation },
    data () {
        return {
            responsiveDisplay: false,
        };
    },
    methods: {
        ...mapActions('mycourses', [
            'toggleOpenGroup',
            'updateConfigValue',
        ]),

        getCourseName(course, include_number = false) {
            let name = course.name;
            if (include_number) {
                name = `${course.number} ${course.name}`;
            }
            return name.trim();
        },

        urlFor(url, parameters, ignore_params) {
            return STUDIP.URLHelper.getURL(url, parameters, ignore_params);
        },

        getCourses (ids) {
            return ids.map(id => this.courses[id]);
        },

        isParent (course) {
            return course.children.length > 0 && course.children.every(childId => {
                return this.courses[childId] !== undefined;
            });
        },
        isChild (course) {
            return course.parent !== null && this.courses[course.parent] !== undefined;
        },

        getHiddenTooltip(course) {
            let infotext = this.$gettext('Versteckte Veranstaltungen können über die Suchfunktionen nicht gefunden werden.');
            infotext += ' ';
            if (course.is_teacher && this.getConfig('allow_dozent_visibility')) {
                infotext += this.$gettext('Um die Veranstaltung sichtbar zu machen, wählen Sie den Punkt "Sichtbarkeit" im Administrationsbereich der Veranstaltung.');
            } else {
                infotext += this.$gettext('Um die Veranstaltung sichtbar zu machen, wenden Sie sich an Administrierende.');
            }
            return infotext;
        },

        getActionMenuForCourse(course, withColorPicker = false) {
            let menu = [];

            if (!course.is_studygroup) {
                menu.push({
                    url: this.urlFor(`dispatch.php/course/details/index/${course.id}`, {from: this.urlFor('dispatch.php/my_courses/index')}),
                    label: this.$gettext('Veranstaltungsdetails'),
                    icon: 'info-circle',
                    attributes: {
                        'data-dialog': ''
                    },
                });
            }

            if (withColorPicker) {
                // Color grouping
                menu.push({
                    emit: 'show-color-picker',
                    emitArguments: [course],
                    label: this.$gettext('Farbgruppierung ändern'),
                    icon: 'group4'
                });
            }

            // Extra navigation?
            if (!course.is_group) {
                if (course.extra_navigation) {
                    menu.push(course.extra_navigation);
                } else if (course.admission_binding) {
                    menu.push({
                        url: this.urlFor('dispatch.php/my_courses/decline_binding'),
                        label: this.$gettext('Aus der Veranstaltung austragen'),
                        icon: 'decline/door-leave',
                        attributes: {
                            title: this.$gettext('Die Teilnahme ist bindend. Bitte wenden Sie sich an die Lehrenden.'),
                        },
                        disabled: true
                    });
                } else {
                    menu.push({
                        url: this.urlFor(`dispatch.php/my_courses/decline/${course.id}`, {cmd: 'suppose_to_kill'}),
                        label: this.$gettext('Aus der Veranstaltung austragen'),
                        icon: 'door-leave'
                    });
                }
            }

            return menu;
        },

        getNavigationForCourse(course, gaps = false) {
            let navigation = {};

            Object.entries(course.navigation).forEach(([key, nav]) => {
                if (!nav && !gaps) {
                    return;
                }

                if (this.getConfig('navigation_show_only_new') && !nav.important) {
                    return;
                }

                let result = nav ? Object.assign({}, nav) : false;
                if (nav) {
                    if (nav.important) {
                        result.class = 'my-courses-navigation-important';
                        result.icon.role = 'attention';
                        result.icon.shape = result.icon.shape.replace(/^new\//, '');
                    } else {
                        result.class = false;
                        result.icon.role = 'clickable';
                    }

                    result.url = this.urlFor('seminar_main.php', {
                        auswahl: course.id,
                        redirect_to: result.url,
                    });
                }

                navigation[key] = result;
            });

            return navigation;
        },
    },

    computed: {
        ...mapState('mycourses', [
            'courses',
            'groups',
            'userid',
            'config',
        ]),
        ...mapGetters('mycourses', [
            'isGroupOpen',
            'getConfig',
        ]),

        viewConfig () {
            return this.responsiveDisplay ? 'responsive_type' : 'display_type';
        },
        numberOfNavElements () {
            return Math.max(
                ...Object.values(this.courses).map(course => {
                    const navigation = this.getNavigationForCourse(course, true);
                    return Object.values(navigation).length;
                })
            );
        }
    },

    created () {
        this.responsiveDisplay = Responsive.media_query.matches;
        Responsive.media_query.addListener(() => {
            console.log('changing responsive display', Responsive.media_query.matches);
            this.responsiveDisplay = Responsive.media_query.matches;
            console.log('changed responsive display', this.responsiveDisplay);
        })
    }
}
