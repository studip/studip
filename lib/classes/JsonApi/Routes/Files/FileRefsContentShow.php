<?php

namespace JsonApi\Routes\Files;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\InternalServerError;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\NonJsonApiController;
use GuzzleHttp\Psr7;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FileRefsContentShow extends NonJsonApiController
{
    use EtagHelperTrait;

    public function invoke(Request $request, Response $response, $args)
    {
        if (!$fileRef = \FileRef::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canDownloadFileRef($this->getUser($request), $fileRef)) {
            throw new AuthorizationFailedException();
        }

        return $this->sendFile($request, $response, $fileRef);
    }

    /**
     * Copied and slightly edited for easy merging of future changes
     * to sendfile.php.
     *
     * @SuppressWarnings(CyclomaticComplexity)
     * @SuppressWarnings(NPathComplexity)
     */
    private function sendFile(Request $request, Response $response, \FileRef $fileRef)
    {
        //replace bad charakters to avoid problems when saving the file
        $fileName = \FileManager::cleanFileName($fileRef->name);
        $filetype = $fileRef->getFileType();
        $pathFile = $filetype->getPath() ?: $filetype->getDownloadURL();
        $contentType = $fileRef->mime_type ?: get_mime_type($fileName);

        // check if linked file is obtainable
        if ('proxy' == $fileRef->file->metadata['access_type']) {
            $linkData = \FileManager::fetchURLMetadata($fileRef->file->metadata['url']);
            if (200 != $linkData['response_code']) {
                throw new InternalServerError(
                    _('Diese Datei wird von einem externen Server geladen und ist dort momentan nicht erreichbar!')
                );
            }
            $contentType = $linkData['Content-Type']
                         ? strstr($linkData['Content-Type'], ';', true)
                         : get_mime_type($fileName);

            $filesize = $linkData['Content-Length'] ?: false;
        }

        if ($filetype->getPath()) {
            $filesize = @filesize($pathFile);
            if (false === $filesize) {
                throw new InternalServerError(
                    _('Fehler beim Laden der Inhalte der Datei')
                );
            }

            list($done, $response) = $this->handleEtag($request, $response, $fileRef);
            if ($done) {
                return $response;
            }
        }

        if ('redirect' == $fileRef->file->metadata['access_type']) {
            return $response->withRedirect($fileRef->file->metadata['url']);
        }

        $contentBlacklisted = function ($mime) {
            foreach (['html', 'javascript', 'svg', 'xml'] as $check) {
                if (false !== stripos($mime, $check)) {
                    return true;
                }
            }

            return false;
        };
        if ($contentBlacklisted($contentType)) {
            $contentType = 'application/octet-stream';
        }

        $headers = [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; '.encode_header_parameter('filename', $fileName),
        ];

        if ($filesize) {
            $headers['Content-Length'] = $filesize;
        }

        $isHttps = 'https' === $request->getUri()->getScheme();
        $headers['Cache-Control'] = $isHttps
                                  ? 'private'
                                  : 'no-cache, no-store, must-revalidate';

        \Metrics::increment('core.file_download');

        foreach ($headers as $key => $value) {
            if ($response->hasHeader($key)) {
                $response = $response->withAddedHeader($key, $value);
            } else {
                $response = $response->withHeader($key, $value);
            }
        }

        $fileRef->incrementDownloadCounter();

        $stream = Psr7\stream_for(fopen($pathFile, 'rb'));

        return $response->withBody($stream);
    }
}
