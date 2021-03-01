<? if ($GLOBALS['perm']->have_studip_perm('dozent', $sem_id) && $GLOBALS['perm']->have_studip_perm('tutor', $sem_id, $m['user_id'])) : ?>
    <a href="<?= $controller->link_for('course/studygroup/edit_members/downgrade', $type, ['user' => $m['username']]) ?>" data-confirm="<?= _('Wollen Sie die Person wirklich runterstufen?') ?>">
        <?= Icon::create('arr_2down')->asImg(['title' => _('Runterstufen')]) ?>
    </a>
<? endif ?>

<? if ($GLOBALS['perm']->have_studip_perm('dozent', $sem_id) && !$GLOBALS['perm']->have_studip_perm('dozent', $sem_id, $m['user_id'])) : ?>
    <a href="<?= $controller->link_for('course/studygroup/edit_members/promote', $type, ['user' => $m['username']]) ?>" data-confirm="<?= _('Wollen Sie die Person wirklich hochstufen?') ?>">
        <?= Icon::create('arr_2up')->asImg(['title' => _('Hochstufen')])?>
    </a>
<? endif ?>

<? if ($m['user_id'] !== $GLOBALS['user']->id && $GLOBALS['perm']->have_studip_perm('dozent', $sem_id)): ?>
    <a href="<?= $controller->link_for('course/studygroup/edit_members/remove', ['user' => $m['username']]) ?>" data-confirm="<?= _('Wollen Sie die Person wirklich rauswerfen?') ?>">
        <?= Icon::create('trash')->asImg(['title' => _('Rauswerfen')])?>
    </a>
<? endif; ?>
