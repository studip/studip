<?/*  $this->flash['new_entry_title'] */ ?>
<script type="text/html" class="new_entry_box">
    <div class="forum_new_entry" data-id="<%- topic_id %>">
        <a name="create"></a>
        <form action="<?= PluginEngine::getLink('coreforum/index/add_entry') ?>" method="post" id="forum_new_entry" onSubmit="$(window).off('beforeunload')" class="default">
            <fieldset>
                <legend>
                    <? if ($constraint['depth'] == 1) : ?>
                        <?= _('Neues Thema erstellen') ?>
                    <? else : ?>
                        <?= _('Antworten') ?>
                    <? endif ?>
                </legend>

                <? if ($constraint['depth'] == 1) : ?>
                    <? if ($GLOBALS['user']->id == 'nobody') : ?>
                        <label>
                            <?= _('Ihr Name') ?>
                            <input class="size-l" type="text" name="author" style="width: 99%"
                                placeholder="<?= _('Ihr Name') ?>" required tabindex="1"><br>
                        </label>
                    <? endif ?>

                    <label>
                        <?= _('Titel') ?>
                        <input class="size-l" type="text" name="name" style="width: 99%" value=""
                            <?= $constraint['depth'] == 1 ? 'required' : '' ?> placeholder="<?= _('Titel') ?>" tabindex="2">
                    </label>
                <? elseif ($GLOBALS['user']->id == 'nobody') : ?>
                    <label>
                        <?= _('Ihr Name') ?>
                        <input type="text" name="author" style="width: 99%" placeholder="<?= _('Ihr Name') ?>" required tabindex="1"><br>
                    </label>
                <? endif; ?>

                <label>
                    <textarea class="add_toolbar wysiwyg size-l" data-textarea="new_entry" name="content" required tabindex="3"
                        placeholder="<?= _('Schreiben Sie hier Ihren Beitrag. Hilfe zu Formatierungen'
                            . ' finden Sie rechts neben diesem Textfeld.') ?>"></textarea>
                </label>

                <? if (Config::get()->FORUM_ANONYMOUS_POSTINGS): ?>
                    <label>
                        <input type="checkbox" name="anonymous" value="1">
                        <?= _('Anonym') ?>
                    </label>
                <? endif; ?>
            </fieldset>

            <footer>
                <?= Studip\Button::createAccept(_('Beitrag erstellen'), ['tabindex' => '3']) ?>

                <?= Studip\LinkButton::createCancel(_('Abbrechen'), '', [
                    'onClick' => "return STUDIP.Forum.cancelNewEntry();",
                    'tabindex' => '4']) ?>

                <?= Studip\LinkButton::create(_('Vorschau'), "javascript:STUDIP.Forum.preview('new_entry', 'new_entry_preview');", ['tabindex' => '5', 'class' => 'js']) ?>
            </footer>

            <input type="hidden" name="parent" value="<?= $topic_id ?>">
            <input type="text" name="nixda" style="display: none;">
        </form>

        <?= $this->render_partial('index/_preview', ['preview_id' => 'new_entry_preview']) ?>
        <br>
    </div>
</script>
