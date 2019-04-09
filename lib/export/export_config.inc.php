<?php
// <<-- EXPORT-SETTINGS
global $export_o_modes,
       $export_ex_types,
       $export_ex_types,
       $skip_page_3,
       $xml_filename,
       $xslt_filename_default,
       $output_formats,
       $export_icon;
// Export modes
$export_o_modes = ["start","file","choose", "direct","processor","passthrough"];
// Exportable output-types
$export_ex_types = ["veranstaltung", "person", "forschung"];

$skip_page_3 = true;
// name of generated XML-file
$xml_filename = "data.xml";
// name of generated output-file
$xslt_filename_default = "studip";

// existing output formats
$output_formats = [
    "html"      =>      "Hypertext Markup Language (HTML)",
    "rtf"       =>      "Rich Text Format (RTF)",
    "txt"       =>      "Text (TXT)",
    "csv"       =>      "Comma Separated Values (Excel)",
    "fo"        =>      "Adobe Postscript (PDF)",
    "xml"       =>      "Extensible Markup Language (XML)"
];

// Icons für die Ausgabeformate
$export_icon["xml"]  = "file-generic";
$export_icon["xslt"] = "file-office";
$export_icon["xsl"]  = "file-office";
$export_icon["rtf"]  = "file-text";
$export_icon["fo"]   = "file-pdf";
$export_icon["pdf"]  = "file-pdf";
$export_icon["html"] = "file-text";
$export_icon["htm"]  = "file-text";
$export_icon["txt"]  = "file-text";
$export_icon["csv"]  = "file-office";
