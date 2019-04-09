<?php
# Lifter010: TODO
/**
 * QuickSearch.class.php - GUI class for quciksearch
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Rasmus <fuhse@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

/**
 * This class provides a small and intuitive GUI-element for an instant search of
 * courses, persons, institutes or other items. Mainly the structure to include
 * a QuickSearch-field is the following:
 *  //code-begin
 *  $sf = new QuickSearch("username");
 *    $sf->withButton();
 *  $sf->specialSQL("SELECT username, CONCAT(Vorname, \" \", Nachname) " .
 *     "FROM auth_user_md5 " .
 *     "WHERE CONCAT(Vorname, \" \", Nachname) LIKE :input " .
 *        "AND perms = 'dozent'", _("Dozenten suchen"));
 *  print $sf->render();
 *  //code-end
 * This code should be included into an html <form>-tag. It will provide a nice looking
 * input-field with a button (this is the $sf->withButton() command for), a javascript
 * instant-search for your items (in this case 'Dozenten') and also a non-javascript
 * oldschool version of a searchfield with a select-box in step 2 of the search (first
 * write what you search, click on the button and then select in the select-box what
 * you wanted to have).
 * You can handle the searchfield in your form-tag as if you have written an
 * '<input type="text" name="username">'.
 *
 * For most cases you may only want to search for persons, courses or institutes.
 * Thus a shortcut is implemented in this class. You may write
 *  //code-begin
 *  $sf = new QuickSearch("username", "username");
 *    $sf->withButton();
 *  print $sf->render();
 *  //code-end
 * to receive a searchfield that is automatically searching for users and inserts
 * the selected users's username in the searchfield. The first parameter of the
 * constructor 'new Quicksearch' is the name of the variable in your form and is
 * completely free to name. The (optional) second parameter describes what you are
 * searching for: username, user_id, Seminar_id or Institut_id.
 *
 * Also you can do method-chaining with this class, so you can press everything
 * you need infront of your semicolon. Watch this example:
 *  //code-begin
 *  print QuickSearch::get("username", "username")->withButton->render();
 *  //code-end
 *
 * Lastly you can replace the second argument of the constructor (or get-method)
 * by an object whose class extends the SearchType-class. This might be
 * useful to create your own searches and handle them with oop-style or even
 * use a totally different search-engine like lucene-index! All you need to
 * do so is implement your searchclass and follow this example:
 *  //code-begin
 *  class TeacherSearch extends SearchType {
 *    ...
 *  }
 *  $searcher = new TeacherSearch();
 *  print QuickSearch::get("username", $searcher)->withButton->render();
 *  //code-end
 * Watch the SearchType class in lib/classes/searchtypes/SearchType.class.php
 * for details.
 * Enjoy!
 */
class QuickSearch
{

    const GC_LIFETIME = 10800; // = 3 * 60 * 60 = 3 hours

    static $count_QS = 0;       //static counter of all instances of this class

    private $name;              //name of the input/select field
    private $search;            //may be an object or a string
    private $avatarLike;        //like "user_id", "username", "Seminar_id" or stuff
    private $withButton;        //if true, the field will be displayed with a looking-glass-button to click on
    private $specialBeschriftung;
    private $selectBox = true;
    private $withAttributes = [];
    private $box_width = "233"; //width of the box withButton
    private $box_align = "right";//align of the lookingglass in the withButton-box
    private $autocomplete_disabled = false;
    private $search_button_name;
    private $reset_button_name;

    /**
     * Deletes all older requests that have not been used for three hours
     * from the session
     *
     * @return int Number of removed searches
     */
    public static function garbageCollect()
    {
        $count = count($_SESSION['QuickSearches']);

        $_SESSION['QuickSearches'] = array_filter($_SESSION['QuickSearches'], function ($query) {
            return $query['time'] + QuickSearch::GC_LIFETIME > time();
        });

        return $count - count($_SESSION['QuickSearches']);
    }

    /**
     * Retrieves the search object for the given id previously stored in
     * the session.
     *
     * @param String $query_id Id of the quicksearch object
     * @return SearchType Quicksearch object
     * @throws RuntimeException when the given query does not exist in session
     */
    public static function getFromSession($query_id)
    {
        self::garbageCollect();

        if (!isset($_SESSION['QuickSearches'][$query_id])) {
            throw new RuntimeException('Quicksearch id not in session');
        }

        // Store last access to search
        $_SESSION['QuickSearches'][$query_id]['time'] = time();

        $query = $_SESSION['QuickSearches'][$query_id];

        if ($query['includePath']) {
            include_once $query['includePath'];
        }

        return unserialize($query['object']);

    }

    /**
     * returns an instance of QuickSearch so you can use it as singleton
     *
     * @param string $name the name of the destinated variable in your html-form. Handle it
     * as if it was an '<input type="text" name="yourname">' input.
     * @param string $search if set to user_id, username, Seminar_id, Arbeitsgruppe_id or Institute_id
     * the searchfield will automatically search for persons, courses, workgroups, institutes and
     * you don't need to call the specialSearch-method.
     *
     * @return object of type QuickSearch
     */
    public static function get($name, $search = NULL)
    {
        return new static($name, $search);
    }


    /**
     * constructor which prepares a searchfield for persons, courses, institutes or
     * special items you may want to search for. This is a GUI-class, see
     * QuickSearch.class.php for further documentation.
     *
     * @param string $name the name of the destinated variable in your html-form. Handle it
     * as if it was an '<input type="text" name="yourname">' input.
     * @param string $search if set to user_id, username, Seminar_id, Arbeitsgruppe_id or Institute_id
     * the searchfield will automatically search for persons, courses, workgroups, institutes and
     * you don't need to call the specialSearch-method.
     *
     * @return void
     */
    public function __construct($name, $search = NULL)
    {
        self::$count_QS++;
        $this->name = $name;
        $this->withButton = false;
        $this->avatarLike = "";
        if ($search instanceof SearchType) {
            $this->search = $search;
        } else {
            $this->search = NULL;
        }
        $this->setAttributes([]);
    }

    /**
     * if set to true, the searchfield will be a nice-looking grey searchfield with
     * a magnifier-symbol as a submit-button. Set this to false to create your own
     * submit-button and style of the form.
     * @param mixed $design  associative array of params.
     *
     * @return QuickSearch
     */
    public function withButton($design = [])
    {
        $this->withButton = true;
        if (isset($design['width'])) {
            $this->box_width = $design['width'];
        }
        $this->box_align = $design['align'] ? $design['align'] : "right";
        $this->search_button_name = $design['search_button_name'];
        $this->reset_button_name = $design['reset_button_name'];
        return $this;
    }

    /**
     * this will disable a submit button for the searchfield
     *
     * @return QuickSearch
     */
    public function withoutButton()
    {
        $this->withButton = false;
        return $this;
    }

    /**
     * Here you can set a default-value for the searchfield
     *
     * @param string $valueID the default-ID that should be stored
     * @param string $valueName the default value, that should be displayed
     * - remember that these may not be the same, they may be
     *   something like "ae2b1fca515949e5d54fb22b8ed95575", "test_dozent"
     *
     * @return QuickSearch
     */
    public function defaultValue($valueID, $valueName)
    {
        $this->defaultID = $valueID;
        $this->defaultName = $valueName;
        return $this;
    }

    /**
     * defines a css class for the searchfield
     *
     * @param string $class any css class name for the "input type=text" tag
     *
     * @return QuickSearch
     */
    public function setInputClass($class)
    {
        $this->withAttributes['class'] = $class;
        return $this;
    }

    /**
     * defines css-proporties for searchfield that will be included as 'style="$style"'
     *
     * @param string $style one or more css-proporties separated with ";"
     *
     * @return QuickSearch
     */
    public function setInputStyle($style)
    {
        $this->withAttributes['style'] = $style;
        return $this;
    }

    /**
     * disables the select-box, which is displayed for non-JS users who will
     * choose with this box, which item they want to have.
     *
     * @param bool $set false if we DO want a select-box, false otherwise
     *
     * @return QuickSearch
     */
    public function noSelectbox($set = true)
    {
        $this->selectBox = !$set;
        return $this;
    }

    /**
     * disables the ajax autocomplete for this searchfield
     * If you want to disable all QuickSearches, you better use the
     * config variable global -> AJAX_AUTOCOMPLETE_DISABLED
     * @param disable boolean: true (default) to disable, false to enable
     * autocomplete via ajax.
     * @return QuickSearch
     */
    public function disableAutocomplete($disable = true) {
        $this->autocomplete_disabled = $disable;
        return $this;
    }

    /**
     * set a JavaScript-function to be fired after the user has selected a
     * value in the QuickSearch field. Arguments are:
     * function fireme(id_of_item, text_of_item)
     * example setting: QS->fireJSFunctionOnSelect('fireme');
     *
     * @param string $function_name the name of the javascript function
     *
     * @return QuickSearch
     */
    public function fireJSFunctionOnSelect($function_name)
    {
        $this->jsfunction = $function_name;
        return $this;
    }

    /**
     * assigns special attributes to the html-element of the searchfield
     *
     * @param array $ttr_array like array("title" => "hello world")
     *
     * @return QuickSearch
     */
    public function setAttributes($attr_array)
    {
        if (is_array($attr_array)) {
            $this->withAttributes = $attr_array;
        }
        if (!isset($this->withAttributes['aria-label'])
                && !isset($this->withAttributes['aria-labelledby'])
                && $this->search) {
            $this->withAttributes['aria-label'] = $this->search->getTitle();
        }
        return $this;
    }

    /**
     * Returns whether the underlying search type requires an extended
     * layout.
     *
     * @return bool indicating whether an extended layout is required
     */
    public function hasExtendedLayout()
    {
        return $this->search->extendedLayout;
    }

    /**
     * last step: display everything and be happy!
     * comment: the Ajax-Result (for the javascript-instant-search) will be also displayed here,
     * but that does not need to concern you.
     *
     * @return string
     */
    public function render()
    {
        if (trim(Request::get($this->name.'_parameter'))
               && (Request::get($this->name.'_parameter') != $this->beschriftung())
               && !Request::get($this->name)
               && $this->selectBox) {
            //No Javascript activated and having searched:
            $searchresults = $this->searchresults(Request::get($this->name.'_parameter'));

            $template = $GLOBALS['template_factory']->open('quicksearch/selectbox.php');
            $template->set_attribute('withButton', $this->withButton);
            $template->set_attribute('box_align', $this->box_align);
            $template->set_attribute('box_width', $this->box_width);
            $template->set_attribute('withAttributes', $this->withAttributes);
            $template->set_attribute('searchresults', $searchresults);
            $template->set_attribute('name', $this->name);
            $template->set_attribute('inputClass', $this->inputClass);
            $template->set_attribute('search_button_name', $this->search_button_name);
            $template->set_attribute('reset_button_name', $this->reset_button_name);
            $template->set_attribute('extendedLayout', $this->hasExtendedLayout());
            return $template->render();

        } else {
            //Abfrage in der Session speichern:
            $query_id = md5(serialize($this->search));
            if ($this->specialQuery) {
                $_SESSION['QuickSearches'][$query_id]['query'] = $this->specialQuery;
            } elseif ($this->search instanceof SearchType) {
                $_SESSION['QuickSearches'][$query_id]['object'] = serialize($this->search);
                if ($this->search instanceof SearchType) {
                    $_SESSION['QuickSearches'][$query_id]['includePath'] = $this->search->includePath();
                }
                $_SESSION['QuickSearches'][$query_id]['time'] = time();
            } else {
                $_SESSION['QuickSearches'][$query_id]['query'] = $this->search;
            }
            $_SESSION['QuickSearches'][$query_id]['time'] = time();
            //var_dump($_SESSION['QuickSearches'][$query_id]);
            //Ausgabe:
            $template = $GLOBALS['template_factory']->open('quicksearch/inputfield.php');
            $template->set_attribute('withButton', $this->withButton);
            $template->set_attribute('box_align', $this->box_align);
            $template->set_attribute('box_width', $this->box_width);
            $template->set_attribute('inputStyle', $this->inputStyle ? $this->inputStyle : "");
            $template->set_attribute('beschriftung', $this->beschriftung());
            $template->set_attribute('name', $this->name);
            $template->set_attribute('defaultID', $this->defaultID);
            $template->set_attribute('defaultName', $this->defaultName);
            $template->set_attribute('inputClass', $this->inputClass);
            $template->set_attribute('withAttributes', $this->withAttributes ? $this->withAttributes : []);
            $template->set_attribute('jsfunction', $this->jsfunction);
            $template->set_attribute('autocomplete_disabled', Config::get()->getValue("AJAX_AUTOCOMPLETE_DISABLED") || $this->autocomplete_disabled);
            $template->set_attribute('count_QS', self::$count_QS);
            $template->set_attribute('id', $this->getId());
            $template->set_attribute('query_id', $query_id);
            $template->set_attribute('search_button_name', $this->search_button_name);
            $template->set_attribute('reset_button_name', $this->reset_button_name);
            $template->set_attribute('extendedLayout', $this->hasExtendedLayout());
            return $template->render();
        }
    }

    /**
     * Convert quicksearch to string by rendering it
     *
     * @return string rendered html
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * returns the id string used for the input field
     *
     * @return string
     */
    public function getId()
    {
        return "qs_".md5($this->name) . '_' . (int)self::$count_QS;
    }

    //////////////////////////////////////////////////////////////////////////////
    //                               private-methods                            //
    //////////////////////////////////////////////////////////////////////////////

    /**
     * private method to get a result-array in the way of array(array(item_id, item-name)).
     *
     * @param string $request the request from the searchfield typed by the user.
     *
     * @return array array(array(item_id, item-name), ...).
     */
    private function searchresults($request)
    {
        if ($this->search instanceof SearchType) {
            try {
                $results = $this->search->getResults($request, $_REQUEST);
            } catch (Exception $exception) {
                //Der Programmierer will ja seine Fehler sehen:
                return [["", $exception->getMessage()]];
            }
            return $results;
        } else {
            $result = [["", _("Kein korrektes Suchobjekt angegeben.")]];
            return $result;
        }
    }

    /**
     * get the label of the searchfield that is written in javascript and disappears
     * when the user focusses on the searchfield.
     *
     * @return string localized-string
     */
    private function beschriftung()
    {
        if ($this->search instanceof SearchType) {
            return $this->search->getTitle();
        } else {
            return "";
        }
    }
}
