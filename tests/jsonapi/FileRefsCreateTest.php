<?php


use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Errors\UnprocessableEntityException;
use JsonApi\Routes\Files\FileRefsCreate;
use JsonApi\Schemas\ContentTermsOfUse;
use JsonApi\Schemas\FileRef;

class FileRefsCreateTest extends \Codeception\Test\Unit
{
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

    protected function createTopFolder($credentials, $course)
    {
        $oldUser = $GLOBALS['user'];
        $GLOBALS['user'] = new \Seminar_User($credentials['id']);

        $rootFolder = Folder::createTopFolder($course->id, 'course');

        $GLOBALS['user'] = $oldUser;

        return $rootFolder;
    }

    public function testShouldCreateFileRefInFolder()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $courseId = 'a07535cf2f8a72df33c12ddfa4b53dde';

        $course = \Course::find($courseId);
        $this->assertNotNull($course);

        $folder = $this->createTopFolder($credentials, $course);
        $this->assertNotNull($folder);

        $this->assertTrue(\ContentTermsOfUse::countBySql('1') > 0);
        $license = \ContentTermsOfUse::findOneBySql('1');

        $name = 'filename.jpg';
        $description = 'a description';

        $response = $this->createFileRefInFolder(
            $credentials,
            $folder,
            $name,
            $description,
            $license
        );
        $this->tester->assertTrue($response->isSuccessfulDocument([201]));

        $document = $response->document();
        $this->tester->assertTrue($document->isSingleResourceDocument());

        $resource = $document->primaryResource();
        $this->tester->assertNotEmpty($resource->id());
        $this->tester->assertSame(FileRef::TYPE, $resource->type());

        $this->tester->assertSame($name, $resource->attribute('name'));
        $this->tester->assertSame($description, $resource->attribute('description'));

        $resourceLink = $resource->relationship('terms-of-use')->firstResourceLink();
        $this->tester->assertSame($license->id, $resourceLink['id']);
    }

    public function testShouldFailOnMissingFolder()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $missingFolder = \Folder::buildExisting(['id' => 'foo']);
        $license = \ContentTermsOfUse::findOneBySql('1');

        $name = 'filename.jpg';
        $description = 'a description';

        $this->tester->expectThrowable(
            RecordNotFoundException::class,
            function () use ($credentials, $missingFolder, $name, $description, $license) {
                $this->createFileRefInFolder(
                    $credentials,
                    $missingFolder,
                    $name,
                    $description,
                    $license
                );
            }
        );
    }

    public function testShouldFailOnEmptyName()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $courseId = 'a07535cf2f8a72df33c12ddfa4b53dde';
        $course = \Course::find($courseId);
        $folder = $this->createTopFolder($credentials, $course);
        $license = \ContentTermsOfUse::findOneBySql('1');

        $name = '';
        $description = 'a description';

        $this->tester->expectThrowable(
            UnprocessableEntityException::class,
            function () use ($credentials, $folder, $name, $description, $license) {
                $this->createFileRefInFolder(
                    $credentials,
                    $folder,
                    $name,
                    $description,
                    $license
                );
            }
        );
    }

    public function testShouldFailOnMissingLicense()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $courseId = 'a07535cf2f8a72df33c12ddfa4b53dde';
        $course = \Course::find($courseId);
        $folder = $this->createTopFolder($credentials, $course);

        $name = 'a-real-filename.gif';
        $description = 'a description';

        $this->tester->expectThrowable(
            UnprocessableEntityException::class,
            function () use ($credentials, $folder, $name, $description) {
                $this->createFileRefInFolder(
                    $credentials,
                    $folder,
                    $name,
                    $description,
                    null
                );
            }
        );
    }

    // **** helper functions ****
    private function createFileRefInFolder($user, $folder, $name, $description, $license)
    {
        $app = $this->tester->createApp(
            $user,
            'POST',
            '/folders/{id}/file-refs',
            FileRefsCreate::class
        );

        $requestBuilder = $this->tester->createRequestBuilder($user);
        $requestBuilder
            ->setJsonApiBody($this->prepareValidBody($name, $description, $license))
            ->setUri('/folders/'.($folder->id).'/file-refs')
            ->create();

        return $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
    }

    private function prepareValidBody($name, $description, $license)
    {
        $json = [
            'data' => [
                'type' => FileRef::TYPE,
                'attributes' => [
                    'name' => $name,
                    'description' => $description,
                ],
                'relationships' => [
                ],
            ],
        ];

        if ($license) {
            $json['data']['relationships']['terms-of-use'] = [
                'data' => [
                    'type' => ContentTermsOfUse::TYPE,
                    'id' => (string) $license->id,
                ],
            ];
        }

        return $json;
    }
}
