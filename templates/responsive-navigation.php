<?php
list($navigation, $activated) = ResponsiveHelper::getNavigationArray();

// TODO: PHP7
// [$navigation, $activated] = ResponsiveHelper::getNavigationArray();
$hash = md5(json_encode($navigation));

$response = compact('activated');
if (!isset($_COOKIE['responsive-navigation-hash']) || $_COOKIE['responsive-navigation-hash'] !== $hash) {
    $response = array_merge($response, compact('navigation', 'hash'));
}
?>
<script>
STUDIP.Navigation = <?= json_encode($response, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
</script>
