<div class="file_uploader">
    <div class="file_upload_window">
        <div class="errorbox" style="display: none;">
            <?= MessageBox::error('<span class="errormessage"></span>')?>
        </div>
        <ul class="filenames clean"></ul>
        <div class="uploadbar">
            <?= Icon::create('ufo', Icon::ROLE_INFO_ALT)->asImg(30, ['class' => 'ufo']) ?>
            <?= Icon::create('upload', Icon::ROLE_INFO_ALT)->asImg(30) ?>
        </div>
    </div>
</div>
