<form method="post" action="<?= PluginEngine::getLink('coreforum/area/edit/' . $entry['topic_id']) ?>" class="default">
    <input type="text" name="name" class="size-l no-hint" maxlength="255" value="<?= $entry['name_raw'] ?>" onClick="jQuery(this).focus()"><br>
    <textarea name="content" class="size-l" style="height: 3em;" onClick="jQuery(this).focus()"><?= $entry['content_raw'] ?></textarea>

    <?= Studip\Button::createAccept(_('Speichern')) ?>
    <?= Studip\LinkButton::createCancel(_('Abbrechen'), PluginEngine::getLink('coreforum/index')) ?>
</form>
