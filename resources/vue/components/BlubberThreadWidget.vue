<template>
    <div class="scrollable_area blubber_thread_widget" v-scroll>
        <transition-group name="blubberthreadwidget-list"
                          tag="ol">
                <li v-for="thread in sortedThreads"
                    :key="thread.thread_id"
                    :data-thread_id="thread.thread_id"
                    :class="(active_thread === thread.thread_id ? 'active' : '') + (thread.unseen_comments > 0 ? ' unseen' : '')"
                    :data-unseen_comments="thread.unseen_comments"
                    @click.prevent="changeActiveThread">
                    <a :href="link(thread.thread_id)">
                        <div class="avatar"
                             :style="{ backgroundImage: 'url(' + thread.avatar + ')' }">
                        </div>
                        <div class="info">
                            <div class="name">
                                {{ thread.name }}
                            </div>
                            <studip-date-time :timestamp="thread.timestamp" :relative="true"></studip-date-time>
                        </div>
                    </a>
                </li>
                <li class="more" v-if="display_more_down" key="more">
                    <studip-asset-img file="ajax-indicator-black.svg" width="20"></studip-asset-img>
                </li>
        </transition-group>
    </div>
</template>

<script>
    export default {
        name: 'blubber-thread-widget',
        props: ['threads', 'active_thread', 'more_down'],
        data () {
            return {
                display_more_down: this.more_down,
                already_loading_down: 0
            };
        },
        methods: {
            changeActiveThread (event) {
                let li = $(event.target).closest('li');
                if (!li.hasClass('active')) {
                    li.siblings('.active').removeClass('active');
                    li.addClass('active');
                    this.$root.changeActiveThread(li.data('thread_id'));
                }
            },
            link (thread_id) {
                return STUDIP.URLHelper.getURL(`dispatch.php/blubber/index/${thread_id}`);
            },
            addThread (thread) {
                let thread_ids = this.threads.map((t) => t.thread_id);
                if (thread_ids.indexOf(thread.thread_id) !== -1) {
                    return;
                }
                this.threads.push(thread);
            }
        },
        directives: {
            scroll: {
                // directive definition
                inserted (el) {
                    let threads = el.__vue__;
                    $(el).parent().on('scroll', function (event) {
                        let top = $(el).parent().scrollTop();
                        let height = $(el).height();

                        $(el).toggleClass('scrolled', top > 0);

                        if (!threads.display_more_down || (top <= height - 1000) || threads.already_loading_down) {
                            return;
                        }

                        threads.already_loading_down = true;

                        let latest_timestamp = threads.threads.reduce((max, thread) => {
                            if (thread.thread_id === 'global') {
                                return max;
                            }
                            return max === null ? thread.timestamp : Math.min(max, thread.timestamp);
                        }, null);

                        //load newer comments
                        STUDIP.api.GET('blubber/threads', {
                            data: {
                                modifier: 'olderthan',
                                timestamp: latest_timestamp,
                                limit: 50
                            }
                        }).done((data) => {
                            data.threads.forEach((thread) => threads.addThread(thread));

                            threads.display_more_down = data.more_down;
                        }).always(() => {
                            threads.already_loading_down = false;
                        });
                    });
                }
            }
        },
        mounted: function () {

        },
        computed: {
            sortedThreads () {
                return this.threads.sort((a, b) => b.timestamp - a.timestamp);
            }
        }
    }
</script>
