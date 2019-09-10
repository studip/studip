<article class="studip">
    <header>
        <h1>
            <?= _('Freigegebene Dateien') ?>
        </h1>
    </header>

    <section>
        <table class="default sortable-table documents" data-sortlist="[[2, 0]]">
            <?= $this->render_partial('files/_files_thead') ?>
            <? foreach($public_files as $file_ref): ?>
                <?= $this->render_partial('files/_fileref_tr',
                    [
                        'file_ref' => $file_ref,
                        'current_folder' => $public_folders[$file_ref->folder_id],
                        'last_visitdate' => time()
                    ]) ?>
            <? endforeach ?>
        </table>
    </section>
</article>
