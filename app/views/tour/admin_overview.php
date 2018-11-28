<? use Studip\Button, Studip\LinkButton; ?>
<?= $delete_question ?>

<form action="<?= $controller->url_for('tour/admin_overview') ?>" id="admin_tour_form" method="post" class="default">
    <input type="hidden" name="tour_filter" value="set">
    <input type="hidden" name="tour_filter_term" value="<?= htmlReady($tour_searchterm) ?>">
    <?= CSRFProtection::tokenTag(); ?>
<? if ($filter_text) : ?>
    <table class="default">
        <tr>
            <td><?= htmlReady($filter_text) ?></td>
            <td>
                <div class="tour_reset_filter">
                    <?= Button::create(_('Auswahl aufheben'), 'reset_filter') ?>
                </div>
            </td>
        </tr>
    </table>
<? endif ?>

    <table class="default">
        <caption>
            <div class="tour_list_title"><?= _('Touren') ?></div>
        </caption>
        <colgroup>
            <col width="20">
            <col>
            <col width="10%">
            <col width="10%">
            <col width="10%">
            <col width="20%">
            <col width="10%">
            <col width="80">
        </colgroup>
        <thead>
            <tr>
                <th><?= _('Aktiv') ?></th>
                <th><?= _('Überschrift') ?></th>
                <th><?= _('Sprache') ?></th>
                <th><?= _('Typ') ?></th>
                <th><?= _('Zugang') ?></th>
                <th><?= _('Startseite') ?></th>
                <th><?= _('Anzahl der Schritte') ?></th>
                <th><?= _('Aktion') ?></th>
            </tr>
        </thead>
    <? if (count($tours)) : ?>
        <tbody>
        <? foreach ($tours as $tour_id => $tour) : ?>
            <tr>
                <td>
                    <input type="checkbox" name="tour_status_<?= $tour_id ?>" value="1"
                           aria-label="<?= _('Status der Tour (aktiv oder inaktiv)') ?>" <?= tooltip(_("Status der Tour (aktiv oder inaktiv)"), false) ?><?= ($tour->settings->active) ? ' checked' : '' ?>>
                </td>
                <td>
                    <a href="<?= $controller->link_for('tour/admin_details/' . $tour_id) ?>">
                        <?= htmlReady($tour->name) ?>
                    </a>
                </td>
                <td><?= $tour->language ?></td>
                <td><?= $tour->type ?></td>
                <td><?= $tour->settings->access ?></td>
                <td>
                <? if (count($tour->steps)): ?>
                    <?= htmlReady($tour->steps[0]->route) ?>
                <? endif; ?>
                </td>
                <td><?= count($tour->steps) ?></td>
                <td class="actions">
                    <?= ActionMenu::get()->addLink(
                        $controller->url_for('tour/admin_details/' . $tour_id),
                        _('Tour bearbeiten'),
                        Icon::create('edit')
                    )->addLink(
                        $controller->url_for('tour/export/' . $tour_id),
                        _('Tour exportieren'),
                        Icon::create('export'),
                        ['disabled' => count($tour->steps) === 0]
                    )->addButton(
                        'tour_remove_' . $tour_id,
                        _('Tour löschen'),
                        Icon::create('trash')
                    ) ?>
                </td>
            </tr>
        <? endforeach ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8">
                    <?= Button::createAccept(_('Speichern'), 'save_tour_settings') ?>
                </td>
            </tr>
        </tfoot>
    <? else : ?>
        <tbody>
            <tr>
                <td colspan="8" style="text-align: center">
                    <?= _('Keine Touren vorhanden.') ?>
                </td>
            </tr>
        </tbody>
    <? endif ?>
    </table>
</form>
