<?php

use JsonApi\Schemas\ContentTermsOfUse;
use JsonApi\Schemas\FileRef;
use JsonApi\Schemas\File as FileSchema;

trait FilesTestHelper
{
    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function prepareTopFolder($credentials, $courseId)
    {
        $course = \Course::find($courseId);
        $this->assertNotNull($course);

        $oldUser = $GLOBALS['user'];
        $GLOBALS['user'] = new \Seminar_User($credentials['id']);

        $rootFolder = Folder::createTopFolder($course->id, 'course');
        $this->assertNotNull($rootFolder);

        $GLOBALS['user'] = $oldUser;

        return $rootFolder;
    }

    protected function getSampleLicense()
    {
        $this->assertTrue(\ContentTermsOfUse::countBySql('1') > 0);

        return \ContentTermsOfUse::findOneBySql('1');
    }

    protected function createLicense($name = "Another License")
    {
        return \ContentTermsOfUse::create(
            [
                'name' => $name
            ]
        );
    }

    protected function prepareValidFileRefBody($name, $description, $license, \FileType $filetype = null)
    {
        $json = [
            'data' => [
                'type' => FileRef::TYPE,
                'attributes' => [
                    'name' => $name,
                    'description' => $description,
                ],
                'relationships' =>
                $filetype
                ? ['file' => [
                        'data' => [
                            'type' => FileSchema::TYPE,
                            'id' => $filetype->getFileRef()['file_id']
                        ]
                    ]
                ]
                : [],
            ],
        ];

        if ($license) {
            $json['data']['relationships']['terms-of-use'] = [
                'data' => [
                    'type' => ContentTermsOfUse::TYPE,
                    'id' => (string) $license->id,
                ],
            ];
        }

        return $json;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getTmpPath()
    {
        return $GLOBALS['TMP_PATH'];
    }

    protected function getTmpFile()
    {
        $filename = tempnam($this->getTmpPath(), 'jsonapi');

        $handle = fopen($filename, "w");
        fwrite($handle, "writing to tempfile");
        fclose($handle);

        return $filename;
    }

    protected function createFileInFolder($credentials, $folder, $name, $description, $license = null)
    {
        $numFiles = \File::countBySQL('1');
        $numFileRefs = \FileRef::countBySQL('1');
        $file = \StandardFile::create(
            [
                'name' => $name,
                'description' => $description,
                'size' => 0,
                'tmp_name' => $this->getTmpFile(),
                'content_terms_of_use_id' => $license
            ],
            $credentials['id']
        );
        $file = $file->addToFolder($folder->getTypedFolder(), $name, $credentials['id']);
        $this->assertSame($numFiles + 1, \File::countBySQL('1'));
        $this->assertSame($numFileRefs + 1, \FileRef::countBySQL('1'));

        return $file;
    }
}
