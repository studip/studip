<?php

namespace JsonApi;

use JsonApi\Contracts\JsonApiPlugin;
use JsonApi\Middlewares\Authentication;
use JsonApi\Middlewares\DangerousRouteHandler;
use JsonApi\Middlewares\JsonApi as JsonApiMiddleware;
use JsonApi\Middlewares\StudipMockNavigation;
use JsonApi\Providers\StudipServices;

/**
 * Diese Klasse ist die JSON-API-Routemap, in der alle Routen
 * registriert werden und die Middleware hinzugefügt wird, die
 * JSON-API spezifische Fehlerbehandlung usw. übernimmt.
 *
 * Routen der Kernklasen sind hier explizit vermerkt.
 *
 * Routen aus Plugins werden über die PluginEngine abgefragt. Plugins
 * können genau dann eigene Routen registrieren, wenn sie das
 * Interface \JsonApi\Contracts\JsonApiPlugin implementieren.
 *
 * Routen können entweder mit Autorisierung oder auch ohne eingetragen
 * werden. Autorisierte Kernrouten werden in
 * RouteMap::authenticatedRoutes vermerkt. Kernrouten ohne
 * notwendige Autorisierung werden in
 * RouteMap::unauthenticatedRoutes registriert. Routen aus Plugins
 * werden jeweils in den Methoden
 * \JsonApi\Contracts\JsonApiPlugin::registerAuthenticatedRoutes und
 * \JsonApi\Contracts\JsonApiPlugin::registerUnauthenticatedRoutes
 * eingetragen.
 *
 * Zu authentifizierende Routen werden in \JsonApi\Middlewares\Authentication
 * authentifiziert.
 *
 * Wie Routen registriert werden, kann man im `User Guide` des
 * Slim-Frameworks nachlesen
 * (http://www.slimframework.com/docs/objects/router.html#how-to-create-routes)
 *
 * Route-Handler können als Funktionen, in der Slim-Syntax
 * "Klassenname:Methodenname" oder auch mit dem Klassennamen einer
 * Klasse, die __invoke implementiert, angegeben werden. Die
 * __invoke-Variante wird hier sehr empfohlen.
 *
 * Beispiel:
 *
 *   use Studip\MeineRoute;
 *
 *   $this->app->post('/article/{id}/comments', MeineRoute::class);
 *
 *
 * @see \JsonApi\Middlewares\JsonApi
 * @see \JsonApi\Middlewares\Authentication
 * @see \JsonApi\Contracts\JsonApiPlugin
 * @see http://www.slimframework.com/docs/objects/router.html#how-to-create-routes
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RouteMap
{
    /**
     * Der Konstruktor.
     *
     * @param \Slim\App $app die Slim-Applikation, in der die Routen
     *                       definiert werden sollen
     */
    public function __construct(\Slim\App $app)
    {
        $this->app = $app;
    }

    /**
     * Hier werden die Routen tatsächlich eingetragen.
     * Autorisierte Routen werden mit der Middleware
     * \JsonApi\Middlewares\Authentication ausgestattet und in
     * RouteMap::authenticatedRoutes eingetragen. Routen ohne
     * Autorisierung werden in RouteMap::unauthenticatedRoutes vermerkt.
     */
    public function __invoke()
    {
        $corsOrigin = \Config::get()->getValue('JSONAPI_CORS_ORIGIN');
        if (is_array($corsOrigin) && count($corsOrigin)) {
            $this->app->add(
                new \Tuupola\Middleware\Cors(
                    [
                        'origin' => $corsOrigin,
                        'methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
                        'headers.allow' => [
                            'Accept',
                            'Accept-Encoding',
                            'Accept-Language',
                            'Authorization',
                            'Content-Type',
                            'Origin',
                        ],
                        'headers.expose' => ['Etag'],
                        'credentials' => true,
                        'cache' => 86400,
                    ]
                )
            );
        }

        $this->app->add(new JsonApiMiddleware($this->app));

        $this->app->add(new StudipMockNavigation());

        $this->app->group('', [$this, 'authenticatedRoutes'])
            ->add(new Authentication($this->getAuthenticator()));
        $this->app->group('', [$this, 'unauthenticatedRoutes']);

        $this->app->get('/discovery', Routes\DiscoveryIndex::class);
    }

    /**
     * Hier werden autorisierte (Kern-)Routen explizit vermerkt.
     * Außerdem wird über die \PluginEngine allen JsonApiPlugins die
     * Möglichkeit gegeben, sich hier einzutragen.
     */
    public function authenticatedRoutes()
    {
        \PluginEngine::sendMessage(JsonApiPlugin::class, 'registerAuthenticatedRoutes', $this->app);

        $this->app->get('/users', Routes\Users\UsersIndex::class);
        $this->app->get('/users/me', Routes\Users\UsersShow::class);
        $this->app->get('/users/{id}', Routes\Users\UsersShow::class);
        $this->app->delete('/users/{id}', Routes\Users\UsersDelete::class)->add(DangerousRouteHandler::class);

        $this->app->get('/users/{id}/activitystream', Routes\ActivityStreamShow::class);
        $this->app->get('/users/{id}/institute-memberships', Routes\InstituteMemberships\ByUserIndex::class);
        $this->app->get('/users/{id}/course-memberships', Routes\CourseMemberships\ByUserIndex::class);
        $this->app->get('/course-memberships/{id}', Routes\CourseMemberships\CourseMembershipsShow::class);
        $this->app->patch('/course-memberships/{id}', Routes\CourseMemberships\CourseMembershipsUpdate::class);

        $this->app->get('/users/{id}/schedule', Routes\Schedule\UserScheduleShow::class)->setName('get-schedule');
        $this->app->get('/schedule-entries/{id}', Routes\Schedule\ScheduleEntriesShow::class);
        $this->app->get('/seminar-cycle-dates/{id}', Routes\Schedule\SeminarCycleDatesShow::class);

        $this->app->get('/users/{id}/config-values', Routes\ConfigValues\ByUserIndex::class);
        $this->app->get('/config-values/{id}', Routes\ConfigValues\ConfigValuesShow::class);
        $this->app->patch('/config-values/{id}', Routes\ConfigValues\ConfigValuesUpdate::class);


        $this->addAuthenticatedBlubberRoutes();
//        $this->addAuthenticatedConsultationRoutes();
        $this->addAuthenticatedContactsRoutes();
        $this->addAuthenticatedCoursesRoutes();
        $this->addAuthenticatedCoursewareRoutes();
        $this->addAuthenticatedEventsRoutes();
        $this->addAuthenticatedFeedbackRoutes();
        $this->addAuthenticatedFilesRoutes();
        $this->addAuthenticatedForumRoutes();
        $this->addAuthenticatedInstitutesRoutes();
        $this->addAuthenticatedMessagesRoutes();
        $this->addAuthenticatedNewsRoutes();
        $this->addAuthenticatedStudyAreasRoutes();
        $this->addAuthenticatedWikiRoutes();
    }

    /**
     * Hier werden unautorisierte (Kern-)Routen explizit vermerkt.
     * Außerdem wird über die \PluginEngine allen JsonApiPlugins die
     * Möglichkeit gegeben, sich hier einzutragen.
     */
    public function unauthenticatedRoutes()
    {
        \PluginEngine::sendMessage(JsonApiPlugin::class, 'registerUnauthenticatedRoutes', $this->app);

        $this->app->get('/semesters', Routes\SemestersIndex::class);
        $this->app->get('/semesters/{id}', Routes\SemestersShow::class);

        $this->app->get('/studip/properties', Routes\Studip\PropertiesIndex::class);
    }

    private function getAuthenticator()
    {
        $container = $this->app->getContainer();

        return $container[StudipServices::AUTHENTICATOR];
    }

    private function addAuthenticatedBlubberRoutes()
    {
        // find BlubberThreads
        $this->app->get('/courses/{id}/blubber-threads', Routes\Blubber\ThreadsIndex::class)
                  ->setArgument('type', 'course');
        $this->app->get('/institutes/{id}/blubber-threads', Routes\Blubber\ThreadsIndex::class)
                  ->setArgument('type', 'institute');
        $this->app->get('/studip/blubber-threads', Routes\Blubber\ThreadsIndex::class)
                  ->setArgument('type', 'public');
        $this->app->get('/users/{id}/blubber-threads', Routes\Blubber\ThreadsIndex::class)
                  ->setArgument('type', 'private');
        $this->app->get('/blubber-threads', Routes\Blubber\ThreadsIndex::class)
                  ->setArgument('type', 'all');
        $this->app->get('/blubber-threads/{id}', Routes\Blubber\ThreadsShow::class);

        // create, read, update and delete BlubberComments
        $this->app->get('/blubber-threads/{id}/comments', Routes\Blubber\CommentsByThreadIndex::class);
        $this->app->post('/blubber-threads/{id}/comments', Routes\Blubber\CommentsCreate::class);
        $this->app->get('/blubber-comments', Routes\Blubber\CommentsIndex::class);
        $this->app->get('/blubber-comments/{id}', Routes\Blubber\CommentsShow::class);
        $this->app->patch('/blubber-comments/{id}', Routes\Blubber\CommentsUpdate::class);
        $this->app->delete('/blubber-comments/{id}', Routes\Blubber\CommentsDelete::class);

        // REL mentions
        $this->addRelationship('/blubber-threads/{id}/relationships/mentions', Routes\Blubber\Rel\Mentions::class);
    }

    private function addAuthenticatedConsultationRoutes()
    {
        $this->app->get('/users/{id}/consultations', Routes\Consultations\BlocksByUserIndex::class);

        $this->app->get('/consultation-blocks/{id}', Routes\Consultations\BlockShow::class);
        $this->app->get('/consultation-blocks/{id}/slots', Routes\Consultations\SlotsByBlockIndex::class);

        $this->app->get('/consultation-slots/{id}', Routes\Consultations\SlotShow::class);
        $this->app->get('/consultation-slots/{id}/bookings', Routes\Consultations\BookingsBySlotIndex::class);
        $this->app->post('/consultation-slots/{id}/bookings', Routes\Consultations\BookingsCreate::class);

        $this->app->get('/consultation-bookings/{id}', Routes\Consultations\BookingsShow::class);
        $this->app->delete('/consultation-bookings/{id}', Routes\Consultations\BookingsDelete::class);

//        $this->addRelationship('/users/{id}/relationships/contacts', Routes\Users\Rel\Contacts::class);
    }

    private function addAuthenticatedContactsRoutes()
    {
        $this->app->get('/users/{id}/contacts', Routes\Users\ContactsIndex::class);
        $this->addRelationship('/users/{id}/relationships/contacts', Routes\Users\Rel\Contacts::class);
    }

    private function addAuthenticatedEventsRoutes()
    {
        $this->app->get('/courses/{id}/events', Routes\Events\CourseEventsIndex::class);
        $this->app->get('/users/{id}/events', Routes\Events\UserEventsIndex::class);

        // not a JSON:API route
        $this->app->get('/users/{id}/events.ics', Routes\Events\UserEventsIcal::class);
    }

    private function addAuthenticatedFeedbackRoutes()
    {
        $this->app->get('/feedback-elements/{id}', Routes\Feedback\FeedbackElementsShow::class);
        $this->app->get('/feedback-elements/{id}/entries', Routes\Feedback\FeedbackEntriesIndex::class);
        $this->app->get('/courses/{id}/feedback-elements', Routes\Feedback\FeedbackElementsByCourseIndex::class);
        $this->app->get('/file-refs/{id}/feedback-elements', Routes\Feedback\FeedbackElementsByFileRefIndex::class);
        $this->app->get('/folders/{id}/feedback-elements', Routes\Feedback\FeedbackElementsByFolderIndex::class);

        $this->app->get('/feedback-entries/{id}', Routes\Feedback\FeedbackEntriesShow::class);
    }

    private function addAuthenticatedInstitutesRoutes()
    {
        $this->app->get('/institute-memberships/{id}', Routes\InstituteMemberships\InstituteMembershipsShow::class);
        $this->app->get('/institutes/{id}', Routes\Institutes\InstitutesShow::class);
        $this->app->get('/institutes', Routes\Institutes\InstitutesIndex::class);

        $this->app->get('/institutes/{id}/status-groups', Routes\Institutes\StatusGroupsOfInstitutes::class);
    }

    private function addAuthenticatedNewsRoutes()
    {
        $this->app->post('/courses/{id}/news', Routes\News\CourseNewsCreate::class);
        $this->app->post('/users/{id}/news', Routes\News\UserNewsCreate::class);
        $this->app->post('/news', Routes\News\StudipNewsCreate::class);
        $this->app->post('/news/{id}/comments', Routes\News\CommentCreate::class);
        $this->app->patch('/news/{id}', Routes\News\NewsUpdate::class);
        $this->app->get('/news/{id}', Routes\News\NewsShow::class);
        $this->app->get('/courses/{id}/news', Routes\News\ByCourseIndex::class);
        $this->app->get('/users/{id}/news', Routes\News\ByUserIndex::class);
        $this->app->get('/news/{id}/comments', Routes\News\CommentsIndex::class);
        $this->app->get('/news', Routes\News\ByCurrentUser::class);
        $this->app->get('/studip/news', Routes\News\GlobalNewsShow::class);
        $this->app->delete('/news/{id}', Routes\News\NewsDelete::class);
        $this->app->delete('/comments/{id}', Routes\News\CommentsDelete::class);

        // RELATIONSHIP: 'ranges'
        $this->addRelationship('/news/{id}/relationships/ranges', Routes\News\Rel\Ranges::class);
    }

    private function addAuthenticatedStudyAreasRoutes()
    {
        $this->app->get('/study-areas', Routes\StudyAreas\StudyAreasIndex::class);
        $this->app->get('/study-areas/{id}', Routes\StudyAreas\StudyAreasShow::class);

        $this->app->get('/study-areas/{id}/children', Routes\StudyAreas\ChildrenOfStudyAreas::class);
        $this->app->get('/study-areas/{id}/courses', Routes\StudyAreas\CoursesOfStudyAreas::class);
        $this->app->get('/study-areas/{id}/institute', Routes\StudyAreas\InstituteOfStudyAreas::class);
        $this->app->get('/study-areas/{id}/parent', Routes\StudyAreas\ParentOfStudyAreas::class);
    }

    private function addAuthenticatedWikiRoutes()
    {
        $this->addRelationship('/wiki-pages/{id:.+}/relationships/parent', Routes\Wiki\Rel\ParentPage::class);
        $this->app->get('/wiki-pages/{id:.+}/children', Routes\Wiki\ChildrenIndex::class);
        $this->app->get('/wiki-pages/{id:.+}/descendants', Routes\Wiki\DescendantsIndex::class);

        $this->app->get('/courses/{id}/wiki-pages', Routes\Wiki\WikiIndex::class);
        $this->app->get('/wiki-pages/{id:.+}', Routes\Wiki\WikiShow::class)->setName('get-wiki-page');

        $this->app->post('/courses/{id}/wiki-pages', Routes\Wiki\WikiCreate::class);
        $this->app->patch('/wiki-pages/{id:.+}', Routes\Wiki\WikiUpdate::class);
        $this->app->delete('/wiki-pages/{id:.+}', Routes\Wiki\WikiDelete::class);
    }

    private function addAuthenticatedCoursesRoutes()
    {
        $this->app->get('/courses', Routes\Courses\CoursesIndex::class);
        $this->app->get('/courses/{id}', Routes\Courses\CoursesShow::class);

        $this->app->get('/users/{id}/courses', Routes\Courses\CoursesByUserIndex::class);

        $this->app->get('/courses/{id}/memberships', Routes\Courses\CoursesMembershipsIndex::class);
        $this->addRelationship('/courses/{id}/relationships/memberships', Routes\Courses\Rel\Memberships::class);

        $this->app->get('/courses/{id}/status-groups', Routes\Courses\StatusGroupsOfCourses::class);

        $this->app->get('/sem-classes', Routes\Courses\SemClassesIndex::class);
        $this->app->get('/sem-classes/{id}', Routes\Courses\SemClassesShow::class);
        $this->app->get('/sem-classes/{id}/sem-types', Routes\Courses\SemTypesBySemClassIndex::class);
        $this->app->get('/sem-types', Routes\Courses\SemTypesIndex::class);
        $this->app->get('/sem-types/{id}', Routes\Courses\SemTypesShow::class);
    }

    private function addAuthenticatedCoursewareRoutes()
    {
        $this->app->get('/{type:courses|users}/{id}/courseware', Routes\Courseware\CoursewareInstancesShow::class);
        $this->app->patch('/courseware-instances/{id}', Routes\Courseware\CoursewareInstancesUpdate::class);
        $this->addRelationship(
            '/courseware-instances/{id}/relationships/bookmarks',
            Routes\Courseware\Rel\BookmarkedStructuralElements::class
        );
        $this->app->get(
            '/courseware-instances/{id}/bookmarks',
            Routes\Courseware\BookmarkedStructuralElementsIndex::class
        );

        $this->app->get('/courseware-blocks/{id}', Routes\Courseware\BlocksShow::class);
        $this->app->post('/courseware-blocks', Routes\Courseware\BlocksCreate::class);
        $this->app->patch('/courseware-blocks/{id}', Routes\Courseware\BlocksUpdate::class);
        $this->app->delete('/courseware-blocks/{id}', Routes\Courseware\BlocksDelete::class);

        $this->addRelationship(
            '/courseware-blocks/{id}/relationships/edit-blocker',
            Routes\Courseware\Rel\BlocksEditBlocker::class
        );

        $this->addRelationship(
            '/courseware-blocks/{id}/relationships/file-refs',
            Routes\Courseware\Rel\BlocksFilerefs::class
        );
        $this->app->get('/courseware-blocks/{id}/file-refs', Routes\Courseware\BlocksListFiles::class);

        // not a JSON route
        $this->app->post('/courseware-blocks/{id}/copy', Routes\Courseware\BlocksCopy::class);

        $this->app->get('/courseware-containers/{id}', Routes\Courseware\ContainersShow::class);
        $this->app->post('/courseware-containers', Routes\Courseware\ContainersCreate::class);
        $this->app->patch('/courseware-containers/{id}', Routes\Courseware\ContainersUpdate::class);
        $this->app->delete('/courseware-containers/{id}', Routes\Courseware\ContainersDelete::class);
        $this->app->get('/courseware-containers/{id}/blocks', Routes\Courseware\BlocksIndex::class);
        $this->addRelationship(
            '/courseware-containers/{id}/relationships/blocks',
            Routes\Courseware\Rel\ContainersBlocks::class
        );
        $this->addRelationship(
            '/courseware-containers/{id}/relationships/edit-blocker',
            Routes\Courseware\Rel\ContainersEditBlocker::class
        );

        // not a JSON route
        $this->app->post('/courseware-containers/{id}/copy', Routes\Courseware\ContainersCopy::class);

        $this->app->get('/courseware-structural-elements/{id}', Routes\Courseware\StructuralElementsShow::class);
        $this->app->get('/courseware-structural-elements', Routes\Courseware\StructuralElementsIndex::class);
        $this->app->post('/courseware-structural-elements', Routes\Courseware\StructuralElementsCreate::class);
        $this->app->patch('/courseware-structural-elements/{id}', Routes\Courseware\StructuralElementsUpdate::class);
        $this->app->delete('/courseware-structural-elements/{id}', Routes\Courseware\StructuralElementsDelete::class);

        $this->app->get(
            '/courseware-structural-elements/{id}/children',
            Routes\Courseware\ChildrenOfStructuralElementsIndex::class
        );
        $this->app->get('/courseware-structural-elements/{id}/containers', Routes\Courseware\ContainersIndex::class);
        $this->addRelationship(
            '/courseware-structural-elements/{id}/relationships/containers',
            Routes\Courseware\Rel\StructuralElementsContainers::class
        );
        $this->addRelationship(
            '/courseware-structural-elements/{id}/relationships/children',
            Routes\Courseware\Rel\StructuralElementsChildren::class
        );
        $this->app->get(
            '/courseware-structural-elements/{id}/descendants',
            Routes\Courseware\DescendantsOfStructuralElementsIndex::class
        );
        $this->addRelationship(
            '/courseware-structural-elements/{id}/relationships/edit-blocker',
            Routes\Courseware\Rel\StructuralElementsEditBlocker::class
        );

        $this->app->post('/courseware-structural-elements/{id}/image', Routes\Courseware\StructuralElementsImageUpload::class);
        $this->app->delete('/courseware-structural-elements/{id}/image', Routes\Courseware\StructuralElementsImageDelete::class);

        // not a JSON route
        $this->app->post('/courseware-structural-elements/{id}/copy', Routes\Courseware\StructuralElementsCopy::class);

        $this->app->get('/courseware-blocks/{id}/user-data-field', Routes\Courseware\UserDataFieldOfBlocksShow::class);
        $this->app->get('/courseware-user-data-fields/{id}', Routes\Courseware\UserDataFieldsShow::class);
        $this->app->patch('/courseware-user-data-fields/{id}', Routes\Courseware\UserDataFieldsUpdate::class);

        $this->app->get('/courseware-blocks/{id}/user-progress', Routes\Courseware\UserProgressOfBlocksShow::class);
        $this->app->get('/courseware-user-progresses/{id}', Routes\Courseware\UserProgressesShow::class);
        $this->app->patch('/courseware-user-progresses/{id}', Routes\Courseware\UserProgressesUpdate::class);

        $this->app->get('/courseware-blocks/{id}/comments', Routes\Courseware\BlockCommentsOfBlocksIndex::class);
        $this->app->post('/courseware-block-comments', Routes\Courseware\BlockCommentsCreate::class);
        $this->app->get('/courseware-block-comments/{id}', Routes\Courseware\BlockCommentsShow::class);
        $this->app->patch('/courseware-block-comments/{id}', Routes\Courseware\BlockCommentsUpdate::class);
        $this->app->delete('/courseware-block-comments/{id}', Routes\Courseware\BlockCommentsDelete::class);

        $this->app->get('/courseware-blocks/{id}/feedback', Routes\Courseware\BlockFeedbacksOfBlocksIndex::class);
        $this->app->post('/courseware-block-feedback', Routes\Courseware\BlockFeedbacksCreate::class);
        $this->app->get('/courseware-block-feedback/{id}', Routes\Courseware\BlockFeedbacksShow::class);
    }

    private function addAuthenticatedFilesRoutes()
    {
        $this->app->get('/terms-of-use', Routes\Files\TermsOfUseIndex::class);
        $this->app->get('/terms-of-use/{id}', Routes\Files\TermsOfUseShow::class);

        $this->app->get('/{type:courses|institutes|users}/{id}/file-refs', Routes\Files\RangeFileRefsIndex::class);
        $this->app->get('/{type:courses|institutes|users}/{id}/folders', Routes\Files\RangeFoldersIndex::class);

        $this->app->post('/{type:courses|institutes|users}/{id}/folders', Routes\Files\RangeFoldersCreate::class);

        $this->app->get('/file-refs/{id}', Routes\Files\FileRefsShow::class);
        $this->app->patch('/file-refs/{id}', Routes\Files\FileRefsUpdate::class);
        $this->app->delete('/file-refs/{id}', Routes\Files\FileRefsDelete::class);
        $this->addRelationship('/file-refs/{id}/relationships/terms-of-use', Routes\Files\Rel\TermsOfFileRef::class);

        $this->app->map(['HEAD'], '/file-refs/{id}/content', Routes\Files\FileRefsContentHead::class);
        $this->app->get('/file-refs/{id}/content', Routes\Files\FileRefsContentShow::class);
        $this->app->post('/file-refs/{id}/content', Routes\Files\FileRefsContentUpdate::class);

        $this->app->get('/folders/{id}', Routes\Files\FoldersShow::class);
        $this->app->patch('/folders/{id}', Routes\Files\FoldersUpdate::class);
        $this->app->delete('/folders/{id}', Routes\Files\FoldersDelete::class);

        // not a JSON route
        $this->app->post('/folders/{id}/copy', Routes\Files\FoldersCopy::class);

        $this->app->get('/folders/{id}/file-refs', Routes\Files\SubfilerefsIndex::class);
        $this->app->get('/folders/{id}/folders', Routes\Files\SubfoldersIndex::class);

        $this->app->post('/folders/{id}/file-refs', Routes\Files\NegotiateFileRefsCreate::class);
        $this->app->post('/folders/{id}/folders', Routes\Files\SubfoldersCreate::class);

        $this->app->get('/files/{id}', Routes\Files\FilesShow::class);
        $this->app->get('/files/{id}/file-refs', Routes\Files\FileRefsOfFilesShow::class);
        $this->addRelationship('/files/{id}/relationships/file-refs', Routes\Files\Rel\FileRefsOfFile::class);
    }

    private function addAuthenticatedMessagesRoutes()
    {
        $this->app->get('/users/{id}/inbox', Routes\Messages\InboxShow::class);

        $this->app->get('/users/{id}/outbox', Routes\Messages\OutboxShow::class);

        $this->app->post('/messages', Routes\Messages\MessageCreate::class);
        $this->app->get('/messages/{id}', Routes\Messages\MessageShow::class);
        $this->app->patch('/messages/{id}', Routes\Messages\MessageUpdate::class);
        $this->app->delete('/messages/{id}', Routes\Messages\MessageDelete::class);
    }

    private function addAuthenticatedForumRoutes()
    {
        $this->app->get('/courses/{id}/forum-categories', Routes\Forum\ForumCategoriesIndex::class);

        $this->app->get('/forum-entries/{id}', Routes\Forum\ForumEntriesShow::class);
        $this->app->get('/forum-entries/{id}/entries', Routes\Forum\ForumEntryEntriesIndex::class);

        $this->app->get('/forum-categories/{id}', Routes\Forum\ForumCategoriesShow::class);

        $this->app->get('/forum-categories/{id}/entries', Routes\Forum\ForumCategoryEntriesIndex::class);

        $this->app->post('/forum-entries/{id}/entries', Routes\Forum\ForumEntryEntriesCreate::class);
        $this->app->post('/forum-categories/{id}/entries', Routes\Forum\ForumCategoryEntriesCreate::class);
        $this->app->post('/courses/{id}/forum-categories', Routes\Forum\ForumCategoriesCreate::class);

        $this->app->patch('/forum-categories/{id}', Routes\Forum\ForumCategoriesUpdate::class);
        $this->app->patch('/forum-entries/{id}', Routes\Forum\ForumEntriesUpdate::class);

        $this->app->delete('/forum-categories/{id}', Routes\Forum\ForumCategoriesDelete::class);
        $this->app->delete('/forum-entries/{id}', Routes\Forum\ForumEntriesDelete::class);
    }

    private function addRelationship($url, $handler)
    {
        $this->app->map(['GET', 'PATCH', 'POST', 'DELETE'], $url, $handler);
    }
}
