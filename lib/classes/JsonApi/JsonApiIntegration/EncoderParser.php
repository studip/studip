<?php

namespace JsonApi\JsonApiIntegration;

use SimpleORMap;
use Neomerx\JsonApi\Encoder\Parser\Parser as NeomerxParser;

/**
 * Eine Instanz von Neomerx\JsonApi\Encoder\Parser\Parser wird
 * benötigt, um Werte, die an den JSON-API-Encoder gehen, zu
 * analysieren und entsprechned weiter zu verarbeiten. Unter anderem
 * wird darin auch die Unterscheidung getroffen, ob Werte, die an den
 * JSON-API-Encoder gehen, Collections sind oder nicht.
 *
 * Bei dieser Analyse werden sinnvollerweise alle Werte, die das
 * PHP-Interface \IteratorAggregate implementieren, als Collections
 * behandelt. Da aber die Stud.IP-Klasse \SimpleORMap
 * ungewöhnlicherweise ebenfalls dieses Interface implementiert, muss
 * hier eine Sonderbehandlung stattfinden.
 *
 * Dazu wird die Methode
 * Neomerx\JsonApi\Encoder\Parser\Parser::analyzeCurrentData so
 * überschrieben, dass Instanzen von \SimpleORMap nicht als
 * Collections gelten.
 *
 * @see Neomerx\JsonApi\Encoder\Parser\Parser
 * @see \SimpleORMap
 */
class EncoderParser extends NeomerxParser
{
    /**
     * {@inheritdoc}
     */
    protected function analyzeCurrentData()
    {
        $relationship = $this->stack->end()->getRelationship();
        $data = $relationship->isShowData() === true ? $relationship->getData() : null;

        if ($data instanceof SimpleORMap) {
            $isEmpty = false;
            $isCollection = false;
            $traversableData = [$data];

            return [$isEmpty, $isCollection, $traversableData];
        }

        return parent::analyzeCurrentData();
    }
}
