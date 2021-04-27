<? if ($enabled) : ?>
    <div id="cache-admin-container">
        <cache-administration :cache-types='<?= htmlReady(json_encode($types)) ?>' current-cache="<?= htmlReady($cache) ?>"
                     :current-config='<?= htmlReady(json_encode($config)) ?>'></cache-administration>
    </div>
<? endif;
