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
list($type, $id) = explode('/', $uri, 2);

// Setup response
$response = new Trails_Response();

// Create response
if (!$type || !$id) {
    // Invalid call
    $response->set_status(400);
} elseif (!in_array($type, ['css', 'js'])) {
    // Invalid type
    $response->set_status(501);
} elseif (!PluginAsset::exists($id)) {
    // Asset does not exist
    $response->set_status(404);
} else {
    // Load asset
    $model = PluginAsset::find($id);
    if ($_SERVER['HTTP_IF_MODIFIED_SINCE'] && $model->chdate <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
        // Cached and still valid
        $response->set_status(304);
    } else {
        // Output asset
        $asset = new Assets\PluginAsset($model);
        try {
            $response->set_body($asset->getContent());

            // Set appropriate header
            $response->add_header('Content-Type', $type === 'css' ? 'text/css' : 'application/javascript');
            $response->add_header('Content-Length', $model->size);
            $response->add_header('Content-Disposition', 'inline; ' . encode_header_parameter('filename', $model->filename));

            // Store cache information
            if (Studip\ENV !== 'development') {
                $response->add_header('Last-Modified', gmdate('D, d M Y H:i:s', $model->chdate) . ' GMT');
                $response->add_header('Expires', gmdate('D, d M Y H:i:s', $model->chdate + PluginAsset::CACHE_DURATION) . ' GMT');
            }
        } catch (Exception $e) {
            $asset->delete();
            $response->set_status(500);
        }
    }
}
$response->output();
