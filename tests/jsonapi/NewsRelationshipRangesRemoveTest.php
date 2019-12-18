<?php


require_once 'NewsTestHelper.php';

use JsonApi\Routes\News\Rel\Ranges;

class NewsRelationshipRangesRemoveTest extends \Codeception\Test\Unit
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
    public function testRemoveRangeToNews()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();

        $title = '';
        $content = '';

        \StudipNews::deleteBySQL('1');
        $this->assertSame(0, \StudipNews::countBySQL('1'));
        $news = $this->createNews($credentials, $title, $content, $credentials['id']);
        $this->assertSame(1, \StudipNews::countBySQL('1'));
        $this->assertCount(1, $news->getRanges());

        $response = $this->removeRangeToNews($credentials, $news, $credentials['id']);
        $this->tester->assertSame(204, $response->getStatusCode());

        $news->restoreRanges();
        $this->assertCount(0, $news->getRanges());
    }

    // **** helper functions ****
    private function removeRangeToNews($credentials, \StudipNews $news, $rangeId)
    {
        return $this->tester->sendMockRequest(
            $this->tester->createApp($credentials, 'delete', '/news/{id}/relationships/ranges', Ranges::class),
            $this->tester->createRequestBuilder($credentials)
            ->setUri('/news/'.$news->id.'/relationships/ranges')
            ->setJsonApiBody($this->prepareValidBody([$rangeId]))
            ->delete()
            ->getRequest()
        );
    }


    private function prepareValidBody(array $rangeIds)
    {
        return [
            'data' => array_map(
                function ($rangeId) {
                    return [
                        'type' => \JsonApi\Schemas\User::TYPE,
                        'id' => $rangeId,
                    ];
                },
                $rangeIds
            ),
        ];
    }
}
