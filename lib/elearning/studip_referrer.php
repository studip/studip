<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* redirect script for studip-users
*
* @author Arne Schroeder <schroeder@data-quest.de>
* @author Andre Noack <noack@data-quest.de>
*
*/

/* ILIAS Version 4.4.x */

if(file_exists("./ilias.ini.php")){
    require_once("./Services/Init/classes/class.ilIniFile.php");
    $ilIliasIniFile = new ilIniFile("./ilias.ini.php");
    $ilIliasIniFile->read();
    $serverSettings = $ilIliasIniFile->readGroup("server");
    if ($serverSettings["studip"] != 1)
    {
        echo 'Option "studip" in ilias.ini.php is not enabled. You need to add studip = "1" to the server section.';
        exit();
    }

    $cookie_path = dirname($_SERVER['PHP_SELF']);
    if (mb_substr($cookie_path,-1) != "/") {
        $cookie_path .= "/";
    }
    if (isset($_GET['sess_id']))
    {
        setcookie('PHPSESSID',$_GET['sess_id'],0, $cookie_path);
        $_COOKIE['PHPSESSID'] = $_GET['sess_id'];
    }

    if (isset($_GET['client_id']))
    {
        setcookie('ilClientId',$_GET['client_id'],0, $cookie_path);
        $_COOKIE['ilClientId'] = $_GET['client_id'];
    }

    require_once "./include/inc.header.php";

    $base_url= "ilias.php?baseClass=ilPersonalDesktopGUI";


    // redirect to specified page
    $redirect = false;
    switch($_GET['target'])
    {
    case 'start':
        switch($_GET['type'])
        {
        case 'lm':
            $base_url= "ilias.php?baseClass=ilLMPresentationGUI";
            break;
        case 'tst':
            $base_url= "ilias.php?cmd=infoScreen&cmdClass=ilobjtestgui&baseClass=ilRepositoryGUI";
            break;
        case 'svy':
            $base_url= "ilias.php?cmd=infoScreen&cmdClass=ilObjSurveyGUI&baseClass=ilRepositoryGUI";
            break;
        case 'exc':
            $base_url= "ilias.php?cmd=infoScreen&cmdClass=ilExerciseHandlerGUI&baseClass=ilRepositoryGUI";
            break;
        case 'sahs':
            $base_url = "ilias.php?baseClass=ilSAHSPresentationGUI";
            break;
        case 'htlm':
            $base_url = "ilias.php?baseClass=ilHTLMPresentationGUI";
            break;
        case 'glo':
            $base_url = "ilias.php?baseClass=ilGlossaryPresentationGUI";
            break;
        case 'cat':
        case 'crs':
            $base_url= "ilias.php?cmd=render&cmdClass=ilrepositorygui&baseClass=ilRepositoryGUI";
            break;
        case 'webr':
            $base_url= "ilias.php?cmd=calldirectlink&baseClass=ilLinkResourceHandlerGUI";
            break;
        }
        break;
    case 'new':
        $base_url = "ilias.php?baseClass=ilRepositoryGUI&cmd=create&new_type=".preg_replace('/[^a-z]/', '', $_GET['type']);
        break;
    case 'edit':
        switch($_GET['type'])
        {
        case 'lm':
            $base_url = "ilias.php?baseClass=ilLMEditorGUI";
            break;
        case 'tst':
            $base_url = "ilias.php?baseClass=ilObjTestGUI";
            break;
        case 'sahs':
            $base_url = "ilias.php?baseClass=ilSAHSEditGUI";
            break;
        case 'htlm':
            $base_url = "ilias.php?baseClass=ilHTLMEditorGUI";
            break;
        case 'glo':
            $base_url = "ilias.php?baseClass=ilGlossaryEditorGUI";
            break;
        case 'svy':
            $base_url = "ilias.php?baseClass=ilObjSurveyGUI";
            break;
        case 'exc':
            $base_url = "ilias.php?baseClass=ilExerciseHandlerGUI";
            break;
        case 'webr':
            $base_url = "ilias.php?baseClass=ilLinkResourceHandlerGUI";
            break;
        }
        break;
    }
    if ($base_url)
    {
        $base_url .= "&ref_id=".(int)$_GET['ref_id'];
        $base_url = html_entity_decode($ilCtrl->appendRequestTokenParameterString($base_url));
        header("Location: " . $base_url);
        exit();
    }
}
?>