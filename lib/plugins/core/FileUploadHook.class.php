<?php
interface FileUploadHook
{
    /**
     * If this method returns a URL the new page will be shown right after adding a file
     * into a filesystem.
     *
     * @param $file_ref
     * @return null|string: URL or null if no page should be added
     */
    public function getAdditionalUploadWizardPage($file_ref);
}
