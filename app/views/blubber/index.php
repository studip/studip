<div class="blubber_panel"
     data-active_thread="<?= htmlReady($thread->getId()) ?>"
     data-thread_data="<?= htmlReady(json_encode($thread_data ?: ['thread_posting' => []])) ?>"
     data-threads_more_down="<?= htmlReady($threads_more_down) ?>"
     :class="waiting ? 'waiting' : ''">

    <div id="blubber_stream_container">
        <blubber-thread :thread_data="thread_data"></blubber-thread>
    </div>

    <div class="blubber_sideinfo responsive-hidden" v-if="thread_data.context_info || thread_data.thread_posting.content">
        <div class="posting" v-show="display_context_posting">
            <div class="header">
                <studip-date-time :timestamp="thread_data.thread_posting.mkdate" :relative="true"></studip-date-time>
                <div>{{ thread_data.thread_posting.user_name }}</div>
            </div>
            <div class="content" v-html="thread_data.thread_posting.html"></div>
        </div>
        <div v-if="thread_data.context_info" class="context_info" v-html="thread_data.context_info"></div>
    </div>
</div>
