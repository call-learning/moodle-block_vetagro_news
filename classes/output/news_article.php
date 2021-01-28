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
 * Vetagronews block renderable
 *
 * @package   block_vetagro_news
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_vetagro_news\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;

/**
 * Class news_article
 *
 * @package   block_vetagro_news
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class news_article implements renderable, templatable {

    /**
     * Default scroll speed for the carousel.
     */
    const DEFAULT_SCROLL_SPEED = 4000;

    /**
     * @var array articles
     */
    public $articles = [];

    /**
     * @var object|null $blockconfig block config
     */
    public $blockconfig = null;

    /**
     * featured_courses constructor.
     * Retrieve matchin courses
     *
     * @param array $articles
     * @param object $blockconfig block configuration
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function __construct($articles, $blockconfig) {
        $this->articles = $articles;
        $this->blockconfig = $blockconfig;
    }

    /**
     * Export for template
     *
     * @param renderer_base $renderer
     * @return array|\stdClass
     */
    public function export_for_template(renderer_base $renderer) {
        $exportedvalue = [
            'articles' => array_values((array) $this->articles),
            'count' => count($this->articles),
            'scrolltimer' => (empty($this->blockconfig) || empty($this->blockconfig->scrolltimer)) ?
                self::DEFAULT_SCROLL_SPEED : $this->blockconfig->scrolltimer
        ];
        return $exportedvalue;
    }
}