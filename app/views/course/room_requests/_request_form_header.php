<form method="post" name="room_request" class="default"
      action="<?= $action ?>">
    <input type="hidden" name="request_id" value="<?= htmlReady($request_id) ?>">
    <?= CSRFProtection::tokenTag() ?>
