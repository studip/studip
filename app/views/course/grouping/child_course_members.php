<?= $this->render_partial('course/grouping/_perm_level', [
    'level'   => 'dozent',
    'current' => $child,
    'members' => $child->members->findBy('status', 'dozent')->orderBy('nachname, vorname'),
]) ?>
<?= $this->render_partial('course/grouping/_perm_level', [
    'level'   => 'deputy',
    'current' => $child,
    'members' => SimpleORMapCollection::createFromArray(Deputy::findByRange_id($child->id))->orderBy('nachname vorname'),
]) ?>
<? foreach (words('tutor autor user') as $level) :
    $members = $child->members->findBy('status', $level)->orderBy('nachname, vorname')
?>
    <? if (count($members) > 0) : ?>
        <?= $this->render_partial('course/grouping/_perm_level', [
            'level' => $level,
            'current' => $child,
            'members' => $members,
        ]) ?>
    <? endif ?>
<? endforeach ?>
