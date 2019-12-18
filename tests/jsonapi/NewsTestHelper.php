<?php

trait NewsTestHelper
{
    private function createNews($credentials, $title, $content, $rangeId = null)
    {
        $news = \StudipNews::create(
            [
                'user_id'        => $credentials['id'],
                'author'         => $credentials['username'],
                'topic'          => $title,
                'body'           => $content,
                'date'           => time()- (15 * 60),
                'expire'         => 2 * 7 * 24 * 60 * 60,
                'allow_comments' => true
            ]
        );
        if ($rangeId) {

            $news->addRange($rangeId);
            $news->storeRanges();
        }

        return $news;
    }
    
    private function buildValidResourceEntry($content, $title)
    {
        return ['data' => [
                'type' => 'news',
                'attributes' => [
                    'title' => $title,
                    'content' => $content,
                    'comments-allowed' => true,
                    'publication-start' => "2020-01-01T12:12:12+00:00",
                    'publication-end' => "2021-01-01T12:12:12+00:00"
                ],
            ],
        ];
    }
    private function buildValidCommentEntry($comment)
    {
        return [
            'data' => [
                'type' => 'comments',
                'attributes' => [
                    'content' => $comment
                ],
            ],
        ];
    }
    private function buildValidUpdateEntry($comment)
    {
        return [
            'data' => [
                'type' => 'news',
                'attributes' => [
                    'content' => 'testy',
                    'comments-allowed' => true,
                    'publication-start' => "2020-01-01T12:12:12+00:00",
                    'publication-end' => "2021-01-01T12:12:12+00:00"
                ],
            ],
        ];
    }
    private function createComment(array $credentials, \StudipNews $news, $content)
    {
        $comment = new \StudipComment();
        $comment->user_id = $credentials['id'];
        $comment->content = $content;
        $comment->object_id = $news->id;

        $comment->store();

        return $comment;
    }
}
