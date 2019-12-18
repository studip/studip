<?php

use JsonApi\Routes\Resources\AssignmentsIndex;
use JsonApi\Routes\Resources\ResourcesIndex;

class ResourcesIndexTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        \DBManager::getInstance()->setConnection('studip', $this->getModule('\\Helper\\StudipDb')->dbh);

        $this->setupResources();
    }

    protected function _after()
    {
    }

    // tests

    public function testShouldShowResources()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();

        $app = $this->tester->createApp($credentials, 'get', '/resources-objects', ResourcesIndex::class);

        $response = $this->tester->sendMockRequest(
            $app,
            $this->tester->createRequestBuilder($credentials)
            ->setUri('/resources-objects')
            ->fetch()
            ->getRequest()
        );

        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $document = $response->document();
        $this->tester->assertTrue($document->isResourceCollectionDocument());
        $resources = $document->primaryResources();
        $this->tester->assertCount(2, $resources);
    }

    public function testShouldShowAssignments()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();
        $resourceId = "728f1578de643fb08b32b4b8afb2db77";

        $app = $this->tester->createApp($credentials, 'get', '/resources-objects/{id}/assignments', AssignmentsIndex::class);

        $response = $this->tester->sendMockRequest(
            $app,
            $this->tester->createRequestBuilder($credentials)
            ->setUri('/resources-objects/'.$resourceId.'/assignments')
            ->setJsonApiFilter(['start' => 0, 'end' => PHP_INT_MAX])
            ->fetch()
            ->getRequest()
        );

        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $document = $response->document();
        $this->tester->assertTrue($document->isResourceCollectionDocument());
        $resources = $document->primaryResources();
        $this->tester->assertCount(1, $resources);
    }

    private function setupResources()
    {
        $queries = [
            // Kategorie
            "INSERT INTO `resources_categories` (`category_id`, `name`, `description`, `system`, `is_room`, `iconnr`) VALUES ('3cbcc99c39476b8e2c8eef5381687461', 'Gebäude', '', 0, 0, 1);",
            // Eigenschaft
            "INSERT INTO `resources_properties` (`property_id`, `name`, `description`, `type`, `options`, `system`) VALUES ('c4f13691419a6c12d38ad83daa926c7c', 'Adresse', '', 'text', '', 0);",
            // Eigenschaft → Kategorie
            "INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `requestable`, `system`) VALUES ('3cbcc99c39476b8e2c8eef5381687461', 'c4f13691419a6c12d38ad83daa926c7c', 0, 0);",

            // Gebäude
            "REPLACE INTO `resources_objects` (`resource_id`, `root_id`, `parent_id`, `category_id`, `owner_id`, `institut_id`, `level`, `name`, `description`, `lockable`, `multiple_assign`, `mkdate`, `chdate`) VALUES('0ff09e4f5a729981e978d970f9d970cb', '0ff09e4f5a729981e978d970f9d970cb', '0', '', '76ed43ef286fb55cf9e41beadb484a9f', '', 0, 'Gebäude', '', 0, 0, 1084640001, 1084640009);",

            // Hörsaalgebäude
            "REPLACE INTO `resources_objects` (`resource_id`, `root_id`, `parent_id`, `category_id`, `owner_id`, `institut_id`, `level`, `name`, `description`, `lockable`, `multiple_assign`, `mkdate`, `chdate`) VALUES('8a57860ca2be4cc3a77c06c1d346ea57', '0ff09e4f5a729981e978d970f9d970cb', '0ff09e4f5a729981e978d970f9d970cb', '3cbcc99c39476b8e2c8eef5381687461', '76ed43ef286fb55cf9e41beadb484a9f', '', 1, 'Hörsaalgebäude', 'Dieses Objekt wurde neu erstellt. Es wurden noch keine Eigenschaften zugewiesen.', 0, 1, 1084640042, 1084640452);",

            // Hörsaal
            "REPLACE INTO `resources_objects` (`resource_id`, `root_id`, `parent_id`, `category_id`, `owner_id`, `institut_id`, `level`, `name`, `description`, `lockable`, `multiple_assign`, `mkdate`, `chdate`) VALUES('728f1578de643fb08b32b4b8afb2db77', '0ff09e4f5a729981e978d970f9d970cb', '8a57860ca2be4cc3a77c06c1d346ea57', '85d62e2a8a87a2924db8fc4ed3fde09d', '76ed43ef286fb55cf9e41beadb484a9f', '', 2, 'Hörsaal 1', 'Dieses Objekt wurde neu erstellt. Es wurden noch keine Eigenschaften zugewiesen.', 0, 0, 1084640456, 1084640468);",

            // Eigenschaft → Hörsaal
            "REPLACE INTO `resources_objects_properties` (`resource_id`, `property_id`, `state`) VALUES('8a57860ca2be4cc3a77c06c1d346ea57', 'c4f13691419a6c12d38ad83daa926c7c', 'Universitätsstr. 1');",

            // Belegung
            "REPLACE INTO `resources_assign` (`assign_id`, `resource_id`, `assign_user_id`, `user_free_name`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `repeat_interval`, `repeat_month_of_year`, `repeat_day_of_month`, `repeat_week_of_month`, `repeat_day_of_week`, `mkdate`, `chdate`, `comment_internal`) VALUES('1b143077169b19fbf21758698d3066b9', '728f1578de643fb08b32b4b8afb2db77', 'aef5fba2738068156e3564e2fae2145a', '', 1539586800, 1539594000, 1539594000, 0, 0, 0, 0, 0, 0, 1543861234, 1543861234, NULL)",
        ];

        $dbm = \DBManager::get();
        foreach ($queries as $query) {
            $dbm->exec($query);
        }
    }
}
