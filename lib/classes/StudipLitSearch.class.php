<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/**
 * Class to build search formular and execute search
 *
 * @author  André Noack <noack@data-quest.de>
 * @license GPL2 or any later version
 */
class StudipLitSearch
{
    public $start_result;
    public $form_template;
    public $inner_form;
    public $outer_form;
    public $term_count;
    public $search_plugin;

    public function __construct()
    {
        global $sess, $_lit_search_plugins;

        URLHelper::bindLinkParam("_start_result",$this->start_result);

        $this->form_template = array('search_term'  =>  array('type' => 'text', 'caption' => _("Suchbegriff"), 'info' => _("Bitte geben Sie hier einen beliebigen Suchbegriff ein.")),
                                    'search_field'  =>  array('type' => 'select', 'caption' => _("Suchfeld"), 'info' => _("Mögliche Suchfelder"),
                                                            'options_callback' => array($this,"getSearchFields")),
                                    'search_truncate'=> array('type' => 'select', 'caption' => _("Trunkieren"), 'info' => _("Wenn Sie eine der Trunkierungsoptionen wählen, werden alle Treffer angezeigt, die mit dem Suchbegriff beginnen (Rechts trunkieren) bzw. enden (Links trunkieren)."),
                                                            'options' => array(array('name' => _("Nein"), "value" => 'none'),
                                                                                array('name' => _("Rechts trunkieren"), "value" => 'right'),
                                                                                /*array('name' => _("Links trunkieren"), "value" => 'left')*/)),
                                    'search_operator'=> array('type' => 'radio', 'options' => array(array('name' =>_("UND"),'value' => 'AND'),
                                                                                                    array('name' =>_("ODER"),'value' => 'OR'),
                                                                                                    array('name' =>_("NICHT"),'value' => 'NOT')),
                                                            'caption' => _("Verknüpfung") ,'info'=>_("Wählen Sie eine Verknüpfungsart"), 'separator' => "&nbsp;", 'default_value' => "AND")
                                    );
        $search_plugins = $this->getAvailablePlugins();
        $preferred_plugin = $this->getPreferredPlugin();
        $i = 0;
        if ($preferred_plugin && isset($search_plugins[$preferred_plugin])) {
            $search_plugin_options[] = array('name' => $search_plugins[$preferred_plugin], 'value' => $preferred_plugin);
        }
        foreach ($search_plugins as $plugin_name => $plugin_displayname) {
            if ($preferred_plugin != $plugin_name) {
                $search_plugin_options[] = array('name' => $plugin_displayname , 'value' => $plugin_name);
            } else {
                unset($search_plugins[$plugin_name]);
                $search_plugins[$plugin_name] = $plugin_displayname;
            }
        }
        $outer_form_fields = array('search_plugin' => array('type' => 'select', 'caption' => _("Welchen Katalog durchsuchen ?"),
                                                            'options' => $search_plugin_options, 'default_value' => $search_plugin_options[0]['value']),
                                    'search_term_count' => array('type' => 'hidden', 'default_value' => 1)
                                    );
        $outer_form_buttons = array('search' => array('caption' => _('Suchen'), 'info' => _("Suche starten")),
                                    'reset' => array('caption' => _('Zurücksetzen'), 'info' => _("Suche zurücksetzen")),
                                    'change' => array('caption' => _('Auswählen'), 'info' => _("Anderen Katalog auswählen")),
                                    'search_add' => array('caption' => _('Hinzufügen'), 'info' => _("Suchfeld hinzufügen")),
                                    'search_sub' => array('caption' => _('Entfernen'), 'info' => _("Suchfeld entfernen")));

        $this->outer_form = new StudipForm($outer_form_fields,$outer_form_buttons,"lit_search");

        if ($this->outer_form->isClicked("search_add")) {
            $this->outer_form->form_values['search_term_count'] = $this->outer_form->getFormFieldValue('search_term_count') + 1;
        }
        if ($this->outer_form->isClicked("search_sub") && $this->outer_form->getFormFieldValue('search_term_count') > 1) {
            $this->outer_form->form_values['search_term_count']--;
        }
        $plugin_name = false;
        if ($this->outer_form->isClicked("reset") || $this->outer_form->isChanged("search_plugin")) {
            $plugin_name = $this->outer_form->getFormFieldValue("search_plugin");
            if($this->outer_form->isClicked("reset")) $this->outer_form->doFormReset();
            $this->outer_form->form_values["search_plugin"] = $plugin_name;
        }

        $this->term_count = $this->outer_form->getFormFieldValue('search_term_count');
        for ($i = 0 ; $i < $this->term_count; ++$i) {
            foreach($this->form_template as $name => $value) {
                $inner_form_fields[$name . "_" . $i] = $value;
            }
        }
        $this->inner_form = new StudipForm($inner_form_fields, null, "lit_search");
        if ($plugin_name !== false) {
            if($this->outer_form->isClicked("reset")) $this->inner_form->doFormReset();
            $this->outer_form->form_values["search_plugin"] = $plugin_name;
        }
        if ( ($init_plugin_name = $this->outer_form->getFormFieldValue("search_plugin")) &&
            in_array($init_plugin_name, array_keys($search_plugins))) {
            $init_plugin_name = "StudipLitSearchPlugin" . $init_plugin_name;
            include_once "lib/classes/lit_search_plugins/" . $init_plugin_name .".class.php";
            $this->search_plugin = new $init_plugin_name();
        } else {
            $plugin_name = false;
            $this->outer_form->doFormReset();
            throw new Exception("Invalid SearchPlugin requested.");
        }
        if ($plugin_name !== false) {
            $this->search_plugin->doResetSearch();
            $this->start_result = 1;
        }

        $this->outer_form->form_fields['search_plugin']['info'] = $this->search_plugin->description;
    }

    public function getSearchFields($caller, $name)
    {
        return $this->search_plugin->getSearchFields();
    }

    public function doSearch()
    {
        return $this->search_plugin->doSearch($this->getSearchValues());
    }

    public function getNumHits()
    {
        return $this->search_plugin->getNumHits();
    }

    public function getSearchResult($num_hit)
    {
        return $this->search_plugin->getSearchResult($num_hit);
    }

    public function getSearchValues()
    {
        $search_values = null;
        for ($i = 0 ; $i < $this->term_count; ++$i) {
            foreach ($this->form_template as $name => $value) {
                $search_values[$i][$name] = $this->inner_form->getFormFieldValue($name . "_" . $i);
            }
        }
        return $search_values;
    }

    public static function GetPreferredPlugin()
    {
        $dbv = DbView::getView('literatur');
        $dbv->params[0] = $GLOBALS['user']->id;
        $rs = $dbv->get_query("view:LIT_GET_FAK_LIT_PLUGIN");
        $rs->next_record();
        return $rs->f('lit_plugin_name');
    }

    public static function GetAvailablePlugins()
    {
        static $available_plugins = null;
        if ($available_plugins === null) {
            $available_plugins = [];
            foreach ($GLOBALS['_lit_search_plugins'] as $plugin) {
                $available_plugins[$plugin['name']] = $plugin['display_name'] ?: $plugin['name'];
            }
        }
        return $available_plugins;
    }

    public static function GetExternalLink($plugin_name)
    {
        static $available_plugins_links = null;
        if ($available_plugins_links === null) {
            $available_plugins_links = [];
            foreach ($GLOBALS['_lit_search_plugins'] as $plugin) {
                $available_plugins_links[$plugin['name']] = $plugin['link'];
            }
        }
        return $available_plugins_links[$plugin_name];
    }

    public static function GetPluginDisplayName($plugin_name)
    {
        $plugins = self::GetAvailablePlugins();
        return $plugins[$plugin_name];
    }

    public function GetAvailablePluginsOptions()
    {
        $ret = [];
        foreach (self::GetAvailablePlugins() as $value => $name) {
            $ret[] = compact('name', 'value');
        }
        return $ret;
    }

    public static function CheckZ3950($accession_number, $one_plugin_name = false)
    {
        static $plugin_list = null;
        if ($plugin_list === null) {
            $plugin_list = [];
            foreach ($GLOBALS['_lit_search_plugins'] as $plugin) {
                if ($plugin['name'] !== 'Studip' && ($one_plugin_name === false || $plugin['name'] == $one_plugin_name)) {
                    $plugin_name = "StudipLitSearchPlugin{$plugin['name']}";
                    include_once "lib/classes/lit_search_plugins/{$plugin_name}.class.php";
                    $plugin_list[$plugin['name']] = new $plugin_name();
                }
            }
        }

        $ret = [];
        foreach($plugin_list as $plugin_name => $plugin_obj) {
             $ret[$plugin_name] = [
                 'found' => $plugin_obj->doCheckAccession($accession_number),
                 'error' => $plugin_obj->getError(),
             ];
        }
        return $ret;
    }
}

//test
/*
$_lit_search_plugins = array("Studip", "Gbv");
page_open(array("sess" => "Seminar_Session"));
$_language = DEFAULT_LANGUAGE;
$_language_path = $INSTALLED_LANGUAGES[$_language]["path"];
$test = new StudipLitSearch();

echo "<table width='500' border =1><tr><td>";
echo $test->outer_form->getFormStart();
echo $test->outer_form->getFormFieldCaption('search_plugin') . $test->outer_form->getFormField('search_plugin');
echo $test->outer_form->getFormButton('reset');
echo "&nbsp;";
echo $test->outer_form->getFormButton('search');

echo "</td></tr>";
for ($i = 0 ; $i < $test->term_count; ++$i){
    echo "<tr><td>";
    if ($i > 0){
        echo $test->inner_form->getFormFieldCaption("search_operator_" . $i);
        echo $test->inner_form->getFormField("search_operator_" . $i);
        echo "<br>";
    }
    echo $test->inner_form->getFormFieldCaption("search_field_" . $i);
    echo $test->inner_form->getFormField("search_field_" . $i);
    echo "<br>";
    echo $test->inner_form->getFormFieldCaption("search_truncate_" . $i);
    echo $test->inner_form->getFormField("search_truncate_" . $i);
    echo "<br>";
    echo $test->inner_form->getFormFieldCaption("search_term_" . $i);
    echo $test->inner_form->getFormField("search_term_" . $i);
    echo "</td></tr>";
}
echo "<tr><td>";
echo $test->outer_form->getFormButton('search_add');
echo "&nbsp;";
echo $test->outer_form->getFormButton('search_sub');
echo "</td></tr>";
echo "</table>";
echo $test->outer_form->getFormEnd();
echo "<pre>";
if ($test->outer_form->isClicked("search")){
    echo "Suchergebnis: " . $test->doSearch() ."<br>";

    for ($i = 1; $i<=$test->getNumHits();++$i){
        $result = $test->getSearchResult($i);
        print_r($result->fields);
    }
}
page_close();
*/
?>
