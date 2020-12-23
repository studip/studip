<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title><?= _('Terminliste') ?></title>
    <style>
    body {
        padding: 0;
        font-family: Arial, Helvetica, sans-serif;
    }
    body:not(.extended) .reason {
        display: none;
    }
    h1 {
        font-weight: bold;
        font-size: 20pt;
        padding-bottom: 3pt;
        border-bottom: 1pt solid #000000;
        margin-bottom: 1em;
    }
    h1::after {
        clear: right;
    }
    a {
        text-decoration: none;
        color: #000000;
    }
    table {
        border: 1px solid #000000;
        border-collapse: collapse;
        border-spacing: 0;
        width: 100%;
    }
    caption {
        text-align: left;
    }
    td, th {
        border: 1px solid #000000;
        padding: 0.5em 1em;
    }
    th, td:nth-child(1), td:nth-child(2) {
        text-align: center;
        white-space: nowrap;
    }
    ul {
        margin: 0;
        padding: 0;
    }
    ul li:only-child {
        list-style: none;
    }
    #toggle {
        float: right;
    }
    </style>
    <style media="print">
    #toggle {
        display: none;
    }
    </style>
</head>
<body id="body">
    <h1>
        <button id="toggle">
            <?= _('Grund anzeigen / verbergen') ?>
        </button>

        <?= sprintf(
            _('Terminliste von %s'),
            htmlReady($current_user->getFullName())
        ) ?>
    </h1>
<? foreach ($blocks as $block): ?>
    <table>
        <caption>
            <h2>
                <?= _('Termin') ?>:
                <?= strftime('%A, %x', $block->start) ?>
                (<?= htmlReady($block->room) ?>)
            </h2>
        </caption>
        <colgroup>
            <col width="20%">
            <col>
            <col width="50%" class="reason">
        </colgroup>
        <thead>
            <tr>
                <th><?= _('Zeit') ?></th>
                <th>
                <? if ($block->size > 1): ?>
                    <?= _('Person(en)') ?>
                <? else: ?>
                    <?= _('Person') ?>
                <? endif; ?>
                </th>
                <th class="reason"><?= _('Grund') ?></th>
            </tr>
        </thead>
        <tbody>
        <? foreach ($block->slots as $slot): ?>
            <tr>
                <td>
                    <?= implode(' - ', [
                        date('H:i', $slot->start_time),
                        date('H:i', $slot->end_time)
                    ]) ?>
                </td>
                <td>
                <? if (count($slot->bookings) > 0): ?>
                    <ul>
                    <? foreach ($slot->bookings as $booking): ?>
                        <li><?= htmlReady($booking->user->getFullName()) ?></li>
                    <? endforeach; ?>
                    </ul>
                <? else: ?>
                    &ndash;
                <? endif; ?>
                </td>
                <td class="reason">
                <? if (count($slot->bookings) > 0): ?>
                    <ul>
                    <? foreach ($slot->bookings as $booking): ?>
                        <li><?= htmlReady($booking->reason) ?></li>
                    <? endforeach; ?>
                    </ul>
                <? else: ?>
                    &ndash;
                <? endif; ?>
                </td>
            </tr>
        <? endforeach; ?>
        </tbody>
    </table>
<? endforeach; ?>

    <script>
    document.getElementById('toggle').addEventListener('click', function (event) {
        document.getElementById('body').classList.toggle('extended');
        event.preventDefault();
    }, false);
    </script>
</body>
</html>
