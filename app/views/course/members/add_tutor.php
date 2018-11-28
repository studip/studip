<? use Studip\Button, Studip\LinkButton; ?>

<h1><?=sprintf(_('%s hinzufügen'), htmlReady($decoratedStatusGroups['tutor']))?></h1>

<form action="<?=$controller->url_for('course/members/set')?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studipticket" value="<?=$studipticket?>">
    <table class="default">
        <thead>
            <tr>
                <th colspan="2"><?=sprintf(_('%s suchen'), htmlReady($decoratedStatusGroups['tutor']))?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <?= QuickSearch::get('new_tutor', $search)
                        ->withButton(array('reset_button_name' => 'reset_tutor', 
                            'search_button_name' => 'search_tutor'))
                        ->render(); ?>  
                    <input type="hidden" name="seminar_id" value="<?= $course_id ?>">
                </td>
                <td>
                <?= Button::createAccept(_('Eintragen'), 'add_tutor', 
                        array('title' => sprintf(_('als %s eintragen'), 
                                htmlReady($decoratedStatusGroups['tutor'])))) ?>
                <?= LinkButton::createCancel(_('Abbrechen'), 
                        $controller->url_for('course/members/index')) ?>
                </td>
            </tr>
        </tbody>
    </table>
</form>
