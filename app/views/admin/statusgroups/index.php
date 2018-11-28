<? if (!$groups): ?>
    <?= MessageBox::info(_('Es wurden noch keine Gruppen angelegt')) ?>
<? endif; ?>
<? foreach ($groups as $group): ?>
    <?= $this->render_partial('admin/statusgroups/_group.php', compact('group')) ?>
<? endforeach; ?>