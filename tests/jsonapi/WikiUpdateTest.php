<?php

use JsonApi\Routes\Wiki\WikiUpdate;

class WikiUpdateTest extends \Codeception\Test\Unit
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
    public function testCourseWikiUpdate()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();
        $rangeId = 'a07535cf2f8a72df33c12ddfa4b53dde';

        $keyword = 'KaineusKalais';
        $content = 'This is just fake wiki.';
        $this->createWikiPage($rangeId, $keyword, $content);

        $newContent = 'Es gibt im Moment in diese Mannschaft, oh, einige Spieler vergessenihren Profi was sie sind. Ich lese nicht sehr viele Zeitungen, aberich habe gehört viele Situationen. Erstens: Wir haben nicht offensivgespielt. Es gibt keine deutsche Mannschaft spielt offensiv und dieNamen offensiv wie Bayern. Letzte Spiel hatten wir in Platz dreiSpitzen: Elber, Jancker und dann Zickler. Wir mussen nicht vergessenZickler. Zickler ist eine Spitzen mehr, Mehmet mehr Basler. Ist klardiese Wörter, ist möglich verstehen, was ich hab’ gesagt? Danke.';

        $response = $this->updateWiki($credentials, $rangeId, $keyword, $newContent);
        $this->tester->assertSame(200, $response->getStatusCode());
        $page = $response->document()->primaryResource();

        $this->tester->assertEquals($newContent, $page->attribute('content'));
    }

    //helpers:
    private function updateWiki($credentials, $rangeId, $keyword, $content)
    {
        $json = [
            'data' => [
                'type' => 'wiki',
                'id' => $rangeId.'_'.$keyword,
                'attributes' => compact('content')
            ],
        ];
        $app = $this->tester->createApp($credentials, 'patch', '/wiki-pages/{id}', WikiUpdate::class);

        return $this->tester->sendMockRequest(
                $app,
                $this->tester->createRequestBuilder($credentials)
                ->setUri('/wiki-pages/'.$rangeId.'_'.$keyword)
                ->setJsonApiBody($json)
                ->update()
                ->getRequest()
        );
    }

    private function createWikiPage($rangeId, $keyword, $body)
    {
        $wikiPage = new \WikiPage([$rangeId, $keyword, 0]);
        $wikiPage->body = studip_utf8decode($body);
        $wikiPage->user_id = 'nobody';
        $wikiPage->store();

        return $wikiPage;
    }
}
