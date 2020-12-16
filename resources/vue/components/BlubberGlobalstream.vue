<template>
    <div class="blubber_globalstream">
        <div class="scrollable_area" v-scroll>
            <blubber-public-composer></blubber-public-composer>
            <ol class="postings" aria-live="polite">
                <li class="more" v-if="stream_data.more_up">
                    <studip-asset-img file="ajax-indicator-black.svg" width="20"></studip-asset-img>
                </li>

                <li :class="blubber.class"
                    v-for="blubber in sortedPostings"
                    :data-thread_id="blubber.thread_id"
                    :key="blubber.thread_id">
                    <div class="thread_posting" v-if="blubber.html">
                        <div class="contextinfo">
                            <studip-date-time :timestamp="blubber.mkdate" :relative="true"></studip-date-time>
                            <div>{{ blubber.user_name }}</div>
                            <div class="avatar" :style="{ backgroundImage: 'url(' + blubber.avatar + ')' }"></div>
                        </div>
                        <div class="content" v-html="blubber.html"></div>
                        <a class="link_to_comments"
                           :href="link(blubber.thread_id)"
                           @click.prevent="changeActiveThread" v-translate>Zur Diskussion</a>
                    </div>
                </li>

                <li class="more" v-if="more_down">
                    <studip-asset-img file="ajax-indicator-black.svg" width="20"></studip-asset-img>
                </li>
            </ol>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'blubber-globalstream',
        data: function () {
            return {
                already_loading_down: 0
            };
        },
        props: ['stream_data', 'more_down'],
        methods: {
            changeActiveThread: function (event) {
                let li = $(event.target).closest('li');
                this.$root.changeActiveThread(li.data('thread_id'));
            },
            link: function (thread_id) {
                return STUDIP.URLHelper.getURL(`dispatch.php/blubber/index/${thread_id}`);
            },
            addPosting: function (posting) {
                let exists = false;
                for (let i in this.stream_data) {
                    if (this.stream_data[i].thread_id === posting.thread_id) {
                        exists = true;
                        return;
                    }
                }
                if (!exists) {
                    posting.class = posting.class + " new";
                    this.stream_data.push(posting);
                    this.$nextTick(() => {
                        STUDIP.Markup.element($(this.$el).find(`.postings > li[data-thread_id="${posting.thread_id}"]`));
                    });
                }
            }
        },
        mounted () { //when everything is initialized
            this.$nextTick(function () {
                $(this.$el).find('.postings .content').each(function () {
                    STUDIP.Markup.element(this);
                });
            });
        },
        computed: {
            sortedPostings: function () {
                return this.stream_data.sort((a, b) => b.mkdate - a.mkdate);
            }
        },
        directives: {
            scroll: {
                // directive definition
                inserted: function (el) {
                    let stream = $(el).closest(".blubber_globalstream")[0].__vue__;
                    $(el).on('scroll', function (event) {
                        let top = $(el).scrollTop();
                        let height = $(el).find(".postings").height();

                        $(el).toggleClass('scrolled', top > 0);

                        if (stream.more_down && (top > $(el).find(".postings").height() - 1000)
                                && !stream.already_loading_down) {
                            stream.already_loading_down = 1;

                            let earliest_mkdate = null;
                            for (let i in stream.stream_data) {
                                if ((earliest_mkdate === null) || stream.stream_data[i].mkdate < earliest_mkdate) {
                                    earliest_mkdate = stream.stream_data[i].mkdate;
                                }
                            }
                            //load older comments
                            $.ajax({
                                url: STUDIP.ABSOLUTE_URI_STUDIP + "api.php/blubber/threads/global",
                                type: "get",
                                dataType: "json",
                                data: {
                                    modifier: "olderthan",
                                    timestamp: earliest_mkdate,
                                    limit: 30
                                },
                                success: function (data) {
                                    for (let i in data.postings) {
                                        stream.addPosting(data.postings[i]);
                                    }
                                    stream.more_down = data.more_down;
                                },
                                complete: function () {
                                    stream.already_loading_down = 0;
                                }
                            });

                        }
                    });
                }
            }
        }
    }
</script>
