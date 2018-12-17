<? if ($GLOBALS['perm']->have_studip_perm('dozent', $sem_id) && $GLOBALS['perm']->have_studip_perm('tutor', $sem_id, $m['user_id'])) : ?>
    <a href="<?=$controller->url_for('course/studygroup/edit_members/downgrade?user=' . $m['username'], ['cid' => $sem_id])?>">
        <?= Icon::create('arr_2down', 'clickable', ['title' => _('Runterstufen')])->asImg(20)?>
    </a>
<? endif ?>

<? if($GLOBALS['perm']->have_studip_perm('dozent', $sem_id) && !$GLOBALS['perm']->have_studip_perm('dozent', $sem_id, $m['user_id'])) : ?>
    <a href="<?=$controller->url_for('course/studygroup/edit_members/promote?user=' . $m['username'], ['cid' => $sem_id])?>">
        <?= Icon::create('arr_2up', 'clickable', ['title' => _('Hochstufen')])->asImg(20)?>
    </a>
<? endif ?>

<? if ($m['user_id'] !== $GLOBALS['user']->id && $GLOBALS['perm']->have_studip_perm('dozent', $sem_id)): ?>
    <a href="<?=$controller->url_for('course/studygroup/edit_members/remove?user=' . $m['username'], ['cid' => $sem_id])?>">
        <?= Icon::create('trash', 'clickable', ['title' => _('Rauswerfen')])->asImg(20)?>
    </a>
<? endif; ?>
