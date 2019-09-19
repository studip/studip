<? $comp_abs = $conflicts->findBy('base_metadate_id', $cycle->id)->pluck('comp_abschnitt_id') ?>
<? $comp_versions = StgteilVersion::findBySQL('INNER JOIN `mvv_stgteilabschnitt` USING (`version_id`) WHERE `abschnitt_id` IN (?) GROUP BY `version_id`', [$comp_abs]); ?>
<? foreach ($comp_versions as $comp_version) : ?>
<li>
    <?= Icon::create('category', Icon::ROLE_INFO); ?>
    <a class="mvv-ovl-version" href="<?= $controller->url_for('admin/overlapping/version_info', $comp_version->id) ?>" data-dialog="size=auto">
        <?= htmlReady($comp_version->getDisplayName()); ?>
    </a>
    <? $comp_abschnitte = $comp_version->abschnitte->findBy('abschnitt_id', $conflicts->findBy('base_metadate_id', $cycle->id)->pluck('comp_abschnitt_id'))->orderBy('position', SORT_NUMERIC) ?>
    <ul>
    <? foreach ($comp_abschnitte as $comp_abschnitt) : ?>
        <li>
        <?= htmlReady($comp_abschnitt->getDisplayName()) ?>
        <? $modul_ids = Modulteil::findAndMapMany(function ($mt) {
            return $mt->modul_id;
        }, $conflicts->findBy('base_metadate_id', $cycle->id)->pluck('comp_modulteil_id')) ?>
        <? $module = StgteilabschnittModul::findBySQL(
            '`abschnitt_id` = ? AND `modul_id` IN (?) ORDER BY `position`',
            [$comp_abschnitt->id, $modul_ids]
        ) ?>
        <ul>
        <? foreach ($module as $modul) : ?>
            <li>
                <?= Icon::create('log', Icon::ROLE_INFO); ?>
                <?= htmlReady($modul->getDisplayName()) ?>
                <? $conflicts_modulteile = $conflicts->filter(function ($c) use ($cycle, $comp_abschnitt) {
                    return $c->base_metadate_id == $cycle->id && $c->comp_abschnitt_id == $comp_abschnitt->id;
                }) ?>
                <? $comp_modulteile = $modul->modul->modulteile->findBy('modulteil_id', $conflicts_modulteile->pluck('comp_modulteil_id')) ?>
                <ul>
                <? foreach ($comp_modulteile as $comp_modulteil) : ?>
                    <li class="mvv-ovl-comp-modulteil">
                        <? $id = md5($base_modul->abschnitt_id . $comp_abschnitt->id . $comp_modulteil->id) ?>
                        <input id="<?= $id ?>" type="checkbox" checked>
                        <label for="<?= $id ?>"></label>
                        <div>
                            <?= htmlReady($comp_modulteil->getDisplayName()) ?>
                        </div>
                        <div>
                        <? foreach (range(1, 6) as $fachsem_nr) : ?>
                            <? $fachsems = $comp_modulteil->abschnitt_assignments->findBy('abschnitt_id', $comp_abschnitt->id); ?>
                            <? $fachsem = $fachsems->findOneBy('fachsemester', $fachsem_nr); ?>
                            <? if ($fachsem) : ?>
                                <div <?= tooltip($GLOBALS['MVV_MODULTEIL_STGABSCHNITT']['STATUS']['values'][$fachsem->differenzierung]['name']) ?>>
                                <?= $GLOBALS['MVV_MODULTEIL_STGABSCHNITT']['STATUS']['values'][$fachsem->differenzierung]['icon']; ?>
                            <? else : ?>
                                <div>
                            <? endif; ?>
                            </div>
                        <? endforeach; ?>
                        </div>
                        <? $comp_cycles = $conflicts->filter(function ($c) use ($comp_abschnitt, $comp_modulteil, $cycle, $base_modul, $modulteil) {
                            return ($c->base_abschnitt_id == $base_modul->abschnitt_id
                                    && $c->base_modulteil_id == $modulteil->id
                                    && $c->base_metadate_id == $cycle->id
                                    && $c->comp_abschnitt_id == $comp_abschnitt->id
                                    && $c->comp_modulteil_id == $comp_modulteil->id);
                        }); ?>
                        <ul class="mvv-overlapping-conflicts">
                        <? foreach ($comp_cycles as $comp_cycle) : ?>
                            <li>
                                <?= htmlReady($comp_cycle->comp_course->VeranstaltungsNummer) ?>
                                <a href="<?= $controller->url_for('admin/overlapping/course_info', $comp_cycle->comp_course_id) ?>" data-dialog="">
                                    <?= Icon::create('info-circle', Icon::ROLE_INFO, ['style' => 'vertical-align: text-bottom;', 'title' => _('Veranstaltungsdetails')]) ?>
                                </a>
                                <?= htmlReady($comp_cycle->comp_course->getFullname('type-name')) ?>
                                <? if ($comp_cycle->comp_course->admission_turnout) : ?>
                                    <?= sprintf(_('(erw. TN %s)'), htmlReady($comp_cycle->comp_course->admission_turnout)) ?>
                                <? endif; ?>
                                <? $dates = $comp_cycle->comp_cycle->dates->filter(function ($c) use ($selected_semester) {
                                    return ($selected_semester->beginn <= $c->date && $selected_semester->ende >= $c->date);
                                }); ?>
                                <?= Icon::create('date-cycle', Icon::ROLE_INFO, ['style' => 'vertical-align: text-bottom;']) ?>
                                <?= sprintf('%s (%sx)', $comp_cycle->comp_cycle->toString('short'), count($dates)); ?>
                                <a href="<?= $controller->url_for('admin/overlapping/admin_info', $comp_cycle->comp_course->id) ?>" data-dialog="size=auto;title='<?= htmlReady($comp_cycle->comp_course->getFullname()) ?>';">
                                    <?= Icon::create('person-online', Icon::ROLE_INFO, ['style' => 'vertical-align: text-bottom;', 'title' => _('Zuständige Administratoren')]) ?>
                                </a>
                                <span title="<?= $comp_cycle->isExcluded() ? _('Veranstaltung nicht berücksichtigen') : _('Veranstaltung berücksichtigen') ?>"
                                      data-mvv-ovl-course="<?= $comp_cycle->comp_course_id ?>"
                                      data-mvv-ovl-selection="<?= $comp_cycle->selection_id ?>"
                                      class="mvv-overlapping-exclude<?= $comp_cycle->isExcluded() ? ' mvv-overlapping-invisible' : '' ?>">
                                </span>
                            </li>
                        <? endforeach; ?>
                        </ul>
                    </li>
                <? endforeach; ?>
                </ul>
            </li>
        <? endforeach; ?>
        </ul>
    </li>
    <? endforeach; ?>
    </ul>
</li>
<? endforeach; ?> 