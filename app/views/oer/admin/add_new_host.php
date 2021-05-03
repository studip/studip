<form class="default" action="<?= $controller->link_for("oer/admin/add_new_host") ?>" method="post">
    <label>
        <?= _('Adresse des Servers plugin.php....') ?>
        <input type="text" name="url" placeholder="https://www.myserver.de/studip/dispatch.php/oer/endpoints/">
    </label>

    <div style="text-align: center;" data-dialog-button>
        <?= \Studip\Button::create(_('Speichern')) ?>
    </div>
</form>
