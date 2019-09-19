<h1>
    <?= Icon::create('category', Icon::ROLE_INFO)->asImg() ?>
    <?= htmlReady($base_version->getDisplayName()); ?>
</h1>
<section>
    <? foreach ($base_version->abschnitte->findBy('id', $conflicts->pluck('base_abschnitt_id')) as $abschnitt) : ?>
    <article>
        <header class="mvv-ovl-base-abschnitt">
            <h2>
                <?= htmlReady($abschnitt->getDisplayName()); ?>
            </h2>
            <div>
            <? foreach (range(1, 6) as $fachsem_nr) : ?>
                <div>
                    <?= $fachsem_nr ?>
                </div>
            <? endforeach; ?>
            </div>
        </header>
        <? foreach ($abschnitt->modul_zuordnungen as $modul) : ?>
            <? if (count(array_intersect($modul->modul->modulteile->pluck('id'), $conflicts->pluck('base_modulteil_id')))) : ?>
                <ul class="collapsable css-tree mvv-ovl-conflict">
                    <li>
                        <?= $this->render_partial('admin/overlapping/modul', ['abschnitt' => $abschnitt, 'modul' => $modul]); ?>
                    </li>
                </ul>
            <? endif; ?>
        <? endforeach; ?>
    </article>
    <? endforeach; ?>
</section>