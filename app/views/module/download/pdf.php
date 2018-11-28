<style>
    table.mvv-modul-details {
        border: 1px solid black;
        border-collapse: collapse;
        hyphens: auto;
        font: 8pt normal;
        width: 100%;
    }
    table.mvv-modul-details td, table.mvv-modul-details th {
        border: 1px solid black;
        hyphens: auto;
    }
    table.mvv-modul-details th {
        text-align: left;
        vertical-align: top;
        hyphens: auto;
    }
</style>
<?= $this->render_partial('shared/modul/_modul') ?>
<br><br>
<? if ($type === 1) : ?>
<?= $this->render_partial('shared/modul/_modullvs') ?>
<br><br>
<?= $this->render_partial('shared/modul/_pruefungen') ?>
<br><br>
<?= $this->render_partial('shared/modul/_regularien') ?>
<? endif;?>
<? if ($type === 2): ?>
<?= $this->render_partial('shared/modul/_modullv') ?>
<? endif; ?>
<? if ($type === 3) : ?>
<?= $this->render_partial('shared/modul/_modul_ohne_lv') ?>
<? endif; ?>
