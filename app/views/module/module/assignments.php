<section>
    <h3><?= _('Verwendet in Studiengängen') ?></h3>
    <? $trails = $modul->getTrails(['Studiengang', 'StgteilVersion', 'StgteilAbschnitt']); ?>
    <? if (count($trails)) : ?>
        <ul>
            <? foreach ($modul->getPathes($trails, ' > ') as $path) : ?>
                <li><?= htmlReady($path) ?></li>
            <? endforeach; ?>
        </ul>
    <? else : ?>
        <strong><?= _('Keine Zuordnungen vorhanden') ?></strong>
    <? endif; ?>
</section>
