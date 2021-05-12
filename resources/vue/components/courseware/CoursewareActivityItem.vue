<template>
    <li class="cw-activity-item">
        <p v-if="item.username" class="cw-activity-item-user">
            <a><studip-icon role="inactive" shape="headache" />{{ item.username }}</a>
        </p>
        <p v-if="item.date" class="cw-activity-item-date">
            <studip-icon role="inactive" shape="timetable" />{{ item.date }}
        </p>
        <p class="cw-activity-item-element">
            <a :href="item.element_id"><studip-icon role="inactive" :shape="shape" />{{ item.element_breadcrumb }}</a>
        </p>
        <p v-if="item.text" class="cw-activity-item-text">
            {{ item.text }}
        </p>
    </li>
</template>

<script>
import StudipIcon from './../StudipIcon.vue';

export default {
    name: 'courseware-activity-item',
    components: {
        StudipIcon,
    },
    props: {
        item: Object,
    },
    computed: {
        shape() {
            switch (this.item.type) {
                case 'comment':
                    return 'item';
                case 'feedback':
                    return 'support';
                case 'new_block':
                case 'new_element':
                    return 'add';
                case 'updated_block':
                case 'updated_element':
                    return 'edit';
                default:
                    return 'question-circle-full';
            }
        },
    },
};
</script>
