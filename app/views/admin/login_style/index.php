<? if (count($pictures) > 0) : ?>
    <table class="default">
        <caption>
            <?= _('Hintergrundbilder für den Startbildschirm') ?>
        </caption>
        <colgroup>
            <col>
            <col width="400">
            <col width="100">
            <col width="25">
        </colgroup>
        <thead>
            <tr>
                <th><?= _('Info') ?></th>
                <th><?= _('Vorschau') ?></th>
                <th><?= _('Aktiviert für') ?></th>
                <th><?= _('Aktionen') ?></th>
            </tr>
        </thead>
        <? foreach ($pictures as $pic) :
            $dim = $pic->getDimensions();
        ?>
            <tr>
                <td>
                    <?= htmlReady($pic->filename) ?>
                    <br>
                    (<?= $dim[0] ?> x <?= $dim[1] ?>,
                    <?= relsize($pic->getFilesize(), false) ?>)
                </td>
                <td>
                    <img src="<?= $pic->getURL() ?>" width="400">
                </td>
                <td>
                    <a href="<?= $controller->link_for("admin/loginstyle/activation/{$pic->id}/desktop", (int) !$pic->desktop) ?>">
                        <?= Icon::create('computer', $pic->desktop ? Icon::ROLE_CLICKABLE : Icon::ROLE_INACTIVE)->asImg(32, [
                            'title' => $pic->desktop
                                     ? _('Bild nicht mehr für die Desktopansicht verwenden')
                                     : _('Bild für die Desktopansicht verwenden')
                        ]) ?>
                    </a>
                    <a href="<?= $controller->link_for("admin/loginstyle/activation/{$pic->id}/mobile", (int) !$pic->mobile) ?>">
                        <?= Icon::create('cellphone', $pic->mobile ? Icon::ROLE_CLICKABLE : Icon::ROLE_INACTIVE)->asImg(32, [
                            'title' => $pic->mobile
                                     ? _('Bild nicht mehr für die Mobilansicht verwenden')
                                     : _('Bild für die Mobilansicht verwenden')
                        ]) ?>
                    </a>
                </td>
                <td class="actions">
                <? if (!$pic->in_release): ?>
                    <a href="<?= $controller->link_for("admin/loginstyle/delete/{$pic->id}") ?>">
                        <?= Icon::create('trash')->asImg([
                            'title'        => _('Bild löschen'),
                            'data-confirm' => _('Soll das Bild wirklich gelöscht werden?'),
                        ]) ?>
                    </a>
                <? endif; ?>
                </td>
            </tr>
        <? endforeach ?>
    </table>
<? else : ?>
    <?= PageLayout::postInfo(_('In Ihrem System sind leider keine Bilder für den Startbildschirm hinterlegt.')) ?>
<? endif ?>
