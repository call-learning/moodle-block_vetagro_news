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
use core_date;
use DateTime;
use DOMDocument;
use DOMXPath;
/**
 * Class adhoc_refresh_news
 *
 * @package block_vetagro_news
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class adhoc_refresh_news extends \core\task\adhoc_task {
    /**
     * Execute the task.
     */
    public function execute() {
        global $DB;
        $data = $this->get_custom_data();
        $blockinstance = $DB->get_record('block_instances', ['id' => $data->id]);
        if ($blockinstance) {
            $instance = block_instance($blockinstance->blockname, $blockinstance);
            try {
                $feedmanager = new \block_vetagro_news\feed_manager($instance);
                $feedmanager->refresh_block();
            } catch (\moodle_exception $e) {
                debugging('Issue when updating Vetagronews block:'. $e->getMessage(), DEBUG_NORMAL);
            }
        }
    }
}
