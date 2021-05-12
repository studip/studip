<template>
    <div class="cw-tools cw-tools-admin">
        <form class="default" @submit.prevent="">
            <fieldset>
                <legend><translate>Allgemeine Einstellungen</translate></legend>
                <label>
                    <span><translate>Art der Kapitelabfolge</translate></span>
                    <select class="size-s" v-model="currentProgression">
                        <option value="false"><translate>Frei</translate></option>
                        <option value="true"><translate>Sequentiell</translate></option>
                    </select>
                </label>

                <label>
                    <span><translate>Editierberechtigung für Tutor/-innen</translate></span>
                    <select class="size-s" v-model="currentPermissionLevel">
                        <option value="dozent"><translate>Nein</translate></option>
                        <option value="tutor"><translate>Ja</translate></option>
                    </select>
                </label>
            </fieldset>
        </form>
        <button class="button" @click="store"><translate>Übernehmen</translate></button>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'cw-tools-admin',
    data() {
        return {
            currentPermissionLevel: '',
            currentProgression: '',
        };
    },
    computed: {
        ...mapGetters({
            courseware: 'courseware',
        }),
    },
    methods: {
        ...mapActions({
            storeCoursewareSettings: 'storeCoursewareSettings',
            companionSuccess: 'companionSuccess',
        }),
        initData() {
            this.currentPermissionLevel = this.courseware.attributes['editing-permission-level'];
            this.currentProgression = this.courseware.attributes['sequential-progression'];
        },
        store() {
            this.companionSuccess({
                info: this.$gettext('Einstellungen wurden übernommen'),
            })
            this.storeCoursewareSettings({
                permission: this.currentPermissionLevel,
                progression: this.currentProgression,
            });
;
        },
    },
    mounted() {
        this.initData();
    },
};
</script>
