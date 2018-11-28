<? use Studip\LinkButton; ?>

<h1><?= _('Versions-Konflikte der Touren') ?></h1>
<form action="<?= $controller->url_for('tour/admin_conflicts') ?>" id="admin_tour_form" method="post">
    <?= CSRFProtection::tokenTag(); ?>
<? if (count($conflicts) > 0) : ?>
    <? foreach ($conflicts as $conflict) : ?>
        <table class="default">
            <? $keys = array_keys($conflict); ?>
            <colgroup>
                <col width="20%">
                <col width="40%">
                <col width="40%">
            </colgroup>
            <tbody>
                <tr>
                    <th><?= _('Feld') ?></th>
                    <th><?= sprintf(_('Lokale Version (%s)'), $conflict[$keys[0]]->studip_version) ?></th>
                    <th><?= sprintf(_('Offizielle Version (%s)'), $conflict[$keys[1]]->studip_version) ?></th>
                </tr>
                <tr>
                    <td><?= _('Titel') ?></td>
                    <td><?= htmlReady($conflict[$keys[0]]->name) ?></td>
                    <td><?= htmlReady($conflict[$keys[1]]->name) ?></td>
                </tr>
                <? foreach ($diff_fields as $field => $title) : ?>
                    <? if ($conflict[$keys[0]]->$field !== $conflict[$keys[1]]->$field) : ?>
                        <tr>
                            <td><?= htmlReady($title) ?></td>
                            <td><?= htmlReady($conflict[$keys[0]]->$field) ?></td>
                            <td><?= htmlReady($conflict[$keys[1]]->$field) ?></td>
                        </tr>
                    <? endif ?>
                <? endforeach ?>
                <? $max_steps = max(
                    count($conflict[$keys[0]]->steps),
                    count($conflict[$keys[1]]->steps)
                ); ?>
                <? for ($nr = 1; $nr <= $max_steps; $nr++) : ?>
                    <? foreach ($diff_step_fields as $field => $title) : ?>
                        <? if ($conflict[$keys[0]]->steps[$nr]->$field != $conflict[$keys[1]]->steps[$nr]->$field) : ?>
                            <tr>
                                <td><?= htmlReady($title) ?> <?= sprintf(_('(Schritt %s)'), $nr) ?></td>
                                <td><?= htmlReady($conflict[$keys[0]]->steps[$nr]->$field) ?></td>
                                <td><?= htmlReady($conflict[$keys[1]]->steps[$nr]->$field) ?></td>
                            </tr>
                        <? endif ?>
                    <? endforeach ?>
                <? endfor ?>
            </tbody>
            <tfoot>
                <tr>
                    <td></td>
                    <td><?= LinkButton::create(_('Übernehmen'), $controller->url_for('tour/resolve_conflict/' . $conflict[$keys[0]]->getId() . '/accept')) ?></td>
                    <td><?= LinkButton::create(_('Löschen'), $controller->url_for('tour/resolve_conflict/' . $conflict[$keys[0]]->getId() . '/delete')) ?></td>
                </tr>
            </tfoot>
        </table>
    <? endforeach ?>
<? else : ?>
    <?= MessageBox::info(_('Keine Konflikte vorhanden.')) ?>
<? endif ?>
</form>
