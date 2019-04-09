<?
# Lifter010: TODO
?>
<div id="lvgruppe_selection">
    <em>
        <?= _("Sie dürfen die Modulzuordnung dieser Veranstaltung nicht verändern.") ?>
        <?= _("Diese Sperrung ist von einer Administratorin oder einem Administrator vorgenommen worden.") ?>
    </em>
    <div id="lvgruppe_selection_chosen" style="width: 50%;">
        <h3><?= _("Bestehende Zuordnungen:") ?></h3>
        <? if ($selection->size()) : ?>
            <em id="lvgruppe_selection_none" style="display:none;"><?= _("Bisher wurde noch keine Zuordnung vorgenommen") ?></em>
        <? else: ?>
            <em id="lvgruppe_selection_none"><?= _("Bisher wurde noch keine Zuordnung vorgenommen") ?></em>
        <? endif ?>
        <ul id="lvgruppe_selection_selected">
        <? foreach ($selection->getAreas() as $area) : ?>
           <?= $this->render_partial('course/lvgselector/selected_entry', ['area' => $area]) ?>
        <? endforeach ?>
        </ul>
    </div>
</div>

