<?php
# Lifter002: TEST
# Lifter007: TEST
# Lifter003: TEST
# Lifter010: TODO
/**
* sendfile.php
*
* Send files to the browser an does permchecks
*
* @author       Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>,
*               André Noack <andre.noack@gmx.net>
* @access       public
* @package      studip_core
* @modulegroup  library
* @module       sendfile.php
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// sendfile.php - Datei an Browser senden
// Copyright (C) 2000 - 2002 Cornelis Kater <ckater@gwdg.de>
// Ralf Stockmann <rstockm@gwdg.de>, André Noack André Noack <andre.noack@gmx.net>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

ob_start();
require '../lib/bootstrap.php';

page_open(["sess" => "Seminar_Session",
                "auth" => "Seminar_Default_Auth",
                "perm" => "Seminar_Perm",
                "user" => "Seminar_User"]);

include 'lib/seminar_open.php';

//basename() needs setlocale()
init_i18n($_SESSION['_language']);

// Set Base URL, otherwise links will fail on SENDFILE_LINK_MODE = rewrite
URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);

$file_id = escapeshellcmd(basename(Request::get('file_id')));
$type = Request::int('type');
if($type < 0 || $type > 7) $type = 0;

$no_access = true;

//download from course or institute or document is a message attachement
if (in_array($type, [0, 6, 7])) {
    if ($file_ref = FileRef::find($file_id)) {
        $folder = $file_ref->folder->getTypedFolder();
        $no_access = !$folder->isFileDownloadable($file_ref, $GLOBALS['user']->id);
    }
}
//download from archive, allowed if former participant
if ($type == 1) {
    $query = "SELECT seminar_id FROM archiv WHERE archiv_file_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$file_id]);
    $archiv_seminar_id = $statement->fetchColumn();
    if ($archiv_seminar_id) {
        $no_access = !archiv_check_perm($archiv_seminar_id);
    } else {
        $query = "SELECT seminar_id FROM archiv WHERE archiv_protected_file_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$file_id]);
        $archiv_seminar_id = $statement->fetchColumn();
        if ($archiv_seminar_id) {
            $no_access = !in_array(archiv_check_perm($archiv_seminar_id), words('tutor dozent admin'));
        }
    }
}

//download ad hoc created files, always allowed
if ($type == 4) {
    $no_access = !Token::isValid(Request::option('token'), $GLOBALS['user']->id);
}

//if download not allowed throw exception to terminate script
if ($no_access) {
    // redirect to login page if user is not logged in
    $auth->login_if($auth->auth['uid'] == 'nobody');
    throw new AccessDeniedException(_("Sie haben keine Zugriffsberechtigung für diesen Download!"));
}

//replace bad charakters to avoid problems when saving the file
$file_name = FileManager::cleanFileName(basename(Request::get('file_name')));

switch ($type) {
    //We want to download from the archive (this mode performs perm checks)
    case 1:
        $path_file = $ARCHIV_PATH . "/" . $file_id;
        $content_type = get_mime_type($file_name);
    break;
    case 4:
        $path_file = $TMP_PATH . "/". $file_id;
        $content_type = get_mime_type($file_name);
    break;
    default:
        $path_file = $file_ref->file->storage == 'disk' ? $file_ref->file->path : $file_ref->file->url;
        $content_type = $file_ref->mime_type ?: get_mime_type($file_name);
    break;
}


// check if linked file is obtainable
if (isset($file_ref) && $file_ref->file->url_access_type == 'proxy') {
    $link_data = FileManager::fetchURLMetadata($file_ref->file->url);
    if ($link_data['response_code'] != 200) {
        throw new Exception(_("Diese Datei wird von einem externen Server geladen und ist dort momentan nicht erreichbar!"));
    }
    $content_type = $link_data['Content-Type'] ? strstr($link_data['Content-Type'], ';', true) : get_mime_type($file_name);

    $filesize = $link_data['Content-Length'];
    if (!$filesize) $filesize = false;
}
if (isset($file_ref) && $file_ref->file->storage == 'disk') {
    $filesize = @filesize($path_file);
    if ($filesize === false) {
        throw new Exception(_('Fehler beim Laden der Inhalte der Datei'));
    }
}
// close session, download will mostly be a parallel action
page_close();

// output_buffering may be explicitly or implicitly enabled
while (ob_get_level()) {
    ob_end_clean();
}

if (isset($file_ref) && $file_ref->file->url_access_type == 'redirect') {
    header('Location: ' . $file_ref->file->url);
    die();
}

$content_blacklisted = function ($mime) {
    foreach (['html', 'javascript', 'svg', 'xml'] as $check) {
        if (stripos($mime, $check) !== false) {
            return true;
        }
    }
    return false;
};

if ($content_blacklisted($content_type)) {
    $content_type = 'application/octet-stream';
}
if (Request::int('force_download') || $content_type == "application/octet-stream") {
    $content_disposition = "attachment";
} else {
    $content_disposition = "inline";
}

$start = $end = null;
if ($filesize && $file_ref->file->storage == 'disk') {
    header("Accept-Ranges: bytes");
    $start = 0;
    $end = $filesize - 1;
    $length = $filesize;
    if (isset($_SERVER['HTTP_RANGE'])) {
        $c_start = $start;
        $c_end   = $end;
        list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
        if (mb_strpos($range, ',') !== false) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header("Content-Range: bytes $start-$end/$filesize");
            exit;
        }
        if ($range[0] == '-') {
            $c_start = $filesize - mb_substr($range, 1);
        } else {
            $range  = explode('-', $range);
            $c_start = $range[0];
            $c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $filesize;
        }
        $c_end = ($c_end > $end) ? $end : $c_end;
        if ($c_start > $c_end || $c_start > $filesize - 1 || $c_end >= $filesize) {
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header("Content-Range: bytes $start-$end/$filesize");
            exit;
        }
        $start  = $c_start;
        $end    = $c_end;
        $length = $end - $start + 1;
        header('HTTP/1.1 206 Partial Content');
    }
    header("Content-Range: bytes $start-$end/$filesize");
    header("Content-Length: $length");
} elseif ($filesize) {
    header("Content-Length: $filesize");
}

header("Expires: Mon, 12 Dec 2001 08:00:00 GMT");
header("Last-Modified: " . gmdate ("D, d M Y H:i:s") . " GMT");
if ($_SERVER['HTTPS'] == "on"){
    header("Pragma: public");
    header("Cache-Control: private");
} else {
    header("Pragma: no-cache");
    header("Cache-Control: no-store, no-cache, must-revalidate");   // HTTP/1.1
}
header("Cache-Control: post-check=0, pre-check=0", false);
header("Content-Type: $content_type");
header("Content-Disposition: $content_disposition; " . encode_header_parameter('filename', $file_name));


Metrics::increment('core.file_download');

readfile_chunked($path_file, $start, $end);
if (isset($file_ref) && !$start) {
    $file_ref->incrementDownloadCounter();
}

//remove temporary file
if ($type == 4) {
    @unlink($path_file);
}
