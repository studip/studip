<template>
    <section class="cw-block-comments">
        <header><translate>Kommentare</translate></header>
        <div class="cw-block-features-content">
            <div class="cw-block-comments-items" ref="comments">
                <courseware-talk-bubble
                    v-for="comment in comments"
                    :key="comment.id"
                    :payload="buildPayload(comment)"
                />
            </div>
            <div class="cw-block-comment-create">
                <textarea v-model="createComment" :placeholder="placeHolder" spellcheck="true"></textarea>
                <button class="button" @click="postComment"><translate>Senden</translate></button>
                <button class="button" @click="$emit('close')"><translate>Schließen</translate></button>
            </div>
        </div>
    </section>
</template>

<script>
import CoursewareTalkBubble from './CoursewareTalkBubble.vue';
import { mapGetters } from 'vuex';

export default {
    name: 'courseware-block-comments',
    components: {
        CoursewareTalkBubble,
    },
    props: {
        block: Object,
        comments: Array,
    },
    data() {
        return {
            createComment: '',
            placeHolder: this.$gettext('Stellen Sie eine Frage oder kommentieren Sie...'),
        };
    },
    computed: {
        ...mapGetters({
            relatedUser: 'users/related',
            userId: 'userId',
        }),
    },
    methods: {
        async postComment() {
            let data = {};
            data.attributes = {};
            data.attributes.comment = this.createComment;
            data.relationships = {};
            data.relationships.block = {};
            data.relationships.block.data = {};
            data.relationships.block.data.id = this.block.id;
            data.relationships.block.data.type = this.block.type;

            await this.$store.dispatch('courseware-block-comments/create', data);
            this.$emit('postComment');
            this.createComment = '';
        },
        buildPayload(comment) {
            const commenter = this.relatedUser({
                parent: { id: comment.id, type: comment.type },
                relationship: 'user',
            });

            const payload = {
                id: comment.id,
                own: comment.relationships.user.data.id === this.userId,
                content: comment.attributes.comment,
                chdate: comment.attributes.chdate,
                mkdate: comment.attributes.mkdate,
                user_id: commenter.id,
                user_name: commenter.attributes['formatted-name'],
                user_avatar: commenter.meta.avatar.small,
            };

            return payload;
        },
    },
    updated() {
        this.$refs.comments.scrollTop = this.$refs.comments.scrollHeight;
    },
};
</script>
