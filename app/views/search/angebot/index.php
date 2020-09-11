<?php
// function link_chars(&$char,
//         $key,
//         $pattern) {
//     $char = sprintf($pattern, $key, ucfirst($key));
// }
//
// array_walk($chars,
//         function (&$char) {
//             $char = sprintf('<a href="#%s">%s</a>', $char, ucfirst($char));
//         });
?>
<nav style="font-weight: bold">
    <ul class="list-pipe-separated">
    <? foreach ($chars as $char): ?>
        <li>
            <a href="#<?= $char ?>"><?= ucfirst($char) ?></a>
        </li>
    <? endforeach; ?>
    </ul>
</nav>
<? foreach ($faecher as $char => $abschluesse): ?>
<article class="studip toggle">
    <header>
        <h1>
            <a name="<?= $char ?>">
                <?= ucfirst($char); ?>
            </a>
        </h1>
    </header>
    <ul class="list-unstyled">
    <? foreach ($abschluesse as $fach): ?>
        <li>
            <a href="<?= $controller->link_for($url, $fach['fach_id'], $fach['abschluss_id']) ?>">
                <?= htmlReady($fach['name']) ?>
            </a>
        </li>
    <? endforeach; ?>
    </ul>
</article>
<? endforeach; ?>
