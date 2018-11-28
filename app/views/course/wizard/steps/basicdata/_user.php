<div class="<?= $class ?>">
    <input type="hidden" name="<?= $inputname ?>[<?= $user->id ?>]" value="1" id="<?= $user->id ?>"/>
    <?= htmlReady($user->getFullname('full_rev')) ?> (<?= htmlReady($user->username) ?>)
    <?= Icon::create('trash', 'clickable')->asInput(["name" => 'remove_'.$class.'['.$user->id.']', "onclick" => "return STUDIP.CourseWizard.removePerson('".$user->id."')"]) ?>
</div>
