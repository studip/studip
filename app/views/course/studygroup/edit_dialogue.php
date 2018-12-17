<?php
use Studip\Button, Studip\LinkButton;
?>

<div class="modaloverlay">
    <div class="messagebox">
        <div class="content">
            <?= formatReady(_("Möchten Sie folgende Inhaltselemente wirklich deaktivieren?")
                    . "\n" . $deactivate_modules_names) ?>
        </div>
        <div class="buttons">
            <form action="<?= $controller->url_for('course/studygroup/update/', ['cid' => $sem_id]) ?>" method=post>
                <?= CSRFProtection::tokenTag() ?>
                <? foreach($this->flash['deactivate_modules'] as $module) :?>
                     <input type="hidden" name="deactivate_modules[]" value="<?=$module?>">
                <? endforeach ?>
                <? foreach($this->flash['deactivate_plugins'] as $plugin) :?>
                     <input type="hidden" name="deactivate_plugins[]" value="<?=$plugin?>">
                <? endforeach ?>

                <input type="hidden" name="really_deactivate" value="1">
                <?= Button::createAccept(_('JA!')); ?>
                <?= LinkButton::createCancel(_('NEIN!'), $controller->url_for('course/studygroup/update/', [
                    'abort_deactivate' => 1,
                    'cid'              => $sem_id
                ])); ?>
            </form>
        </div>
    </div>
</div>
