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
 * Edit Form
 *
 * @package   block_vetagro_news
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_vetagro_news\feed_manager;
use block_vetagro_news\output\news_article;

/**
 * Class block_vetagro_news_edit_form
 *
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_vetagro_news_edit_form extends block_edit_form {

    /**
     * Form definition
     *
     * @param object $mform
     * @throws coding_exception
     */
    protected function specific_definition($mform) {

        // Section header title according to language file.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        // Title of the block.
        $mform->addElement('text', 'config_title', get_string('config:title', 'block_vetagro_news'));
        $mform->setDefault('config_title', get_string('title', 'block_vetagro_news'));
        $mform->setType('config_title', PARAM_TEXT);

        // Site URL.
        $mform->addElement('url', 'config_pageurl', get_string('config:pageurl', 'block_vetagro_news'));
        $mform->setDefault('config_pageurl', 'default value');
        $mform->setType('config_pageurl', PARAM_URL);

        $mform->addElement('text', 'config_itemxpath', get_string('config:itemxpath', 'block_vetagro_news'));
        $mform->setDefault('config_itemxpath', feed_manager::DEFAULT_VALUES['itemxpath']);
        $mform->setType('config_itemxpath', PARAM_RAW);

        $mform->addElement('text', 'config_linkxpath', get_string('config:linkxpath', 'block_vetagro_news'));
        $mform->setDefault('config_linkxpath', feed_manager::DEFAULT_VALUES['linkxpath']);
        $mform->setType('config_linkxpath', PARAM_RAW);

        $mform->addElement('text', 'config_imagexpath', get_string('config:imagexpath', 'block_vetagro_news'));
        $mform->setDefault('config_imagexpath', feed_manager::DEFAULT_VALUES['imagexpath']);
        $mform->setType('config_imagexpath', PARAM_RAW);

        $mform->addElement('text', 'config_datexpath', get_string('config:datexpath', 'block_vetagro_news'));
        $mform->setDefault('config_datexpath', feed_manager::DEFAULT_VALUES['datexpath']);
        $mform->setType('config_datexpath', PARAM_RAW);

        $mform->addElement('text', 'config_categoriesxpath', get_string('config:categoryxpath', 'block_vetagro_news'));
        $mform->setDefault('config_categoriesxpath', feed_manager::DEFAULT_VALUES['categoriesxpath']);
        $mform->setType('config_categoriesxpath', PARAM_RAW);

        $mform->addElement('text', 'config_titlexpath', get_string('config:titlexpath', 'block_vetagro_news'));
        $mform->setDefault('config_titlexpath', feed_manager::DEFAULT_VALUES['titlexpath']);
        $mform->setType('config_titlexpath', PARAM_RAW);

        $mform->addElement('text', 'config_scrolltimer', get_string('config:scrolltimer', 'block_vetagro_news'));
        $mform->setDefault('config_scrolltimer', news_article::DEFAULT_SCROLL_SPEED);
        $mform->setType('config_scrolltimer', PARAM_INT);
    }
}
