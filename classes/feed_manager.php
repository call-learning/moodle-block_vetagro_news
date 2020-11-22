<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Vetagronews Tasks utils
 *
 * @package   block_vetagro_news
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_vetagro_news;

defined('MOODLE_INTERNAL') || die();

use block_base;
use core\plugininfo\block;
use core_date;
use DateTime;
use DOMDocument;
use DOMXPath;

/**
 * Class feed_manager
 *
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class feed_manager {

    /**
     * Default values for xpath settings.
     */
    const DEFAULT_VALUES = [
        'itemxpath' => '//item',
        'linkxpath' => '//link',
        'imagexpath' => "//div[contains(@class, 'blog-img')]/img/@src", // Xpath in subdocument.
        'datexpath' => "//pubDate",
        'categoriesxpath' => "//category",
        'titlexpath' => "//title"
    ];

    /**
     * Block instance
     *
     * @var block_base $blockinstance
     */
    protected $blockinstance = null;

    /**
     * feed_manager constructor.
     *
     * @param block_base $blockinstance
     */
    public function __construct($blockinstance) {
        $this->blockinstance = $blockinstance;
    }

    /**
     * Immediately refresh the block
     *
     * @throws \moodle_exception
     */
    public function refresh_block() {
        if ($this->blockinstance->config && $this->blockinstance->config->pageurl) {
            $feedcontent = file_get_contents($this->blockinstance->config->pageurl);
            $articles = static::get_articles_from_page($feedcontent);
            $this->blockinstance->config->articles = $articles;
            $this->blockinstance->instance_config_save($this->blockinstance->config, true); // Small Hack here, so
            // we are sure we don't launch a refresh task in a loop.
        }
    }

    /**
     * Retrieve an article from a feed. Typically from a wordpress news feed.
     *
     * See http://www.vetagro-sup.fr/feed/ for example.
     *
     * @param string $remotehomepagecontent
     * @return array
     * @throws \moodle_exception
     */
    public function get_articles_from_page($remotehomepagecontent) {
        if ($this->blockinstance->config) {
            $domdocument = new DOMDocument();
            @$domdocument->loadXML($remotehomepagecontent);
            $moodlearticles = [];
            if ($domdocument) {
                $xpath = new DOMXPath($domdocument);
                if ($this->blockinstance->config->itemxpath) {
                    $articles = $xpath->query($this->blockinstance->config->itemxpath);
                    foreach ($articles as $article) {
                        $linkurlnode = $xpath->query('.' . $this->blockinstance->config->linkxpath, $article);
                        $datenode = $xpath->query('.' . $this->blockinstance->config->datexpath, $article);
                        $categoriesnodes = $xpath->query('.' . $this->blockinstance->config->categoriesxpath, $article);
                        $titlenode = $xpath->query('.' . $this->blockinstance->config->titlexpath, $article);
                        if ($linkurlnode->length == 0) {
                            continue;
                        }
                        $linkurl = $linkurlnode->item(0)->nodeValue;
                        $date = $datenode->length > 0 ? $datenode->item(0)->textContent : '';
                        $title = $titlenode->length > 0 ? $titlenode->item(0)->textContent : '';
                        $catnodes = [];
                        foreach ($categoriesnodes as $cat) {
                            $catnodes[] = (object)
                            [
                                'text' => html_to_text($cat->textContent)
                            ];
                        }
                        $dateobject = new DateTime(html_to_text($date));
                        $imageurl = $this->get_image_from_article($linkurl);
                        if ($imageurl && $title) {
                            $moodlearticles[] =
                                (object) [
                                    'imageurl' => (new \moodle_url($imageurl))->out(),
                                    'linkurl' => (new \moodle_url($linkurl))->out(),
                                    'categories' => $catnodes,
                                    'date' => $dateobject->getTimestamp(),
                                    'title' => html_to_text($title)
                                ];
                        }
                    }
                }
            }
            return $moodlearticles;
        }
    }

    /**
     * This gets the related article content to retrieve the image
     *
     * This is because natively wordpress feed do not serve images.
     *
     *
     * @param string $articleurl
     * @return false|string
     */
    public function get_image_from_article($articleurl) {
        // The image is in the article itself. So let's get it.
        $article = $this->get_article_content($articleurl);
        $articledocument = new DOMDocument();
        @$articledocument->loadHTML($article);
        $articlepath = new DOMXPath($articledocument);
        $imageurl = "";
        $imageurlnode = $articlepath->query($this->blockinstance->config->imagexpath);
        if ($imageurlnode->length > 0) {
            $imageurl = $imageurlnode->item(0)->textContent;
        }
        return $imageurl;
    }

    /**
     * Get article content.
     *
     * This can be overriden by unit tests if necessary.
     * @param string $articleurl
     * @return false|string
     */
    protected function get_article_content($articleurl) {
        return file_get_contents($articleurl);
    }
}
