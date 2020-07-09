<? if (FileManager::fileIsAudio($file)) : ?>
    <audio class="file_preview" src="<?= htmlReady($file->getDownloadURL()) ?>"
           type="<?= htmlReady($mime_type) ?>" controls></audio>
<? elseif (FileManager::fileIsImage($file)) : ?>
    <img class="file_preview" src="<?= htmlReady($file->getDownloadURL()) ?>">
<? elseif (FileManager::fileIsVideo($file)) : ?>
    <video class="file_preview" src="<?= htmlReady($file->getDownloadURL()) ?>"
           type="<?= htmlReady($mime_type) ?>" controls></video>
<? elseif ($mime_type == 'application/pdf') : ?>
    <iframe class="file_preview" src="<?= htmlReady($file->getDownloadURL()) ?>"></iframe>
<? elseif ($mime_type == 'text/plain') : ?>
    <iframe class="file_preview" src="<?= htmlReady($file->getDownloadURL()) ?>"></iframe>
<? endif ?>
