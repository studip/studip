<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* Config file for xslt-script inclusion
*
* This file contains several arrays, that define which xslt-scripts are available to the export-module.
* To add new designs and file-formats to the export-module, just add a new set of filetype, name,
* description and format-name to this file.
*
* @author       Arne Schroeder <schroeder@data.quest.de>
* @access       public
* @modulegroup      export_modules
* @module       export_xml_vars
* @package      Export
*/

$xslt_files["txt-standard"]["name"] = _("Standardmodul");
$xslt_files["txt-standard"]["desc"] = _("Standardmodul zur Ausgabe von Personen- oder Veranstaltungsdaten im Textformat. Die Daten werden nur mit Tabulatoren und Bindestrichen formatiert. Die Ausgabe-Datei kann in einer Textverarbeitung bearbeitet werden.");
$xslt_files["txt-standard"]["file"] = "txt-vp-1.xsl";
$xslt_files["txt-standard"]["txt"] = true;
$xslt_files["txt-standard"]["person"] = true;
$xslt_files["txt-standard"]["veranstaltung"] = true;

$xslt_files["txt-noformat"]["name"] = _("Unformatierte Ausgabe");
$xslt_files["txt-noformat"]["desc"] = _("Modul zur Ausgabe von Personen- oder Veranstaltungsdaten im Textformat. Die Daten werden nicht formatiert. Die Ausgabe-Datei kann in einer Textverarbeitung bearbeitet werden.");
$xslt_files["txt-noformat"]["file"] = "txt-vp-2.xsl";
$xslt_files["txt-noformat"]["txt"] = true;
$xslt_files["txt-noformat"]["person"] = true;
$xslt_files["txt-noformat"]["veranstaltung"] = true;

$xslt_files["txt-noformat-2"]["name"] = _("Unformatiert mit Veranstaltungsnummern");
$xslt_files["txt-noformat-2"]["desc"] = _("Modul zur Ausgabe von Personen- oder Veranstaltungsdaten im Textformat. Die Daten werden nicht formatiert. Es werden auch die Veranstaltungsnummern ausgegeben. Die Ausgabe-Datei kann in einer Textverarbeitung bearbeitet werden.");
$xslt_files["txt-noformat-2"]["file"] = "txt-v-3.xsl";
$xslt_files["txt-noformat-2"]["txt"] = true;
$xslt_files["txt-noformat-2"]["veranstaltung"] = true;



$xslt_files["html-standard"]["name"] = _("Standardmodul");
$xslt_files["html-standard"]["desc"] = _("Standardmodul zur Ausgabe von Personen- oder Veranstaltungsdaten als HTML-Seite. Personendaten werden als Tabelle angezeigt. Die Ausgabe-Datei kann in einem Web-Browser angezeigt werden.");
$xslt_files["html-standard"]["file"] = "html-vp-1.xsl";
$xslt_files["html-standard"]["htm"] = true;
$xslt_files["html-standard"]["html"] = true;
$xslt_files["html-standard"]["person"] = true;
$xslt_files["html-standard"]["veranstaltung"] = true;

$xslt_files["html-druck"]["name"] = _("Druckmodul");
$xslt_files["html-druck"]["desc"] = _("Modul zur Ausgabe von Personen- oder Veranstaltungsdaten als HTML-Seite. Es wird eine druckbare HTML-Seite ohne Farben erzeugt. Die Ausgabe-Datei kann in einem Web-Browser angezeigt und ausgedruckt werden.");
$xslt_files["html-druck"]["file"] = "html-vp-2.xsl";
$xslt_files["html-druck"]["htm"] = true;
$xslt_files["html-druck"]["html"] = true;
$xslt_files["html-druck"]["person"] = true;
$xslt_files["html-druck"]["veranstaltung"] = true;

$xslt_files["html-liste"]["name"] = _("Übersicht");
$xslt_files["html-liste"]["desc"] = _("Modul zur Ausgabe von Personen- oder Veranstaltungsdaten als HTML-Seite. Es werden nur die Veranstaltungs-Grunddaten bzw. Namen, Telefonnummern und E-Mail-Adressen in eine Tabelle geschrieben. Die Ausgabe-Datei kann in einem Web-Browser angezeigt werden.");
$xslt_files["html-liste"]["file"] = "html-vp-3.xsl";
$xslt_files["html-liste"]["htm"] = true;
$xslt_files["html-liste"]["html"] = true;
$xslt_files["html-liste"]["person"] = true;
$xslt_files["html-liste"]["veranstaltung"] = true;

$xslt_files["html-standard-2"]["name"] = _("Standardmodul mit Veranstaltungsnummern");
$xslt_files["html-standard-2"]["desc"] = _("Standardmodul zur Ausgabe von Veranstaltungsdaten als HTML-Seite. Es werden auch die Veranstaltungsnummern ausgegeben. Die Ausgabe-Datei kann in einem Web-Browser angezeigt werden.");
$xslt_files["html-standard-2"]["file"] = "html-v-4.xsl";
$xslt_files["html-standard-2"]["htm"] = true;
$xslt_files["html-standard-2"]["html"] = true;
$xslt_files["html-standard-2"]["veranstaltung"] = true;

$xslt_files["html-teiln"]["name"] = _("Teilnehmendenliste");
$xslt_files["html-teiln"]["desc"] = _("Modul zur Ausgabe von Personendaten als HTML-Seite. Es werden die Grunddaten der Teilnehmenden einer einzelnen Veranstaltung in eine Tabelle geschrieben. Die Ausgabe-Datei kann in einem Web-Browser angezeigt werden.");
$xslt_files["html-teiln"]["file"] = "html-t-1.xsl";
$xslt_files["html-teiln"]["htm"] = true;
$xslt_files["html-teiln"]["html"] = true;
//$xslt_files["html-teiln"]["person"] = true;



$xslt_files["rtf-standard"]["name"] = _("Standardmodul");
$xslt_files["rtf-standard"]["desc"] = _("Standardmodul zur Ausgabe von Personen- oder Veranstaltungsdaten als RTF-Datei. Die Ausgabe-Datei kann in einer Textverarbeitung bearbeitet werden.");
$xslt_files["rtf-standard"]["file"] = "rtf-vp-1.xsl";
$xslt_files["rtf-standard"]["rtf"] = true;
$xslt_files["rtf-standard"]["person"] = true;
$xslt_files["rtf-standard"]["veranstaltung"] = true;

$xslt_files["rtf-liste"]["name"] = _("Übersicht");
$xslt_files["rtf-liste"]["desc"] = _("Modul zur Ausgabe von Personen- oder Veranstaltungsdaten als RTF-Datei. Es werden nur die Grunddaten in eine Tabelle geschrieben (Lehrende, Titel, Status, Termin und Raum bzw.Name, Telefon, Sprechzeiten, Raum, E-Mail). Ein Deckblatt wird automatisch erzeugt. Die Ausgabe-Datei kann in einer Textverarbeitung bearbeitet werden.");
$xslt_files["rtf-liste"]["file"] = "rtf-vp-2.xsl";
$xslt_files["rtf-liste"]["rtf"] = true;
$xslt_files["rtf-liste"]["person"] = true;
$xslt_files["rtf-liste"]["veranstaltung"] = true;

$xslt_files["rtf-kommentar"]["name"] = _("Vorlesungskommentar");
$xslt_files["rtf-kommentar"]["desc"] = _("Modul zur Ausgabe von Veranstaltungsdaten als Vorlesungskommentar im Rich-Text-Format. Der Kommentar enthält die Veranstaltungsdetails. Es wird automatisch ein Deckblatt generiert. Die Ausgabe-Datei kann in einer Textverarbeitung bearbeitet werden.");
$xslt_files["rtf-kommentar"]["file"] = "rtf-vp-3.xsl";
$xslt_files["rtf-kommentar"]["rtf"] = true;
$xslt_files["rtf-kommentar"]["veranstaltung"] = true;

$xslt_files["rtf-kommentar-2"]["name"] = _("Vorlesungskommentar mit Veranstaltungsnummern");
$xslt_files["rtf-kommentar-2"]["desc"] = _("Modul zur Ausgabe von Veranstaltungsdaten als Vorlesungskommentar im Rich-Text-Format. Der Kommentar enthält die Veranstaltungs-Details. Es wird automatisch ein Deckblatt generiert. Die Ausgabe-Datei kann in einer Textverarbeitung bearbeitet werden.");
$xslt_files["rtf-kommentar-2"]["file"] = "rtf-v-4.xsl";
$xslt_files["rtf-kommentar-2"]["rtf"] = true;
$xslt_files["rtf-kommentar-2"]["veranstaltung"] = true;

$xslt_files["rtf-teiln"]["name"] = _("Teilnehmendenliste");
$xslt_files["rtf-teiln"]["desc"] = _("Modul zur Ausgabe von Personendaten als RTF-Datei. Personendaten inkl. Studiengänge und Kontingente werden in eine Tabelle geschrieben. Die Ausgabe-Datei kann in einer Textverarbeitung bearbeitet werden.");
$xslt_files["rtf-teiln"]["file"] = "rtf-t-1.xsl";
$xslt_files["rtf-teiln"]["rtf"] = true;

$xslt_files["rtf-gruppen"]["name"] = _("Liste der Guppen und Funktionen");
$xslt_files["rtf-gruppen"]["desc"] = _("Modul zur Ausgabe von Personendaten als RTF-Datei. Personendaten werden in eine Tabelle geschrieben. Die Ausgabe-Datei kann in einer Textverarbeitung bearbeitet werden.");
$xslt_files["rtf-gruppen"]["file"] = "rtf-t-2.xsl";
$xslt_files["rtf-gruppen"]["rtf"] = true;

$xslt_files["rtf-warteliste"]["name"] = _("Warteliste");
$xslt_files["rtf-warteliste"]["desc"] = _("Modul zur Ausgabe von Personendaten als RTF-Datei. Personen der Warteliste einer einzelnen Veranstaltung in eine Tabelle geschrieben. Es werden auch die Kontingente und Studiengänge ausgegeben. Die Ausgabe-Datei kann in einer Textverarbeitung bearbeitet werden.");
$xslt_files["rtf-warteliste"]["file"] = "rtf-t-3.xsl";
$xslt_files["rtf-warteliste"]["rtf"] = true;

$xslt_files["pdf-standard"]["name"] = _("Standardmodul");
$xslt_files["pdf-standard"]["desc"] = _("Standardmodul zur Ausgabe von Veranstaltungs- und Personendaten als Vorlesungskommentar bzw. MitarbeiterInnenlisten mit Seitenzahlen im Adobe PDF-Format. Die Datei kann mit dem Acrobat PDF-Reader gelesen werden.");
$xslt_files["pdf-standard"]["file"] = "pdf-vp-1.xsl";
$xslt_files["pdf-standard"]["fo"] = true;
$xslt_files["pdf-standard"]["person"] = true;
$xslt_files["pdf-standard"]["veranstaltung"] = true;

$xslt_files["pdf-kommentar"]["name"] = _("Vorlesungskommentar");
$xslt_files["pdf-kommentar"]["desc"] = _("Modul zur Ausgabe von Veranstaltungsdaten als Vorlesungskommentar im Adobe PDF-Format. Die Seiten enthalten eine Kopfzeile und eine Fußzeile mit der Seitenzahl. Deckblatt und Inhaltsverzeichnis werden automatisch generiert. Die Datei kann mit dem Acrobat PDF-Reader gelesen werden.");
$xslt_files["pdf-kommentar"]["file"] = "pdf-vp-2.xsl";
$xslt_files["pdf-kommentar"]["fo"] = true;
$xslt_files["pdf-kommentar"]["veranstaltung"] = true;

$xslt_files["pdf-kommentar-2"]["name"] = _("Vorlesungskommentar mit Veranstaltungsnummern");
$xslt_files["pdf-kommentar-2"]["desc"] = _("Modul zur Ausgabe von Veranstaltungsdaten als Vorlesungskommentar im Adobe PDF-Format. Die Seiten enthalten eine Kopfzeile und eine Fußzeile mit der Seitenzahl. Deckblatt und Inhaltsverzeichnis werden automatisch generiert. Es werden auch die Veranstaltungsnummern ausgegeben. Die Datei kann mit dem Acrobat PDF-Reader gelesen werden.");
$xslt_files["pdf-kommentar-2"]["file"] = "pdf-v-4.xsl";
$xslt_files["pdf-kommentar-2"]["fo"] = true;
$xslt_files["pdf-kommentar-2"]["veranstaltung"] = true;

$xslt_files["pdf-staff"]["name"] = _("MitarbeiterInnenlisten");
$xslt_files["pdf-staff"]["desc"] = _("Modul zur Ausgabe von Personendaten als MitarbeiterInnenlisten im Adobe PDF-Format. Die Grunddaten der Personen (Name, Telefon, Sprechzeiten, Raum, E-Mail-Adresse) werden in einer Tabelle angezeigt. Die Seiten enthalten eine Kopfzeile und eine Fußzeile mit der Seitenzahl. Es wird automatisch ein Deckblatt und ein Inhaltsverzeichnis generiert. Die Datei kann mit dem PDF-Acrobat Reader gelesen werden.");
$xslt_files["pdf-staff"]["file"] = "pdf-vp-2.xsl";
$xslt_files["pdf-staff"]["fo"] = true;
$xslt_files["pdf-staff"]["person"] = true;

$xslt_files["pdf-liste"]["name"] = _("Vorlesungsverzeichnis");
$xslt_files["pdf-liste"]["desc"] = _("Modul zur Ausgabe von Veranstaltungsdaten als Vorlesungsverzeichnis im Adobe PDF-Format. Die Grunddaten der Veranstaltungen (Lehrende, Titel, Status, erster Termin und Raum) werden in einer Tabelle angezeigt. Es wird automatisch ein Deckblatt und ein Inhaltsverzeichnis generiert. Die Datei kann mit dem Acrobat Reader gelesen werden.");
$xslt_files["pdf-liste"]["file"] = "pdf-v-3.xsl";
$xslt_files["pdf-liste"]["fo"] = true;

$xslt_files["csv-teiln"]["name"] = _("Teilnehmendenliste");
$xslt_files["csv-teiln"]["desc"] = _("Modul zur Ausgabe von Personendaten als CSV-Datei. Es werden die Grunddaten der Personen in eine Tabelle geschrieben. Es werden auch die Kontingente und Studiengänge ausgegeben. Die Ausgabe-Datei kann in Excel bearbeitet werden.");
$xslt_files["csv-teiln"]["file"] = "csv-t-1.xsl";
$xslt_files["csv-teiln"]["csv"] = true;

$xslt_files["csv-warteliste"]["name"] = _("Warteliste");
$xslt_files["csv-warteliste"]["desc"] = _("Modul zur Ausgabe von Personendaten als CSV-Datei. Es werden die Grunddaten der Personen auf der Warteliste einer einzelnen Veranstaltung in eine Tabelle geschrieben. Es werden auch die Kontingente und Studiengänge ausgegeben. Die Ausgabe-Datei kann in Excel bearbeitet werden.");
$xslt_files["csv-warteliste"]["file"] = "csv-t-2.xsl";
$xslt_files["csv-warteliste"]["csv"] = true;

$xslt_files["csv-gruppen"]["name"] = _("Liste der Teilnehmenden mit Gruppen");
$xslt_files["csv-gruppen"]["desc"] = _("Modul zur Ausgabe von Personendaten mit Gruppenzugehörigkeit als CSV-Datei. Es werden die Grunddaten der Teilnehmenden einer einzelnen Veranstaltung in eine Tabelle geschrieben. Es werden auch die Kontingente und Studiengänge ausgegeben. Die Ausgabe-Datei kann in Excel bearbeitet werden.");
$xslt_files["csv-gruppen"]["file"] = "csv-t-3.xsl";
$xslt_files["csv-gruppen"]["csv"] = true;

$xslt_files["csv-person"]["name"] = _("Standardmodul");
$xslt_files["csv-person"]["desc"] = _("Standardmodul zur Ausgabe von Personendaten als CSV-Datei. Die Ausgabe-Datei kann in Excel oder OpenOffice angezeigt werden.");
$xslt_files["csv-person"]["file"] = "csv-p-1.xsl";
$xslt_files["csv-person"]["csv"] = true;
$xslt_files["csv-person"]["person"] = true;

$xslt_files["csv-person-2"]["name"] = _("Übersicht");
$xslt_files["csv-person-2"]["desc"] = _("Modul zur Ausgabe von Personendaten als CSV-Datei. Es werden nur Name, Telefonnummern und E-Mail-Adressen gespeichert. Die Ausgabe-Datei kann in Excel oder OpenOffice angezeigt werden.");
$xslt_files["csv-person-2"]["file"] = "csv-p-2.xsl";
$xslt_files["csv-person-2"]["csv"] = true;
$xslt_files["csv-person-2"]["person"] = true;

$xslt_files["csv-veranstaltung"]["name"] = _("Standardmodul");
$xslt_files["csv-veranstaltung"]["desc"] = _("Standardmodul zur Ausgabe von Veranstaltungsdaten als CSV-Datei. Die Ausgabe-Datei kann in Excel oder OpenOffice angezeigt werden.");
$xslt_files["csv-veranstaltung"]["file"] = "csv-v-1.xsl";
$xslt_files["csv-veranstaltung"]["csv"] = true;
$xslt_files["csv-veranstaltung"]["veranstaltung"] = true;

$xslt_files["csv-veranstaltung-2"]["name"] = _("Übersicht");
$xslt_files["csv-veranstaltung-2"]["desc"] = _("Modul zur Ausgabe von Veranstaltungsdaten als CSV-Datei. Die Ausgabe-Datei kann in Excel oder OpenOffice angezeigt werden.");
$xslt_files["csv-veranstaltung-2"]["file"] = "csv-v-2.xsl";
$xslt_files["csv-veranstaltung-2"]["csv"] = true;
$xslt_files["csv-veranstaltung-2"]["veranstaltung"] = true;

?>
