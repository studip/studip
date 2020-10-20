<?php

use JsonApi\Routes\Files\FileRefsDelete;
use JsonApi\Schemas\ContentTermsOfUse;
use JsonApi\Schemas\FileRef;
use JsonApi\Errors\RecordNotFoundException;

require_once 'FilesTestHelper.php';

class FileRefsDeleteTest extends \Codeception\Test\Unit
{
    use FilesTestHelper;

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

    public function testShouldDeleteFileRef()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $courseId = 'a07535cf2f8a72df33c12ddfa4b53dde';
        $folder = $this->prepareTopFolder($credentials, $courseId);
        $file = $this->createFileInFolder($credentials, $folder, 'file.txt', 'some description');

        $response = $this->sendDeleteFileRef($credentials, $file->getId());
        $this->tester->assertSame($response->getStatusCode(), 204);
    }

    public function testShouldNotDeleteMissingFileRef()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $missingId = 'missing-id';

        $this->tester->expectThrowable(RecordNotFoundException::class, function () use ($credentials, $missingId) {
            $this->sendDeleteFileRef($credentials, $missingId);
        });
    }

    // **** helper functions ****
    private function sendDeleteFileRef($user, $fileId)
    {
        $app = $this->tester->createApp(
            $user,
            'DELETE',
            '/file-refs/{id}',
            FileRefsDelete::class
        );

        $requestBuilder = $this->tester->createRequestBuilder($user);
        $requestBuilder
            ->setUri('/file-refs/'.($fileId))
            ->delete();

        return $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
    }
}
