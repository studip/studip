<html lang="de" xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
    <head>
       <meta charset="UTF-8">
        <title><?= htmlReady($stgversion->getDisplayName()) ?></title>

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
            body {
                font-size: 10pt;
            }
            body, h1, h2, h3, h4, h5 {
                font-family: Arial, Sans Serif;
            }
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
                width: 100%;
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
        <?= Assets::stylesheet('print') ?>
    </head>
    <body>
        <div>
        <?
            echo $this->render_partial('shared/version/_version', ['version' => $stgversion]);
            echo $this->render_partial('shared/version/_versionmodule', ['version' => $stgversion]);
        ?>
        </div>
    </body>
</html>