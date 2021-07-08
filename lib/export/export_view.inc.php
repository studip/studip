<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* Visual presentation of the Export-module.
*
* This file outputs the forms, messages and errors to the screen.
* The HTML-Design and table-Structure for export-pages are part of this file.
* It is used for any part of the export-module. If $o_mode is "direct" or "passthrough"
* it writes only the xml-stream or the output-file to the screen.
*
* @author       Arne Schroeder <schroeder@data.quest.de>
* @access       public
* @modulegroup      export_modules
* @module       export_view
* @package      Export
*/
if (($o_mode != "direct") AND ($o_mode != "passthrough"))
{
// Start of Output
    PageLayout::setTitle($export_pagename);
    Navigation::activateItem('/browse/export');
 ?>
<?
    if (isset($export_error)) {
        PageLayout::postError($export_error);
    }
    if (isset($export_msg)) {
        PageLayout::postSuccess($export_msg);
    }
    if (isset($export_info)) {
        PageLayout::postInfo($export_info);
    }
?>
            <?
            echo $export_pagecontent;

            if (isset($xml_printlink))
            {
            ?>
            <table cellspacing="0" cellpadding="0" border="0" width="100%">
                <tr>
                    <?
                    printhead ("99%", FALSE, "", "open", true, ' ' . $xml_printimage, $xml_printlink, $xml_printdesc);
                    ?>
                </tr>
            </table>
            <table cellspacing="0" cellpadding="0" border="0" width="100%">
                <tr>
                    <?
                    printcontent("99%", FALSE, $xml_printcontent, "");
                    ?>
                </tr>
            </table>
            <?
            }
            if (isset($xslt_printlink))
            {
            ?>
            <table cellspacing="0" cellpadding="0" border="0" width="100%">
                <tr>
                    <?
                    printhead ("99%", FALSE, "", "open", true, ' ' . $xslt_printimage, $xslt_printlink, $xslt_printdesc);
                    ?>
                </tr>
            </table>
            <table cellspacing="0" cellpadding="0" border="0" width="100%">
                <tr>
                    <?
                    printcontent("99%", FALSE, $xslt_printcontent, "");
                    ?>
                </tr>
            </table>
            <?
            }
            if (isset($result_printlink))
            {
            ?>
            <table cellspacing="0" cellpadding="0" border="0" width="100%">
                <tr>
                    <?
                    printhead ("99%", FALSE, "", "open", true, ' ' . $result_printimage, $result_printlink, $result_printdesc);
                    ?>
                </tr>
            </table>
            <table cellspacing="0" cellpadding="0" border="0" width="100%">
                <tr>
                    <?
                    printcontent("99%", FALSE, $result_printcontent, "");
                    ?>
                </tr>
            </table>
            <br>
            <?
            }
            if (isset($export_weiter_button))
            {
                echo $export_weiter_button;
            }
            ?>

<?php
}
elseif ($export_error_num > 0)
{
    echo $export_error;
}
?>
