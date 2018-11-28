<div id="afeed">
<script>
    jQuery(document).ready(function() {
        STUDIP.ActivityFeed.user_id = '<?= $user_id ?>';
        STUDIP.ActivityFeed.scrolledfrom = '<?= $scrolledfrom ?>';
        STUDIP.ActivityFeed.filter = '<?= json_encode($config) ?>';
        STUDIP.ActivityFeed.init();
    });
</script>
<div id="stream-container">
    <div class="loading-indicator">
        <span class="load-1"></span>
        <span class="load-2"></span>
        <span class="load-3"></span>
    </div>
</div>

<?= $this->render_partial('_jstemplates'); ?>
</div>
