<td colspan="4">
    <table id="abschluesse_<?= $kategorie->id ?>" class="default sortable">
        <? foreach ($kategorie->abschluesse as $abschluss) : ?>
            <? if (count($perm_institutes) === 0
                || array_intersect($perm_institutes, array_keys($abschluss->getAssignedInstitutes()))) : ?>
                <tbody id="<?= $kategorie->id . '_' . $abschluss->id ?>"<?= MvvPerm::haveFieldPermPosition('AbschlussZuord', MvvPerm::PERM_WRITE) ? ' class="sort_items"' : '' ?>>
                    <tr class="header-row">
                        <td>
                            <?= htmlReady($abschluss->name) ?>
                        </td>
                    </tr>
                </tbody>
            <? endif; ?>
        <? endforeach; ?>
    </table>
</td>