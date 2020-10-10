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
 * Vetagronews Block task
 *
 * @package   block_vetagro_news
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_vetagro_news\tasks;

defined('MOODLE_INTERNAL') || die();

use block_base;
use DOMDocument;
use DOMXPath;

class refresh_vetagro_news extends \core\task\scheduled_task {

    const BLOCK_NAME = 'vetagro_news';

    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     */
    public function get_name() {
        return get_string('refreshnewstasks', 'block_vetagro_news');
    }

    /**
     * Execute the task.
     */
    public function execute() {
        global $DB;
        $blockinstances = $DB->get_records('block_instances', ['blockname' => self::BLOCK_NAME]);
        foreach ($blockinstances as $blockinstance) {
            $instance = block_instance($blockinstance->blockname, $blockinstance);
            $articles = static::get_articles_from_page($instance);
            $instance->config->articles = $articles;
            $instance->instance_config_save($instance->config);
        }
    }

    public static function get_articles_from_page(block_base $instance) {
        if ($instance->config && $instance->config->pageurl) {
            $homepagecontent = file_get_contents($instance->config->pageurl);
            $domdocument = new DOMDocument();
            @$domdocument->loadHTML($homepagecontent);
            $moodlearticles = [];
            if ($domdocument) {
                $xpath = new DOMXPath($domdocument);
                if ($instance->config->itemxpath) {
                    $articles = $xpath->query($instance->config->itemxpath);
                    foreach ($articles as $article) {
                        $linkurlnode = $xpath->query('.' . $instance->config->linkxpath, $article);
                        $imageurlnode = $xpath->query('.' . $instance->config->imagexpath, $article);
                        $datenode = $xpath->query('.' . $instance->config->datexpath, $article);
                        $categoriesnodes = $xpath->query('.' . $instance->config->categoriesxpath, $article);
                        $titlenode = $xpath->query('.' . $instance->config->titlexpath, $article);

                        $imageurl = $imageurlnode->length > 0 ? $imageurlnode->item(0)->nodeValue : '';
                        $linkurl = $linkurlnode->length > 0 ? $linkurlnode->item(0)->nodeValue : '';
                        $date = $datenode->length > 0 ? $datenode->item(0)->textContent : '';
                        $title = $titlenode->length > 0 ? $titlenode->item(0)->textContent : '';
                        $catnodes = [];
                        foreach ($categoriesnodes as $cat) {
                            $catnodes[] = (object)
                            [
                                'text' => html_to_text($cat->textContent),
                                'url' => $cat->getAttribute('href')
                            ];
                        }
                        if ($imageurl && $title) {
                            $moodlearticles[] =
                                (object) [
                                    'imageurl' => (new \moodle_url($imageurl))->out(),
                                    'linkurl' => (new \moodle_url($linkurl))->out(),
                                    'categories' => $catnodes,
                                    'date' => html_to_text($date),
                                    'title' => html_to_text($title)
                                ];
                        }
                    }
                }
            }
            return $moodlearticles;
        }
    }
}
