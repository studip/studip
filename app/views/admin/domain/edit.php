<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>

<form action="<?= $controller->url_for('admin/domain/save') ?>" method="POST" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default">
        <caption>
            <?= _('Liste der Nutzerdomänen') ?>
        </caption>
        <?= $this->render_partial('admin/domain/domains') ?>
        <? if (!isset($edit_id)): ?>
            <tr>
                <td>
                    <input type="hidden" name="new_domain" value="1">
                    <input type="text" style="width: 80%;" name="name" value="">
                </td>
                <td>
                    <input type="text" style="width: 80%;" name="id" value="">
                </td>
                <td></td>
                <td></td>
            </tr>
        <? endif ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" align="center">
                    <?= Button::createAccept(_('Übernehmen'), 'uebernehmen', ['title' => _('Änderungen speichern')]) ?>
                    <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/domain')) ?>
                </td>
            </tr>
        </tfoot>
    </table>
</form>
