<?php
/**
 * show terms on first login and check if user accept them
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.2
 */
class TermsController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if ($GLOBALS['user']->cfg->TERMS_ACCEPTED) {
            $this->redirectUser();
        }
    }

    public function index_action()
    {
        PageLayout::setTitle(_('Nutzungsbedingungen'));

        $this->return_to = Request::get('return_to');
        $this->redirect_token = Request::get('redirect_token');

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();
            if (Request::submitted('accept')) {
                $GLOBALS['user']->cfg->store('TERMS_ACCEPTED', 1);
                $this->redirectUser();
            } else {
                $this->redirectUser('logout.php');
            }
        }
    }

    private function redirectUser($target = null)
    {
        if (Token::isValid(Request::option('redirect_token')) && Request::get('return_to')) {
            $target = Request::get('return_to') ;
        } else {
            $target = $target ?: 'dispatch.php/start';
        }
        $this->redirect(URLHelper::getURL($target));
    }
}
