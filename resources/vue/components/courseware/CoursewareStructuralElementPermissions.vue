<template>
    <div class="cw-element-permissions">
        <label>
            <input type="checkbox" class="default" v-model="userPermsReadAll" />
            <translate>Alle Teilnehmenden haben Leserechte</translate>
        </label>
        <label>
            <input type="checkbox" class="default" v-model="userPermsWriteAll" />
            <translate>Alle Teilnehmenden haben Schreibrechte</translate>
        </label>

        <table class="default" v-if="autor_members.length">
            <caption>
                <translate>Studierende</translate>
            </caption>
            <colgroup>
                <col style="width:20%" />
                <col style="width:35%" />
                <col style="width:45%" />
            </colgroup>
            <thead>
                <tr>
                    <th><translate>Lesen</translate></th>
                    <th><translate>Lesen und Schreiben</translate></th>
                    <th><translate>Name</translate></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in autor_members" :key="user.user_id">
                    <td class="perm">
                        <input
                            type="checkbox"
                            :id="user.user_id + `_read`"
                            :value="user.user_id"
                            v-model="userPermsReadUsers"
                        />
                    </td>
                    <td class="perm">
                        <input
                            type="checkbox"
                            :id="user.user_id + `_write`"
                            :value="user.user_id"
                            v-model="userPermsWriteUsers"
                        />
                    </td>

                    <td>
                        <label :for="user.user_id + `_read`">
                            {{ user.formattedname }}
                            <i>{{ user.username }}</i>
                        </label>
                    </td>
                </tr>
            </tbody>
        </table>

        <table class="default" v-if="user_members.length">
            <caption>
                <translate>Leser/-innen</translate>
            </caption>
            <colgroup>
                <col style="width:55%" />
                <col style="width:45%" />
            </colgroup>
            <thead>
                <tr>
                    <th><translate>Lesen</translate></th>
                    <th><translate>Name</translate></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in user_members" :key="user.user_id">
                    <td>
                        <input
                            type="checkbox"
                            :id="user.user_id + `_read`"
                            :value="user.id"
                            v-model="userPermsReadUsers"
                        />
                    </td>
                    <td>
                        <label :for="user.user_id + `_read`">
                            {{ user.firstname }}
                            {{ user.lastname }}
                            <i>{{ user.username }}</i>
                        </label>
                    </td>
                </tr>
            </tbody>
        </table>

        <table class="default" v-if="groups.length">
            <caption>
                <translate>Gruppen</translate>
            </caption>
            <colgroup>
                <col style="width:20%" />
                <col style="width:35%" />
                <col style="width:45%" />
            </colgroup>
            <thead>
                <tr>
                    <th><translate>Lesen</translate></th>
                    <th><translate>Lesen und Schreiben</translate></th>
                    <th><translate>Name</translate></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="group in groups" :key="group.id">
                    <td class="perm">
                        <input
                            type="checkbox"
                            :id="group.id + `_read`"
                            :value="group.id"
                            v-model="userPermsReadGroups"
                        />
                    </td>
                    <td class="perm">
                        <input
                            type="checkbox"
                            :id="group.id + `_write`"
                            :value="group.id"
                            v-model="userPermsWriteGroups"
                        />
                    </td>

                    <td>
                        <label :for="group.id + `_read`">
                            {{ group.name }}
                        </label>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<script>
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-structural-element-permissions',
    props: {
        element: Object,
    },
    data() {
        return {
            user_perms: {},
            userPermsReadUsers: [],
            userPermsReadGroups: [],
            userPermsReadAll: Boolean,
            userPermsWriteUsers: [],
            userPermsWriteGroups: [],
            userPermsWriteAll: Boolean,
        };
    },

    mounted() {
        if (this.element.attributes['read-approval'].users !== undefined) {
            this.userPermsReadUsers = this.element.attributes['read-approval'].users;
        }
        if (this.element.attributes['read-approval'].groups !== undefined) {
            this.userPermsReadGroups = this.element.attributes['read-approval'].groups;
        }
        if (this.element.attributes['read-approval'].all !== undefined) {
            this.userPermsReadAll = this.element.attributes['read-approval'].all;
        } else {
            this.userPermsReadAll = true;
        }
        if (this.element.attributes['write-approval'].users !== undefined) {
            this.userPermsWriteUsers = this.element.attributes['write-approval'].users;
        }
        if (this.element.attributes['write-approval'].groups !== undefined) {
            this.userPermsWriteGroups = this.element.attributes['write-approval'].groups;
        }
        if (this.element.attributes['write-approval'].all !== undefined) {
            this.userPermsWriteAll = this.element.attributes['write-approval'].all;
        } else {
            this.userPermsWriteAll = false;
        }

        // load memberships for coursewares in a course context
        if (this.context.type === 'courses') {
            const parent = { type: 'courses', id: this.context.id };
            this.loadCourseMemberships({ parent, relationship: 'memberships', options: { include: 'user' } });
            this.loadCourseStatusGroups({ parent, relationship: 'status-groups' });
        }
    },

    computed: {
        ...mapGetters({
            context: 'context',
            courseware: 'courseware',
            course: 'courses/related',
            relatedCourseMemberships: 'course-memberships/related',
            relatedCourseStatusGroups: 'status-groups/related',
            relatedUser: 'users/related',
        }),
        users() {
            const parent = { type: 'courses', id: this.context.id };
            const relationship = 'memberships';
            const memberships = this.relatedCourseMemberships({ parent, relationship });

            return (
                memberships?.map((membership) => {
                    const parent = { type: membership.type, id: membership.id };
                    const member = this.relatedUser({ parent, relationship: 'user' });

                    return {
                        user_id: member.id,
                        formattedname: member.attributes['formatted-name'],
                        username: member.attributes['username'],
                        perm: membership.attributes['permission'],
                    };
                }) ?? []
            );
        },
        groups() {
            const parent = { type: 'courses', id: this.context.id };
            const relationship = 'status-groups';
            const statusGroups = this.relatedCourseStatusGroups({ parent, relationship });

            return (
                statusGroups?.map((statusGroup) => {
                    return {
                        id: statusGroup.id,
                        name: statusGroup.attributes['name'],
                    };
                }) ?? []
            );
        },
        autor_members() {
            if (Object.keys(this.users).length === 0 && this.users.constructor === Object) {
                return [];
            }

            let members = this.users.filter(function (user) {
                return user.perm === 'autor';
            });

            return members;
        },

        user_members() {
            if (Object.keys(this.users).length === 0 && this.users.constructor === Object) {
                return [];
            }

            let members = this.users.filter(function (user) {
                return user.perm === 'user';
            });

            return members;
        },

        readApproval() {
            return {
                all: this.userPermsReadAll,
                users: this.userPermsReadUsers,
                groups: this.userPermsReadGroups,
            };
        },

        writeApproval() {
            return {
                all: this.userPermsWriteAll,
                users: this.userPermsWriteUsers,
                groups: this.userPermsWriteGroups,
            };
        },
    },

    methods: {
        ...mapActions({
            loadCourseMemberships: 'course-memberships/loadRelated',
            loadCourseStatusGroups: 'status-groups/loadRelated',
        }),
    },

    watch: {
        userPermsReadUsers(newVal, oldVal) {
            this.$emit('updateReadApproval', this.readApproval);
        },
        userPermsReadGroups(newVal, oldVal) {
            this.$emit('updateReadApproval', this.readApproval);
        },
        userPermsReadAll(newVal, oldVal) {
            this.$emit('updateReadApproval', this.readApproval);
            if (newVal === true) {
                this.userPermsWriteAll = false;
            }
        },
        userPermsWriteUsers(newVal, oldVal) {
            this.$emit('updateWriteApproval', this.writeApproval);
        },
        userPermsWriteGroups(newVal, oldVal) {
            this.$emit('updateWriteApproval', this.writeApproval);
        },
        userPermsWriteAll(newVal, oldVal) {
            this.$emit('updateWriteApproval', this.writeApproval);
            if (newVal === true) {
                this.userPermsReadAll = false;
            }
        },
    },
};
</script>
