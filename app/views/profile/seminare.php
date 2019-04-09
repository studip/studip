<article class="studip">
    <header>
        <h1>
            <?= _('Veranstaltungen') ?>
        </h1>
    </header>

    <section>
    <? foreach ($seminare as $semester => $seminar) :?>
    <b><?= htmlReady($semester) ?></b><br>

        <? foreach ($seminar as $one) :?>
            <a href="<?= URLHelper::getScriptLink('dispatch.php/course/details', ['sem_id' => $one->id])?>">
                <?= htmlReady($one->getFullname('number-name')) ?>
                <? if ($one->start_semester !== $one->end_semester) : ?>
                    (<?= htmlReady($one->getFullname('sem-duration-name')) ?>)
                <? endif ?>
            </a><br>
        <?endforeach?>
    <?endforeach?>
    </section>
</article>