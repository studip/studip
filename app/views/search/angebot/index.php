<h4><strong><?= implode(' | ',$chars)?> </strong></h4>
<? foreach ($faecher as $char => $abschluesse): ?>
<a name="<?= $char ?>"></a>
<section class="contentbox">
    <header>
        <h1><?= ucfirst($char); ?></h1>
    </header>
    <ul style="list-style-type: none;">
    <? foreach ($abschluesse as $fach): ?>
        <li>
            <a href="<?= $controller->url_for($url, $fach['fach_id'], $fach['abschluss_id']) ?>"> <?= htmlReady($fach['name']) ?></a>
        </li>
    <? endforeach; ?>
    </ul>
</section>
<? endforeach; ?>
