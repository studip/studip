<span class="tooltip tooltip-icon <? if ($important) echo 'tooltip-important'; ?>" data-tooltip <? if (!$html) printf('title="%s"', htmlReady($text)) ?>>
<? if ($html): ?>
    <span class="tooltip-content"><?= $text ?></span>
<? endif; ?>
</span>
