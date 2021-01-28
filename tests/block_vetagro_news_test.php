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
 * Base class for unit tests for block_vetagro_news.
 *
 * @package   block_vetagro_news
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_thumblinks_action\output\thumblinks_action;
use block_vetagro_news\feed_manager;

defined('MOODLE_INTERNAL') || die();

/**
 * Class mock_feed_manager
 *
 * Class to mock get_article_content.
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mock_feed_manager extends feed_manager {
    /**
     * Mock article content retrieval from fixtures
     *
     * @param string $articleurl
     * @return false|string
     */
    protected function get_article_content($articleurl) {
        global $CFG;
        switch($articleurl) {
            case 'http://mysite.fr/la-sante-globale-fil-conducteur-de-la-strategie-detablissement/':
                return file_get_contents($CFG->dirroot.'/blocks/vetagro_news/tests/fixtures/article1.html');
            case 'http://mysite.fr/les-chiffres-cles-de-la-rentree-2020-de-vetagro-sup/':
                return file_get_contents($CFG->dirroot.'/blocks/vetagro_news/tests/fixtures/article2.html');
        }
    }
}

/**
 * Unit tests for block_vetagro_news
 *
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_vetagro_news_test extends advanced_testcase {

    /**
     * Expected config data.
     */
    const EXPECTED_CONFIG = '[{"imageurl":"http:\/\/mysite.fr\/wp-content\/uploads\/2020\/11\/DSC_0009-2-480x519.jpg",'
    .'"linkurl":"http:\/\/mysite.fr\/la-sante-globale-fil-conducteur-de-la-strategie-detablissement\/",'
    .'"categories":[{"text":"One Health"}],"date":"1604419500","title":"La sant\u00e9 globale, fil conducteur de la strat\u00e9gie'
    .' d\u2019\u00e9tablissement"},{"imageurl":"http:\/\/mysite.fr\/wp-content\/uploads\/2020\/10\/Rentree-VETO-2020-1024x504.jpg"'
    .',"linkurl":"http:\/\/mysite.fr\/les-chiffres-cles-de-la-rentree-2020-de-vetagro-sup\/","categories":[{"text":"Etudiants"}'
    .',{"text":"Formations"}],"date":"1603116642","title":"Les chiffres cl\u00e9s de la rentr\u00e9e 2020 de VetAgro Sup"}]';

    /**
     * Current block
     *
     * @var block_base|false|null
     */
    protected $block = null;
    /**
     * Current user
     *
     * @var stdClass|null
     */
    protected $user = null;

    /**
     * Basic setup for these tests.
     */
    public function setUp() {
        $this->resetAfterTest(true);
        $this->user = $this->getDataGenerator()->create_user();
        $this->setUser($this->user);
        // Create a Sponsor block.
        $page = new moodle_page();
        $page->set_context(context_system::instance());
        $page->set_pagelayout('frontpage');
        $blockname = 'vetagro_news';
        $page->blocks->load_blocks();
        $page->blocks->add_block_at_end_of_default_region($blockname);
        // Here we need to work around the block API. In order to get 'get_blocks_for_region' to work,
        // we would need to reload the blocks (as it has been added to the DB but is not
        // taken into account in the block manager).
        // The only way to do it is to recreate a page so it will reload all the block.
        // It is a main flaw in the  API (not being able to use load_blocks twice).
        // Alternatively if birecordsbyregion was nullable,
        // should for example have a load_block + create_all_block_instances and
        // should be able to access to the block.
        $page = new moodle_page();
        $page->set_context(context_system::instance());
        $page->set_pagelayout('frontpage');
        $page->blocks->load_blocks();
        $blocks = $page->blocks->get_blocks_for_region($page->blocks->get_default_region());
        $block = end($blocks);
        $block = block_instance($blockname, $block->instance);
        $this->block = $block;
        $block = block_instance_by_id($this->block->instance->id);
        $configdata = (object) feed_manager::DEFAULT_VALUES;
        $block->instance_config_save((object) $configdata);
        $this->block = $block;
    }

    /**
     * Test that we can retrieve an article from a feedd
     */
    public function test_get_articles_from_page() {
        global $CFG;
        // We need to reload the block so config is there.
        $block = block_instance_by_id($this->block->instance->id);
        $feedmanager = new mock_feed_manager($block);
        $content = file_get_contents($CFG->dirroot.'/blocks/vetagro_news/tests/fixtures/sample-feed.txt');
        $settings = $feedmanager->get_articles_from_page($content);
        $this->assertEquals(json_decode(self::EXPECTED_CONFIG), $settings);
    }


    /**
     * Test that output is as expected. This also test file loading into the plugin.
     */
    public function test_simple_content() {
        // We need to reload the block so config is there.
        $block = block_instance_by_id($this->block->instance->id);
        $block->config->articles = json_decode(self::EXPECTED_CONFIG);
        $block->instance_config_save($block->config);
        $content = $block->get_content();
        $this->assertNotNull($content->text);
        $text = $content->text;
        $this->assertContains('http://mysite.fr/la-sante-globale-fil-conducteur-de-la-strategie-detablissement/', $text);
        $this->assertContains('http://mysite.fr/wp-content/uploads/2020/11/DSC_0009-2-480x519.jpg', $text);
        $this->assertContains('http://mysite.fr/les-chiffres-cles-de-la-rentree-2020-de-vetagro-sup/', $text);
        $this->assertContains('http://mysite.fr/wp-content/uploads/2020/10/Rentree-VETO-2020-1024x504.jpg', $text);
        $this->assertContains('La santé globale, fil conducteur de la stratégie d’établissement', $text);
        $this->assertContains('Les chiffres clés de la rentrée 2020 de VetAgro Sup', $text);
    }
}

