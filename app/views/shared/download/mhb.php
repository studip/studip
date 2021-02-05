<? if (!empty($Stgteile)): ?>
    <div style="page-break-after:always;">
    <h1><?= $_('Übersicht') ?> <?= $semName ?></h1>
    <br>
    <? foreach ($Stgteile as $abs => $teile) :
        $abs_kat =AbschlussKategorie::getEnriched($abs);?>
        <section class="contentbox" style="page-break-after:always;">
            <header>
                <h2><?= $abs_kat->getDisplayName() ?></h2>
            </header>
            <? foreach ($teile as $id => $content) :
                $stgtv =StgteilVersion::getEnriched($id); ?>
                <section class="contentbox">
                    <header>
                        <h2><?= $stgtv->getDisplayName() ?></h2>
                    </header>
                    <? foreach ($content as $id_teil_abschnitt => $modulinfolist) :
                        $teilabschnitt = StgteilAbschnitt::getEnriched($id_teil_abschnitt); ?>
                        <section class="contentbox"  style="margin-left: 10pt;">
                            <header><b><?= $teilabschnitt->getDisplayName() ?></b></p></header>
                            <? foreach ($teilabschnitt->getChildren() as $modul) : ?>
                            	<? if (empty($module->find($modul->id))): ?>
                                    <? $module[] = $modul; ?>
                                <? endif; ?>
                                <p>
                                    <a href="#modref_<?= $modul->id; ?>" style="margin-left: 10pt;" >
                                        <?= $modul->getDisplayName(); ?>
                                    </a>
                                </p>
                            <? endforeach; ?>
                        </section>
                    <? endforeach; ?>
                </section>
            <? endforeach; ?>
        </section>
    <? endforeach; ?>
</div>
<br>
<? endif; ?>
<? if ($language == 'DE'): ?>
	<h3 style="text-align: center">
        <?= sprintf(_('Modulhandbuch %s'), $StgteilVersion->studiengangteil->getDisplayName())?>
    </h3>
    <h5 style="text-align: right"><?= sprintf(_('Datum %s'), strftime('%x', time()))?></h5>
<? else: ?>
    <h3>Modules for
        <? if (!is_null($StgteilVersion->studiengangteil->fach->name)
            && strlen($StgteilVersion->studiengangteil->fach->name) > 0) : ?>
                <?= htmlReady($StgteilVersion->studiengangteil->fach->name) ?>
                <? if (count($StgteilVersion->studiengangteil->studiengang) > 0) : ?>
                    <? if (!is_null($StgteilVersion->studiengangteil->studiengang->zusatz)
                        && strlen($StgteilVersion->studiengangteil->studiengang->zusatz) > 0) : ?>
                            <?= htmlReady($StgteilVersion->studiengangteil->studiengang->zusatz) ?>
                    <? endif; ?>
                <? endif; ?>
        <? else : ?>
            <?= $StgteilVersion->studiengangteil->getDisplayName() ?>
        <? endif; ?>
    </h3>
    <h6 style="text-align: right">Date <?= strftime('%x', time())?></h6>
<? endif; ?>
<? foreach($module as $part_id =>  $values) : ?>
    <h1><?= $values['part']->getDisplayName() ?></h1>
    <? foreach($values['modules'] as $mod_id => $modul) : ?>
        <? $modul_desc = ModulDeskriptor::findOneBySQL('modul_id =?', array($modul->id)); ?>
        <h3 id="modref_<?= $modul->id ?>"><?= !empty($modul_desc) ? $modul['code'] . ' - ' . $modul_desc['bezeichnung'] : $modul->getDisplayName() ?></h3>
        <div style="page-break-after:always;">
            <div>
                <?= $archiv->getMVVPluginModulDescription($modul, $language); ?>
            </div>
            <? if (is_array($modulseminare) && in_array($modul->id, array_keys($modulseminare)) ) : ?>
                <br>
                <div><p><b><?= _('dem Modul zugoerdnete Veranstaltungen') ?>:</b></p>
                    <? foreach($modulseminare[$modul->id] as $modulteilName => $modulteil): ?><?= $modulteilName ?>
                        <ul>
                            <? foreach($modulteil as $name => $id): ?>
                                <li><a href="#semref_<?= $id ?>"><?= $name ?></a></li>
                            <? endforeach; ?>
                        </ul>
                    <? endforeach; ?>
                </div>
            <? endif; ?>
        </div>
    <?endforeach; ?>
<? endforeach; ?>
