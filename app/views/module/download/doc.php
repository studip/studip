<html lang="de" xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
    <head>
       <meta charset="UTF-8">
        <title><?= htmlReady($modul->getDisplayName()) ?></title>

        <!--[if gte mso 9]>
            <xml>
                <w:WordDocument>
                <w:View>Print</w:View>
                <w:Zoom>90</w:Zoom>
                <w:DoNotOptimizeForBrowser/>
                </w:WordDocument>
            </xml>
        <![endif]-->
        
        <style>
            
            div.Section1 {
                page: Section1;
                font: italic 10pt arial, sans-serif;
            }
            table.mvv-modul-details {
                border: 1px solid black;
                border-collapse: collapse;
                font: 8pt arial, sans-serif;
                -webkit-hyphens: auto;
                hyphens: auto;
                margin-bottom: 10px;
            }
            table.mvv-modul-details td, table.mvv-modul-details th {
                border: 1px solid black;
                -webkit-hyphens: auto;
                hyphens: auto;
            }
            table.mvv-modul-details th {
                text-align: left;
                vertical-align: top;
                -webkit-hyphens: auto;
                hyphens: auto;
            }
            
        </style>
    </head>
    <body>
        <div class="Section1" style="font: 10pt arial, sans-serif;">
    <?= $this->render_partial('shared/modul/_modul') ?>
    <? if ($type === 1) : ?>
    <?= $this->render_partial('shared/modul/_modullvs') ?>
    <?= $this->render_partial('shared/modul/_pruefungen') ?>
    <?= $this->render_partial('shared/modul/_regularien') ?>
    <? endif;?>
    <? if ($type === 2): ?>
    <?= $this->render_partial('shared/modul/_modullv') ?>
    <? endif; ?>
    <? if ($type === 3) : ?>
    <?= $this->render_partial('shared/modul/_modul_ohne_lv') ?>
    <? endif; ?>
        </div>
    </body>
</html>