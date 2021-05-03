<? if ($file['is_folder']) : ?>
    <? if ($name !== "__MACOSX") : ?>
        <li class="folder">
            <?= Icon::create("folder-full", Icon::ROLE_INFO)->asImg("20px", ['class' => "text-bottom"]) ?>
            <?= htmlReady($name) ?>
            <ol>
                <? foreach ($file['structure'] as $name => $subfile) : ?>
                    <?= $this->render_partial("oer/market/_details_file.php", ['name' => $name, 'file' => $subfile]) ?>
                <? endforeach ?>
            </ol>
        </li>
    <? endif ?>
<? else : ?>
    <li>
        <div class="size" style="float: right">
            <?= htmlReady(number_format($file['size'] / 1024, 2)) ?> KB
        </div>
        <?= FileManager::getFileIcon($name, "info")->asImg("20px", ['class' => "text-bottom"]) ?>
        <?= htmlReady($name) ?>
    </li>
<? endif ?>
