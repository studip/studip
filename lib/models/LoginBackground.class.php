<?php
/**
 * LoginBackground.class.php
 * model class for table loginbackgrounds
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string background_id database column
 * @property string id alias column for background_id
 * @property string filename database column
 * @property string active database column
 * @property string in_release database column
 */
class LoginBackground extends SimpleORMap
{
    /**
     * Configures this model.
     *
     * @param Array $config
     */
    protected static function configure($config = array())
    {
        $config['db_table'] = 'loginbackgrounds';

        parent::configure($config);
    }

    /**
     * @return string The full URL to this picture.
     */
    public function getURL()
    {
        return URLHelper::getURL(
            self::getRelativePath() . '/' . $this->id
            . '.' . pathinfo($this->filename, PATHINFO_EXTENSION),
            null,
            true
        );
    }

    /**
     * @return string the full file system path to this picture.
     */
    public function getPath()
    {
        return self::getPictureDirectory() . DIRECTORY_SEPARATOR
             . $this->id . '.' . pathinfo($this->filename, PATHINFO_EXTENSION);
    }

    /**
     * @return int The file size in bytes.
     */
    public function getFilesize()
    {
        return filesize($this->getPath());
    }

    /**
     * @return int The picture dimensions as provided by getimagesize.
     */
    public function getDimensions()
    {
        return getimagesize($this->getPath());
    }

    /**
     * Deletes the current picture by removing all data from database and file system.
     * @return int
     */
    public function delete()
    {
        unlink($this->getPath());
        return parent::delete();
    }


    /**
     * Provides a random picture for the given view.
     * @param string $view one of 'desktop', 'mobile'.
     * @return LoginBackground One of the available pictures.
     */
    public static function getRandomPicture($view = 'desktop')
    {
        if (!in_array($view, ['desktop', 'mobile'])) {
            throw new Exception('Unknown view mode');
        }

        $pic = self::findOneBySQL($view . " = 1 ORDER BY RAND() LIMIT 1");
        return $pic;
    }

    /**
     * @return string The relative path to the Stud.IP web root.
     */
    public static function getRelativePath()
    {
        return 'pictures' . DIRECTORY_SEPARATOR . 'loginbackgrounds';
    }

    /**
     * @return The directory where all available background pictures live.
     */
    public static function getPictureDirectory()
    {
        return $GLOBALS['STUDIP_BASE_PATH'] . DIRECTORY_SEPARATOR
             . 'public' . DIRECTORY_SEPARATOR . self::getRelativePath();
    }

}
