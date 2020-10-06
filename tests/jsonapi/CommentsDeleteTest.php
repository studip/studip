<?php


class CommentsDeleteTest extends \Codeception\Test\Unit
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

    public function testDeleteComment()
    {
        $commentId = '372d6c3bd41cd503c022961e73698d4c';
        $credentials = $this->tester->getCredentialsForRoot();
        $response = $this->deleteComment($credentials, $newsId);
    }

    private function deleteComment(array $credentials, $commentId)
    {
        $app = $this->tester->createApp($credentials, 'delete', '/comments/{id}', CommentsDelete::class);

        return $this->tester->sendMockRequest(
            $app,
            $this->tester
            ->createRequestBuilder($credentials)
            ->setUri('/comments/'.$commentId)
            ->delete()
            ->getRequest()
        );
    }
}
