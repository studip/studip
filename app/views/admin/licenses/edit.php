<form action="<?= $controller->link_for("admin/licenses/store", ['identifier' => $license->getId()]) ?>"
      method="post"
      enctype="multipart/form-data"
      class="default">

    <label>
        <?= _("Lizenzkürzel (nach SPDX wenn möglich)") ?>
        <input type="text" name="data[identifier]" value="<?= htmlReady($license['identifier']) ?>" required>
    </label>

    <label>
        <?= _("Name") ?>
        <input type="text" name="data[name]" value="<?= htmlReady($license['name']) ?>" required>
    </label>

    <input type="hidden" name="data[default]" value="0">
    <label>
        <input type="checkbox" name="data[default]" value="1"<?= $license['default'] ? " checked" : "" ?>>
        <?= _("Standardlizenz") ?>
    </label>

    <label>
        <?= _("Link zur Lizenz") ?>
        <input type="text" name="data[link]" value="<?= htmlReady($license['link']) ?>" required>
    </label>

    <label>
        <?= _("Beschreibung") ?>
        <textarea name="data[description]"><?= htmlReady($license['description']) ?></textarea>
    </label>

    <label class="file-upload">
        <?= _('Bild hochladen (PNG, JPG, GIF)') ?>
        <input type="file" name="avatar" accept=".jpg,.png,.jpeg,.gif">
    </label>

    <label>
        <input type="checkbox" name="delete_avatar" value="1">
        <?= _("Bild löschen") ?>
    </label>

    <div data-dialog-button>
        <?= \Studip\Button::create(_("Speichern")) ?>
    </div>
</form>
