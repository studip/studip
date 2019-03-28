<td colspan="2">
    <table class="default">
        <colgroup>
            <col>
            <col style="width: 1%">
        </colgroup>
        <? foreach ($faecher as $fach) : ?>
            <tr>
                <td class="label-cell">
                    <?= htmlReady($fach->name) ?>
                </td>
                <td class="actions">
                    <? if (MvvPerm::havePermWrite($fach)) : ?>
                        <a href="<?= $controller->url_for('/fach/' . $fach->id) ?>">
                            <?= Icon::create('edit', Icon::ROLE_CLICKABLE, ['title' => _('Fach bearbeiten')])->asImg(); ?>
                        </a>
                    <? endif; ?>
                </td>
            </tr>
        <? endforeach; ?>
    </table>
</td>
