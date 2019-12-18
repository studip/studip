<?php

namespace JsonApi\Routes;

use Psr\Http\Message\ServerRequestInterface as Request;
use JsonApi\Errors\UnprocessableEntityException;

trait ValidationTrait
{
    use ArrayHelperTrait;

    /**
     * In dieser Funktion wird die tatsächliche Validierung
     * implementiert.
     * Man erhält das `resource document` bereits dekodiert als Array
     * und kann anhand dessen die Validität prüfen.
     *
     * Will man einen Fehler melden, muss die Funktion diesen als
     * String zurückgeben.
     * Wenn das `resource document` valide ist, gibt man nichts bzw. `null`
     * zurück.
     *
     * @param array $json das dekodierte JSON des `resource document`
     *                    als PHP-Array
     * @param any   $data beliebige Daten, die an `validate` übergeben
     *                    wurden
     *
     * @return mixed im Fehlerfall ein String, der den Fehler
     *               beschreibt; ansonsten nichts bzw. `null`
     */
    abstract protected function validateResourceDocument($json, $data);

    /**
     * Wenn man das `resource document`, das im `body` des Requests
     * mitgeschickt wurde, validieren möchte, ruft man in seinem
     * JsonApiController diese Funktion auf.
     * Diese Funktion dekodiert dann den das `resource document` aus
     * dem Request und ruft dann damit die im JsonApiController implementierte
     * Funktion `validateResourceDocument` auf. Hat
     * `validateResourceDocument` einen Fehler gemeldet, wird eine
     * UnprocessableEntityException geworfen; ansonsten gibt diese
     * Funktion das dekodierte `resource document` als PHP-Array
     * zurück.
     *
     * @param Request $request der Request, der das `resource
     *                         document` enthält
     * @param any     $data    beliebige Daten, die an
     *                         `validateResourceDocument` durchgereicht werden
     *
     * @return array das dekodierte `resource document` als PHP-Array
     *
     * @throws UnprocessableEntityException falls bei der Validierung
     *                                      ein Fehler auftrat
     */
    protected function validate(Request $request, $data = null)
    {
        $json = $this->decodeRequestBody($request);
        if ($error = $this->validateResourceDocument($json, $data)) {
            throw new UnprocessableEntityException($error);
        }

        return $json;
    }

    /**
     * Dekodiert den im Body des Requests enthaltenen JSON-String und
     * gibt einen Wert im entsprechenden PHP-Typ zurück.
     *
     * @param $request Request der eingehende Request
     *
     * @return mixed gibt den JSON-kodierten Wert in geeignetem
     *               PHP-Typ zurück (siehe json_decode)
     */
    protected function decodeRequestBody(Request $request)
    {
        $body = (string) $request->getBody();
        if ('' === $body) {
            throw new UnprocessableEntityException('Empty request');
        }
        $result = json_decode($body, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new UnprocessableEntityException(json_last_error_msg());
        }

        return $result;
    }

    /**
     * Überprüft, ob der Wert eines Pfads in einem JSON-Array ein
     * gültiges JSON-API Resource Object ist. Optional wird auch der
     * type des Resource Objects geprüft.
     *
     * @param $json array   das JSON-Array
     * @param $path string  der Pfad in das JSON-Array
     * @param $optionalType mixed optionaler String, der den Typ des
     * Resource Objects beschreibt
     *
     * @return bool TRUE, wenn es sich um das gesuchte (und optional
     *              typgeprüfte) Resource Object handelt, sonst FALSE
     */
    protected function validateResourceObject($json, $path, $optionalType = null)
    {
        if (mb_strlen($path)) {
            $path .= '.';
        }

        return
            self::arrayHas($json, $path.'id')
            &&
            self::arrayHas($json, $path.'type')
            &&
            $optionalType ? self::arrayGet($json, $path.'type') === $optionalType : true;
    }

    // TODO
    protected function validateResourceLinkage($json, $path, $type, $toManyRelation = false, $mayBeEmpty = false)
    {
        if (!self::arrayHas($json, $path.'.data')) {
            return false;
        }

        $data = self::arrayGet($json, $path.'.data');

        if ($toManyRelation) {
            if (!is_array($data)) {
                return false;
            }
            if (0 === count($data)) {
                return $mayBeEmpty;
            }

            foreach ($data as $resourceIdentifier) {
                if (!$this->validateResourceObject($resourceIdentifier, '', $type)) {
                    return false;
                }
            }
        } else {
            if (is_null($data)) {
                return $mayBeEmpty;
            }

            return $this->validateResourceObject($data, '', $type);
        }
    }
}
