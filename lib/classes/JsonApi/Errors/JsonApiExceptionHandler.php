<?php

namespace JsonApi\Errors;

use JsonApi\Providers\JsonApiConfig as C;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;
use Neomerx\JsonApi\Contracts\Schema\ContainerInterface as SC;
use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Encoder\EncoderOptions;
use Neomerx\JsonApi\Exceptions\ErrorCollection;
use Neomerx\JsonApi\Exceptions\JsonApiException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Dieser spezielle Exception Handler wird in der Slim-Applikation
 * für alle JSON-API-Routen installiert und sorgt dafür, dass auch
 * evtl. Fehler JSON-API-kompatibel geliefert werden.
 */
class JsonApiExceptionHandler
{
    private $previous;

    private $container;

    /**
     * Der Konstruktor...
     *
     * @param ContainerInterface $container der Dependency Container,
     *                                      der in der Slim-Applikation verwendet wird
     * @param callable           $previous  der zuvor installierte `Error
     *                                      Handler` als Fallback
     */
    public function __construct(ContainerInterface $container, $previous = null)
    {
        $this->previous = $previous;
        $this->container = $container;
    }

    /**
     * Diese Methode wird aufgerufen, sobald es zu einer Exception
     * kam, und generiert eine entsprechende JSON-API-spezifische Response.
     *
     * @param Request    $request   der eingehende Request
     * @param Response   $response  die vorbereitete ausgehende Response
     * @param \Exception $exception die aufgetretene Exception
     *
     * @return Response die JSON-API-kompatible Response
     */
    public function __invoke(Request $request, Response $response, \Exception $exception)
    {
        if ($exception instanceof JsonApiException) {
            $httpCode = $exception->getHttpCode();
            $errors = $exception->getErrors();
        } else {
            $httpCode = 500;
            $details = null;

            $debugEnabled = \Studip\ENV === 'development';
            if ($debugEnabled === true) {
                $message = $exception->getMessage();
                $details = (string) $exception;
            }
            $errors = new ErrorCollection();
            $errors->add(new Error(null, null, $httpCode, null, $message, $details));
        }

        if (sizeof($errors)) {
            $encoder = $this->createEncoder();
            $response = $response
                      ->withHeader(
                          'Content-Type',
                          sprintf('%s/%s',
                                  MediaTypeInterface::JSON_API_TYPE,
                                  MediaTypeInterface::JSON_API_SUB_TYPE
                          )
                      )
                      ->write($encoder->encodeErrors($errors));
        }

        return $response->withStatus($httpCode);
    }

    private function createEncoder()
    {
        $factory = $this->container[FactoryInterface::class];
        $schemaContainer = $this->container[SC::class];

        $urlPrefix = $this->container[C::JSON_URL_PREFIX];

        $encoderOptions = new EncoderOptions(0, $urlPrefix);

        return $factory->createEncoder($schemaContainer, $encoderOptions);
    }
}
