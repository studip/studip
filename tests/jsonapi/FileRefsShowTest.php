<?php

use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Errors\UnprocessableEntityException;
use JsonApi\Routes\Files\FileRefsShow;
use JsonApi\Schemas\ContentTermsOfUse;
use JsonApi\Schemas\FileRef;

require_once 'FilesTestHelper.php';

class FileRefsShowTest extends \Codeception\Test\Unit
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

    public function testShouldShowFile()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $courseId = 'a07535cf2f8a72df33c12ddfa4b53dde';
        $folder = $this->prepareTopFolder($credentials, $courseId);
        $license = $this->getSampleLicense();
        $name = 'filename.jpg';
        $description = 'a description';

        $file = $this->createFileInFolder($credentials, $folder, $name, $description, $license->id);

        $response = $this->sendShowFileRef($credentials, $file->getId());
        $this->assertFileRefCreated($response, $name, $description, $license);
    }

    public function testShouldFailIfFileIsMissing()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();

        $this->tester->expectThrowable(RecordNotFoundException::class, function () use ($credentials) {
            $this->sendShowFileRef($credentials, 'missing-id');
        });
    }

    // **** helper functions ****
    private function sendShowFileRef($user, $fileId)
    {
        $app = $this->tester->createApp(
            $user,
            'GET',
            '/file-refs/{id}',
            FileRefsShow::class
        );

        $requestBuilder = $this->tester->createRequestBuilder($user);
        $requestBuilder
            ->setUri('/file-refs/'.($fileId))
            ->fetch();

        return $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
    }

    private function assertFileRefCreated($response, $name, $description, $license)
    {
        $this->tester->assertTrue($response->isSuccessfulDocument([200]));

        $document = $response->document();
        $this->tester->assertTrue($document->isSingleResourceDocument());

        $resource = $document->primaryResource();
        $this->tester->assertNotEmpty($resource->id());
        $this->tester->assertSame(FileRef::TYPE, $resource->type());

        $this->tester->assertSame($name, $resource->attribute('name'));
        $this->tester->assertSame($description, $resource->attribute('description'));

        $this->tester->assertTrue($resource->attribute('is-readable'));
        $this->tester->assertTrue($resource->attribute('is-downloadable'));
        $this->tester->assertTrue($resource->attribute('is-editable'));
        $this->tester->assertTrue($resource->attribute('is-writable'));

        $resourceLink = $resource->relationship('terms-of-use')->firstResourceLink();
        $this->tester->assertSame($license->id, $resourceLink['id']);
    }
}
