<td colspan="3">
    <table class="default">
        <colgroup>
            <col width="70%">
            <col width="29%">
            <col width="1%">
        </colgroup>
    <? foreach ($degree as $key => $deg) : ?>
        <tbody>
            <tr>
                <td><?= htmlReady($deg->name) ?></td>
                <td>
                <?= UserStudyCourse::countBySql('fach_id = :fach_id AND abschluss_id = :abschluss_id',
                        [':fach_id' => $studycourse->fach_id, ':abschluss_id' => $deg->abschluss_id])?>
                    </td>
                <td class="actions">
                    <? $action =ActionMenu::get()
                        ->addLink($controller->url_for('/messagehelper',
                            ['fach_id' => $studycourse->fach_id, 'abschluss_id' => $deg->abschluss_id]),
                            _('Nachricht an Studierende schreiben'),
                            Icon::create('mail', Icon::ROLE_CLICKABLE,
                                ['title' => htmlReady(sprintf(_('Nachricht an alle Studierende mit dem Studiengang %s mit dem Abschluss %s'),
                                        $studycourse->name, $deg->name))]),
                            ['data-dialog' => '']) ?>
                    <?= $action ?>
                </td>
            </tr>
        </tbody>
    <? endforeach; ?>
    </table>
</td>