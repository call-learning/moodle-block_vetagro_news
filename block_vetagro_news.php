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
 * Vetagronews Block
 *
 * @package   block_vetagro_news
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Class block_vetagro_news
 *
 * @package    block_vetagro_news
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_vetagro_news extends block_base {

    /**
     * Init function
     *
     * @throws coding_exception
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_vetagro_news');
    }

    /**
     * Update the block title from config values
     */
    public function specialization() {
        if (!empty($this->config->title)) {
            $this->title = $this->config->title;
        }
    }

    /**
     * Content for the block
     *
     * @return \stdClass|string|null
     * @throws coding_exception
     */
    public function get_content() {
        $this->page->requires->css(
            new moodle_url('/blocks/vetagro_news/js/glide/dist/css/glide.core' .
                (debugging() ? '.min' : '') . '.css'));
        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        if ($this->config && !empty($this->config->articles)) {
            $articles = $this->config->articles;
            $renderer = $this->page->get_renderer('core');
            $this->content->text = $renderer->render(
                new \block_vetagro_news\output\news_article(
                    $articles
                ));
        } else {
            $this->content = '';
        }
        return $this->content;
    }

    /**
     * All applicable formats
     *
     * @return array
     */
    public function applicable_formats() {
        return array('all' => true);
    }

    /**
     * Multiple blocks ?
     *
     * @return bool
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Serialize and store config data
     *
     * This will also immediately parse the data from the remote site.
     *
     * @param stdClass $data
     * @param false $nolongerused
     * @throws coding_exception
     */
    public function instance_config_save($data, $nolongerused = false) {
        parent::instance_config_save($data);
        if (!$nolongerused) {
            // We use this field so not to create duplicate tasks.
            $refresh = new \block_vetagro_news\tasks\adhoc_refresh_news();
            $refresh->set_custom_data(array(
                'id' => $this->instance->id
            ));
            \core\task\manager::queue_adhoc_task($refresh, true); // Very important here
            // not to reschedule a new tasks as it will end up in infinite loop (the save routing is
            // called in the adhoc_task).
        }
    }

    /**
     * Has configuration ?
     *
     * @return bool
     */
    public function has_config() {
        return true;
    }
}
