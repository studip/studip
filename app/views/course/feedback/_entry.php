<article class="studip feedback-entry" data-id="<?= $entry->id ?>">
    <header>
        <h1>
            <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $entry->user->username) ?>">
                <?= Avatar::getAvatar($entry->user_id)->getImageTag(Avatar::SMALL) ?>
                <?= $entry->user->getFullName(); ?>
            </a>
        </h1>
        <nav>
            <? if ($entry->isEditable()) : ?>
            <a href="<?= $controller->link_for('course/feedback/entry_edit_form/' . $entry->id) ?>"
                title="<?= _('Bearbeiten') ?>" data-dialog="size=auto"
                class="feedback-entry-edit" >
                <?= Icon::create('edit'); ?>
            </a>
            <? endif; ?>
            <? if ($entry->isDeletable()) : ?>
            <a href="<?= $controller->link_for('course/feedback/entry_delete/' . $entry->id) ?>"
                title="<?= _('Löschen') ?>" data-dialog="size=auto"
                data-confirm="<?= _('Feedback löschen?') ?>"
                class="feedback-entry-delete"
                onclick="return STUDIP.Dialog.confirmAsPost($(this).attr('data-confirm'), this.href);">
                <?= Icon::create('trash'); ?>
            </a>
            <? endif; ?>
        </nav>
    </header>
    <? if ($entry->feedback->mode != 0) : ?>
        <div class="rating">
            <span title="<?= $entry->rating ?>">
                <? for ($i=0; $i < $entry->feedback->getMaxRating(); $i++) : ?>
                    <?= ($i >= $entry->rating) ? Icon::create('star-empty', 'info') : Icon::create('star', 'info') ?>
                <? endfor; ?>
            </span>
        </div>
    <? endif; ?>
    <div class="comment">
        <?= htmlReady($entry->comment) ?>
    </div>
    <div class="date">
        <span title="<?= strftime('%x %X', $entry->chdate) ?>">
                <? if ($entry->chdate != $entry->mkdate) : ?>
                <?= _('Bearbeitet:') ?>
                <? endif; ?>
                <?= $entry->chdate ? reltime($entry->chdate) : "" ?>
        </span>
    </div>
</article>
