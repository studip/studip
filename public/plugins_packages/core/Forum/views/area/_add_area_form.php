<tr class="new_area">
    <td class="areaentry"></td>
    <td class="areaentry">
        <form class="add_area_form" method="post" action="<?= PluginEngine::getLink('coreforum/area/add/' . $category_id) ?>" class="default">
            <?= CSRFProtection::tokenTag() ?>
            <input type="text" name="name" class="size-l no-hint" maxlength="255" placeholder="<?= _('Name des neuen Bereiches') ?>" required><br>
            <textarea name="content" class="size-l" style="height: 3em;" placeholder="<?= _('Optionale Beschreibung des neuen Bereiches') ?>"></textarea>

            <?= Studip\Button::create(_('Bereich hinzufügen')) ?>
            <?= Studip\LinkButton::createCancel(_('Abbrechen'), PluginEngine::getLink('coreforum/index/index#cat_'. $category_id)) ?>
        </form>
    </td>
    <td class="postings">0</td>
    <td class="answer" colspan="2"><br><?= _('keine Antworten') ?></td>
</tr>
