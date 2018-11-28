<?php
/**
 * Adds the wiki's help tours.
 *
 * @author  Gerd Hoffmann <gerd.hoffmann@uni-oldenburg.de>
 * @license GPL2 or any later version
 */

class AddWikiHelpTours extends Migration
{
    /**
     * Returns the description of the migration.
     */
    public function description()
    {
        return "Adds the wiki's help tours.";
    }

    /**
     * Adds the wiki's help tours in to the database.
     */
    public function up()
    {
        // add tour data
        $insert = "INSERT IGNORE INTO `help_tours` (`tour_id`, `name`, `description`, `type`, `roles`, `version`, `language`, `studip_version`, `installation_id`, `mkdate`) VALUES
            ('4d41c9760a3248313236af202275107a', 'Allgemeines zum Wiki', 'Die Tour gibt einen allgemeinen Überblick über das Wiki.', 'tour', 'autor,tutor,dozent,admin,root', 1, 'de', '3.1', '', 1441276241),
            ('4d41c9760a3248313236af202275107b', 'Schreiben im Wiki', 'Die Tour erklärt, wie das Wiki bearbeitet werden kann.', 'tour', 'autor,tutor,dozent,admin,root', 1, 'de', '3.1', '', 1441276241),
            ('4d41c9760a3248313236af202275107c', 'Lesen im Wiki', 'Die Tour erklärt die verschiedenen Anzeige-Modalitäten zum Lesen des Wikis.', 'tour', 'autor,tutor,dozent,admin,root', 1, 'de', '3.1', '', 1441276241),
            ('5d41c9760a3248313236af202275107a', 'General information on the Wiki', 'This tour provides general information about the Wiki.', 'tour', 'autor,tutor,dozent,admin,root', 1, 'en', '3.1', '', 1441276241),
            ('5d41c9760a3248313236af202275107b', 'Editing the Wiki', 'This tour provides help for editing Wiki pages.', 'tour', 'autor,tutor,dozent,admin,root', 1, 'en', '3.1', '', 1441276241),
            ('5d41c9760a3248313236af202275107c', 'Reading the Wiki', 'This tour provides help for reading Wiki pages.', 'tour', 'autor,tutor,dozent,admin,root', 1, 'en', '3.1', '', 1441276241);
            ";

        DBManager::get()->exec($insert);

        // add steps
        $insert = "INSERT IGNORE INTO `help_tour_steps` (`tour_id`, `step`, `title`, `tip`, `orientation`, `interactive`, `css_selector`, `route`, `mkdate`) VALUES
            ('4d41c9760a3248313236af202275107a', 1, 'Allgemeines zum Wiki', 'Diese Tour gibt einen allgemeinen Überblick über das Wiki.\r\n\r\nUm zum nächsten Schritt zu gelangen, klicken Sie bitte rechts unten auf \"Weiter\".', 'T', 0, '', 'wiki.php', 1441276241),
            ('4d41c9760a3248313236af202275107a', 2, 'Kooperative Textarbeit', 'Das Wiki ist ein Tool für kooperative Textarbeit. Alle Teilnehmenden einer Veranstaltung haben das Recht, Texte zu erstellen, zu ändern und zu löschen.', 'B', 0, '', 'wiki.php', 1441276241),
            ('4d41c9760a3248313236af202275107a', 3, 'Textänderungen schaden nicht', 'Weil das Wiki alle Textänderungen einer Seite protokolliert, können vorhergehende Versionen der Seite wiederhergestellt werden.', 'B', 0, '', 'wiki.php', 1441276241),
            ('4d41c9760a3248313236af202275107a', 4, 'Textänderungen zurücknehmen', 'Textänderungen in einer Wiki-Seite lassen sich rückgängig machen, indem eine vorhergehende Version der Seite wiederhergestellt wird.', 'B', 0, '', 'wiki.php', 1441276241),
            ('4d41c9760a3248313236af202275107a', 5, 'Neue Version einer Wiki-Seite', 'Wird eine Wiki-Seite bearbeitet, so erfolgt die Übernahme der Textänderungen sofort beim Speichern. Eine neue Version der Seite wird dreißig Minuten nach der Speicherung erstellt.', 'B', 0, '', 'wiki.php', 1441276241),
            ('4d41c9760a3248313236af202275107a', 6, 'Kein synchrones Schreiben', 'Das Wiki ist nicht zum synchronen Schreiben geeignet. Es kann immer nur eine Person an einer Seite gleichzeitig arbeiten. Sobald eine zweite Person die Seite im Editor öffnet, erscheint eine Warnmeldung.', 'B', 0, '', 'wiki.php', 1441276241),

            ('4d41c9760a3248313236af202275107b', 1, 'Schreiben im Wiki', 'Diese Tour gibt einen Überblick über die Erstellung und Bearbeitung von Wiki-Seiten.\r\n\r\nUm zum nächsten Schritt zu gelangen, klicken Sie bitte rechts unten auf \"Weiter\".', 'T', 0, '', 'wiki.php', 1441276241),
            ('4d41c9760a3248313236af202275107b', 2, 'WikiWikiWeb', 'Zeigt die Basis-Seite des Wikis an. Sie bildet die strukturelle Grundlage des gesamten Wikis.', 'R', 0, '#nav_wiki_show', 'wiki.php', 1441276241),
            ('4d41c9760a3248313236af202275107b', 3, 'Neue Seiten', 'Zeigt eine tabellarische Übersicht neu erstellter und neu bearbeiteter Wiki-Seiten an.', 'R', 0, '#nav_wiki_listnew', 'wiki.php', 1441276241),
            ('4d41c9760a3248313236af202275107b', 4, 'Alle Seiten', 'Zeigt eine tabellarische Übersicht aller Wiki-Seiten an.', 'R', 0, '#nav_wiki_listall', 'wiki.php', 1441276241),
            ('4d41c9760a3248313236af202275107b', 5, 'Wiki-Seite bearbeiten', 'Durch einen Klick auf die Schaltfläche \"Bearbeiten\" öffnet sich ein Editor, über den eine Wiki-Seite mit Inhalt gefüllt werden kann.\r\n\r\nDie Eingabe eines Namens in doppelten eckigen Klammern erzeugt eine neue Wiki-Seite und vernetzt sie mit der angezeigten Seite.', 'B', 0, '#main_content TABLE:eq(1)  TBODY:eq(0)  TR:eq(0)  TD:eq(0)  DIV:eq(0)  A:eq(0)', 'wiki.php', 1441276241),
            ('4d41c9760a3248313236af202275107b', 6, 'Inhalt einer Wiki-Seite löschen', 'Der Inhalt einer Wiki-Seite lässt sich mit Hilfe eines Klicks auf die Schaltfläche \"Löschen\" entfernen. Die Wiki-Seite bleibt dabei erhalten.', 'B', 0, '#main_content TABLE:eq(1)  TBODY:eq(0)  TR:eq(0)  TD:eq(0)  DIV:eq(0)  A:eq(1)', 'wiki.php', 1441276241),
            ('4d41c9760a3248313236af202275107b', 7, 'QuickLinks', 'Dieser Bildschirmbereich zeigt eine Liste von QuickLinks (Verweisen) auf Wiki-Seiten. Ein Klick auf einen QuickLink öffnet die korrelierende Wiki-Seite. Deren Inhalt lässt sich mit Hilfe der Schaltflächen \"Bearbeiten\" und \"Löschen\" gestalten.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(5)  DIV:eq(0)', 'wiki.php', 1441276241),
            ('4d41c9760a3248313236af202275107b', 8, 'QuickLinks bearbeiten', 'Ein Klick auf dieses Icon öffnet einen Editor, der zur Bearbeitung der QuickLinks dient.\r\n\r\nNeue QuickLinks lassen sich mit doppelten eckigen Klammern erstellen: [[Name]]. Das Löschen eines QuickLinks entfernt die korrelierende Seite aus der Liste.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(5)  DIV:eq(0)  DIV:eq(0)  A:eq(0)  IMG:eq(0)', 'wiki.php', 1441276241),

            ('4d41c9760a3248313236af202275107c', 1, 'Lesen im Wiki', 'Diese Tour gibt einen Überblick über die Anzeige von Wiki-Seiten.\r\n\r\nUm zum nächsten Schritt zu gelangen, klicken Sie bitte rechts unten auf \"Weiter\".', 'T', 0, '', 'wiki.php', 1441276241),
            ('4d41c9760a3248313236af202275107c', 2, 'WikiWikiWeb', 'Zeigt die Basis-Seite des Wikis an.', 'R', 0, '#nav_wiki_show', 'wiki.php', 1441276241),
            ('4d41c9760a3248313236af202275107c', 3, 'Neue Seiten', 'Zeigt eine tabellarische Übersicht neu erstellter und neu bearbeiteter Wiki-Seiten an.', 'R', 0, '#nav_wiki_listnew', 'wiki.php', 1441276241),
            ('4d41c9760a3248313236af202275107c', 4, 'Alle Seiten', 'Zeigt eine tabellarische Übersicht aller Wiki-Seiten an.', 'R', 0, '#nav_wiki_listall', 'wiki.php', 1441276241),
            ('4d41c9760a3248313236af202275107c', 5, 'Ansichten', 'Wenn eine Textänderung in einer Wiki-Seite vorgenommen wurde, stehen drei Anzeigemodi zur Auswahl:\r\n- Standard: Ohne Zusatzinformation\r\n- Textänderungen anzeigen: Welche Textpassagen wurden geändert?\r\n- Text mit AutorInnenzuordnung anzeigen: Wer hat hat etwas geändert?', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(11)  DIV:eq(0)', 'wiki.php', 1441276241),
            ('4d41c9760a3248313236af202275107c', 6, 'Suche', 'Zeigt die Wiki-Seiten an, in denen der eingegebene Suchbegriff vorkommt. Die Suche steht nur in der Standard-Ansicht zur Verfügung.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(14)  DIV:eq(0)', 'wiki.php', 1441276241),
            ('4d41c9760a3248313236af202275107c', 7, 'Kommentare', 'Stellt verschiedene Modalitäten zur Anzeige von Kommentaren bereit, die in einer Wiki-Seite eingetragen wurden.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(20)  DIV:eq(0)', 'wiki.php', 1441276241),
            ('4d41c9760a3248313236af202275107c', 8, 'Kommentare einblenden', 'Alle Kommentare werden als Textblock an der Textposition angezeigt, an der sie in die Wiki-Seite eingefügt wurden.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(20)  DIV:eq(1)  UL:eq(0)  LI:eq(0)  A:eq(0)', 'wiki.php', 1441276241),
            ('4d41c9760a3248313236af202275107c', 9, 'Kommentare als Icon einblenden', 'Für jeden Kommentar wird an der Stelle, wo er in die Wiki-Seite eingefügt wurde, ein Icon angezeigt. Ein Klick auf das Icon öffnet den Kommentar.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(20)  DIV:eq(1)  UL:eq(0)  LI:eq(1)  A:eq(0)', 'wiki.php', 1441276241),
            ('4d41c9760a3248313236af202275107c', 10, 'Kommentare ausblenden', 'Die in einer Wiki-Seite eingefügten Kommentare werden nicht angezeigt.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(20)  DIV:eq(1)  UL:eq(0)  LI:eq(2)  A:eq(0)', 'wiki.php', 1441276241),

            ('5d41c9760a3248313236af202275107a', 1, 'General information on the Wiki', 'This tour provides general information about the Wiki.\r\n\r\nTo proceed, please click \"Continue\" on the lower-right button.', 'T', 0, '', 'wiki.php', 1441276241),
            ('5d41c9760a3248313236af202275107a', 2, 'Tool for collaborative use', 'The Wiki is a collaborative tool. Every user may create, edit and delete content.', 'B', 0, '', 'wiki.php', 1441276241),
            ('5d41c9760a3248313236af202275107a', 3, 'Changes in a Wiki page', 'Since all changes in a Wiki page are saved in a protocol, previous versions of its content can be restored.', 'B', 0, '', 'wiki.php', 1441276241),
            ('5d41c9760a3248313236af202275107a', 4, 'New version of a Wiki page', 'While editing text in a Wiki page, clicking the Save-Button will save its content immediately. A new version of a Wiki page is displayed thirty minutes after saving at the latest.', 'B', 0, '', 'wiki.php', 1441276241),
            ('5d41c9760a3248313236af202275107a', 5, 'Undo changes', 'All changes can be undone by restoring a previous version of text.', 'B', 0, '', 'wiki.php', 1441276241),
            ('5d41c9760a3248313236af202275107a', 6, 'No support of synchronous editing', 'The editor is not designed for synchronous writing. Only one person may edit a page at the same time. If a second person links up to edit the same page, a warning message appears.', 'B', 0, '', 'wiki.php', 1441276241),

            ('5d41c9760a3248313236af202275107b', 1, 'Editing the Wiki', 'This tour provides a general overview of how to create and edit Wiki pages.\r\n\r\nTo proceed, please click \"Continue\" on the lower-right button.', 'T', 0, '', 'wiki.php', 1441276241),
            ('5d41c9760a3248313236af202275107b', 2, 'WikiWikiWeb', 'Displays the basic Wiki page, which is the foundation of all further Wiki pages.', 'R', 0, '#nav_wiki_show', 'wiki.php', 1441276241),
            ('5d41c9760a3248313236af202275107b', 3, 'New pages', 'Displays a survey of all recently created or edited Wiki pages in table form.', 'R', 0, '#nav_wiki_listnew', 'wiki.php', 1441276241),
            ('5d41c9760a3248313236af202275107b', 4, 'All pages', 'Displays a survey of all Wiki pages in table form.', 'R', 0, '#nav_wiki_listall', 'wiki.php', 1441276241),
            ('5d41c9760a3248313236af202275107b', 5, 'Editing the a Wiki page', 'Clicking here will open an editor, allowing to fill a Wiki page with content.', 'B', 0, '#main_content TABLE:eq(1)  TBODY:eq(0)  TR:eq(0)  TD:eq(0)  DIV:eq(0)  A:eq(0)', 'wiki.php', 1441276241),
            ('5d41c9760a3248313236af202275107b', 6, 'Deleting the content of a Wiki page', 'Clicking here will delete all content and links of a Wiki page leaving it blank.', 'B', 0, '#main_content TABLE:eq(1)  TBODY:eq(0)  TR:eq(0)  TD:eq(0)  DIV:eq(0)  A:eq(1)', 'wiki.php', 1441276241),
            ('5d41c9760a3248313236af202275107b', 7, 'QuickLinks', 'This box displays links, leading to other Wiki pages. Selecting a link will forward to the related page. The content there may be edited the same way as described in step 5 and six.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(5)  DIV:eq(0)', 'wiki.php', 1441276241),
            ('5d41c9760a3248313236af202275107b', 8, 'Editing QuickLinks', 'A click on this icon will open an editor to edit the QuickLinks.\r\n\r\nEntering a name within double square brackets like [[name]] in the editor will create a new QuickLink leading to a correlating page. Deleting a QuickLink will cause its deletion in the QuickLink box.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(5)  DIV:eq(0)  DIV:eq(0)  A:eq(0)  IMG:eq(0)', 'wiki.php', 1441276241),

            ('5d41c9760a3248313236af202275107c', 1, 'Reading the Wiki', 'This tour gives a general overview of the different modes to read Wiki pages.\r\n\r\nTo proceed, please click \"Continue\" on the lower-right button.', 'T', 0, '', 'wiki.php', 1441276241),
            ('5d41c9760a3248313236af202275107c', 2, 'WikiWikiWeb', 'Displays the basic Wiki page, which is the foundation of all further Wiki pages.', 'R', 0, '#nav_wiki_show', 'wiki.php', 1441276241),
            ('5d41c9760a3248313236af202275107c', 3, 'New pages', 'Displays a survey of all recently created or edited Wiki pages in table form.', 'R', 0, '#nav_wiki_listnew', 'wiki.php', 1441276241),
            ('5d41c9760a3248313236af202275107c', 4, 'All pages', 'Displays a survey of all Wiki pages in table form.', 'R', 0, '#nav_wiki_listall', 'wiki.php', 1441276241),
            ('5d41c9760a3248313236af202275107c', 5, 'Views', 'If a Wiki page has been edited, the user may choose between three modes of viewing content:\r\n- Standard: Without extra information\r\n- Show text changes: Which parts of text have been edited?\r\n- Show text changes and associated author: Who was editing a part of text?', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(11)  DIV:eq(0)', 'wiki.php', 1441276241),
            ('5d41c9760a3248313236af202275107c', 6, 'Search', 'Shows all Wiki pages which contain the entered search term. The search is supported in Standard-View only.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(14)  DIV:eq(0)', 'wiki.php', 1441276241),
            ('5d41c9760a3248313236af202275107c', 7, 'Comments', 'Supports three modes of showing comments added to a Wiki page.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(20)  DIV:eq(0)', 'wiki.php', 1441276241),
            ('5d41c9760a3248313236af202275107c', 8, 'Show comments', 'All comments are shown as a block of text exactly in that position, in which they were added.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(20)  DIV:eq(1)  UL:eq(0)  LI:eq(0)  A:eq(0)', 'wiki.php', 1441276241),
            ('5d41c9760a3248313236af202275107c', 9, 'Show comments as icon', 'All comments are represented by an icon exactly in that position, in which a comment was added. A click on an icon shows the correlating comment.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(20)  DIV:eq(1)  UL:eq(0)  LI:eq(1)  A:eq(0)', 'wiki.php', 1441276241),
            ('5d41c9760a3248313236af202275107c', 10, 'Hide comments', 'All added comments are hidden while displaying a page.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(20)  DIV:eq(1)  UL:eq(0)  LI:eq(2)  A:eq(0)', 'wiki.php', 1441276241);
            ";

        DBManager::get()->exec($insert);

        // add settings
        $insert = "INSERT IGNORE INTO `help_tour_settings` (`tour_id`, `active`, `access`) VALUES
            ('4d41c9760a3248313236af202275107a', 1, 'standard'),
            ('4d41c9760a3248313236af202275107b', 1, 'standard'),
            ('4d41c9760a3248313236af202275107c', 1, 'standard'),
            ('5d41c9760a3248313236af202275107a', 1, 'standard'),
            ('5d41c9760a3248313236af202275107b', 1, 'standard'),
            ('5d41c9760a3248313236af202275107c', 1, 'standard');
            ";

        DBManager::get()->exec($insert);
    }

    /**
     * Removes the wiki's help tours from the database.
     */
    public function down() {

        // delete settings
        $delete = "DELETE FROM help_tour_settings WHERE
            tour_id = '4d41c9760a3248313236af202275107a' OR
            tour_id = '4d41c9760a3248313236af202275107b' OR
            tour_id = '4d41c9760a3248313236af202275107c' OR
            tour_id = '5d41c9760a3248313236af202275107a' OR
            tour_id = '5d41c9760a3248313236af202275107b' OR
            tour_id = '5d41c9760a3248313236af202275107c'
            ";

        DBManager::get()->exec($delete);

        // delete steps
        $delete = "DELETE FROM help_tour_steps WHERE
            tour_id = '4d41c9760a3248313236af202275107a' OR
            tour_id = '4d41c9760a3248313236af202275107b' OR
            tour_id = '4d41c9760a3248313236af202275107c' OR
            tour_id = '5d41c9760a3248313236af202275107a' OR
            tour_id = '5d41c9760a3248313236af202275107b' OR
            tour_id = '5d41c9760a3248313236af202275107c'
            ";

        DBManager::get()->exec($delete);

        // delete tour data
        $delete = "DELETE FROM help_tours WHERE
            tour_id = '4d41c9760a3248313236af202275107a' OR
            tour_id = '4d41c9760a3248313236af202275107b' OR
            tour_id = '4d41c9760a3248313236af202275107c' OR
            tour_id = '5d41c9760a3248313236af202275107a' OR
            tour_id = '5d41c9760a3248313236af202275107b' OR
            tour_id = '5d41c9760a3248313236af202275107c'
            ";

        DBManager::get()->exec($delete);
    }
}