<template>
    <div class="cw-manager-copy-selector">
        <div v-if="sourceEmpty" class="cw-manager-copy-selector-source">
            <button class="hugebutton" @click="selectSource('own'); loadOwnCourseware()"><translate>Aus meine Inhalte kopieren</translate></button>
            <button class="hugebutton" @click="selectSource('remote')"><translate>Aus Veranstaltung kopieren</translate></button>
        </div>
        <div v-else>
            <button class="button" @click="reset"><translate>Quelle auswählen</translate></button>
            <div v-if="sourceRemote">
                <h2 v-if="!hasRemoteCid"><translate>Veranstaltungen</translate></h2>
                <ul v-if="!hasRemoteCid">
                    <li v-for="course in courses" :key="course.id" >
                        <button class="hugebutton" @click="loadRemoteCourseware(course.id)">{{course.attributes.title}}</button>
                    </li>
                </ul>
                <courseware-manager-element
                    v-if="hasRemoteCid"
                    type="remote"
                    :currentElement="remoteElement"
                    @selectElement="setRemoteId"
                    @loadSelf="loadSelf"
                />
            </div>
            <div v-if="sourceOwn">
                <courseware-manager-element
                    v-if="ownId !== ''"
                    type="own"
                    :currentElement="ownElement"
                    @selectElement="setOwnId"
                    @loadSelf="loadSelf"
                />
                <courseware-companion-box
                    v-else
                    :msgCompanion="$gettext('Sie haben noch keine eigenen Inhalte angelegt')"
                    mood="sad"
                />
            </div>
        </div>
    </div>
</template>

<script>
import CoursewareManagerElement from './CoursewareManagerElement.vue';
import { mapActions, mapGetters } from 'vuex';
import CoursewareCompanionBox from './CoursewareCompanionBox.vue';

export default {
    name: 'courseware-manager-copy-selector',
    components:{
        CoursewareManagerElement,
        CoursewareCompanionBox,
    },
    props: {},
    data() {return{
        source: '',
        courses: [],
        remoteCid: '',
        remoteCoursewareInstance: {},
        remoteId: '',
        remoteElement: {},
        ownCoursewareInstance: {},
        ownId: '',
        ownElement: {},

    }},
    computed: {
        ...mapGetters({
            userId: 'userId',
            structuralElementById: 'courseware-structural-elements/byId',
        }),
        sourceEmpty() {
            return this.source === '';
        },
        sourceOwn() {
            return this.source === 'own';
        },
        sourceRemote() {
            return this.source === 'remote';
        },
        hasRemoteCid() {
            return this.remoteCid !== '';
        },
    },
    methods: {
        ...mapActions({
            loadUsersCourses: 'loadUsersCourses',
            loadStructuralElement: 'loadStructuralElement',
            loadRemoteCoursewareStructure: 'loadRemoteCoursewareStructure',
        }),
        selectSource(source) {
            this.source = source;
        },
        async loadRemoteCourseware(cid) {
            this.remoteCid = cid;
            this.remoteCoursewareInstance = await this.loadRemoteCoursewareStructure({rangeId: this.remoteCid, rangeType: 'courses'});
            if (this.remoteCoursewareInstance !== null) {
                this.setRemoteId(this.remoteCoursewareInstance.relationships.root.data.id);
            } else {
                console.debug('can not load');
            }
            
        },
        async loadOwnCourseware() {
            this.ownCoursewareInstance = await this.loadRemoteCoursewareStructure({rangeId: this.userId, rangeType: 'users'});
            if (this.ownCoursewareInstance !== null) {
                this.setOwnId(this.ownCoursewareInstance.relationships.root.data.id);
            } else {
                console.debug('can not load');
            }
            
        },
        reset() {
            this.selectSource('');
            this.remoteCid = '';
        },
        async setRemoteId(target) {
            this.remoteId = target;
            await this.loadStructuralElement(this.remoteId);
            this.initRemote();
        },
        initRemote() {
            this.remoteElement = this.structuralElementById({ id: this.remoteId });
        },
        async setOwnId(target) {
            this.ownId = target;
            await this.loadStructuralElement(this.ownId);
            this.initOwn();
        },
        initOwn() {
            this.ownElement = this.structuralElementById({ id: this.ownId });
        },
        loadSelf(data) {
            this.$emit('loadSelf', data);
        }
    },
    async mounted() {
        this.courses = await this.loadUsersCourses(this.userId);
    }

}
</script>