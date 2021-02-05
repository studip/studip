<? if ($booking): ?>
    <? if ($user_has_user_perms): ?>
        <p>
            <?= sprintf(
                _('Letzte Änderung am %s'),
                date('d.m.Y', $booking->chdate)
            ) ?>
        </p>
    <? endif ?>
    <h3><?= _('Gebuchte Ressource') ?></h3>
    <? $derived_resource = $booking->resource
                         ? $booking->resource->getDerivedClassInstance()
                         : null ?>
    <?= $derived_resource
      ? $derived_resource->getFullName()
      : _('unbekannt') ?>

    <h3><?= _('Zeiträume') ?></h3>
    <?
    $cycle_date = $booking->assigned_course_date->cycle;
    $booking_has_metadate = ($cycle_date instanceof SeminarCycleDate);
    ?>
    <? $intervals = $booking->getTimeIntervals() ?>
    <? if (count($intervals) == 1) : ?>
        <div>
            <?= $intervals[0] ?>
        </div>
        <? if ($booking_has_metadate) :?>
        <div><?= _('Diese Buchung ist Teil der folgenden Terminserie:') ?></div>
        <?= htmlReady($cycle_date->toString('full')) ?>
        <? endif ?>
    <? elseif ($intervals): ?>
        <ul>
            <? foreach ($intervals as $interval): ?>
                <li>
                    <?= $interval ?>
                </li>
            <? endforeach ?>
        </ul>
    <? else: ?>
        <?= _('Nicht verfügbar') ?>
    <? endif ?>

    <? if ($booking->booking_type == '2'): ?>
        <? if ($user_has_user_perms): ?>
            <h3><?= _('Gesperrt für:') ?></h3>
            <?= htmlReady($booking->getAssignedUserName()) ?>
        <? endif ?>
        <h3><?= _('Sperrung vorgenommen von:') ?></h3>
        <?= $booking->booking_user
          ? htmlReady($booking->booking_user->getFullName())
          : _('unbekannt') ?>
    <? elseif ($booking->booking_type == '1'): ?>
        <? if ($user_has_user_perms): ?>
            <h3><?= _('Reserviert für:') ?></h3>
            <?= htmlReady($booking->getAssignedUserName()) ?>
        <? endif ?>
        <h3><?= _('Reservierung vorgenommen von:') ?></h3>
        <?= $booking->booking_user
          ? htmlReady($booking->booking_user->getFullName())
          : _('unbekannt') ?>
    <? else: ?>
        <? if ($user_has_user_perms): ?>
            <h3><?= _('Gebucht von:') ?></h3>
            <? if ($booking->booking_user) :?>
                <a href="<?= URLHelper::getScriptLink(
                         'dispatch.php/profile',
                         ['username' => $booking->booking_user->username]
                         ) ?>">
                <?= htmlReady($booking->booking_user->getFullName()) ?>
                </a>
                <a href="<?= URLHelper::getScriptLink(
                         'dispatch.php/messages/write',
                         ['rec_uname' => $booking->booking_user->username]
                         ) ?>" data-dialog="size=auto">
                <?= Icon::create('mail')->asImg(20, ['class' => 'text-bottom']) ?>
                </a>
            <? else :?>
                <?= _('unbekannt') ?>
            <? endif ?>
        <? endif ?>
    <? endif ?>
    <? if ($booking->getAssignedUserType() === 'course'): ?>
        <h3><?= _('Gebucht für:') ?></h3>
        <a href="<?= URLHelper::getScriptLink(
                 'dispatch.php/course/details/index/'
                 . $booking->getAssignedUser()->id
                 ) ?>" data-dialog>
            <?= htmlReady($booking->getAssignedUserName(), true, true) ?>
            <?= Icon::create(
                'link-intern',
                Icon::ROLE_CLICKABLE,
                [
                    'title' => _('Veranstaltungsdetails anzeigen'),
                    'class' => 'text-bottom'
                ]
            ) ?>
        </a>
        <? if (Seminar_Perm::get()->have_studip_perm('dozent', $booking->getAssignedUser()->id)): ?>
            <div>
                <a href="<?= URLHelper::getLink('dispatch.php/course/timesrooms', [ 'cid' => $booking->getAssignedUser()->id]) ?>">
                    <?=_('Verwaltung von Zeiten und Räumen')?>
                    <?= Icon::create(
                        'schedule',
                        Icon::ROLE_CLICKABLE,
                        [
                            'title' => _('Verwaltung von Zeiten und Räumen'),
                            'class' => 'text-bottom'
                        ]) ?>
                </a>
            </div>
        <? endif ?>
    <? elseif ($booking->getAssignedUserType() === 'user') : ?>
        <? if (($booking->assigned_user->visible == 'yes') ||
               ($booking->assigned_user->id == $GLOBALS['user']->id) ||
               $user_has_user_perms) : ?>
            <h3><?= _('Gebucht für:') ?></h3>
            <a href="<?= URLHelper::getScriptLink(
                     'dispatch.php/profile',
                     ['username' => $booking->assigned_user->username]
                     ) ?>">
                <?= htmlReady($booking->assigned_user->getFullName()) ?>
            </a>
            <a href="<?= URLHelper::getScriptLink(
                     'dispatch.php/messages/write',
                     ['rec_uname' => $booking->assigned_user->username]
                     ) ?>" data-dialog="size=auto">
                <?= Icon::create('mail')->asImg(20, ['class' => 'text-bottom']) ?>
            </a>
        <? endif ?>
    <? else : ?>
        <?= htmlReady($booking->description) ?>
    <? endif ?>
    <? if ($make_comment_editable): ?>
        <form class="default" method="post"
              action="<?= htmlReady(
                      $controller->link_for('resources/booking/index/' . $booking->id)
                      ) ?>" data-dialog="reload-on-close">
            <?= CSRFProtection::tokenTag() ?>
    <? endif ?>
    <? if ($show_internal_comment): ?>
        <h3><?= _('Interner Kommentar zur Buchung') ?>:</h3>
        <? if ($make_comment_editable): ?>
            <textarea name="internal_comment"><?= htmlReady($booking->internal_comment) ?></textarea>
        <? else: ?>
            <?= htmlReady($booking->internal_comment) ?>
        <? endif ?>
    <? endif ?>
    <? if ((Request::isDialog() || $make_comment_editable) && !$hide_buttons): ?>
        <div data-dialog-button>
            <? if ($make_comment_editable): ?>
                <?= \Studip\Button::create(_('Speichern'), 'save') ?>
                <? if (!$booking->isReadOnlyForUser($current_user)): ?>
                    <?= \Studip\LinkButton::create(
                        _('Bearbeiten'),
                        $controller->url_for('resources/booking/edit/' . $booking->id),
                        [
                            'data-dialog' => '1'
                        ]
                    ) ?>
                    <?= \Studip\LinkButton::create(
                        _('Löschen'),
                        $controller->url_for('resources/booking/delete/' . $booking->id),
                        [
                            'data-dialog' => '1'
                        ]
                    ) ?>
                <? endif ?>
            <? endif ?>
        </div>
    <? endif ?>
    <? if ($make_comment_editable): ?>
        </form>
    <? endif ?>
<? endif ?>
