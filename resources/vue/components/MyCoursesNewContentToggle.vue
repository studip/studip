<template>
    <ul class="widget-list widget-options">
        <li>
            <a href="#" class="options-checkbox" :class="showNewContents ? 'options-checked' : 'options-unchecked'" @click.prevent="toggleNewContents">
                <translate>Nur neue Inhalte anzeigen</translate>
            </a>
        </li>
    </ul>
</template>

<script>
import Sidebar from "../../assets/javascripts/lib/sidebar.js";
import MyCoursesMixin from '../mixins/MyCoursesMixin.js';

export default {
    name: 'my-courses-new-content-toggle',
    mixins: [MyCoursesMixin],
    computed: {
        showNewContents () {
            return this.getConfig('navigation_show_only_new');
        },
    },
    methods: {
        toggleNewContents() {
            this.updateConfigValue({
                key: 'navigation_show_only_new',
                value: !this.getConfig('navigation_show_only_new'),
            }).then(() => {
                Sidebar.close();
            });
        },
    },
};
</script>
