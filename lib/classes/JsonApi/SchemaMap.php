<?php

namespace JsonApi;

class SchemaMap
{
    public function __invoke(\Slim\Container $container)
    {
        return [
            \Slim\Route::class => \JsonApi\Schemas\SlimRoute::class,

            \JsonApi\Models\ScheduleEntry::class => \JsonApi\Schemas\ScheduleEntry::class,

            \BlubberComment::class => \JsonApi\Schemas\BlubberComment::class,
            \BlubberStatusgruppeThread::class => \JsonApi\Schemas\BlubberStatusgruppeThread::class,
            \BlubberThread::class => \JsonApi\Schemas\BlubberThread::class,

            \CalendarEvent::class => \JsonApi\Schemas\CalendarEvent::class,
            \CourseEvent::class => \JsonApi\Schemas\CourseEvent::class,
            \ContentTermsOfUse::class => \JsonApi\Schemas\ContentTermsOfUse::class,
            \Course::class => \JsonApi\Schemas\Course::class,
            \CourseMember::class => \JsonApi\Schemas\CourseMember::class,
            \JsonApi\Models\ForumCat::class => \JsonApi\Schemas\ForumCategory::class,
            \JsonApi\Models\ForumEntry::class => \JsonApi\Schemas\ForumEntry::class,
            \Institute::class => \JsonApi\Schemas\Institute::class,
            \InstituteMember::class => \JsonApi\Schemas\InstituteMember::class,
            \Message::class => \JsonApi\Schemas\Message::class,
            \Semester::class => \JsonApi\Schemas\Semester::class,
            \SeminarCycleDate::class => \JsonApi\Schemas\SeminarCycleDate::class,
            \Statusgruppen::class => \JsonApi\Schemas\Statusgruppen::class,
            \JsonApi\Models\Studip::class => \JsonApi\Schemas\Studip::class,
            \JsonApi\Models\StudipProperty::class => \JsonApi\Schemas\StudipProperty::class,
            \StudipComment::class => \JsonApi\Schemas\StudipComment::class,
            \StudipNews::class => \JsonApi\Schemas\StudipNews::class,
            \WikiPage::class => \JsonApi\Schemas\WikiPage::class,
            \Studip\Activity\Activity::class => \JsonApi\Schemas\Activity::class,
            \User::class => \JsonApi\Schemas\User::class,
            \File::class => \JsonApi\Schemas\File::class,
            \FileRef::class => \JsonApi\Schemas\FileRef::class,
            \FolderType::class => \JsonApi\Schemas\Folder::class,

        ];
    }
}
