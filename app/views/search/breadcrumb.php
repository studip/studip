<div style="padding-bottom: 20px;">
<? $bc_points = $this->breadcrumb->getTrail(); ?>
<? $sum_points = count($bc_points) - 1; ?>
<? $index = 0; ?>
<? foreach($bc_points as $type => $point):?>
    <? $id2 = reset(array_values((array) $point['add'])); ?>
    <? $link = $controller->link_for('/' . $point['actn'], $point['id'], $id2); ?>
    <? if (is_array($point['add'])) : ?>
        <? $mvv_object = $type::find($point['id']); ?>
        <? if ($type == 'Fach' && $additional_object = Abschluss::find($point['add']['Abschluss'])) : ?>
            <a href="<?= $link ?>"><?= htmlReady($mvv_object->getDisplayName() . ' (' . $additional_object->name . ')') ?></a>
        <? endif; ?>
        <? if ($type == 'StgteilBezeichnung' && $additional_object = StudiengangTeil::find($point['add']['StudiengangTeil'])) : ?>
            <a href="<?= $link ?>"><?= htmlReady($mvv_object->getDisplayName() . ': ' . $additional_object->getDisplayName(ModuleManagementModel::DISPLAY_FACH)) ?></a>
        <? endif; ?>
    <? else : ?>
        <? if ($type == 'StudiengangTeil' && $mvv_object = $type::find($point['id'])) : ?>
            <a href="<?= $link ?>"><?= htmlReady($mvv_object->getDisplayName(ModuleManagementModel::DISPLAY_FACH)) ?></a>
        <? elseif ($point['id'] && $mvv_object = $type::find($point['id'])) : ?>
            <a href="<?= $link ?>"><?= htmlReady($mvv_object->getDisplayName(0)) ?></a>
        <? else : ?>
            <a href="<?= $link ?>"><?= htmlReady($point['name']) ?></a>
        <? endif; ?>
    <? endif; ?>
    <? if ($point['actn'] == $controller->action) break; ?>
    <?= $index++ < $sum_points ? '>' : null; ?>
<? endforeach; ?>
</div>
