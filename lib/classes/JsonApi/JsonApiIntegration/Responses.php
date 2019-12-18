<?php

namespace JsonApi\JsonApiIntegration;

use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\SupportedExtensionsInterface;
use Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use Neomerx\JsonApi\Http\Responses as NeomerxResponses;
use Slim\Http\Headers;
use Slim\Http\Response;

/**
 * Diese Factory-Klasse verknüpft die "neomerx/json-api"-Bibliothek mit der
 * Slim-Applikation. Hier wird festgelegt, wie Slim-artige Response-Objekte gebildet
 * werden.
 */
class Responses extends NeomerxResponses
{
    /**
     * @var EncodingParametersInterface|null
     */
    private $parameters;

    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var MediaTypeInterface
     */
    private $outputMediaType;

    /**
     * @var SupportedExtensionsInterface
     */
    private $extensions;

    /**
     * @var ContainerInterface
     */
    private $schemes;

    /**
     * @var null|string
     */
    private $urlPrefix;

    /**
     * Dieser Konstruktor wird in \JsonApi\Providers\JsonApiServices
     * befüllt.
     *
     * @param MediaTypeInterface               $outputMediaType
     * @param SupportedExtensionsInterface     $extensions
     * @param EncoderInterface                 $encoder
     * @param ContainerInterface               $schemes
     * @param EncodingParametersInterface|null $parameters
     * @param string|null                      $urlPrefix
     *
     * @internal
     */
    public function __construct(
        MediaTypeInterface $outputMediaType,
        SupportedExtensionsInterface $extensions,
        EncoderInterface $encoder,
        ContainerInterface $schemes,
        EncodingParametersInterface $parameters = null,
        $urlPrefix = null
    ) {
        $this->extensions = $extensions;
        $this->encoder = $encoder;
        $this->outputMediaType = $outputMediaType;
        $this->schemes = $schemes;
        $this->urlPrefix = $urlPrefix;
        $this->parameters = $parameters;
    }

    /**
     * Diese Methode ist die Schlüsselstelle der ganzen Klasse. Es
     * werden Body, Statuscode und Headers der zukünftigen Response
     * übergeben und eine \Slim\Http\Response zurückgegeben.
     *
     * @param string|null $content    der Body der zukünftigen Response
     * @param int         $statusCode der numerische Statuscode der
     *                                zukünftigen Response
     * @param array       $headers    die Header der zukünftigen Response
     *
     * @return \Slim\Http\Response die fertige Slim-Response
     */
    protected function createResponse($content, $statusCode, array $headers)
    {
        $headers = new Headers($headers);
        $response = new Response($statusCode, $headers);
        $response->getBody()->write($content);

        return $response->withProtocolVersion('1.1');
    }

    /**
     * {@inheritdoc}
     *
     * @internal
     */
    protected function getEncoder()
    {
        return $this->encoder;
    }

    /**
     * {@inheritdoc}
     *
     * @internal
     */
    protected function getUrlPrefix()
    {
        return $this->urlPrefix;
    }

    /**
     * {@inheritdoc}
     *
     * @internal
     */
    protected function getEncodingParameters()
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     *
     * @internal
     */
    protected function getSchemaContainer()
    {
        return $this->schemes;
    }

    /**
     * {@inheritdoc}
     *
     * @internal
     */
    protected function getSupportedExtensions()
    {
        return $this->extensions;
    }

    /**
     * {@inheritdoc}
     *
     * @internal
     */
    protected function getMediaType()
    {
        return $this->outputMediaType;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifiersResponse(
        $data,
        $statusCode = self::HTTP_OK,
        $links = null,
        $meta = null,
        array $headers = []
    ) {
        $encoder = $this->getEncoder();

        $links === null ?: $encoder->withLinks($links);
        $meta === null ?: $encoder->withMeta($meta);
        $content = $encoder->encodeIdentifiers($data, $this->getEncodingParameters());

        return $this->createJsonApiResponse($content, $statusCode, $headers);
    }

    /**
     * Widen method visibility from protected to public.
     *
     * {@inheritdoc}
     */
    public function getResourceLocationUrl($resource)
    {
        return parent::getResourceLocationUrl($resource);
    }
}
