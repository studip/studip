<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Document\Link;

class WikiPage extends SchemaProvider
{
    const REGEXP_KEYWORD = '/([\w\.\-\:\(\)_§\/@# ]|&[AOUaou]uml;|&szlig;)+/A';

    const TYPE = 'wiki-pages';
    const REL_AUTHOR = 'author';
    const REL_CHILDREN = 'children';
    const REL_DESCENDANTS = 'descendants';
    const REL_PARENT = 'parent';
    const REL_RANGE = 'range';

    protected $resourceType = self::TYPE;

    /**
     * {@inheritdoc}
     */
    public function getResourceLinks($resource)
    {
        $url = $this->getDiContainer()->get('router')->pathFor(
            'get-wiki-page',
            ['id' => sprintf("%s_%s", $resource->range_id, $resource->keyword)]
        );
        $links = [ Link::SELF => $this->createLink($url) ];

        return $links;
    }

    public static function getRangeClasses()
    {
        return [
            'sem' => \Course::class,
            'inst' => \Institute::class,
        ];
    }

    public static function getRangeTypes()
    {
        return [
            'sem' => Course::TYPE,
            'inst' => Institute::TYPE,
        ];
    }

    public static function getRangeClass($resource)
    {
        $classes = self::getRangeClasses();

        return $classes[
            get_object_type(
                $resource->range_id,
                array_keys($classes)
            )
        ];
    }

    public static function getRangeType($resource)
    {
        $types = self::getRangeTypes();

        return $types[
            get_object_type(
                $resource->range_id,
                array_keys($types)
            )
        ];
    }

    public function getId($wiki)
    {
        return sprintf(
            '%s_%s',
            $wiki->range_id,
            $wiki->keyword
        );
    }

    public function getAttributes($wiki)
    {
        return [
            'keyword' => $wiki->keyword,
            'content' => $wiki->body,
            'chdate' => date('c', $wiki->chdate),
            'version' => (int) $wiki->version,
        ];
    }

    public function getRelationships($wiki, $isPrimary, array $includeList)
    {
        $relationships = [];

        if ($isPrimary) {
            $relationships = $this->addAuthorRelationship($relationships, $wiki, $includeList);
            $relationships = $this->addChildrenRelationship($relationships, $wiki, $includeList);
            $relationships = $this->addDescendantsRelationship($relationships, $wiki, $includeList);
            $relationships = $this->addParentRelationship($relationships, $wiki, $includeList);
            $relationships = $this->addRangeRelationship($relationships, $wiki, $includeList);
        }

        return $relationships;
    }

    private function addParentRelationship($relationships, $wiki, $includeList)
    {
        $related = $wiki->parent;
        $relationships[self::REL_PARENT] = [
            self::SHOW_SELF => true,
            self::DATA => $related
        ];
        if ($related) {
            $relationships[self::REL_PARENT][self::LINKS] = [
                Link::RELATED => $this->getSchemaContainer()
                    ->getSchema($related)
                    ->getSelfSubLink($related)
            ];
        }

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function addChildrenRelationship($relationships, $wiki, $includeList)
    {
        $relationships[self::REL_CHILDREN] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($wiki, self::REL_CHILDREN),
            ],
        ];

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function addDescendantsRelationship($relationships, $wiki, $includeList)
    {
        $relationships[self::REL_DESCENDANTS] = [
            self::LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($wiki, self::REL_DESCENDANTS),
            ],
        ];

        return $relationships;
    }

    private function addAuthorRelationship($relationships, $wiki, $includeList)
    {
        $data = in_array(self::REL_AUTHOR, $includeList)
              ? $wiki->author
              : \User::build(['id' => $wiki->user_id], false);
        $relationships[self::REL_AUTHOR] = [
            self::LINKS => [
                Link::RELATED => new Link('/users/' . $wiki->user_id),
            ],
            self::DATA => $data,
        ];

        return $relationships;
    }

    private function addRangeRelationship($relationships, $wiki, $includeList)
    {
        $relationships[self::REL_RANGE] = [
            self::LINKS => [
                Link::RELATED => new Link('/' . self::getRangeType($wiki) . '/' . $wiki->range_id),
            ],
            self::DATA => $this->prepareRange($wiki, $includeList),
        ];

        return $relationships;
    }

    private function prepareRange($wiki)
    {
        $class = self::getRangeClass($wiki);

        return $class::build(['id' => $wiki->range_id], false);
    }
}
