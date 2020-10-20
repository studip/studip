<?php

use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Errors\UnprocessableEntityException;
use JsonApi\Routes\Files\FileRefsOfFilesShow;
use JsonApi\Schemas\ContentTermsOfUse;
use JsonApi\Schemas\FileRef;

require_once 'FilesTestHelper.php';

class FileRefsOfFilesShowTest extends \Codeception\Test\Unit
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

    public function testShouldShowFileRefs()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $courseId = 'a07535cf2f8a72df33c12ddfa4b53dde';
        $folder = $this->prepareTopFolder($credentials, $courseId);
        $file = $this->createFileInFolder($credentials, $folder, 'filename.txt', 'a description');
        $fileId = $file->getFileRef()['file_id'];
        $this->assertNotNull(\File::find($fileId));

        $response = $this->sendShowFileRefsOfFiles($credentials, $fileId);
        $this->assertSuccess($response, 1);
    }

    public function testShouldShowEmptyFileRefs()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $file = \File::create(
            [
                'name' => 'name.txt',
                'mime_type' => 'text/plain',
                'filetype'  => \StandardFile::class,
                'size' => 0,
                'user_id' => $credentials['id']
            ]
        );

        $this->assertNotNull(\File::find($file->id));

        $response = $this->sendShowFileRefsOfFiles($credentials, $file->id);
        $this->assertSuccess($response, 0);
    }

    public function testShouldShowMultipleFileRefs()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $courseId = 'a07535cf2f8a72df33c12ddfa4b53dde';
        $folder = $this->prepareTopFolder($credentials, $courseId);
        $file1 = $this->createFileInFolder($credentials, $folder, 'filename1.txt', 'a description');
        $file2 = new \StandardFile($file1->getFileRef());
        $file2->addToFolder($folder->getTypedFolder(), "filename2.txt", $credentials['id']);

        $fileId = $file1->getFileRef()['file_id'];

        $response = $this->sendShowFileRefsOfFiles($credentials, $fileId);
        $this->assertSuccess($response, 2);
    }


    // **** helper functions ****
    private function sendShowFileRefsOfFiles($user, $fileId)
    {
        $app = $this->tester->createApp(
            $user,
            'GET',
            '/files/{id}/file-refs',
            FileRefsOfFilesShow::class
        );

        $requestBuilder = $this->tester->createRequestBuilder($user);
        $requestBuilder
            ->setUri('/files/'.($fileId).'/file-refs')
            ->fetch();

        return $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
    }

    private function assertSuccess($response, $count)
    {
        $this->tester->assertTrue($response->isSuccessfulDocument([200]));

        $document = $response->document();
        $this->tester->assertTrue($document->isResourceCollectionDocument());

        $this->assertCount($count, $resources = $document->primaryResources());
        // $this->tester->assertNotEmpty($resource->id());
        // $this->tester->assertSame(FileRef::TYPE, $resource->type());
    }
}
