<?php

use JsonApi\Models\ForumCat;
use JsonApi\Models\ForumEntry;

trait ForumTestHelper
{
    private function buildValidResourceEntry($content, $title)
    {
        return ['data' => [
                'type' => 'forum-entries',
                'attributes' => [
                    'title' => $title,
                    'content' => $content,
                ],
            ],
        ];
    }

    private function buildValidResourceEntryUpdate()
    {
        return ['data' => [
                'type' => 'forum-entries',
                'attributes' => [
                    'title' => 'updated entry',
                    'content' => 'this has been updated by testcase',
                ],
            ],
        ];
    }

    private function buildValidResourceCategory()
    {
        return [
            'data' => [
                'type' => 'forum-categories',
                'attributes' => [
                    'title' => 'Test-Kategorie',
                ],
            ],
        ];
    }

    private function buildValidResourceCategoryUpdate()
    {
        return [
            'data' => [
                'type' => 'forum-categories',
                'attributes' => [
                    'title' => 'Updated-Kategorie',
                ],
            ],
        ];
    }

    private function createCategory($credentials)
    {
        $seminar_id = 'a07535cf2f8a72df33c12ddfa4b53dde';
        $cat_name = 'Test-Kategorie';
        $cat = new ForumCat();
        $cat->seminar_id = $seminar_id;
        $cat->entry_name = $cat_name;
        $cat->store();

        return $cat;
    }

    private function createBadCategory($credentials)
    {
        $seminar_id = 'badCourse';
        $cat_name = 'Test-Kategorie';
        $cat = new ForumCat();
        $cat->seminar_id = $seminar_id;
        $cat->entry_name = $cat_name;
        $cat->store();

        return $cat;
    }

    private function createEntry($credentials, $category_id)
    {
        echo 'test:'.$category_id;
        if (!$parent = ForumCat::find($category_id)) {
            $entry_id = $category_id;
            $parent = ForumEntry::find($entry_id);
        }

        $topic_id = md5(uniqid(rand()));
        $data = array(
            'topic_id' => $topic_id,
            'seminar_id' => $parent->seminar_id,
            'user_id' => $credentials['id'],
            'name' => 'Test-Entry',
            'content' => 'Try to append new entries',
            'author' => $credentials['username'],
            'anonymous' => 0,
        );
        $entry = new ForumEntry();
        $entry->setData($data);

        $entry->storeWith($parent, $entry);

        return $entry;
    }

    private function createBadEntry($credentials)
    {
        $entry_name = 'Test-Entry';
        $entry = new ForumEntry();
        $entry->seminar_id = 'badSeminar';
        $entry->entry_name = $entry_name;
        $entry->store();

        return $entry;
    }
}
