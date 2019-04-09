<form class="default" action="<?= $controller->url_for('admission/ruleadministration/save_compat') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default">
        <caption>
            <?= _('Welche Anmelderegeln sind wie miteinander kombinierbar?') ?>
        </caption>
        <colgroup>
            <col width="15%">
            <?php foreach ($ruletypes as $data) : ?>
                <col width="<?= floor(85 / count($ruletypes)) ?>%">
            <?php endforeach ?>
        </colgroup>
        <thead>
            <tr>
                <th>&nbsp;</th>
                <th colspan="<?= count($ruletypes) + 1 ?>">
                    <?= _('ist kompatibel mit') ?>
                </th>
            </tr>
            <tr>
                <th><?= _('Regeltyp') ?></th>
                <?php foreach ($ruletypes as $class => $type) : ?>
                    <th>
                        <?= htmlReady($type['name']) ?>
                    </th>
                <?php endforeach ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ruletypes as $class => $type) : ?>
                <tr>
                    <td>
                        <?= htmlReady($type['name']) ?>
                    </td>
                    <?php foreach ($ruletypes as $compat_class => $compat_type) : ?>
                        <td>
                            <input type="checkbox" name="compat[<?= $class ?>][]"
                                   value="<?= $compat_class ?>"<?=
                                    in_array($compat_class, $matrix[$class] ?: []) ? ' checked' : ''?>>
                        </td>
                    <?php endforeach ?>
                </tr>
            <?php endforeach ?>
        </tbody>

        <tfoot>
            <tr>
                <td colspan="<?= count($ruletypes) + 2 ?>">
                    <?= Studip\Button::createAccept(_('Speichern'), 'save') ?>
                </td>
            </tr>
        </tfoot>
    </table>
  
</form>
