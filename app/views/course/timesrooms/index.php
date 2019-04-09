<? if ($show['roomRequest']) : ?>
    <!--Raumanfragen-->
    <?= $this->render_partial('course/timesrooms/_roomRequestInfo.php') ?>
<? endif; ?>

<? if (Request::isXhr()): ?>
    <?= $this->render_partial('course/timesrooms/_select_semester_range.php') ?>
<? endif ?>

<? if ($show['regular']) : ?>
    <!--Regelmäßige Termine-->
    <?= $this->render_partial('course/timesrooms/_regularEvents.php') ?>
<? endif; ?>

<? if ($show['irregular']) : ?>
    <!--Unregelmäßige Termine-->
    <?= $this->render_partial('course/timesrooms/_irregularEvents') ?>
<? endif; ?>

<? if ($show['roomRequest']) : ?>
    <!--Raumanfrage-->
    <?= $this->render_partial('course/timesrooms/_roomRequest.php') ?>
<? endif; ?>

<? if (Request::isXhr() && !$locked && Config::get()->RESOURCES_ENABLE && Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS): ?>
    <div data-dialog-button>
    <?= Studip\LinkButton::create(_('Raumanfrage erstellen'), 
            $controller->url_for('course/room_requests/edit/' . $course->id,
            ['cid' => $course->id, 'new_room_request_type' => 'course', 'origin' => 'admin_courses']),
            ['data-dialog' => 'size=big']) ?>
    </div>
<? endif; ?>