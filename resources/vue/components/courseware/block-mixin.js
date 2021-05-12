import { mapActions, mapGetters } from 'vuex';

export const blockMixin = {
    computed: {
        ...mapGetters({
            getUserProgress: 'courseware-user-progresses/related',
        }),
        userProgress: {
            get: function () {
                return this.getUserProgress({ parent: this.block, relationship: 'user-progress' });
            },
            set: function (grade) {
                this.userProgress.attributes.grade = grade;

                return this.updateUserProgress(this.userProgress);
            },
        },
    },
    methods: {
        ...mapActions({
            updateUserProgress: 'courseware-user-progresses/update',
        }),
    },
};
