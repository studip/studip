<html lang="de" xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
    <head>
       <meta charset="UTF-8">
        <title><?= htmlReady($studiengang->getDisplayName()) ?></title>

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
            table.mvv-modul-details {
                border: 1px solid black;
                border-collapse: collapse;
                font: 8pt arial, sans-serif;
                -webkit-hyphens: auto;
                hyphens: auto;
            }
            table.mvv-modul-details td, table.mvv-modul-details th {
                border: 1px solid black;
                -webkit-hyphens: auto;
                hyphens: auto;
            }
            table.mvv-modul-details tbody th {
                text-align: left;
                -webkit-hyphens: auto;
                hyphens: auto;
            }
        </style>
    </head>
    <body>
        <div>
        <?= $this->render_partial('shared/studiengang/_studiengang', array('studiengang' => $studiengang)); ?>
        <? if (count($studiengang->studiengangteile)) : ?>
            <h1><?= _('Studiengangteile') ?></h1>
            <? if (count($studiengang->stgteil_bezeichnungen)) : ?>
                <? foreach ($studiengang->stgteil_bezeichnungen as $stgteilbez) : ?>
                    <h2><?= $stgteilbez->name; ?></h2>
                    <? $stg_stgteile = $studiengang->stgteil_assignments->findBy('stgteil_bez_id', $stgteilbez->id); ?>
                    <? foreach ($stg_stgteile as $stg_stgteil) : ?>
                        <?= $this->render_partial('shared/studiengang/_studiengangteil', ['stgteil' => $stg_stgteil->studiengangteil]); ?>
                    <? endforeach; ?>
                <? endforeach; ?>
            <? else : ?>
                <? foreach($studiengang->studiengangteile as $stgteil) :?>
                    <? echo $this->render_partial('shared/studiengang/_studiengangteil', ['stgteil' => $stgteil]); ?>
                <? endforeach; ?>
            <? endif; ?>
        <? endif; ?>
        </div>
    </body>
</html>
