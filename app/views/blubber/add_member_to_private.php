<form action="<?= $controller->add_member_to_private($thread) ?>" method="post" class="default" data-dialog>
    <label>
        <?= _('Person suchen') ?>
        <?= QuickSearch::get('user_id', new StandardSearch('user_id'))->render() ?>
    </label>

    <div data-dialog-button>
        <?= Studip\Button::create(_('Hinzufügen')) ?>
    </div>
</form>
