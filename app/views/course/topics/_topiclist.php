<? foreach ($topics as $topic) : ?>
<label>
    <input type="checkbox" name="topic[<?= htmlReady($topic->getId()) ?>]" value="1" checked>
    <?= htmlReady($topic['title']) ?>
</label>
<? endforeach ?>
