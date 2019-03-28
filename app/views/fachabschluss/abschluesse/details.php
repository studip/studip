<td colspan="4">
    <table class="default">
        <colgroup>
            <col>
            <col style="width: 1%;">
        </colgroup>
        <? foreach ($abschluss->getFaecher() as $fach) : ?>
            <? if (count($perm_institutes) === 0
                || count(array_intersect($perm_institutes, $fach->getFachbereiche()->pluck('institut_id')))) : ?>
                <tr>
                    <td>
                        <?= htmlReady($fach->name) ?>
                    </td>
                    <td class="actions">
                        <? if (MvvPerm::havePermWrite($fach)) : ?>
                            <a data-dialog href="<?= $controller->url_for('/fach/' . $fach->id) ?>">
                                <?= Icon::create('edit', 'clickable', ['title' => _('Fach bearbeiten')])->asImg(); ?>
                            </a>
                        <? endif; ?>
                    </td>
                </tr>
            <? endif; ?>
        <? endforeach; ?>
    </table>
</td>



