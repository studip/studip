<div class="calhead" style="white-space: nowrap; position: relative;">
    <label>
        <?= $calLabel ?>
        <?= Icon::create('arr_1down', 'clickable') ?>

        <input type="text"
               id="date-chooser"
               value="<?= strftime('%F', $atime) ?>"
               data-url="<?= $controller->url_for('calendar/single/' . $calType, ['atime' => '%ATIME%']) ?>"
               style="width: 0.1px; height: 0.1px; opacity: 0; overflow: hidden; position: absolute; left: 50%; z-index: -1;">
    </label>

    <script>
     jQuery('#date-chooser').datepicker({ dateFormat: 'yy-mm-dd', onSelect: function () { window.location = $(this).data('url').replace(encodeURI("%ATIME%"), Math.floor($(this).datepicker('getDate').valueOf() / 1000)) } })
    </script>
</div>
