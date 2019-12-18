<?php

use JsonApi\Routes\Wiki\WikiCreate;

class WikiCreateTest extends \Codeception\Test\Unit
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

    //testszenario:
    public function testWikiCreate()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();
        $rangeId = 'a07535cf2f8a72df33c12ddfa4b53dde';

        $keyword = 'IphiklosIphitos';
        $content = 'This is just fake wiki.';

        $json = [
            'data' => [
                'type' => 'wiki',
                'attributes' => compact('keyword', 'content'),
            ],
        ];

        $this->tester->assertCount(0, \WikiPage::findLatestPages($rangeId));

        $response = $this->createWikiPage($credentials, $rangeId, $json);
        $this->tester->assertSame(201, $response->getStatusCode());

        $this->tester->assertCount(1, \WikiPage::findLatestPages($rangeId));

        $page = $response->document()->primaryResource();

        $this->tester->assertEquals($content, $page->attribute('content'));
    }

    //helpers:
    private function createWikiPage($credentials, $rangeId, $json)
    {
        $app = $this->tester->createApp(
            $credentials,
            'post',
            '/courses/{id}/wiki',
            WikiCreate::class
        );

        return $this->tester->sendMockRequest(
            $app,
            $this->tester->createRequestBuilder($credentials)
            ->setUri('/courses/'.$rangeId.'/wiki')
            ->setJsonApiBody($json)
            ->create()
            ->getRequest()
        );
    }
}
