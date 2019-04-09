<? if ($drill_down_id) : ?>
<div style="width: 100%; text-align:right;">
    <a href="<?= $controller->url_for('search/module/reset_drilldown') ?>">
    <?= _('Auswahl aufheben') ?>
    <?= Icon::create('decline', 'clickable', ['title' => _('Auswahl aufheben')])->asImg(); ?>
    </a>
</div>
<? endif; ?>
<? /* 
<dl style="margin:0;">
<? foreach ($lists as $list) : ?>
    <? if (count($list['objects'])) : ?>
    <dt style="font-weight:bold;margin:5px 0;"><?= $list['name'] ?></dt>
    <dd>
        <ul style="list-style-type:none; margin:0; padding:0;">
            <? foreach ($list['objects'] as $object) : ?>
            <li style="margin:5px 0;">
                <a href="<?= $controller->url_for('search/module/drilldown', array('type' => get_class($object), 'id' => $object->getId())) ?>">
                <?= htmlReady($object->getDisplayName()) . ' (' . /*$object->count_module .*/ ')' ?>
 <? /*                <? if ($object->getId() == $drill_down_id) : ?>
                    <?= Icon::create('accept', 'clickable', array('title' => _('Bereich ausgewählt')))->asImg(); ?>
                <? endif; ?>
                </a>
            </li>
            <? endforeach; ?>
        </ul>
    </dd>
    <? endif; ?>
<? endforeach; ?>
</dl>
 */ ?>
 
 
<form action="<?= $controller->link_for('/index') ?>" method="post">
    <select name="actlist" style="margin:0;" class="submit-upon-select">
    <? foreach ($lists as $lname => $list) : ?>
    	<option value="<?= $lname ?>" <?= $lname == $act_list ? 'selected' :''; ?>><?= $list['name'] ?></option>
    <? endforeach; ?>
    </select>
</form>
<dl style="margin:0;">
    <? if (count($lists[$act_list]['objects'])) : ?>
    <dd>
        <ul style="list-style-type:none; margin:0; padding:0;">
            <? foreach ($lists[$act_list]['objects'] as $object) : ?>
            <li style="margin:5px 0;">
                <a href="<?= $controller->url_for('search/module/drilldown', ['type' => get_class($object), 'id' => $object->getId()]) ?>">
                <?= htmlReady($object->getDisplayName()) . ' (' . $object->count_module . ')' ?>
                <? if ($object->getId() == $drill_down_id) : ?>
                    <?= Icon::create('accept', 'clickable', ['title' => _('Bereich ausgewählt')])->asImg(); ?>
                <? endif; ?>
                </a>
            </li>
            <? endforeach; ?>
        </ul>
    </dd>
    <? endif; ?>
</dl>