<?php
/**
 * banner.php - model class for the banner administration
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Nico Müller <nico.mueller@uni-oldenburg.de>
 * @copyright   2012 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     admin
 * @since       2.4
 *
 * @property string ad_id database column
 * @property string id alias column for ad_id
 * @property string banner_path database column
 * @property string description database column
 * @property string alttext database column
 * @property string target_type database column
 * @property string target database column
 * @property string startdate database column
 * @property string enddate database column
 * @property string priority database column
 * @property string views database column
 * @property string clicks database column
 * @property string mkdate database column
 * @property string chdate database column
 */

class Banner extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'banner_ads';
        parent::configure($config);
    }

    /**
     * Returns a random banner
     */
    public static function getRandomBanner()
    {
        $query = "SELECT ad_id, priority, startdate, enddate
                  FROM banner_ads
                  WHERE priority > 0
                    AND (startdate = 0 OR startdate < UNIX_TIMESTAMP())
                    AND (enddate = 0 OR enddate > UNIX_TIMESTAMP())";
        $statement = DBManager::get()->query($query);

        // array that contains banner ids and an offset
        // offsets start with 0 and increase by pow(2, priority)
        // a random number between 0 and sum(pow(2,priorities)) is
        // drawn and the banner with the highest offset smaller than
        // this number is chosen

        $banners = [];
        $sum = 0;
        // collect banners to consider, build banners array
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $sum += pow(2, $row['priority']);
            $banners[] = [
                'ad_id'  => $row['ad_id'],
                'offset' => $sum
            ];
        }

        // draw random number and select banner
        $x = mt_rand(0, $sum);
        $ad_id = false;
        foreach ($banners as $i) {
            if ($i['offset'] >= $x) {
                $ad_id = $i['ad_id'];
                break;
            }
        }

        return new Banner($ad_id);
    }

    /**
     * Get all banners
     *
     * @return  array() list of banners
     */
    public static function getAllBanners()
    {
        $query = "SELECT ad_id FROM banner_ads ORDER BY priority DESC";
        $statement = DBManager::get()->query($query);
        $ids = $statement->fetchAll(PDO::FETCH_COLUMN);

        $banners = [];
        foreach ($ids as $id) {
            $banners[$id] = new Banner($id);
        }
        return $banners;
    }

    /**
     * delete entry from database
     * the object is cleared and turned to new state
     * @return boolean
     */
    public function delete()
    {
        if (!$this->isNew()) {
            // Remove banner file
            unlink($GLOBALS['DYNAMIC_CONTENT_PATH'] . '/banner/' . $this->banner_path);
        }
        return parent::delete();
    }

    /*
     * Check the priority for a banner
     * @param Int $prio priority (1-10)
     * @return
     */
    public function getViewProbability()
    {
        static $computed = false, $sum = null;

        if ($this->priority == 0) {
            return '--';
        }

        if ($computed === false) {
            $sum = DBManager::get()->query("SELECT SUM(POW(2, priority)) FROM banner_ads WHERE priority > 0")
                                   ->fetchColumn();
            $computed = true;
        }
//        return '1/' . (1 / (pow(2, $prio) / $sum));
        return number_format(100 / (1 / (pow(2, $this->priority) / $sum)), 2, ',', '.') . '%';
    }


    /**
     * Returns the appropriate link for this banner.
     *
     * @return string
     */
    public function getLink($internal = false)
    {
        if ($this->isNew()) {
            return '';
        }

        if ($internal) {
            return URLHelper::getLink('dispatch.php/banner/click/' . $this->ad_id);
        }

        if ($this->target_type === 'url') {
            return $this->target;
        }
        if ($this->target_type === 'seminar') {
            return URLHelper::getLink('dispatch.php/course/details/', ['sem_id' => $this->target]);
        }
        if ($this->target_type === 'user') {
            return URLHelper::getLink('dispatch.php/profile', ['username' => $this->target]);
        }
        if ($this->target_type === 'inst') {
            return URLHelper::getLink(
                'dispatch.php/institute/overview',
                ['auswahl' => $this->target]
            );
        }

        return '';
    }

    /**
     * Returns the img-tag for this banner
     *
     * @return string
     */
    public function toImg($attributes = [])
    {
        $attr = [
            'src'    => $GLOBALS['DYNAMIC_CONTENT_URL'] . '/banner/' . $this->banner_path,
            'border' => '0',
        ];
        if ($this->alttext) {
            $attr['title'] = $attr['alt'] = $this->alttext;
        }
        $attr = array_merge($attr, $attributes);

        $attr_string = '';
        foreach ($attr as $key => $value) {
            $attr_string .= sprintf(' %s="%s"', $key, htmlReady($value));
        }

        return '<img' . $attr_string . '>';
    }

    /**
     * Returns the complete html (link + img) for this banner
     *
     * @return string
     */
    public function toHTML($internal = true)
    {
        if ($this->isNew()) {
            return '';
        }

        if ($this->target_type === 'url') {
            $template = '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>';
        } elseif ($this->target_type === 'none') {
            $template = '%2$s';
        } else {
            $template = '<a href="%s">%s</a>';
        }

        $link = sprintf($template, $this->getLink($internal), $this->toImg());

        $this->views += 1;
        $this->store();

        return sprintf('<div style="padding: 5px; text-align: center;">%s</div>', $link);
   }
}
