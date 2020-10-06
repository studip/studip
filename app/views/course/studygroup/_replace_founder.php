<section>
    <?= _('GruppengründerInnen') ?>
</section>

<div class="hgroup">
<? if (is_array($founders) && count($founders) > 0) : ?>
    <ul>
    <? foreach ($founders as $founder) : ?>
        <li><?= htmlReady(get_fullname_from_uname($founder['username'])) ?></li>
    <? endforeach; ?>
    </ul>
<? endif; ?>

<? if (!empty($tutors)) :?>
    <?= Icon::create('arr_2left', Icon::ROLE_SORT)->asInput([
        'title' => _('Als GruppengründerIn eintragen'),
        'class' => 'middle',
        'name'  => 'replace_founder',
    ]) ?>
    <select name="choose_founder">
    <? foreach($tutors as $uid => $tutor) : ?>
        <option value="<?= htmlReady($uid) ?>">
            <?= htmlReady($tutor['fullname']) ?>
        </option>
    <? endforeach ; ?>
    </select>
<? endif; ?>
</div>
