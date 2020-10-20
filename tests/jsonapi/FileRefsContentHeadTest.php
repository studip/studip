<?php

use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Errors\UnprocessableEntityException;
use JsonApi\Routes\Files\FileRefsContentHead;
use JsonApi\Schemas\ContentTermsOfUse;
use JsonApi\Schemas\FileRef;

require_once 'FilesTestHelper.php';

class FileRefsContentHeadTest extends \Codeception\Test\Unit
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

    public function testShouldGetHead()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $courseId = 'a07535cf2f8a72df33c12ddfa4b53dde';
        $folder = $this->prepareTopFolder($credentials, $courseId);
        $file = $this->createFileInFolder($credentials, $folder, 'file.txt', 'some description');

        $this->assertNotNull(\FileRef::find($file->getFileRef()->id));

        $response = $this->getHeadResponse($credentials, $file);

        $this->tester->assertSame($response->getStatusCode(), 200);
        $this->tester->assertArrayHasKey('ETag', $headers = $response->getHeaders());
        $this->tester->assertNotEmpty($headers['ETag']);
        $this->tester->assertIsString(current($headers['ETag']));
    }

    public function testShouldNotGetHeadOnMissingFile()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $missingId = 'missing-id';
        $this->assertNull(\FileRef::find($missingId));

        $response = $this->getHeadResponse($credentials, $missingId);

        $this->tester->assertSame($response->getStatusCode(), 404);
    }


    // **** helper functions ****
    private function getHeadResponse($user, $fileOrId)
    {
        $app = $this->tester->createApp(
            $user,
            'HEAD',
            '/file-refs/{id}/content',
            FileRefsContentHead::class
        );

        $requestBuilder = $this->tester->createRequestBuilder($user);
        $requestBuilder
            ->setUri('/file-refs/'.(is_object($fileOrId) ? $fileOrId->getFileRef()->id : $fileOrId).'/content')
            ->setMethod('HEAD');

        return $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
    }
}
