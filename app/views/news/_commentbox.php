<article class="comment open">
    <time><?= reltime($comment[3]) ?></time>
    <h1>#<?= $index + 1 ?>
        <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $comment[2]) ?>">
             <?= htmlReady($comment[1]) ?>
        </a>
    </h1>
    <?= formatReady($comment[0]) ?>
</article>