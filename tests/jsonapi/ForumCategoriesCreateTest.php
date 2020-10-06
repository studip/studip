<?php

require_once 'ForumTestHelper.php';

use JsonApi\Routes\Forum\ForumCategoriesCreate;
use JsonApi\Errors\RecordNotFoundException;

class ForumCategoriesCreateTest extends \Codeception\Test\Unit
{
    use ForumTestHelper;

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
    public function testShouldCreateCategory()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();
        $cat = $this->createCategory($credentials);
        $course_id = 'a07535cf2f8a72df33c12ddfa4b53dde';
        $cat_document = $this->buildValidResourceCategory();
        $app = $this->tester->createApp($credentials, 'POST', '/courses/{id}/forum-categories', ForumCategoriesCreate::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder
            ->setUri('/courses/'.$course_id.'/forum-categories')
            ->create()
            ->setJsonApiBody($cat_document);

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());

        $this->tester->assertTrue($response->isSuccessfulDocument([201]));
        $document = $response->document();
        $resourceObject = $document->primaryResource();
        $this->tester->assertSame($cat->entry_name, $resourceObject->attribute('title'));
    }

    public function testShouldNotCreateCategory()
    {
        $this->tester->expectThrowable(RecordNotFoundException::class, function () {
            $credentials = $this->tester->getCredentialsForTestAutor();
            $cat = $this->createCategory($credentials);
            $course_id = 'badCourse';
            $cat_document = $this->buildValidResourceCategory();
            $app = $this->tester->createApp($credentials, 'POST', '/courses/{id}/forum-categories', ForumCategoriesCreate::class);

            $requestBuilder = $this->tester->createRequestBuilder($credentials);
            $requestBuilder
                ->setUri('/courses/'.$course_id.'/forum-categories')
                ->create()
                ->setJsonApiBody($cat_document);

            $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());

            $this->tester->assertTrue($response->isSuccessfulDocument([201]));
            $document = $response->document();
            $resourceObject = $document->primaryResource();
            $this->tester->assertSame($cat->entry_name, $resourceObject->attribute('title'));
        });
    }
}
