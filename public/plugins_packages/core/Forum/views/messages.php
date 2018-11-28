<? if (!empty($flash['messages'])) foreach ($flash['messages'] as $type => $message): ?>
    <? if ($type == 'info_html') : ?>
        <?= MessageBox::info($message) ?>
    <? else : ?>
        <?= MessageBox::$type(htmlReady($message)) ?>
    <? endif ?>
<? endforeach ?>
