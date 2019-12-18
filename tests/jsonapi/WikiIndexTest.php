<?php

use JsonApi\Routes\Wiki\WikiIndex;

class WikiIndexTest extends \Codeception\Test\Unit
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
    public function testShowWiki()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();
        $rangeId = 'a07535cf2f8a72df33c12ddfa4b53dde';
        $body = studip_utf8decode('Es gibt im Moment in diese Mannschaft, oh, einige Spieler vergessen ihren Profi was sie sind. Ich lese nicht sehr viele Zeitungen, aberich habe gehört viele Situationen. Erstens: Wir haben nicht offensivgespielt.');

        $this->createWikiPage($credentials['id'], $rangeId, 'yxilo', $body);
        $this->createWikiPage($credentials['id'], $rangeId, 'ulyq', $body);
        $countPages = \WikiPage::findLatestPages($rangeId);
        $this->tester->assertCount(2, $countPages);

        $response = $this->getWikiIndex($credentials, $rangeId);

        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $wikiPages = $response->document()->primaryResources();
        $this->tester->assertCount(count($countPages), $wikiPages);

        $wikiPage = current($wikiPages);
        $this->tester->assertEquals($body, studip_utf8decode($wikiPage->attribute('content')));

        $this->tester->storeJsonMd('get_wiki_pages', $response, 2, '[...]');
    }

    //helpers:
    private function getWikiIndex($credentials, $rangeId)
    {
        $app = $this->tester->createApp($credentials, 'get', '/courses/{id}/wiki', WikiIndex::class);

        return $this->tester->sendMockRequest(
            $app,
            $this->tester->createRequestBuilder($credentials)
            ->setUri('/courses/'.$rangeId.'/wiki')
            ->fetch()
            ->getRequest()
        );
    }

    private function createWikiStartPage($userId, $courseId, $body)
    {
        return $this->createWikiPage($userId, $courseId, 'WikiWikiWeb', $body);

    }

    private function createWikiPage($userId, $courseId, $keyword, $body)
    {
        // EVIL HACK
        $oldPerm = $GLOBALS['perm'];
        $oldUser = $GLOBALS['user'];
        $GLOBALS['perm'] = new \Seminar_Perm();
        $GLOBALS['user'] = \User::find($userId);

        $latest = \WikiPage::findLatestPage($courseId, $keyword);
        $result = \WikiPage::create(
            [
                'user_id' => $userId,
                'range_id' => $courseId,
                'keyword' => $keyword,
                'version' => $latest->version + 1,
                'body' => $body
            ]
        );

        // EVIL HACK
        $GLOBALS['user'] = $oldUser;
        $GLOBALS['perm'] = $oldPerm;

        return $result;
    }
}
