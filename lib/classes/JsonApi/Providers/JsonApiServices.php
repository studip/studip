<?php

namespace JsonApi\Providers;

use JsonApi\Contracts\JsonApiPlugin;
use JsonApi\JsonApiIntegration\Factory;
use JsonApi\JsonApiIntegration\Responses;
use JsonApi\Providers\JsonApiConfig as C;
use Neomerx\JsonApi\Contracts\Codec\CodecMatcherInterface;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\HeaderParametersInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\HeadersCheckerInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;
use Neomerx\JsonApi\Contracts\Http\ResponsesInterface;
use Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use Neomerx\JsonApi\Encoder\EncoderOptions;
use Neomerx\JsonApi\Http\Headers\MediaType;
use Neomerx\JsonApi\Http\Headers\SupportedExtensions;
use Neomerx\JsonApi\Decoders\ArrayDecoder;

class JsonApiServices implements \Pimple\ServiceProviderInterface
{
    /**
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function register(\Pimple\Container $container)
    {
        // register factory
        $container[FactoryInterface::class] = function ($c) {
            $factory = new Factory();

            $factory->setDependencyInjectionContainer(new \Pimple\Psr11\Container($c));

            if ($c->has('logger')) {
                $factory->setLogger($c['logger']);
            }

            return $factory;
        };

        // register schemas
        $container[ContainerInterface::class] = function ($c) {
            $schemas = isset($c[C::SCHEMAS]) ? $c[C::SCHEMAS] : [];
            $schemaContainer = $c[FactoryInterface::class]->createContainer($schemas);

            $pluginSchemas = \PluginEngine::sendMessage(JsonApiPlugin::class, 'registerSchemas', $schemaContainer);
            if (is_array($pluginSchemas) && count($pluginSchemas)) {
                foreach ($pluginSchemas as $arrayOfSchemas) {
                    $schemaContainer->registerArray($arrayOfSchemas);
                }
            }

            return $schemaContainer;
        };

        // register codec matcher
        $container[CodecMatcherInterface::class] = function ($c) {
            return $this->createCodecMatcher($c);
        };

        // TODO wo wird das gebraucht
        $container[HeadersCheckerInterface::class] = function ($c) {
            return $c[FactoryInterface::class]->createHeadersChecker($c[CodecMatcherInterface::class]);
        };

        // register query params
        $container[EncodingParametersInterface::class] = function ($c) {
            return $c[FactoryInterface::class]->createQueryParametersParser()->parse($c['request']);
        };

        $container[HeaderParametersInterface::class] = function ($c) {
            return $c[FactoryInterface::class]->createHeaderParametersParser()->parse($c['request']);
        };

        // register responses
        $container[ResponsesInterface::class] = function ($c) {
            return $this->createResponses($c);
        };
    }

    /**
     * @param ContainerInterface $container
     *
     * @return ResponsesInterface
     */
    protected function createResponses($container)
    {
        $codecMatcher = $container[CodecMatcherInterface::class];
        $parameters = $container[EncodingParametersInterface::class];
        $params = $container[HeaderParametersInterface::class];

        $codecMatcher->matchEncoder($params->getAcceptHeader());
        $encoder = $codecMatcher->getEncoder();

        $schemaContainer = $container[ContainerInterface::class];
        $urlPrefix = $container[C::JSON_URL_PREFIX];

        $responses = new Responses(
            new MediaType(MediaTypeInterface::JSON_API_TYPE, MediaTypeInterface::JSON_API_SUB_TYPE),
            new SupportedExtensions(),
            $encoder,
            $schemaContainer,
            $parameters,
            $urlPrefix
        );

        return $responses;
    }

    /**
     * @param ContainerInterface $schemaContainer
     *
     * @return CodecMatcherInterface
     */
    protected function createCodecMatcher($container)
    {
        $factory = $container[FactoryInterface::class];
        $schemaContainer = $container[ContainerInterface::class];

        $encoderOptions = new EncoderOptions(0, $container[C::JSON_URL_PREFIX]);

        $decoderClosure = $this->getDecoderClosure();
        $encoderClosure = $this->getEncoderClosure($factory, $schemaContainer, $encoderOptions);
        $codecMatcher = $factory->createCodecMatcher();
        $jsonApiType = $factory->createMediaType(
            MediaTypeInterface::JSON_API_TYPE,
            MediaTypeInterface::JSON_API_SUB_TYPE
        );
        $jsonApiTypeUtf8 = $factory->createMediaType(
            MediaTypeInterface::JSON_API_TYPE,
            MediaTypeInterface::JSON_API_SUB_TYPE,
            ['charset' => 'UTF-8']
        );
        $codecMatcher->registerEncoder($jsonApiType, $encoderClosure);
        $codecMatcher->registerDecoder($jsonApiType, $decoderClosure);
        $codecMatcher->registerEncoder($jsonApiTypeUtf8, $encoderClosure);
        $codecMatcher->registerDecoder($jsonApiTypeUtf8, $decoderClosure);

        return $codecMatcher;
    }

    /**
     * @return Closure
     */
    protected function getDecoderClosure()
    {
        return function () {
            return new ArrayDecoder();
        };
    }

    /**
     * @param FactoryInterface   $factory
     * @param ContainerInterface $container
     * @param EncoderOptions     $encoderOptions
     *
     * @return Closure
     */
    private function getEncoderClosure(
        FactoryInterface $factory,
        ContainerInterface $container,
        EncoderOptions $encoderOptions
    ) {
        return function () use ($factory, $container, $encoderOptions) {
            return $factory->createEncoder($container, $encoderOptions);
        };
    }
}
