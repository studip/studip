<div class="text-center avatar-widget">
    <?= $avatar->getImageTag(Avatar::NORMAL, [
        'class' => 'profile-avatar',
        'style' => ''
    ]) ?>
    <?php if ($GLOBALS['perm']->have_profile_perm('user', $current_user)) : ?>
        <div class="avatar-overlay">
            <a href="<?= URLHelper::getURL('dispatch.php/avatar/update/user/' . $current_user) ?>" data-dialog>
                <span><?= _('Bild hochladen oder löschen') ?></span>
            </a>
        </div>
    <?php endif ?>
</div>
<div class="profile-sidebar-details">
    <? if ($kings): ?>
        <div><?= $kings ?></div>
    <? endif; ?>
        <div class="minor">
            <?= _('Profilbesuche:') ?>
            <?= number_format($views, 0, ',', '.') ?>
        </div>
    <? if ($score && $score_title): ?>
        <div class="minor">
            <a href="<?= URLHelper::getLink('dispatch.php/score') ?>" title="<?= _('Zur Rangliste') ?>">
                <div><?= _('Stud.IP-Punkte') ?>: <?= number_format($score, 0, ',', '.') ?></div>
                <div><?= _('Rang') ?>: <?= htmlReady($score_title) ?></div>
            </a>
        </div>
    <? endif; ?>
</div>
