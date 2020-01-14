<?
/**
 * Template parameter documentation:
 *
 * - $keys: A two-dimensional array with the keys.
 *   Each array element has the following structure:
 *   [
 *       'colour' => The colour for the key.
 *       'text' => The description for the key.
 *   ]
 *
 */
?>

<? if ($keys): ?>
    <ul class="default map-key-list">
        <? foreach ($keys as $key): ?>
            <li class="map-key">
                <span style="background-color:<?= $key['colour'] ?>">
                    &nbsp;
                </span>
                <?= htmlReady($key['text']) ?>
            </li>
        <? endforeach ?>
    </ul>
<? endif ?>
