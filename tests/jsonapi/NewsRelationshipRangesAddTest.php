<?php


require_once 'NewsTestHelper.php';

use JsonApi\Routes\News\Rel\Ranges;

class NewsRelationshipRangesAddTest extends \Codeception\Test\Unit
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
    public function testShouldAddRangeToNews()
    {
        $credentials = $this->tester->getCredentialsForTestDozent();

        $title = '';
        $content = '';

        \StudipNews::deleteBySQL('1');
        $this->assertSame(0, \StudipNews::countBySQL('1'));
        $news = $this->createNews($credentials, $title, $content, $credentials['id']);
        $this->assertSame(1, \StudipNews::countBySQL('1'));

        $courseId = 'a07535cf2f8a72df33c12ddfa4b53dde';

        $response = $this->addRangeToNews($credentials, $news, $courseId);
        $this->tester->assertSame(204, $response->getStatusCode());

        $news->restoreRanges();
        $this->assertCount(2, $news->getRanges());
    }

    // **** helper functions ****
    private function addRangeToNews($credentials, \StudipNews $news, $rangeId)
    {
        return $this->tester->sendMockRequest(
            $this->tester->createApp($credentials, 'post', '/news/{id}/relationships/ranges', Ranges::class),
            $this->tester->createRequestBuilder($credentials)
            ->setUri('/news/'.$news->id.'/relationships/ranges')
            ->setJsonApiBody($this->prepareValidBody([$rangeId]))
            ->create()
            ->getRequest()
        );
    }


    private function prepareValidBody(array $rangeIds)
    {
        return [
            'data' => array_map(
                function ($rangeId) {
                    return [
                        'type' => \JsonApi\Schemas\Course::TYPE,
                        'id' => $rangeId,
                    ];
                },
                $rangeIds
            ),
        ];
    }
}
