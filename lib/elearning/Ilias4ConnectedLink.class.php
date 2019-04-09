<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

use Studip\Button, Studip\LinkButton;

/**
 * class to generate links to ILIAS 4
 *
 * This class contains methods to generate links to ILIAS 4.
 *
 * @author    Arne Schröder <schroeder@data-quest.de>
 * @access    public
 * @modulegroup    elearning_interface_modules
 * @module        Ilias4ConnectedLink
 * @package    ELearning-Interface
 */
class Ilias4ConnectedLink extends Ilias3ConnectedLink
{
    /**
     * constructor
     *
     * init class.
     * @access
     * @param string $cms system-type
     */
    function __construct($cms)
    {
        parent::__construct($cms);
        $this->cms_link = "ilias3_referrer.php";
    }

    /**
     * get module link
     *
     * returns link to the specified ilias object. works without initializing module-class.
     * @access public
     * @return string html-code
     */
    function getModuleLink($title, $module_id, $module_type)
    {
        global $connected_cms, $view, $search_key, $cms_select, $current_module;

        if ($connected_cms[$this->cms_type]->isAuthNecessary() AND (! $connected_cms[$this->cms_type]->user->isConnected())) {
            return false;
        }
        $output = "<a href=\"" . UrlHelper::getLink($this->cms_link . "?"
        . "client_id=" . $connected_cms[$this->cms_type]->getClientId()
        . "&cms_select=" . $this->cms_type
        . "&ref_id=" . $module_id
        . "&type=" . $module_type
        . "&target=start"). "\" target=\"_blank\" rel=\"noopener noreferrer\">";
        $output .= $title;
        $output .= "</a>&nbsp;";

        return $output;
    }

    /**
     * get admin module links
     *
     * returns links add or remove a module from course
     * @access public
     * @return string returns html-code
     */
    function getAdminModuleLinks()
    {
        global $connected_cms, $view, $search_key, $cms_select, $current_module;

        if (! $connected_cms[$this->cms_type]->content_module[$current_module]->isDummy()) {
            $result = $connected_cms[$this->cms_type]->soap_client->getPath($connected_cms[$this->cms_type]->content_module[$current_module]->getId());
        }
        if ($result) {
            $output .= "<i>Pfad: ". htmlReady($result) . "</i><br><br>";
        }
        $output .= "<form method=\"POST\" action=\"" . URLHelper::getLink() . "\">\n";
        $output .= CSRFProtection::tokenTag();
        $output .= "<input type=\"HIDDEN\" name=\"view\" value=\"" . htmlReady($view) . "\">\n";
        $output .= "<input type=\"HIDDEN\" name=\"search_key\" value=\"" . htmlReady($search_key) . "\">\n";
        $output .= "<input type=\"HIDDEN\" name=\"cms_select\" value=\"" . htmlReady($cms_select) . "\">\n";
        $output .= "<input type=\"HIDDEN\" name=\"module_type\" value=\"" . htmlReady($connected_cms[$this->cms_type]->content_module[$current_module]->getModuleType()) . "\">\n";
        $output .= "<input type=\"HIDDEN\" name=\"module_id\" value=\"" . htmlReady($connected_cms[$this->cms_type]->content_module[$current_module]->getId()) . "\">\n";
        $output .= "<input type=\"HIDDEN\" name=\"module_system_type\" value=\"" . htmlReady($this->cms_type) . "\">\n";

        if ($connected_cms[$this->cms_type]->content_module[$current_module]->isConnected()) {
            $output .= "&nbsp;" . Button::create(_('Entfernen'), 'remove');
        } elseif ($connected_cms[$this->cms_type]->content_module[$current_module]->isAllowed(OPERATION_WRITE)) {
            $output .= "<div align=\"left\">";
            if ($connected_cms[$this->cms_type]->content_module[$current_module]->isAllowed(OPERATION_COPY) AND (! in_array($connected_cms[$this->cms_type]->content_module[$current_module]->module_type, ["lm", "htlm", "sahs", "cat", "crs", "dbk"]))) {
                $output .= "<input type=\"CHECKBOX\" name=\"copy_object\" value=\"1\">";
                $output .= _("Als Kopie anlegen") . "&nbsp;";
                $output .= Icon::create('info-circle', 'inactive', ['title' => _('Wenn Sie diese Option wählen, wird eine identische Kopie als eigenständige Instanz des Lernmoduls erstellt. Anderenfalls wird ein Link zum Lernmodul gesetzt.')])->asImg();
                $output .= "<br>";
            }
            $output .= "<input type=\"RADIO\" name=\"write_permission\" value=\"none\" checked>";
            $output .= _("Keine Schreibrechte") . "&nbsp;";
            $output .= Icon::create('info-circle', 'inactive', ['title' => _('Nur der/die BesitzerIn des Lernmoduls hat Schreibzugriff für Inhalte und Struktur des Lernmoduls. Tutor/-innen und Lehrende können die Verknüpfung zur Veranstaltung wieder löschen.')])->asImg();
            $output .= "<br>";
            $output .= "<input type=\"RADIO\" name=\"write_permission\" value=\"dozent\">";
            $output .= _("Mit Schreibrechten für alle Lehrenden dieser Veranstaltung") . "&nbsp;";
            $output .= Icon::create('info-circle', 'inactive', ['title' => _('Lehrende haben Schreibzugriff für Inhalte und Struktur des Lernmoduls. Tutor/-innen und Lehrende können die Verknüpfung zur Veranstaltung wieder löschen.')])->asImg();
            $output .= "<br>";
            $output .= "<input type=\"RADIO\" name=\"write_permission\" value=\"tutor\">";
            $output .= _("Mit Schreibrechten für alle Lehrenden und Tutor/-innen dieser Veranstaltung") . "&nbsp;";
            $output .= Icon::create('info-circle', 'inactive', ['title' => _('Lehrende und Tutor/-innen haben Schreibzugriff für Inhalte und Struktur des Lernmoduls. Tutor/-innen und Lehrende können die Verknüpfung zur Veranstaltung wieder löschen.')])->asImg();
            $output .= "<br>";
            $output .= "<input type=\"RADIO\" name=\"write_permission\" value=\"autor\">";
            $output .= _("Mit Schreibrechten für alle Personen dieser Veranstaltung") . "&nbsp;";
            $output .= Icon::create('info-circle', 'inactive', ['title' => _('Lehrende, Tutor/-innen und Teilnehmende haben Schreibzugriff für Inhalte und Struktur des Lernmoduls. Tutor/-innen und Lehrende können die Verknüpfung zur Veranstaltung wieder löschen.')])->asImg();
            $output .= "</div>";
            $output .= "</div><br>" . Button::create(_('Hinzufügen'), 'add') . "<br>";
        } else {
            $output .= "&nbsp;" . Button::create(_('Hinzufügen'), 'add');
        }
        $output .= "</form>";

        return $output;
    }
}
?>
