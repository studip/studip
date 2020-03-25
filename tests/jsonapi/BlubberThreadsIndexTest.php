<?php

use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Routes\Blubber\ThreadsIndex;

require_once 'BlubberTestHelper.php';

class BlubberThreadsIndexTest extends \Codeception\Test\Unit
{
    use BlubberTestHelper;

    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        \DBManager::getInstance()->setConnection('studip', $this->getModule('\\Helper\\StudipDb')->dbh);
    }

    protected function _after()
    {
    }

    // tests
    public function testIndexAllThreadsVisibleToUser()
    {
        // given
        $credentials = $this->tester->getCredentialsForTestAutor();

        $num = $this->tester->withPHPLib($credentials, function ($credentials) {
            return count(\BlubberThread::findMyGlobalThreads(9999, null, null, $credentials['id']));
        });

        $response = $this->fetchAllThreads($credentials);

        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $document = $response->document();
        $this->tester->assertTrue($document->isResourceCollectionDocument());

        $resources = $document->primaryResources();
        $this->tester->assertCount($num, $resources);
    }

    public function testIndexAllPublicThreads()
    {
        // given
        $credentials = $this->tester->getCredentialsForTestAutor();

        $num = $this->tester->withPHPLib($credentials, function ($credentials) {
            return count(
                $this->upgradeAndFilterThreads(
                    $credentials,
                    \BlubberThread::findBySQL('context_type = "public" AND visible_in_stream')
                )
            );
        });

        $response = $this->fetchPublicThreads($credentials);

        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $document = $response->document();
        $this->tester->assertTrue($document->isResourceCollectionDocument());

        $resources = $document->primaryResources();
        $this->tester->assertCount($num, $resources);
    }

    public function testIndexPrivateThreadsWithAnotherUser()
    {
        // given
        $credentials1 = $this->tester->getCredentialsForTestAutor();
        $credentials2 = $this->tester->getCredentialsForTestDozent();

        $this->createPrivateBlubberThreadForUser(
            $credentials1,
            [$credentials1, $credentials2],
            "I have seen Eribotes! @{$credentials2['username']}, have you seen him too?"
        );

        $response = $this->fetchPrivateThreads($credentials1, $credentials2);

        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $document = $response->document();
        $this->tester->assertTrue($document->isResourceCollectionDocument());

        $resources = $document->primaryResources();
        $this->tester->assertCount(1, $resources);

        // TODO:
    }

    public function testIndexAllThreadsOfACourse()
    {
        // given
        $credentials = $this->tester->getCredentialsForTestAutor();
        $courseId = 'a07535cf2f8a72df33c12ddfa4b53dde';

        $this->createCourseBlubberThreadForUser($credentials, $courseId, 'I have seen Eribotes!');

        $num = $this->tester->withPHPLib($credentials, function ($credentials) use ($courseId) {
            return count(\BlubberThread::findBySeminar($courseId, false, $credentials['id']));
        });

        $response = $this->fetchCourseThreads($credentials, $courseId);

        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $document = $response->document();
        $this->tester->assertTrue($document->isResourceCollectionDocument());

        $resources = $document->primaryResources();
        $this->tester->assertCount($num, $resources);
    }

    public function testCannotIndexAllThreadsOfAMissingCourse()
    {
        // given
        $credentials = $this->tester->getCredentialsForTestAutor();

        $this->tester->expectThrowable(RecordNotFoundException::class, function () use ($credentials) {
            $courseId = 'missing';
            $this->fetchCourseThreads($credentials, $courseId);
        });
    }

    public function testIndexAllThreadsOfAnInstitute()
    {
        // given
        $credentials = $this->tester->getCredentialsForTestAutor();
        $institute = \Institute::findOneBySQL('1');

        $this->createInstituteBlubberThreadForUser($credentials, $institute, 'I have seen Eribotes!');

        $num = $this->tester->withPHPLib($credentials, function ($credentials) use ($institute) {
            return count(\BlubberThread::findByInstitut($institute->id, false, $credentials['id']));
        });

        $response = $this->fetchInstituteThreads($credentials, $institute->id);

        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $document = $response->document();
        $this->tester->assertTrue($document->isResourceCollectionDocument());

        $resources = $document->primaryResources();
        $this->tester->assertCount($num, $resources);
    }

    public function testCannotIndexAllThreadsOfAMissingInstitute()
    {
        // given
        $credentials = $this->tester->getCredentialsForTestAutor();

        $this->tester->expectThrowable(RecordNotFoundException::class, function () use ($credentials) {
            $instituteId = 'missing';
            $this->fetchInstituteThreads($credentials, $instituteId);
        });
    }

    public function testIndexAllThreadsWithSinceFilter()
    {
        // given
        $credentials = $this->tester->getCredentialsForTestAutor();

        // assert that there are no BlubberThreads since now
        $now = new DateTime();
        $threads = $this->tester->withPHPLib($credentials, function ($credentials) use ($now) {
            return \BlubberThread::findMyGlobalThreads(9999, $now->getTimestamp(), null, $credentials['id']);
        });
        $this->tester->assertCount(0, $threads);

        $response = $this->fetchAllThreads($credentials, ['since' => $now->format(\DATE_ATOM)]);
        $this->tester->assertCount(0, $response->document()->primaryResources());


        // now create one new BlubberThread and assert its presence
        $thread = $this->createPrivateBlubberThreadForUser($credentials, [$credentials], 'some clever content');
        $threads = $this->tester->withPHPLib($credentials, function ($credentials) use ($now) {
            return \BlubberThread::findMyGlobalThreads(9999, $now->getTimestamp() - 1, null, $credentials['id']);
        });
        $this->tester->assertCount(1, $threads);

        $pivot = $thread['chdate'];
        $this->tester->assertTrue((new DateTime("@".$pivot))->getTimestamp() == $thread['chdate']);

        // assert that there is one thread since (inclusive) pivot
        $response = $this->fetchAllThreads($credentials, ['since' => (new DateTime("@".($pivot - 1)))->format(\DATE_ATOM)]);
        $this->tester->assertCount(1, $threads = $response->document()->primaryResources());
        $this->tester->assertSame($threads[0]->id(), $thread->id);

        // assert that there is no thread later than (not inclusive) pivot
        $response = $this->fetchAllThreads($credentials, ['since' => (new DateTime("@".($pivot - 1)))->modify('+1 second')->format(\DATE_ATOM)]);
        $this->tester->assertCount(0, $response->document()->primaryResources());
    }

    public function testIndexAllThreadsWithBeforeFilter()
    {
        // given
        $credentials = $this->tester->getCredentialsForTestAutor();

        // assert that there are no BlubberThreads since now
        $now = new DateTime();
        $threads = $this->tester->withPHPLib($credentials, function ($credentials) use ($now) {
            return \BlubberThread::findMyGlobalThreads(9999, $now->getTimestamp(), null, $credentials['id']);
        });
        $this->tester->assertCount(0, $threads);

        // now create one new BlubberThread and assert its presence
        $thread = $this->createPrivateBlubberThreadForUser($credentials, [$credentials], 'some clever content');
        $threads = $this->tester->withPHPLib($credentials, function ($credentials) use ($now) {
            return \BlubberThread::findMyGlobalThreads(9999, $now->getTimestamp() - 1, null, $credentials['id']);
        });
        $this->tester->assertCount(1, $threads);

        $pivot = $thread['chdate'];
        $this->tester->assertTrue((new DateTime("@".$pivot))->getTimestamp() == $thread['chdate']);

        // count all the threads
        $countAllThreads = $this->tester->withPHPLib($credentials, function ($credentials) use ($now) {
            return count(\BlubberThread::findMyGlobalThreads(9999, null, null, $credentials['id']));
        });
        $this->tester->assertTrue($countAllThreads > 0);

        // assert that we get all the threads before (exclusive) pivot
        $response = $this->fetchAllThreads($credentials, ['before' => (new DateTime("@".($pivot + 1)))->format(\DATE_ATOM)]);
        $this->tester->assertCount($countAllThreads, $threads = $response->document()->primaryResources());
        $this->tester->assertSame($threads[0]->id(), $thread->id);

        // assert that we get all the threads minus 1 before (exclusive) pivot
        $response = $this->fetchAllThreads($credentials, ['before' => (new DateTime("@".($pivot + 1)))->modify("-1 seconds")->format(\DATE_ATOM)]);
        $this->tester->assertCount($countAllThreads - 1, $threads = $response->document()->primaryResources());
    }

    private function fetchAllThreads(array $credentials, array $filters = [])
    {
        $requestBuilder = $this->tester
                        ->createRequestBuilder($credentials)
                        ->setUri('/blubber-threads/all')
                        ->fetch();

        if (count($filters)) {
            $requestBuilder->setJsonApiFilter($filters);
        }

        return $this->tester->sendMockRequest(
            $this->tester->createApp($credentials, 'get', '/blubber-threads/{type:all}', ThreadsIndex::class),
            $requestBuilder->getRequest()
        );
    }

    private function fetchPublicThreads(array $credentials, array $filters = [])
    {
        $requestBuilder = $this->tester
                        ->createRequestBuilder($credentials)
                        ->setUri('/studip/blubber-threads/public')
                        ->fetch();

        if (count($filters)) {
            $requestBuilder->setJsonApiFilter($filters);
        }

        return $this->tester->sendMockRequest(
            $this->tester->createApp($credentials, 'get', '/studip/blubber-threads/{type:public}', ThreadsIndex::class),
            $requestBuilder->getRequest()
        );
    }

    private function fetchCourseThreads(array $credentials, string $courseId, array $filters = [])
    {
        $requestBuilder = $this->tester
                        ->createRequestBuilder($credentials)
                        ->setUri('/courses/'.$courseId.'/blubber-threads/course')
                        ->fetch();
        if (count($filters)) {
            $requestBuilder->setJsonApiFilter($filters);
        }

        return $this->tester->sendMockRequest(
            $this->tester->createApp(
                $credentials,
                'get',
                '/courses/{id}/blubber-threads/{type:course}',
                ThreadsIndex::class
            ),
            $requestBuilder->getRequest()
        );
    }

    private function fetchInstituteThreads(array $credentials, string $instituteId, array $filters = [])
    {
        $requestBuilder = $this->tester
                        ->createRequestBuilder($credentials)
                        ->setUri('/institutes/'.$instituteId.'/blubber-threads/institute')
                        ->fetch();

        if (count($filters)) {
            $requestBuilder->setJsonApiFilter($filters);
        }

        return $this->tester->sendMockRequest(
            $this->tester->createApp(
                $credentials,
                'get',
                '/institutes/{id}/blubber-threads/{type:institute}',
                ThreadsIndex::class
            ),
            $requestBuilder->getRequest()
        );
    }

    private function fetchPrivateThreads(array $credentials1, array $credentials2, array $filters = [])
    {
        $requestBuilder = $this->tester
                        ->createRequestBuilder($credentials1)
                        ->setUri('/users/'.$credentials2['id'].'/blubber-threads/private')
                        ->fetch();

        if (count($filters)) {
            $requestBuilder->setJsonApiFilter($filters);
        }

        return $this->tester->sendMockRequest(
            $this->tester->createApp(
                $credentials1,
                'get',
                '/users/{id}/blubber-threads/{type:private}',
                ThreadsIndex::class
            ),
            $requestBuilder->getRequest()
        );
    }
}
