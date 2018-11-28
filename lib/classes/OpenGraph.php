<?php
/**
 * Open Graph class that extracts open graph urls from a given string.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 3.4
 */
class OpenGraph
{
    /**
     * Extracts urls and their according open graph infos from a given string
     *
     * @param String $string Text to extract urls and open graph infos from
     * @return OpenGraphURLCollection containing the extracted urls
     */
    public static function extract($string)
    {
        $collection = new OpenGraphURLCollection;

        if (Config::get()->OPENGRAPH_ENABLE) {
            $regexp = StudipCoreFormat::getStudipMarkup('links')['start'];
            $matched = preg_match_all('/' . $regexp . '/ums', $string, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $url = $match[2];

                if (!$url) {
                    continue;
                }

                if (!isLinkIntern($url)) {
                    $og_url = OpenGraphURL::fromURL($url);
                    if ($og_url && !$collection->find($og_url->id)) {
                        $og_url->store();

                        $collection[] = $og_url;
                    }
                }
            }
        }

        return $collection;
    }
}
