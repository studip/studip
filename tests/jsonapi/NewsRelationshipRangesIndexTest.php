<?php


require_once 'NewsTestHelper.php';

use JsonApi\Routes\News\Rel\Ranges;

class NewsRelationshipRangesIndexTest extends \Codeception\Test\Unit
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

    // tests
    public function testShouldShowRangeAfterCreatingNews()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();

        $title = '';
        $content = '';

        \StudipNews::deleteBySQL('1');
        $this->assertSame(0, \StudipNews::countBySQL('1'));
        $news = $this->createNews($credentials, $title, $content, $credentials['id']);
        $this->assertSame(1, \StudipNews::countBySQL('1'));

        $response = $this->getRangesRelationshipOfNews($credentials, $news);
        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $resources = $response->document()->primaryResources();
        $this->tester->assertCount(1, $resources);

        $this->tester->assertSame($resources[0]->type(), \JsonApi\Schemas\User::TYPE);
        $this->tester->assertSame($resources[0]->id(), $credentials['id']);
    }

    // **** helper functions ****
    private function getRangesRelationshipOfNews($credentials, \StudipNews $news)
    {
        return $this->tester->sendMockRequest(
            $this->tester->createApp($credentials, 'get', '/news/{id}/relationships/ranges', Ranges::class),
            $this->tester->createRequestBuilder($credentials)
            ->setUri('/news/'.$news->id.'/relationships/ranges')
            ->fetch()
            ->getRequest()
        );
    }
}
