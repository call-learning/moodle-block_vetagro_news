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
 * Class refresh_vetagro_news
 *
 * @package block_vetagro_news
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class refresh_vetagro_news extends \core\task\scheduled_task {

    /**
     * Block name
     */
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
        $blockinstancesrecord = $DB->get_records('block_instances', ['blockname' => self::BLOCK_NAME]);
        foreach ($blockinstancesrecord as $blockrecord) {
            $instance = block_instance($blockrecord->blockname, $blockrecord);
            try {
                $feedmanager = new \block_vetagro_news\feed_manager($instance);
                $feedmanager->refresh_block();
                mtrace("Updating Vetagronews bloc: ". $instance->id);/**/
            } catch (\moodle_exception $e) {
                mtrace("Issue when updating Vetagronews bloc: ".$e->getMessage());
            }

        }
    }
}
