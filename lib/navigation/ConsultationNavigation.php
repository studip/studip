<?php
class ConsultationNavigation extends Navigation
{
    protected $range;

    public function __construct(Range $range)
    {
        $this->range = $range;

        parent::__construct($range->getConfiguration()->CONSULTATION_TAB_TITLE);
    }

    public function initItem()
    {
        parent::initItem();

        if ($this->range->isEditableByUser()) {
            $this->setURL('dispatch.php/consultation/admin');
        }
    }

    /**
     * Determine whether this navigation item is active.
     */
    public function isActive()
    {
        $active = parent::isActive();

        if ($active) {
            if ($this->range instanceof User) {
                URLHelper::addLinkParam('username', $this->range->username);
            } elseif ($this->range instanceof Course || $this->range instanceof Institute) {
                URLHelper::addLinkParam('cid', $this->range->id);
            }
        }

        return $active;
    }

    /**
     * Initialize the subnavigation of this item. This method
     * is called once before the first item is added or removed.
     */
    public function initSubNavigation()
    {
        parent::initSubNavigation();

        if ($this->range->isEditableByUser()) {
            $this->addSubNavigation('admin', new Navigation(
                _('Verwaltung'),
                'dispatch.php/consultation/admin'
            ));

            return;
        }

        if ($this->range instanceof User) {
            // Permissions that are allowed to book reservervations
            $allowed = ['user', 'autor', 'tutor'];
            if (Config::get()->CONSULTATION_ALLOW_DOCENTS_RESERVING) {
                $allowed[] = 'dozent';
            }

            // User does not have required permissions
            if (!in_array($GLOBALS['user']->perms, $allowed)) {
                return null;
            }
        }

        // Create visitor navigation
        $this->addSubNavigation('overview', new Navigation(_('Übersicht'), 'dispatch.php/consultation/overview'));
        $this->addSubNavigation('booked', new Navigation(_('Meine Buchungen'), 'dispatch.php/consultation/overview/booked'));
    }
}
