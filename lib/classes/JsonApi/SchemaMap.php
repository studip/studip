<?php

namespace JsonApi;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SchemaMap
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(\Slim\Container $container)
    {
        return [
            \Slim\Route::class => \JsonApi\Schemas\SlimRoute::class,

            \JsonApi\Models\ScheduleEntry::class => \JsonApi\Schemas\ScheduleEntry::class,

            \BlubberComment::class => \JsonApi\Schemas\BlubberComment::class,
            \BlubberStatusgruppeThread::class => \JsonApi\Schemas\BlubberStatusgruppeThread::class,
            \BlubberThread::class => \JsonApi\Schemas\BlubberThread::class,

            \CalendarEvent::class => \JsonApi\Schemas\CalendarEvent::class,
            \ConfigValue::class => \JsonApi\Schemas\ConfigValue::class,
            \CourseEvent::class => \JsonApi\Schemas\CourseEvent::class,
            \ContentTermsOfUse::class => \JsonApi\Schemas\ContentTermsOfUse::class,
            \Course::class => \JsonApi\Schemas\Course::class,
            \CourseMember::class => \JsonApi\Schemas\CourseMember::class,
            \FeedbackElement::class => \JsonApi\Schemas\FeedbackElement::class,
            \FeedbackEntry::class => \JsonApi\Schemas\FeedbackEntry::class,
            \JsonApi\Models\ForumCat::class => \JsonApi\Schemas\ForumCategory::class,
            \JsonApi\Models\ForumEntry::class => \JsonApi\Schemas\ForumEntry::class,
            \Institute::class => \JsonApi\Schemas\Institute::class,
            \InstituteMember::class => \JsonApi\Schemas\InstituteMember::class,
            \Message::class => \JsonApi\Schemas\Message::class,
            \SemClass::class => \JsonApi\Schemas\SemClass::class,
            \Semester::class => \JsonApi\Schemas\Semester::class,
            \SemType::class => \JsonApi\Schemas\SemType::class,
            \SeminarCycleDate::class => \JsonApi\Schemas\SeminarCycleDate::class,
            \Statusgruppen::class => \JsonApi\Schemas\StatusGroup::class,
            \JsonApi\Models\Studip::class => \JsonApi\Schemas\Studip::class,
            \JsonApi\Models\StudipProperty::class => \JsonApi\Schemas\StudipProperty::class,
            \StudipComment::class => \JsonApi\Schemas\StudipComment::class,
            \StudipNews::class => \JsonApi\Schemas\StudipNews::class,
            \StudipStudyArea::class => \JsonApi\Schemas\StudyArea::class,
            \WikiPage::class => \JsonApi\Schemas\WikiPage::class,
            \Studip\Activity\Activity::class => \JsonApi\Schemas\Activity::class,
            \User::class => \JsonApi\Schemas\User::class,
            \File::class => \JsonApi\Schemas\File::class,
            \FileRef::class => \JsonApi\Schemas\FileRef::class,
            \FolderType::class => \JsonApi\Schemas\Folder::class,

            \Courseware\Block::class => \JsonApi\Schemas\Courseware\Block::class,
            \Courseware\BlockComment::class => \JsonApi\Schemas\Courseware\BlockComment::class,
            \Courseware\BlockFeedback::class => \JsonApi\Schemas\Courseware\BlockFeedback::class,
            \Courseware\Container::class => \JsonApi\Schemas\Courseware\Container::class,
            \Courseware\Instance::class => \JsonApi\Schemas\Courseware\Instance::class,
            \Courseware\StructuralElement::class => \JsonApi\Schemas\Courseware\StructuralElement::class,
            \Courseware\UserDataField::class => \JsonApi\Schemas\Courseware\UserDataField::class,
            \Courseware\UserProgress::class => \JsonApi\Schemas\Courseware\UserProgress::class,
        ];
    }
}
