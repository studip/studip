<?php

require_once 'NewsTestHelper.php';
use JsonApi\Models\C;

use JsonApi\Routes\News\CourseNewsCreate;
use JsonApi\Routes\News\UserNewsCreate;
use JsonApi\Routes\News\StudipNewsCreate;
use JsonApi\Routes\News\CommentCreate;
use JsonApi\Routes\News\NewsUpdate; 
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Routes\News\CommentsDelete;

class NewsCreateTest extends \Codeception\Test\Unit
{
    use NewsTestHelper;

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

    public function testShouldStudipNewsCreate()
    {
        $credentials = $this->tester->getCredentialsForRoot();
        $title = 'n new test';
        $content = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit';
        $entry_json = $this->buildValidResourceEntry($content, $title);
        $app = $this->tester->createApp($credentials, 'post', '/news', StudipNewsCreate::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder
            ->setUri('/news')
            ->create()
            ->setJsonApiBody($entry_json);

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());

        $this->tester->assertTrue($response->isSuccessfulDocument([201]));
        $document = $response->document();
        $resourceObject = $document->primaryResource();
        $this->tester->assertNotNull($resourceObject->attribute('title'));
        $this->tester->assertNotNull($resourceObject->attribute('content'));
        $newsId = $news->id;
    }
    public function testShouldNotStudipNewsCreate()
    {

        $this->tester->expectThrowable(AuthorizationFailedException::class, function () {
            $credentials = $this->tester->getCredentialsForTestDozent();
            $title = 'A public testing title';
            $content = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit';
            $entry_json = $this->buildValidResourceEntry($content, $title);
            $app = $this->tester->createApp($credentials, 'post', '/news', StudipNewsCreate::class);
  
            $requestBuilder = $this->tester->createRequestBuilder($credentials);
            $requestBuilder
                ->setUri('/news')
                ->create()
                ->setJsonApiBody($entry_json);

          $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
          $this->tester->assertTrue($response->isSuccessfulDocument([201]));
          $document = $response->document();
          $resourceObject = $document->primaryResource();
          $this->tester->assertNotNull($resourceObject->attribute('title'));
          $this->tester->assertNotNull($resourceObject->attribute('content'));
          $newsId = $news->id;

        });
    }
    public function testShouldNewsUpdate() {
        $title = 'A course testing title';
        $credentials = $this->tester->getCredentialsForTestDozent();
        $content = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit';
        $news = $this->createNews($credentials, $title, $content);
        
        $changedContent = 'Lorem ipsum dolor sit amet';
        $entry_json = $this->buildValidUpdateEntry($changedContent);
        $app = $this->tester->createApp($credentials, 'patch', '/news/{id}', NewsUpdate::class);
        
        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder
            ->setUri('/news/'.$news->id)
            ->update()
            ->setJsonApiBody($entry_json);
            
    
        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
        $this->tester->assertTrue($response->isSuccessfulDocument());

        $document = $response->document();
        $resourceObject = $document->primaryResource();
        $this->tester->assertNotEquals($news->body, $resourceObject->attribute('title'));
    }

    public function testShouldCourseNewsCreate()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        $courseId = 'a07535cf2f8a72df33c12ddfa4b53dde';
        $title = 'A course testing title';
        $content = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit';
        $entry_json = $this->buildValidResourceEntry($content, $title);
        $app = $this->tester->createApp($credentials, 'post', '/courses/{id}/news', CourseNewsCreate::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder
            ->setUri('/courses/'.$courseId.'/news')
            ->create()
            ->setJsonApiBody($entry_json);

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());

        $this->tester->assertTrue($response->isSuccessfulDocument([201]));
        $document = $response->document();
        $resourceObject = $document->primaryResource();
        $this->tester->assertNotNull($resourceObject->attribute('title'));
        $this->tester->assertNotNull($resourceObject->attribute('content'));
        $newsId = $news->id;
    }
    public function testShouldNotCourseNewsCreate()
    {
      $this->tester->expectThrowable(AuthorizationFailedException::class, function () {

        $credentials = $this->tester->getCredentialsForTestAutor();
        $courseId = 'a07535cf2f8a72df33c12ddfa4b53dde';
        $title = 'A course testing title';
        $content = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit';
        $entry_json = $this->buildValidResourceEntry($content, $title);
        $app = $this->tester->createApp($credentials, 'post', '/courses/{id}/news', CourseNewsCreate::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder
            ->setUri('/courses/'.$courseId.'/news')
            ->create()
            ->setJsonApiBody($entry_json);

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());

        $this->tester->assertTrue($response->isSuccessfulDocument([201]));
        $document = $response->document();
        $resourceObject = $document->primaryResource();
        $this->tester->assertNotNull($resourceObject->attribute('title'));
        $this->tester->assertNotNull($resourceObject->attribute('content'));
        $newsId = $news->id;

      });
    }
    public function testShouldUserNewsCreate()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();
        $userId = $credentials['id'];
        $title = 'A course testing title';
        $content = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit';
        $entry_json = $this->buildValidResourceEntry($content, $title);
        $app = $this->tester->createApp($credentials, 'post', '/users/{id}/news', UserNewsCreate::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder
            ->setUri('/users/'.$userId.'/news')
            ->create()
            ->setJsonApiBody($entry_json);

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());

        $this->tester->assertTrue($response->isSuccessfulDocument([201]));
        $document = $response->document();
        $resourceObject = $document->primaryResource();
        $this->tester->assertNotNull($resourceObject->attribute('title'));
        $this->tester->assertNotNull($resourceObject->attribute('content'));
        $newsId = $news->id;
    }
    public function testShouldNotUserNewsCreate()
    {
      $this->tester->expectThrowable(AuthorizationFailedException::class, function () {

        $credentials = $this->tester->getCredentialsForTestAutor();
        $otherUser = $this->tester->getCredentialsForTestDozent();
        $userId = $otherUser['id'];
        $title = 'A course testing title';
        $content = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit';
        $entry_json = $this->buildValidResourceEntry($content, $title);
        $app = $this->tester->createApp($credentials, 'post', '/users/{id}/news', UserNewsCreate::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder
            ->setUri('/users/'.$userId.'/news')
            ->create()
            ->setJsonApiBody($entry_json);

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());

        $this->tester->assertTrue($response->isSuccessfulDocument([201]));
        $document = $response->document();
        $resourceObject = $document->primaryResource();
        $this->tester->assertNotNull($resourceObject->attribute('title'));
        $this->tester->assertNotNull($resourceObject->attribute('content'));
        $newsId = $news->id;

      });
    }
    public function testShouldCommentCreate()
    {
        $title = 'A course testing title';
        $credentials = $this->tester->getCredentialsForTestDozent();
        $content = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit';
        $news = $this->createNews($credentials, $title, $content);
        $comment = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit';
        $entry_json = $this->buildValidCommentEntry($comment);

        $app = $this->tester->createApp($credentials, 'post', '/news/{id}/comments', CommentCreate::class);
        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder
            ->setUri('/news/'.$news->id.'/comments')
            ->create()
            ->setJsonApiBody($entry_json);

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());

        $this->tester->assertTrue($response->isSuccessfulDocument([201]));
        $document = $response->document();
        $resourceObject = $document->primaryResource();
        $this->tester->assertNotNull($resourceObject->attribute('content'));
    }
    public function testShouldNotCommentCreate()
    {
        //missing title
        $this->tester->expectThrowable(RecordNotFoundException::class, function () {
            $title = 'A course testing title';
            $credentials = $this->tester->getCredentialsForTestDozent();
            $content = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit';
            $news = $this->createNews($credentials, $title, $content);
            $comment = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit';
            $entry_json = $this->buildValidCommentEntry($comment);

            $app = $this->tester->createApp($credentials, 'post', '/news/{id}/comments', CommentCreate::class);
            $requestBuilder = $this->tester->createRequestBuilder($credentials);
            $requestBuilder
                ->setUri('/news/badId/comments')
                ->create()
                ->setJsonApiBody($entry_json);

            $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());

            $this->tester->assertTrue($response->isSuccessfulDocument([201]));
            $document = $response->document();
            $resourceObject = $document->primaryResource();
            $this->tester->assertNotNull($resourceObject->attribute('content'));
        });
    }
    

}
