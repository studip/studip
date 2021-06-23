<template>
    <div class="cw-tabs">
        <ul class="cw-tabs-nav">
            <li
                v-for="(tab, index) in tabs"
                :key="index"
                :class="[
                    tab.isActive ? 'is-active' : '',
                    tab.icon !== '' && tab.name !== '' ? 'cw-tabs-nav-icon-text-' + tab.icon : '',
                    tab.icon !== '' && tab.name === '' ? 'cw-tabs-nav-icon-solo-' + tab.icon : '',
                ]"
                :href="tab.href"
                :tabindex="index"
                @click="selectTab(tab)"
                @keydown.enter="selectTab(tab)"
                @keydown.space="selectTab(tab)"
            >
                {{ tab.name }}
            </li>
        </ul>
        <div class="cw-tabs-content">
            <slot></slot>
        </div>
    </div>
</template>

<script>
export default {
    name: 'courseware-tabs',
    data() {
        return { tabs: [] };
    },
    created() {
        this.tabs = this.$children;
    },
    methods: {
        selectTab(selectedTab) {
            this.tabs.forEach((tab) => {
                tab.isActive = tab.index + '-' + tab.name === selectedTab.index + '-' + selectedTab.name;
            });
            this.$emit('selectTab', selectedTab.name);
        },
    },
};
</script>
