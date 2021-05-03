<div style="display: flex; width: 100%; margin-bottom: 20px;">
    <div>
        <img class="avatar-normal" src="<?= htmlReady($user['avatar']) ?>">
    </div>
    <div style="width: 100%; padding-left: 10px;">
        <h1><?= htmlReady($user['name']) ?></h1>
        <div>
            <?= formatReady($user['description']) ?>
        </div>
    </div>
</div>

<? if (count($materials)) : ?>
    <section class="contentbox">
        <header>
            <h1>
                <?= Icon::create("service", Icon::ROLE_CLICKABLE)->asImg("16px") ?>
                <?= _('Lernmaterialien') ?>
            </h1>
        </header>
        <section>
            <?= $this->render_partial("mymaterial/_material_list.php", ['materialien' => $materials]) ?>
        </section>
    </section>
<? endif ?>

