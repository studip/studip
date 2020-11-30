<?
$icons = [
    'user' => "person",
    'course' => "seminar",
    'institute' => "institute"
];
?>
<div class="file_select_possibilities">
    <div>
        <a href="<?= $controller->link_for("questionnaire/edit", ['range_type' => Context::getType(), 'range_id' => Context::get()->id]) ?>"
           data-dialog>
            <?= Icon::create($icons[Context::getType()], Icon::ROLE_CLICKABLE)->asImg(50) ?>
            <?= htmlReady(Context::get()->name) ?>
        </a>
        <? foreach ($statusgruppen as $statusgruppe) : ?>
            <a href="<?= $controller->link_for("questionnaire/edit", ['range_type' => "statusgruppe", 'range_id' => $statusgruppe->getId()]) ?>"
               data-dialog>
                <?= Icon::create('group2', Icon::ROLE_CLICKABLE)->asImg(50) ?>
                <?= htmlReady($statusgruppe->name) ?>
            </a>
        <? endforeach ?>
    </div>
</div>
