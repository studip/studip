<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>
<? if (isset($flash['error'])): ?>
    <?= MessageBox::error($flash['error'], $flash['error_detail']) ?>
<? elseif (isset($flash['message'])): ?>
    <?= MessageBox::info($flash['message']) ?>
<? elseif (isset($flash['success'])): ?>
    <?= MessageBox::success($flash['success'], $flash['success_detail']) ?>
<? elseif (isset($flash['delete'])): ?>
    <?= createQuestion(_('Wollen Sie die Zuordnung der Veranstaltung zum automatischen Eintragen wirklich löschen?'),  ['delete' => 1], ['back' => 1], $controller->url_for('admin/autoinsert/delete') .'/'. $flash['delete']) ?>
<? endif; ?>

<h2>
    <?= _('Automatisches Eintragen von Erstnutzern in Veranstaltungen') ?>
</h2>
<form class="default" action="<?= $controller->url_for('admin/autoinsert') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <?= $this->render_partial("admin/autoinsert/_search.php", ['semester_data' => $semester_data]) ?>
</form>

<? if (is_array($seminar_search) && count($seminar_search) > 0): ?>

<form class="default" action="<?= $controller->url_for('admin/autoinsert/new') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
          <?= _('Suchergebnisse') ?>
        </legend>

        <label>
            <?= _('Veranstaltung') ?>
            <select name="sem_id" id="sem_id">
                <? foreach ($seminar_search as $seminar): ?>
                    <option value="<?= $seminar[0] ?>">
                        <?= htmlReady($seminar[1]) ?>
                    </option>
                <? endforeach; ?>
            </select>
        </label>

        <h2>
            <?= _('Automatisches Eintragen mit Nutzerstatus:') ?>
        </h2>

        <?php foreach($userdomains as $domain):?>
            <h3>
                <?= htmlReady($domain['name']) ?>
            </h3>
            <section class="hgroup">
                <label>
                      <input type="checkbox" name="rechte[<?= $domain['id']?>][]" value="dozent">
                      <?= _('Dozent') ?>
                </label>
                <label>
                    <input type="checkbox" name="rechte[<?= $domain['id']?>][]" value="tutor">
                    <?= _('Tutor') ?>
                </label>
                <label>
                    <input type="checkbox" name="rechte[<?= $domain['id']?>][]" value="autor">
                    <?= _('Autor') ?>
                </label>
            </section>
        <?php endforeach;?>
    </fieldset>
    <footer>
        <?= Button::create(_('Anlegen'),'anlegen')?>
    </footer>
</form>
<? endif;?>

<h3><?= _('Vorhandene Zuordnungen') ?></h3>
<table width="100%" class="default">
    <thead>
        <tr>
            <th><?= _('Veranstaltungen') ?></th>
            <th style="text-align: center;"><?= _('Dozent') ?></th>
            <th style="text-align: center;"><?= _('Tutor') ?></th>
            <th style="text-align: center;"><?= _('Autor') ?></th>
            <th style="text-align: right;"><?= _('Aktionen') ?></th>
        </tr>
    </thead>
    <tbody>
    <? foreach ($auto_sems as $auto_sem): ?>
        <tr class="<?= TextHelper::cycle('hover_odd', 'hover_even') ?>">
            <td>
                <a href="<?= URLHelper::getLink('seminar_main.php?auswahl='.$auto_sem['seminar_id']) ?>">
                    <?= htmlReady($auto_sem['Name'])?>
                </a>
            </td>

            <?= $this->render_partial("admin/autoinsert/_status.php", ['status' => 'dozent', 'auto_sem' => $auto_sem,'domains'=>$userdomains]) ?>
            <?= $this->render_partial("admin/autoinsert/_status.php", ['status' => 'tutor', 'auto_sem' => $auto_sem,'domains'=>$userdomains]) ?>
            <?= $this->render_partial("admin/autoinsert/_status.php", ['status' => 'autor', 'auto_sem' => $auto_sem,'domains'=>$userdomains]) ?>
            <td align="right">
                <a href="<?=$controller->url_for('admin/autoinsert/delete')?>/<?= $auto_sem['seminar_id'] ?>">
                    <?= Icon::create('trash', 'clickable', ['title' => _('Veranstaltung entfernen'), 'class' => 'text-top'])->asImg() ?>
                </a>
            </td>
        </tr>
        <? $i ++?>
    <? endforeach; ?>
    </tbody>
</table>

<?
$sidebar = Sidebar::Get();
$sidebar->setTitle('Automatisiertes Eintragen');
$links = new ActionsWidget();
$links->addLink(_('Benutzergruppen manuell eintragen'), $controller->url_for('admin/autoinsert/manual'), Icon::create('visibility-visible', 'clickable'));
$sidebar->addWidget($links);
