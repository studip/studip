<?= Icon::create('log', Icon::ROLE_INFO); ?> <?= htmlReady($modul->getDisplayName()); ?>
<ul>
    <? foreach ($modul->modul->modulteile->findBy('id', $conflicts->pluck('base_modulteil_id')) as $modulteil) : ?>
    <li class="mvv-ovl-base-modulteil">
        <? $id = md5($modul->abschnitt_id . $modulteil->id) ?>
        <input id="<?= $id ?>" type="checkbox" checked>
        <label for="<?= $id ?>"></label>
        <div>
            <?= htmlReady($modulteil->getDisplayName()); ?>
        </div>
        <? $fachsems = $modulteil->abschnitt_assignments->findBy('abschnitt_id', $abschnitt->id); ?>
        <div>
        <? foreach (range(1, 6) as $fachsem_nr) : ?>
            <? $fachsem = $fachsems->findOneBy('fachsemester', $fachsem_nr); ?>
            <? if ($fachsem) : ?>
                <div <?= tooltip($GLOBALS['MVV_MODULTEIL_STGABSCHNITT']['STATUS']['values'][$fachsem->differenzierung]['name']) ?>>
                    <?= $GLOBALS['MVV_MODULTEIL_STGABSCHNITT']['STATUS']['values'][$fachsem->differenzierung]['icon']; ?>
                </div>
            <? else : ?>
                <div></div>
            <? endif; ?>
        <? endforeach; ?>
        </div>
        <ul>
            <?= $this->render_partial('admin/overlapping/courses', ['modulteil' => $modulteil, 'modul' => $modul]); ?>
        </ul>
    </li>
    <? endforeach; ?>
</ul>
