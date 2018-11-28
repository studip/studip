<?php
/**
 * Output plugin assets
 *
 * This will load and output plugin assets. For now, this will be the
 * compiled LESS files of plugins.
 * All served assets will set the appropriate headers so that the browser
 * will cache the assets for a certain amount of time.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 3.4
 */

require_once '../lib/bootstrap.php';

// Obtain request information
$uri = ltrim($_SERVER['PATH_INFO'], '/');
list($type, $id) = explode('/', $uri);

// Setup response
$response = new RESTAPI\Response();

// Create response
if (!$type || !$id) {
    // Invalid call
    $response->status = 400;
} elseif ($type !== 'css') {
    // Invalid type
    $response->status = 501;
} elseif (!PluginAsset::exists($id)) {
    // Asset does not exist
    $response->status = 404;
} else {
    // Load asset
    $model = PluginAsset::find($id);
    if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] && $model->chdate <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
        // Cached and still valid
        $response->status = 304;
    } else {
        // Output asset
        $asset = new Assets\PluginAsset($model);
        try {
            $response->body = $asset->getContent();
        } catch (Exception $e) {
            $response->body = false;
        }
        if ($response->body === false) {
            // Could not obtain asset contents
            $asset->delete();
            $response->status = 500;
        } else {
            // Set appropriate header
            $response['Content-Type']        = 'text/css';
            $response['Content-Length']      = $model->size;
            $response['Content-Disposition'] = 'inline; ' . encode_header_parameter('filename', $model->filename);

            // Store cache information
            if (Studip\ENV !== 'development') {
                $response['Last-Modified'] = gmdate('D, d M Y H:i:s', $model->chdate) . ' GMT';
                $response['Expires']       = gmdate('D, d M Y H:i:s', $model->chdate + PluginAsset::CACHE_DURATION) . ' GMT';
            }
        }
    }
}
$response->output();

