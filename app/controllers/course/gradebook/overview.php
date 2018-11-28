<?php

require_once __DIR__.'/template_helpers.php';

/**
 * @SuppressWarnings(PHPMD.CamelCaseClassName)
 */
class Course_Gradebook_OverviewController extends AuthenticatedController
{
    use GradebookTemplateHelpers;

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function index_action()
    {
        if ($this->viewerIsStudent()) {
            $route = 'course/gradebook/students';
        } elseif ($this->viewerIsLecturer()) {
            $route = 'course/gradebook/lecturers';
        } else {
            throw new AccessDeniedException();
        }

        $this->redirect($route);
    }
}
