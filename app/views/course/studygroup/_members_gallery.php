<h3><?= htmlReady($title) ?></h3>

<ul class="studygroup-gallery">
    <? foreach ($members as $user_id => $m) : ?>
        <? $fullname = $m instanceof CourseMember ? $m->user->getFullname('no_title_rev') : $m['fullname']?>
        <? ($last_visitdate <= $m['mkdate'] && $GLOBALS['perm']->have_studip_perm('tutor', $sem_id))
            ? $options = ['style' => 'border: 3px solid rgb(255, 100, 100);'
                . 'border: 1px solid rgba(255, 0, 0, 0.5)']
            : $options = [] ?>
        <li>
            <div>
                <a href="<?= $controller->url_for('profile', ['username' => $m['username']]) ?>">
                    <?= Avatar::getAvatar($m['user_id'])->getImageTag(Avatar::MEDIUM, $options) ?>
                </a>
            </div>

            <div>
                <a href="<?= $controller->url_for('messages/write', ['rec_uname' => $m['username']]) ?>"
                   data-dialog="size=50%">
                    <?= Icon::create('mail', 'clickable', ['title' => _('Nachricht schreiben')])->asImg(20) ?>
                </a>
                <? if ($GLOBALS['perm']->have_studip_perm('tutor', $sem_id) || $GLOBALS['perm']->have_studip_perm('admin', $sem_id)) : ?>
                    <?= $this->render_partial('course/studygroup/_members_options.php', compact('m')) ?>
                <? endif ?>
            </div>

            <div style="font-size: 0.8em;">
                <a href="<?= $controller->url_for('profile', ['username' => $m['username']]) ?>">
                    <?= $fullname ? htmlReady($fullname) : _("unbekannt") ?>
                </a>
            </div>
        </li>
    <? endforeach ?>
</ul>
