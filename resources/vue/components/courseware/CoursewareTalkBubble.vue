<template>
    <div :class="{ 'cw-talk-bubble-own-post': payload.own }" class="cw-talk-bubble">
        <div class="cw-talk-bubble-user" v-if="!payload.own">
            <div class="cw-talk-bubble-avatar">
                <img :src="payload.user_avatar" />
            </div>
            <span>{{ payload.user_name }}</span>
        </div>
        <div class="cw-talk-bubble-talktext">
            <p>{{ payload.content }}</p>
            <p class="cw-talk-bubble-talktext-time"><iso-date :date="payload.chdate" /></p>
        </div>
    </div>
</template>

<script>
import IsoDate from './IsoDate.vue';

export default {
    name: 'courseware-talk-bubble',
    components: { IsoDate },
    props: {
        payload: Object,
    },
};
</script>
