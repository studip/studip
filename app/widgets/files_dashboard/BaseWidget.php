<?php

namespace Widgets\FilesDashboard;

use Icon;
use Widgets\WidgetAction;
use Widgets\Element;
use Widgets\Response;

/**
 * Base Widget of all files dashboard widgets.
 *
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 4.1
 */
abstract class BaseWidget extends \Widgets\Widget
{
    /**
     * Retrieve a list of files and their folders of a range to be
     * displayed in the widget element. Similar to FileManager::getFolderFilesRecursive.
     *
     * @param Range $range The range whose files and folders shall be retrieved
     * @param mixed $scope Optional scope which was given to BaseWidget::getContent
     *
     * @return mixed[] a mixed array containing instances of
     *                 FolderType in 'folders' and instances of FileRef in 'files'
     */
    abstract protected function getFilesAndFolders(\Range $range, $scope);

    /**
     * {@inheritdoc}
     */
    public function suitableForRange(\Range $range, $scope = null)
    {
        return $range->getRangeType() === 'user' && $scope === 'dashboard';
    }

    /**
     * {@inheritdoc}
     */
    public function getContent(\Range $range, $scope)
    {
        return $this
            ->getTemplate('base-compact.php')
            ->render($this->getVariables($range, $scope));
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * {@inheritdoc}
     */
    public function getActions(\Range $range, $scope)
    {
        $list = function ($element) {
            $action = new WidgetAction(_('Listenansicht im Dialog'));
            $action->setIcon(Icon::create('maximize', Icon::ROLE_CLICKABLE, ['size' => 20]));
            $action->setCallback([$this, 'getListTemplate']);
            $action->setAttributes(
                [
                    'href' => $this->url_for('list'),
                    'data-dialog' => 'size=big',
                ]
            );

            return $action;
        };

        $saveConfiguration = function ($element) {
            $action = new WidgetAction(_('Speichern'));
            $action->setCallback([$element, 'saveConfiguration']);
            $action->hasIcon(false);

            return $action;
        };

        return array_filter(
            [
                'list' => $list($this),
                'config' => $this->createConfigurationAction(),
                'saveConfiguration' => $saveConfiguration($this),
            ]
        );
    }

    // ***** CONFIGURATION *****

    /**
     * This method creates the `config` action if the widget has a
     * configuration.
     *
     * @return WidgetAction the created `config` action
     */
    protected function createConfigurationAction()
    {
        if (!$this->hasConfiguration()) {
            return null;
        }

        $action = new WidgetAction(_('Konfigurieren'));
        $action->setIcon(Icon::create('edit', Icon::ROLE_CLICKABLE, ['size' => 20]));
        $action->setCallback([$this, 'getConfigurationTemplate']);
        $action->setAttributes(
            [
                'href' => $this->url_for('config'),
                'data-dialog' => 'size=auto',
            ]
        );

        return $action;
    }

    /**
     * If an element of a widget needs a configuration icon in its
     * header bar, this method should return true.
     *
     * @return bool return true if the widget needs a configuration
     *              page or false if it does not
     */
    protected function hasConfiguration()
    {
        return false;
    }

    /**
     * If this widget has a `config` action, this method returns the
     * the template to be shown.
     *
     * @param Element  $element  the widget element whose `config`
     *                           action shall be shown
     * @param Response $response a response object given to all widget
     *                           actions
     *
     * @return mixed Content of the response (might be a string or
     *               a flexi template)
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getConfigurationTemplate(Element $element, Response $response)
    {
        return null;
    }

    /**
     * If this widget has a `config` action, this method may store the
     * configuration.
     *
     * @param Element  $element  the widget element whose `config`
     *                           action was performed
     * @param Response $response a response object given to all widget
     *                           actions
     *
     * @return mixed Content of the response (might be a string or
     *               a flexi template)
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function saveConfiguration(Element $element, Response $response)
    {
        $response->addHeader('X-Dialog-Close', 1);

        return false;
    }

    // ***** LIST VIEW *****

    /**
     * This method is the callback of the `list` action. It returns a
     * list view of the results of calling self::getFilesAndFolders.
     *
     * @param Element  $element  the widget element whose `list`
     *                           action is performing
     * @param Response $response a response object given to all widget
     *                           actions
     *
     * @return mixed Content of the response (might be a string or
     *               a flexi template)
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getListTemplate(Element $element, Response $response)
    {
        $response->addHeader('X-Title', _('Listenansicht').': '.rawurlencode($this->getTitle()));
        $response->addHeader(
            'X-Dialog-Execute',
            'STUDIP.FilesDashboard.enhanceList(:element_id)'
        );

        return $this->getTemplate(
            'base-list.php',
            $this->getVariables($GLOBALS['user']->getAuthenticatedUser(), 'dashboard')
        );
    }

    // ***** HELPERS *****

    /**
     * This method return all the variables used for the templates of
     * the `basic` widget view and of the `list` view.
     *
     * @param Range $range The range whose files and folders shall be retrieved
     *
     * @return array an array of all the template variables
     */
    protected function getVariables(\Range $range, $scope)
    {
        return array_merge(
            [
                'options' => array_merge($this->getDefaultOptions(), $this->getOptions()),
                'controller' => $this->getFilesController(),
            ],
            $this->getFilesAndFolders($range, $scope)
        );
    }

    /**
     * All widget have options. This method may be overridden to
     * defines default option values.
     *
     * @see Widget::setOptions
     * @see Widget::getOptions
     *
     * @return array an array of default option values
     */
    protected function getDefaultOptions()
    {
        return [];
    }

    /**
     * This helper method creates a \FilesController which is used by
     * the templates of the `basic` and `list` view action.
     *
     * @see BaseWidget::getVariables
     *
     * @return FilesController an instance of a FilesController
     */
    protected function getFilesController()
    {
        require_once 'app/controllers/files.php';

        return new \FilesController(new \StudipDispatcher());
    }

    // ***** TEMPLATE HELPERS *****

    /**
     * This method creates a string containing a 'title' of a folder.
     *
     * @param FolderType $folder the folder a 'title' shall be created
     *
     * @return return_type the 'title' of a folder
     */
    public function getRangeLabel(\FolderType $folder)
    {
        switch ($folder->range_type) {
            case 'course':
                $label = $folder->course->name;
                break;

            case 'institute':
                $label = $folder->institute->name;
                break;

            case 'message':
                $label = _('Nachrichtenanhang');
                break;

            case 'user':
                $label = $folder->user->getFullName('no_title_rev');
                break;

            default:
                $label = null;
                break;
        }

        return $label;
    }
}
