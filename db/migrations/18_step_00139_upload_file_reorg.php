<?php
require_once 'lib/datei.inc.php';

class Step00139UploadFileReorg extends Migration
{
    function description ()
    {
        return 'reorganize uploaded files into sub-folders';
    }

    function up ()
    {
        global $UPLOAD_PATH;

        $db = DBManager::get();
        $result = $db->query('SELECT dokument_id FROM dokumente');

        foreach ($result as $row) {
            $document = $row['dokument_id'];
            $file_path = $UPLOAD_PATH.'/'.$document;

            if (is_file($file_path)) {
                rename($file_path, get_upload_file_path($document));
            }
        }
    }

    function down ()
    {
        global $UPLOAD_PATH;

        $db = DBManager::get();
        $result = $db->query('SELECT dokument_id FROM dokumente');

        foreach ($result as $row) {
            $document = $row['dokument_id'];
            $file_path = get_upload_file_path($document);

            if (is_file($file_path)) {
                rename($file_path, $UPLOAD_PATH.'/'.$document);
            }
        }

        for ($i = 0; $i <= 0xff; ++$i) {
            $directory = sprintf('%s/%02x', $UPLOAD_PATH, $i);

            if (is_dir($directory)) {
                rmdir($directory);
            }
        }
    }
}
?>
