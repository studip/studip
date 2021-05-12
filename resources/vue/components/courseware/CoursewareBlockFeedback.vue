<template>
    <section class="cw-block-feedback">
        <header><translate>Feedback</translate></header>
        <div class="cw-block-features-content">
            <div class="cw-block-feedback-items"  ref="feedbacks">
                <courseware-talk-bubble
                    v-for="feedback in feedback"
                    :key="feedback.id"
                    :payload="buildPayload(feedback)"
                />
            </div>
            <div class="cw-block-feedback-create">
                <textarea v-model="feedbackText" :placeholder="placeHolder" spellcheck="true"></textarea>
                <button class="button" @click="postFeedback"><translate>Senden</translate></button>
                <button class="button" @click="$emit('close')"><translate>Schließen</translate></button>
            </div>
        </div>
    </section>
</template>

<script>
import CoursewareTalkBubble from './CoursewareTalkBubble.vue';
import { mapActions, mapGetters } from 'vuex';

export default {
    name: 'courseware-block-feedback',
    components: {
        CoursewareTalkBubble,
    },
    props: {
        block: Object,
        canEdit: Boolean,
        isTeacher: Boolean,
    },
    data() {
        return {
            feedbackText: '',
            placeHolder: this.$gettext('Schreiben Sie ein Feedback...'),
        };
    },
    computed: {
        ...mapGetters({
            userId: 'userId',
            getRelatedFeedback: 'courseware-block-feedback/related',
            getRelatedUser: 'users/related',
        }),
        feedback() {
            const { id, type } = this.block;

            return this.getRelatedFeedback({ parent: { id, type }, relationship: 'feedback' });
        },
    },
    methods: {
        ...mapActions({
            loadFeedback: 'loadFeedback',
            createFeedback: 'createFeedback',
        }),
        async postFeedback() {
            this.createFeedback({ blockId: this.block.id, feedback: this.feedbackText });
            this.feedbackText = '';
        },
        buildPayload(feedback) {
            const { id, type } = feedback;
            const user = this.getRelatedUser({ parent: { id, type }, relationship: 'user' });

            return {
                own: user.id === this.userId,
                content: feedback.attributes.feedback,
                chdate: feedback.attributes.chdate,
                mkdate: feedback.attributes.mkdate,
                user_name: user?.attributes?.['formatted-name'] ?? '',
                user_avatar: user?.meta?.avatar.small,
            };
        },
    },
    async mounted() {
        await this.loadFeedback(this.block.id);
    },
    updated() {
        this.$refs.feedbacks.scrollTop = this.$refs.feedbacks.scrollHeight;
    },
};
</script>
