<div class="institute">
    <input type="hidden" name="participating[<?= $inst->id ?>]" value="1" id="<?= $inst->id ?>"/>
    <?= htmlReady($inst->name) ?>
    <?= Icon::create('trash', 'clickable')->asInput(["name" => 'remove_participating['.$inst->id.']', "onclick" => "return STUDIP.CourseWizard.removeParticipatingInst('".$inst->id."')"]) ?>
</div>
