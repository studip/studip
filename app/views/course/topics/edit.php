<? $date_ids = $topic->dates->pluck("termin_id") ?>
<form action="<?= URLHelper::getLink("dispatch.php/course/topics") ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <input type="hidden" name="issue_id" value="<?=htmlReady($topic->getId())  ?>">
    <input type="hidden" name="open" value="<?=htmlReady($topic->getId())  ?>">
    <input type="hidden" name="edit" value="1">

    <fieldset>
        <legend><?= _('Thema bearbeiten') ?></legend>

        <label for="topic_title">
            <span class="required"><?= _("Titel") ?></span>
            <input type="text" name="title" id="topic_title" value="<?= htmlReady($topic['title']) ?>" required>
        </label>


        <label for="topic_description">
            <?= _("Beschreibung") ?>

            <textarea class="add_toolbar wysiwyg size-l" name="description" id="topic_description"><?= wysiwygReady($topic['description']) ?></textarea>
            <? if (Request::isAjax()) : ?>
            <script>jQuery('.add_toolbar').addToolbar();</script>
            <? endif ?>
        </label>

        <? if ($documents_activated) : ?>
            <label>
            <? $folder = $topic->folders->first() ?>
            <? if ($folder) : ?>
                <?= Icon::create('accept', 'accept')->asImg(['class' => "text-bottom"]) ?>
                <?= _("Dateiordner vorhanden ") ?>
            <? else : ?>
                <input type="checkbox" name="folder" id="topic_folder">
                <?= _("Dateiordner anlegen") ?>
            <? endif ?>
            </label>
        <? endif ?>

        <? if ($forum_activated) : ?>
            <label>
            <? if ($topic->forum_thread_url) : ?>
                <?= Icon::create('accept', 'accept')->asImg(['class' => "text-bottom"]) ?>
                <?= _("Forenthema vorhanden ") ?>
            <? else : ?>
                <input type="checkbox" name="forumthread" id="topic_forumthread">
                <?= _("Forenthema anlegen") ?>
            <? endif ?>
            </label>
        <? endif ?>

        <h2><?= _("Termine") ?></h2>
        <? foreach ($dates as $date) : ?>
            <label>
                <input type="checkbox" name="date[<?= $date->getId() ?>]" value="1" class="text-bottom"<?= in_array($date->getId(), $date_ids) ? " checked" : "" ?>>
                <?= Icon::create('date', 'info')->asImg(['class' => "text-bottom"]) ?>
                <?= (floor($date['date'] / 86400) !== floor($date['end_time'] / 86400)) ? date("d.m.Y, H:i", $date['date'])." - ".date("d.m.Y, H:i", $date['end_time']) : date("d.m.Y, H:i", $date['date'])." - ".date("H:i", $date['end_time']) ?>
            <? $localtopics = $date->topics ?>
            <? if (count($localtopics)) : ?>
            (
                <? foreach ($localtopics as $key => $localtopic) : ?>
                    <a href="<?= URLHelper:: getLink("dispatch.php/course/topics/index", ['open' => $localtopic->getId()]) ?>">
                        <?= Icon::create('topic', 'clickable')->asImg(['class' => "text-bottom"]) ?>
                        <?= htmlReady($localtopic['title']) ?>
                    </a>
                <? endforeach ?>
            )
            <? endif ?>
            </label>
        <? endforeach ?>

        <h2><?= _('Hausarbeit/Referat') ?></h2>
        <label>
            <input type="checkbox" name="paper_related" value="1"
                   <? if ($topic->paper_related) echo 'checked'; ?>>
            <?= _('Thema behandelt eine Hausarbeit oder ein Referat') ?>
        </label>
    </fieldset>
    <footer data-dialog-button>
        <div class="button-group">
            <?= \Studip\Button::createAccept(_("Speichern")) ?>

            <? if (!$topic->isNew()) : ?>
                <?= \Studip\LinkButton::create(
                    _('Löschen'),
                    $controller->url_for('course/topics/delete/' . $topic->getId()),
                    ['data-confirm' => _('Wirklich löschen?')]
                ) ?>
            <? endif ?>
        </div>
    </footer>
</form>

<br>
