<?php
/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */
class ConsultationModule extends CorePlugin implements StudipModule, SystemPlugin, PrivacyPlugin, HomepagePlugin
{
    public function __construct()
    {
        parent::__construct();

        NotificationCenter::on('UserDidDelete', function ($event, $user) {
            // Delete consultation bookings and slots
            ConsultationBooking::deleteByUser_id($user->id);
            ConsultationBlock::deleteByTeacher_id($user->id);
            ConsultationBlock::deleteBySQL("range_id = ? AND range_type = 'user'", [$user->id]);
        });
        NotificationCenter::on('CourseDidDelete', function ($event, $course) {
            // Delete consultation blocks
            ConsultationBlock::deleteBySQL("range_id = ? AND range_type = 'course'", [$course->id]);
        });
        NotificationCenter::on('InstituteDidDelete', function ($event, $institute) {
            // Delete consultation blocks
            ConsultationBlock::deleteBySQL("range_id = ? AND range_type = 'institute'", [$institute->id]);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function isActivatableForContext(Range $context)
    {
        // Consultations globally disabled?
        if (!Config::get()->CONSULTATION_ENABLED) {
            return false;
        }

        // Context is user and current user has required permission?
        if ($context instanceof User) {
            return $GLOBALS['perm']->have_perm(Config::get()->CONSULTATION_REQUIRED_PERMISSION, $context->getRangeId());
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getIconNavigation($course_id, $last_visit, $user_id)
    {
        // TODO

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabNavigation($course_id)
    {
        if ($GLOBALS['user']->id !== 'nobody') {
            return ['consultation' => new ConsultationNavigation(RangeFactory::find($course_id))];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getInfoTemplate($course_id)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getHomepageTemplate($user_id)
    {
        return null;
    }


    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        return [
            'summary'     => _('Generische Terminvergabe'),
            'description' => _('Über die generische Terminvergabe können jegliche Formen von Terminen ' .
                               'angeboten werden, zu denen sich Personen oder auch Gruppen von Personen ' .
                               'anmelden können.'),
            'category'    => _('Kommunikation und Zusammenarbeit'),
            'keywords'    => _('Terminvergabe, Sprechstunden'),
            'displayname' => _('Terminvergabe'),
            'icon'        => Icon::create('consultation', Icon::ROLE_INFO),
            // 'screenshots' => [
            //     'path'     => '../../assets/images/plus/screenshots/Consultations',
            //     'pictures' => [
            //         [
            //             'source' => 'Lti_tool_demo.jpg',
            //             'title'  => 'Beispiel für Wordpress-Einbindung'
            //         ],
            //     ]
            // ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function exportUserData(StoredUserData $storage)
    {
        $storage->addTabularData(
            _('Terminblöcke'),
            'consultation_blocks',
            ConsultationBlock::findAndMapBySQL(
                function ($block) {
                    return $block->toRawArray();
                },
                "teacher_id = :user_id OR (range_id = :user_id AND range_type = 'user')",
                [':user_id' => $storage->user_id]
            )
        );
        $storage->addTabularData(
            _('Terminbuchungen'),
            'consultation_bookings',
            ConsultationBooking::findAndMapBySQL(
                function ($booking) {
                    return $booking->toRawArray();
                },
                'user_id = ?',
                [$storage->user_id]
            )
        );
    }
}
