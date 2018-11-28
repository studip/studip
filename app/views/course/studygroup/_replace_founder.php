<section>
    <?= _('GruppengründerInnen') ?>
</section>

<div class="hgroup">

    <? if(is_array($founders) && sizeof($founders) > 0) : ?>
        <ul>
        <? foreach($founders as $founder) : ?>
            <li><?= htmlReady(get_fullname_from_uname($founder['username'])) ?></li>
        <? endforeach; ?>
        </ul>
    <? endif; ?>

    <? if(!empty($tutors)) :?>
        <?= Icon::create('arr_2left', 'sort', ['title' => _('Als GruppengründerIn eintragen')])->asInput(["type" => "image", "class" => "middle", "name" => "replace_founder"]) ?>
        <select name="choose_founder">
            <? foreach($tutors as $uid => $tutor) : ?>
                <option value="<?=$uid?>"> <?= htmlReady($tutor['fullname']) ?> </option>
            <? endforeach ; ?>
        </select>
    <? endif; ?>
</div>
