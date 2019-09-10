<article class="studip">
    <header>
        <h1>
            <?= _('Freigegebene Dateien') ?>
        </h1>
    </header>

    <section>
        <? $folder = current($public_folders); ?>
        <form method="post" action="<?= $controller->link_for('file/bulk/' . $folder->id) ?>">
            <?= CSRFProtection::tokenTag() ?>
            <table class="default sortable-table documents" data-sortlist="[[2, 0]]">
                <?= $this->render_partial('files/_files_thead') ?>
                <? foreach ($public_files as $file_ref): ?>
                    <?= $this->render_partial('files/_fileref_tr',
                        [
                            'file_ref' => $file_ref,
                            'current_folder' => $public_folders[$file_ref->folder_id],
                            'last_visitdate' => time()
                        ]) ?>
                <? endforeach ?>
                <tfoot>
                    <tr>
                        <td colspan="7">
                            <span class="multibuttons">
                                <?= Studip\Button::create(_('Herunterladen'), 'download', [
                                    'data-activates-condition' => 'table.documents tr[data-permissions*=d] :checkbox:checked'
                                ]) ?>
                                <?= Studip\Button::create(_('Kopieren'), 'copy', ['data-dialog' => '']) ?>
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </form>
    </section>
</article>
