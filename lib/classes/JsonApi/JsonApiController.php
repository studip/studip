<?php

namespace JsonApi;

use JsonApi\JsonApiIntegration\JsonApiTrait;
use Psr\Container\ContainerInterface;

use Neomerx\JsonApi\Contracts\Http\Headers\HeadersCheckerInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\HeaderParametersInterface;

/**
 * Ein JsonApiController ist die einfachste Möglichkeit, eine eigene
 * JSON-API-Route zu erstellen.
 *
 * Dazu erstellt man eine Unterklasse von JsonApiController und kann
 * darin __invoke oder andere Methoden definieren und diese in der
 * RouteMap registrieren.
 *
 * Wenn man auf den JsonApiController verzichten möchte, muss man den
 * JsonApiTrait in seiner eigenen Lösung einbinden und außerdem den
 * Dependency Container als Instanzvariabel $this->container eintragen
 * und die Methode JsonApiTrait::initJsonApiSupport aufrufen.
 *
 * Diese Klasse hier übernimmt all diese Aufgaben selbst.
 *
 * @see \JsonApi\JsonApiIntegration\JsonApiTrait
 * @see \JsonApi\RouteMap
 */
class JsonApiController
{
    use JsonApiTrait;

    /**
     * Der Konstruktor.
     *
     * @param ContainerInterface $container der Dependency Container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->initJsonApiSupport($container);

        $headerChecker = $this->container[HeadersCheckerInterface::class];
        $headerChecker->checkHeaders($this->container[HeaderParametersInterface::class]);
    }
}
