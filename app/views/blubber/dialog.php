<div class="blubber_panel"
     data-thread_data="<?= htmlReady(json_encode($thread_data ?: [])) ?>"
     data-threads_more_down="<?= htmlReady($threads_more_down) ?>">

    <div id="blubber_stream_container" :class="waiting ? 'waiting' : ''">
        <blubber-thread :thread_data="thread_data"></blubber-thread>
    </div>
</div>

<div data-dialog-button>
    <?= \Studip\LinkButton::create(_("Zum Kontext springen"), $thread->getURL()) ?>
</div>