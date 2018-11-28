<? if (count($domains) == 0) : ?>
    <?= MessageBox::info(_('Es sind keine Nutzerdomänen vorhanden.')) ?>
<? else : ?>
    <form method="post">
        <?= CSRFProtection::tokenTag() ?>
        <table class="default">
            <colgroup>
                <col style="width: 40%">
                <col style="width: 35%">
                <col style="width: 15%">
                <col style="width: 10%">
            </colgroup>
            <caption>
                <?= _('Liste der Nutzerdomänen') ?>
            </caption>
            <?= $this->render_partial('admin/domain/domains') ?>
        </table>
    </form>
<? endif ?>
