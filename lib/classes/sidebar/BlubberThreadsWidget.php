<?php

class BlubberThreadsWidget extends SidebarWidget
{
    protected $active_thread = null;
    protected $with_composer = false;

    public function addThread($thread)
    {
        $this->elements[] = $thread;
    }

    public function setActive($thread_id)
    {
        $this->active_thread = $thread_id;
    }

    public function withComposer($with = true)
    {
        $this->with_composer = $with;
    }

    public function render($variables = [])
    {
        $template = $GLOBALS['template_factory']->open('blubber/threads-overview.php');
        if (count($this->elements) > 30) {
            array_pop($this->elements);
            $template->more_down = true;
        }

        $json = [];
        foreach ($this->elements as $thread) {
            if ($thread->getId() === "global") {
                $unseen_comments = BlubberThread::countBySQL("context_type = 'public' AND mkdate > ?", [$thread->getLastVisit() ?: object_get_visit_threshold()]);
            } else {
                $unseen_comments = BlubberComment::countBySQL("thread_id = ? AND mkdate >= ?", [
                    $thread->getId(),
                    $thread->getLastVisit() ?: object_get_visit_threshold()
                ]);
            }

            $json[] = [
                'thread_id' => $thread->getId(),
                'avatar' => $thread->getAvatar(),
                'name' => $thread->getName(),
                'timestamp' => (int) $thread->getLatestActivity(),
                'unseen_comments' => $unseen_comments
            ];
        }

        $template->threads = $this->elements;
        $template->with_composer = $this->with_composer;
        $template->json = $json;
        return $template->render();
    }
}
