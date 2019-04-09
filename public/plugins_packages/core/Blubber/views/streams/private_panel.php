<p>
    <strong><?= _("Privat") ?></strong> - <?= _("nur die folgenden Personen dürfen den Blubber sehen:") ?>
</p>
<? $i_shared = false ?>
<ul class="blubber_contacts">
    <li>
        <? $author = $thread->getUser() ?>
        <? if ($author['user_id'] === $GLOBALS['user']->id) $i_shared = true ?>
        <a href="<?= URLHelper::getLink($author->getURL()) ?>">
            <?= $author->getAvatar()->getImageTag(Avatar::MEDIUM, ['title' => $author->getName()]) ?>
        </a>
    </li>
    <? foreach ($thread->getRelatedUsers() as $user_id) : ?>
    <? if ($author['user_id'] !== $user_id) : ?>
    <li>
        <? $user = new BlubberUser($user_id) ?>
        <a href="<?= URLHelper::getLink($user->getURL()) ?>">
            <?= $user->getAvatar()->getImageTag(Avatar::MEDIUM, ['title' => $user->getName()]) ?>
        </a>
    </li>
    <? endif ?>
    <? endforeach ?>
    <li class="want_to_share">
        <?= Icon::create('add', 'clickable', ['title' => _("Weitersagen / teilen")])->asImg(24) ?>
    </li>
</ul>
<hr>
<br><br>
