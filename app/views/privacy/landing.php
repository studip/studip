<ul class="boxed-grid">
<? foreach ($sections as $key => $row): ?>
    <li>
        <a href="<?= $controller->link_for("privacy/index/{$user_id}/{$key}") ?>" <? if (Request::isDialog()) echo 'data-dialog="size=big"'; ?>>
            <h3>
                <?= $row['icon']->asImg(false) ?>
                <?= htmlReady($row['title']) ?>
            </h3>
            <p>
                <?= htmlReady($row['description']) ?>
            </p>
        </a>
    </li>
<? endforeach; ?>

<!--
    this is pretty ugly but we need to spawn some empty elements so that the
    last row of the flex grid won't be messed up if the boxes don't line up
-->
    <li></li><li></li><li></li>
    <li></li><li></li><li></li>
</ul>
