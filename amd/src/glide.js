/**
 * Thumblinks Action block Tiny Slider.
 *
 * @package    block_thumblinks_action
 * @copyright 2020 - CALL Learning - Laurent David <laurent@call-learning>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([ 'jquery', 'block_vetagro_news/config'], function ($) {
    return function (locator, config) {
        require(['glide'], function (Glide) {
            // Show the slider now we are initialised.
            $(locator).removeClass('d-none');
            new Glide(locator, config).mount();
        });
    };
});