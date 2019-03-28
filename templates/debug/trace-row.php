<?php
$file = ltrim(str_replace($GLOBALS['STUDIP_BASE_PATH'], '', $file), '/');
$trac = htmlReady("https://develop.studip.de/trac/browser/trunk/{$file}#L{$line}");
?>
<code>
    <?= htmlReady(sprintf(
        '%s(%s)',
        isset($class) ? "{$class}{$type}{$function}" : $function,
        implode(', ', array_map(function ($arg) { return is_object($arg) ? get_class($arg) : (string) $arg; }, $args))
    )) ?>
</code>
<span>called at</span>
<a href="<?= $trac ?>" target="_blank"><?= htmlReady("{$file}:{$line}") ?></a>
