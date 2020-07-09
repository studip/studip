<?php

namespace JsonApi\Routes\Files;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

trait EtagHelperTrait
{
    protected static function createEtag(\FileRef $fileRef, $fetchLinked = false)
    {
        $etag = null;
        $filetype = $fileRef->getFileType();

        if ($pathFile = $filetype->getPath()) {
            if ($stat = stat($pathFile)) {
                $etag = sprintf('"%s %d %d"', $stat['ino'], $stat['mtime'], $stat['size']);
            }
        } elseif ($fetchLinked && is_a("URLFile", $filetype)) {
            $metadata = \FileManager::fetchURLMetadata($fileRef->file->metadata['url']);
            if (isset($metadata['Etag'])) {
                $etag = $metadata['Etag'];
            }
        }

        return $etag;
    }

    protected function handleEtag(Request $request, Response $response, \FileRef $fileRef, $fetchLinked = false)
    {
        if (!$etag = self::createEtag($fileRef, $fetchLinked)) {
            return [false, $response];
        }
        $response = $response->withHeader('ETag', $etag);

        if ($ifNoneMatch = $this->getIfNoneMatchHeader($request)) {
            $etagList = preg_split('@\s*,\s*@', $ifNoneMatch);

            if (in_array($etag, $etagList) || in_array('*', $etagList)) {
                return [true, $response->withStatus(304)];
            }
        }

        return [false, $response];
    }

    protected function getIfNoneMatchHeader(Request $request)
    {
        if ($request->hasHeader('If-None-Match')) {
            $ifNoneMatch = $request->getHeaderLine('If-None-Match');
        } elseif ($request->hasHeader('HTTP_IF_NON_MATCH')) {
            $ifNoneMatch = $request->getHeaderLine('HTTP_IF_NON_MATCH');
        } else {
            $ifNoneMatch = false;
        }

        return $ifNoneMatch;
    }
}
