<td colspan="5">
    <table class="default collapsable">
        <colgroup>
            <col>
            <col style="width: 1%">
        </colgroup>
        <? $stgteile_bez = StgteilBezeichnung::findByStudiengang($studiengang->id) ?>
        <? foreach ($stgteile_bez as $bez_stgteil) : ?>
            <tbody class="<?= ($bez_stgteil->count_stgteile ? '' : 'empty') ?> <?= (($stg_stgbez_id === $studiengang->id . '_' . $bez_stgteil->id) ? 'not-collapsed' : 'collapsed') ?>">
                <tr class="header-row" id="stgteil_<?= $bez_stgteil->id ?>">
                    <td class="toggle-indicator">
                        <? if ($bez_stgteil->count_stgteile) : ?>
                            <a class="mvv-load-in-new-row"
                               href="<?= $controller->url_for('/details_studiengang', $studiengang->id, $bez_stgteil->id) ?>">
                                <?= htmlReady($bez_stgteil->name) ?>
                            </a>
                        <? else : ?>
                            <?= htmlReady($bez_stgteil->name) ?>
                        <? endif; ?>
                    </td>
                    <td class="dont-hide actions"></td>
                </tr>
                <? if ($stg_stgbez_id === $studiengang->id . '_' . $bez_stgteil->id) : ?>
                    <tr class="loaded-details nohover">
                        <?= $this->render_partial('studiengaenge/studiengaenge/studiengangteile', compact('studiengang', 'stg_stgbez_id', 'search')) ?>
                    </tr>
                <? endif; ?>
            </tbody>
        <? endforeach; ?>
        <? if ($studiengang->typ === 'mehrfach' && MvvPerm::haveFieldPermStudiengangteil($studiengang, MVVPerm::PERM_CREATE)) : ?>
            <tfoot>
                <tr>
                    <td colspan="3">
                        <form style="width: 100%;"
                              action="<?= $controller->url_for('/add_stgteil/' .  $studiengang->id) ?>" method="post">
                            <?= CSRFProtection::tokenTag() ?>
                            <div style="float: left; padding-right: 10px;"><?= _('Studiengangteil hinzufügen') ?></div>
                            <div style="float: left; padding-right: 10px;">
                                <?= $search_stgteil->render() ?>
                                <?= Icon::create(
                                    'search',
                                    Icon::ROLE_CLICKABLE ,
                                    [
                                        'title'          => _('Studiengangteil zuordnen'),
                                        'name'           => 'search_stgteil', 'data-qs_name' => $search_stgteil->getId(),
                                        'data-qs_id'     => $qs_search_stgteil_id,
                                        'data-qs_submit' => 'no',
                                        'class'          => 'mvv-qs-button'
                                    ])->asInput(); ?>
                            </div>
                            <label><?= _('als') ?>
                                <select name="stgteil_bez_id" size="1">
                                    <option value="">-- <?= _('Bitte wählen') ?> --</option>
                                    <? foreach (StgteilBezeichnung::getAllEnriched() as $stgteil_bez) : ?>
                                        <option value="<?= $stgteil_bez->getId() ?>"><?= htmlReady($stgteil_bez->name) ?></option>
                                    <? endforeach; ?>
                                </select>
                            </label>
                            <input type="hidden" name="level" value="stg">
                            <input name="add_stgteil" class="text-top mvv-submit" type="image"
                                   title="<?= _('Studiengangteil hinzufügen') ?>"
                                   src="<?= Icon::create('accept')->asImagePath(); ?>">
                        </form>
                    </td>
                </tr>
            </tfoot>
        <? endif; ?>
    </table>
</td>
