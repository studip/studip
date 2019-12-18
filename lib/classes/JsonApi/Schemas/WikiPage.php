<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Document\Link;

class WikiPage extends SchemaProvider
{
    const REGEXP_KEYWORD = '/([\w\.\-\:\(\)_§\/@# ]|&[AOUaou]uml;|&szlig;)+/A';

    const TYPE = 'wiki-pages';
    const REL_AUTHOR = 'author';
    const REL_RANGE = 'range';

    protected $resourceType = self::TYPE;

    /**
     * {@inheritdoc}
     */
    public function getResourceLinks($resource)
    {
        $links = [
//            LinkInterface::SELF => $this->getSelfSubLink($resource),
        ];

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
        return studip_utf8encode(
            sprintf(
                '%s_%s',
                $wiki->range_id,
                $wiki->keyword
            )
        );
    }

    public function getAttributes($wiki)
    {
        return [
            'keyword' => studip_utf8encode($wiki->keyword),
            'content' => studip_utf8encode($wiki->body),
            'chdate' => date('c', $wiki->chdate),
            'version' => (int) $wiki->version,
        ];
    }

    public function getRelationships($wiki, $isPrimary, array $includeList)
    {
        $relationships = [];

        if ($isPrimary) {
            $relationships = $this->addAuthorRelationship($relationships, $wiki, $includeList);
            $relationships = $this->addRangeRelationship($relationships, $wiki, $includeList);
        }

        return $relationships;
    }

    private function addAuthorRelationship($relationships, $wiki, $includeList)
    {
        $data = in_array(self::REL_AUTHOR, $includeList)
              ? $wiki->author
              : \User::build(['id' => $wiki->user_id], false);
        $relationships[self::REL_AUTHOR] = [
            self::LINKS => [
                Link::RELATED => new Link('/users/'.studip_utf8encode($wiki->user_id)),
            ],
            self::DATA => $data,
        ];

        return $relationships;
    }

    private function addRangeRelationship($relationships, $wiki, $includeList)
    {
        $relationships[self::REL_RANGE] = [
            self::LINKS => [
                Link::RELATED => new Link('/'.self::getRangeType($wiki).'/'.studip_utf8encode($wiki->range_id)),
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
