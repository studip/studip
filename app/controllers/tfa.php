<?php
class TfaController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        Navigation::activateItem('/profile/settings/tfa');
        PageLayout::setTitle(_('Zwei-Faktor-Authentifizierung'));

        $this->user = User::findCurrent();
        $this->is_root = $GLOBALS['perm']->have_perm('root');

        if ($this->is_root && Request::submitted('username')) {
            $username = Request::username('username');
            $this->user = User::findOneByUsername($username);

            if (!$this->user) {
                throw new Exception(_('Diesen Nutzer gibt es nicht'));
            }

            URLHelper::addLinkParam('username', Request::username('username'));

            PageLayout::postMessage(
                MessageBox::info(sprintf(
                    _('Daten von: %1$s (%2$s), Status: %3$s'),
                    htmlReady($this->user->getFullName()),
                    htmlReady($username),
                    htmlReady($this->user->perms)
                )),
                'settings-user-anncouncement'
            );
        }

        $this->secret = new TFASecret($this->user->id);
    }

    public function index_action()
    {
        if ($this->secret->isNew()) {
            $this->render_action('setup');
        } elseif (!$this->secret->confirmed) {
            $this->confirm_action();
        }
    }

    public function setup_action()
    {
    }

    public function create_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        $this->secret->type = Request::option('type', 'email');
        $this->secret->store();

        PageLayout::postSuccess(_('Die Zwei-Faktor-Authentifizierung wurde eingerichtet'));
        $this->redirect('tfa/confirm');
    }

    public function confirm_action()
    {
        if ($this->secret->isNew()) {
            $this->redirect('tfa/index');
            return;
        }

        TwoFactorAuth::get()->confirm(
            '2fa',
            _('Bitte bestätigen Sie die Aktivierung.'),
            ['global' => true]
        );

        PageLayout::postSuccess(_('Die Zwei-Faktor-Authentifizierung wurde aktiviert.'));
        $this->redirect('tfa/index');
    }

    public function abort_action()
    {
        if ($this->secret && $this->secret->confirmed) {
            $this->redirect('tfa/revoke');
            return;
        }

        $this->secret->delete();

        PageLayout::postSuccess(_('Das Einrichten der Zwei-Faktor-Authentifizierung wurde abgebrochen.'));
        $this->redirect('tfa/index');
    }

    public function revoke_action()
    {
        if (!$this->is_root || $this->user->id === $GLOBALS['user']->id) {
            TwoFactorAuth::get()->confirm(
                '2fa-revoke',
                _('Bestätigen Sie das Aufheben der Methode')
            );
        }

        $this->secret->delete();

        if (!$this->is_root || $this->user->id === $GLOBALS['user']->id) {
            TwoFactorAuth::removeCookie();
        }

        PageLayout::postSuccess(_('Die Zwei-Faktor-Authentifizierung wurde deaktiviert.'));
        $this->redirect('tfa/index');
    }
}
