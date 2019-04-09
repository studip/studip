<?php
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

$_GET['cancel_login'] = '1';

require '../lib/bootstrap.php';

use Studip\Button, Studip\LinkButton;

ob_start();

page_open(['sess' => 'Seminar_Session', 'auth' => 'Seminar_Default_Auth', 'perm' => 'Seminar_Perm', 'user' => 'Seminar_User']);

function head($headline, $red=False) {
    echo sprintf('<h1>%s</h1>', $headline);
}

function footer() {
}

function reenter_mail() {
    echo '<br>';
    echo '<form action="' . URLHelper::getLink() . '" method="post" class="default">';
    echo '<fieldset>';
    echo '<legend>'._('Sollten Sie keine E-Mail erhalten haben, können Sie sich einen neuen Aktivierungsschlüssel zuschicken lassen. Geben Sie dazu Ihre gewünschte E-Mail-Adresse unten an') . '</legend>'
        . CSRFProtection::tokenTag()
        .'<input type="hidden" name="uid" value="'. htmlReady(Request::option('uid')) .'">'
        .'<label>' . _('E-Mail')
        .'<input type="email" name="email1" required>'
        .'</label>'
        .'<label>' . _('Wiederholung')
        .'<input type="email" name="email2" required>'
        .'</label>';
    echo '</fieldset>';
    echo '<footer>' . Button::createAccept() . '</footer>';
    echo '</form>';
}

function mail_explain() {
    echo '<form action="' . URLHelper::getLink() . '" method="post" class="default">';
    echo '<fieldset>';
    echo '<legend>' .  _('Sie haben Ihre E-Mail-Adresse geändert. 
    Um diese frei zu schalten müssen Sie den Ihnen an Ihre neue Adresse zugeschickten Aktivierungs Schlüssel im unten stehenden Eingabefeld eintragen.') . '</legend>';
    echo CSRFProtection::tokenTag();
    echo '<label>' . _('Aktivierungs Schlüssel')
        .'<input type="text" name="key"><input name="uid" type="hidden" value="'.htmlReady(Request::option('uid')).'">';
    echo '</fieldset>';
    echo '<footer>' . Button::createAccept() . '</footer>';
    echo '</form>';

}

if(!Request::option('uid'))
    header("Location: index.php");

URLHelper::addLinkParam('cancel_login', 1);

// set up user session
include 'lib/seminar_open.php';

// display header
PageLayout::setTitle(_('E-Mail Aktivierung'));

$uid = Request::option('uid');
if(Request::get('key') !== null) {

    $db = DBManager::get();
    $sth = $db->prepare("SELECT validation_key FROM auth_user_md5 WHERE user_id=?");
    $sth->execute([$uid]);
    $result = $sth->fetch();
    $key = $result['validation_key'];
    
    if(Request::quoted('key') == $key) {
        $sth = $db->prepare("UPDATE auth_user_md5 SET validation_key='' WHERE user_id=?");
        $sth->execute([$uid]);
        unset($_SESSION['semi_logged_in']);
        head(PageLayout::getTitle());
        PageLayout::postSuccess(_('Ihre E-Mail-Adresse wurde erfolgreich geändert.'));
        printf(' <a href="' . URLHelper::getLink('index.php') . '">%s</a>', _('Zum Login'));
    } else if ($key == '') {
        head(PageLayout::getTitle());
        PageLayout::postInfo(_('Ihre E-Mail-Adresse ist bereits geändert.'));
        printf(' <a href="' . URLHelper::getLink('index.php') . '">%s</a>', _('Zum Login'));
    } else {
        if (Request::get('key')) {
            PageLayout::postError(_("Falscher Bestätigungscode."));
        }
        head(PageLayout::getTitle());
        mail_explain();
        if($_SESSION['semi_logged_in'] == Request::option('uid')) {
            reenter_mail();
        } else {
            printf(_('Sie können sich %seinloggen%s und sich den Bestätigungscode neu oder an eine andere E-Mail-Adresse schicken lassen.'),
                    '<a href="' . URLHelper::getLink('index.php?again=yes') . '">', '</a>');
        }
    }

// checking semi_logged_in is important to avoid abuse
} else if(Request::get('email1') && Request::get('email2') && $_SESSION['semi_logged_in'] == Request::option('uid')) {
    if(Request::get('email1') == Request::get('email2')) {
        // change mail
        $tmp_user = User::find(Request::option('uid'));
        if($tmp_user && $tmp_user->changeEmail(Request::quoted('email1'), true)) {
            $_SESSION['semi_logged_in'] = False;
        }
        
    } else {
        PageLayout::postError(_('Die eingegebenen E-Mail-Adressen stimmen nicht überein. Bitte überprüfen Sie Ihre Eingabe.'));
    }
    mail_explain();
    reenter_mail();
} else {
    // this never happens unless someone manipulates urls (or the presented link within the mail is broken)
    head(PageLayout::getTitle());
    mail_explain();
    reenter_mail();
}

$template = $GLOBALS['template_factory']->open('layouts/base.php');
$template->content_for_layout = ob_get_clean();
echo $template->render();
page_close();
