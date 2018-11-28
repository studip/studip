<script type="text/template" class="edit_category">
<span class="edit_category">
    <form class="default">
        <input type="text" required name="name" maxlength="255" class="size-m no-hint" value="<%- name %>">

        <?= ForumHelpers::replace(Studip\LinkButton::createAccept(_('Kategorie speichern'),
                "javascript:STUDIP.Forum.saveCategoryName('%%%- category_id ###');")) ?>
        <?= ForumHelpers::replace(Studip\LinkButton::createCancel(_('Abbrechen'),
                "javascript:STUDIP.Forum.cancelEditCategoryName('%%%- category_id ###')")) ?>
    </form>
</span>
</script>

<script type="text/template" class="edit_area">
<span class="edit_area">
    <form class="default">
        <input type="text" name="name" class="size-l no-hint" maxlength="255" value="<%- name %>" onClick="jQuery(this).focus()"><br>
        <textarea name="content" class="size-l" style="height: 3em;" onClick="jQuery(this).focus()"><%- content %></textarea>

        <?= ForumHelpers::replace(Studip\LinkButton::createAccept(_('Speichern'),
                "javascript:STUDIP.Forum.saveArea('%%%- area_id ###');")) ?>
        <?= ForumHelpers::replace(Studip\LinkButton::createCancel(_('Abbrechen'),
            "javascript:STUDIP.Forum.cancelEditArea('%%%- area_id ###');")) ?>
    </form>
</span>
</script>

<script type="text/template" class="add_area">
<tr class="new_area">
    <td class="areaentry"></td>
    <td class="areaentry">
        <form class="add_area_form default">
            <?= CSRFProtection::tokenTag() ?>
            <input type="hidden" name="category_id" value="<%- category_id %>">
            <input type="text" name="name" class="size-l no-hint" maxlength="255" placeholder="<?= _('Name des neuen Bereiches') ?>" required><br>
            <textarea name="content" class="size-l" style="height: 3em;" placeholder="<?= _('Optionale Beschreibung des neuen Bereiches') ?>"></textarea>

            <?= Studip\Button::create(_('Bereich hinzufügen')) ?>
            <?= Studip\LinkButton::createCancel(_('Abbrechen'), "javascript:STUDIP.Forum.cancelAddArea();") ?>
        </form>
    </td>
    <td class="postings">0</td>
    <td class="answer" colspan="2"><br><?= _('keine Antworten') ?></td>
</tr>
</script>
