<template>
    <div class="cw-dashboard-progress">
        <div class="cw-dashboard-progress-breadcrumb">
            <span v-if="currentChapter.parent_id !== null" @click="getRoot"><studip-icon shape="home" /></span>
            <span v-if="currentChapter.parent_id !== null" @click="selectChapter(currentChapter.parent_id)">
                / {{ currentChapter.parent_name }}</span
            >
        </div>
        <div class="cw-dashboard-progress-chapter">
                <h1><a :href="chapterUrl">{{ currentChapter.name }}</a></h1>
            <courseware-progress-circle
                :title="$gettext('diese Seite inkl. darunter liegende Seiten')"
                :value="parseInt(currentChapter.progress.total)"
            />
            <courseware-progress-circle
                :title="$gettext('diese Seite')"
                class="cw-dashboard-progress-current"
                :value="parseInt(currentChapter.progress.current)"
            />
        </div>
        <div class="cw-dashboard-progress-subchapter-list">
            <courseware-dashboard-progress-item
                v-for="chapter in currentChapter.children"
                :key="chapter.id"
                :name="chapter.name"
                :value="chapter.progress.total"
                :chapterId="chapter.id"
                @selectChapter="selectChapter"
            />
            <div v-if="currentChapter.children.length === 0">
                <translate>Dieses Seite enthält keine darunter liegenden Seiten</translate>
            </div>
        </div>
    </div>
</template>

<script>
import StudipIcon from '../StudipIcon.vue';
import CoursewareDashboardProgressItem from './CoursewareDashboardProgressItem.vue';
import CoursewareProgressCircle from './CoursewareProgressCircle.vue';

export default {
    name: 'courseware-dashboard-progress',
    components: {
        CoursewareDashboardProgressItem,
        CoursewareProgressCircle,
        StudipIcon,
    },
    data() {
        return {
            currentProgressData: 0,
        };
    },
    computed: {
        progressData() {
            return STUDIP.courseware_progress_data;
        },
        currentChapter() {
            return this.progressData[this.currentProgressData];
        },
        chapterUrl() {
            return STUDIP.URLHelper.base_url + 'dispatch.php/course/courseware/?cid=' + STUDIP.URLHelper.parameters.cid + '#/structural_element/' + this.currentChapter.id;
        },
    },
    methods: {
        getRoot() {
            this.progressData.every((element, index) => {
                if (element.parent_id === null) {
                    this.currentProgressData = index;
                    return false;
                } else {
                    return true;
                }
            });
        },
        selectChapter(id) {
            this.progressData.every((element, index) => {
                if (element.id === id) {
                    this.currentProgressData = index;
                    return false;
                } else {
                    return true;
                }
            });
        },
    },
    mounted() {
        this.getRoot();
    },
};
</script>
