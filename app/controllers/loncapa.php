<?php
require_once 'app/controllers/authenticated_controller.php';

class LoncapaController extends AuthenticatedController
{
    public function enter_action()
    {
        checkObject();
        checkObjectModule("elearning_interface");

        $cms_type = Request::get('cms_type');
        $module = Request::get('module');
        $course_id = Context::getId();

        if ($GLOBALS['perm']->have_studip_perm('user', $course_id)
            && isset($GLOBALS['ELEARNING_INTERFACE_MODULES'][$cms_type]))
        {
            require_once 'lib/elearning/ELearningUtils.class.php';
            require_once 'lib/elearning/ObjectConnections.class.php';

            $object_connections = new ObjectConnections($course_id);
            $connected_modules = $object_connections->getConnections();
            $reference = "{$cms_type}_loncapa_{$module}";

            if (isset($connected_modules[$reference])
                && ELearningUtils::isCMSActive($cms_type))
            {
                ELearningUtils::loadClass($cms_type);
                $lclink = new LonCapaConnectedLink($cms_type);
                $this->redirect($lclink->getRedirectUrl($module, $course_id));
                return;
            }
        }
        throw new AccessDeniedException(_('LonCapa Zugang nicht erlaubt'));
    }
}
