<? if ($groups): ?>
    <ol class="dd-list">
    <? foreach ($groups as $group): ?>
        <li class="dd-item" data-id="<?= $group->id ?>">
            <div class="dd-handle"><?= formatReady($group->name) ?></div>
            <?= $this->render_partial('admin/statusgroups/_group-nestable', array('groups' => $group->children)) ?>
        </li>
    <? endforeach; ?>
    </ol>
<? endif; ?>