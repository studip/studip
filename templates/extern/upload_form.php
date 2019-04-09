<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>
<!-- upload extern config -->
<tr class="nohover">
    <td colspan="10" align="center" class="<?= $class ?>">
        <a name="upload"></a>

        <form enctype="multipart/form-data" name="upload_form" action="<?= URLHelper::getLink() ?>" method="post" class="default">
            <?= CSRFProtection::tokenTag() ?>
            <fieldset>
                <legend><?= _('Konfiguration hochladen') ?></legend>

                <b><?= _("Maximale Größe:") ?> <?= ($max_filesize / 1024) ?></b> <?= _("Kilobyte") ?>

                <label>
		<a class="button"><?= _('Datei zum Hochladen auswählen') ?></a>
                    <input name="the_file" type="file">
                </label>

            </fieldset>
            <footer>
                <?= Button::createAccept(_('Hochladen'), ['onClick' => 'return STUDIP.OldUpload.upload_start(jQuery(this).closest(\'form\'))'])?>
                <?= LinkButton::createCancel(_('Abbrechen'), URLHelper::getURL('?cancel_x=true'))?>
            </footer>

            <input type="hidden" name="com" value="do_upload_config">
            <input type="hidden" name="check_module" value="<?= $module ?>">
            <input type="hidden" name="config_id" value="<?= $config_id ?>">
        </form>
    </td>
</tr>
<!-- end of upload extern config -->
