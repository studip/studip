<td colspan="3">
    <table class="default">
        <colgroup>
            <col>
            <col style="width: 40%">
            <col style="width: 1%">
        </colgroup>
        <?php $abschluesse = $fach->getAbschluesse()?>
        <? foreach ($abschluesse as $abschluss) : ?>
            <tr>
                <td><?= htmlReady($abschluss->getDisplayName()) ?></td>
                <td><?= htmlReady($abschluss->category->getDisplayName()) ?></td>
                <td class="actions">
                    <? if (MvvPerm::havePermWrite($abschluss)) : ?>
                        <a data-dialog href="<?= $controller->url_for('/abschluss/' . $abschluss->id) ?>">
                            <?= Icon::create('edit', Icon::ROLE_CLICKABLE, ['title' => _('Abschluss bearbeiten')])->asImg(); ?>
                        </a>
                    <? endif; ?>
                </td>
            </tr>
        <? endforeach; ?>
    </table>
</td>
