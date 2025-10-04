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

namespace tiny_elements\local;

use core\hook\output\before_http_headers;
use stdClass;

/**
 * Test class for the utils functions.
 *
 * @package    tiny_elements
 * @copyright  2024 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class utils_test extends \advanced_testcase {
    /**
     * Tests the caching and cache invalidation functionality for the delivering of the tiny_elements css.
     *
     * @covers \tiny_elements\local\utils::get_complete_css_as_string
     * @covers \tiny_elements\local\utils::purge_css_cache
     * @covers \tiny_elements\local\utils::rebuild_css_cache
     */
    public function test_get_complete_css_as_string(): void {
        global $DB;
        $this->resetAfterTest();

        $compcatrecord1 = new stdClass();
        $compcatrecord1->name = 'testcategory1';
        $compcatrecord1->displayname = '<span lang="de" class="multilang">Kategorie 1</span><span lang="en" class="multilang">' .
            'Category 1</span>';
        $compcatrecord1->css = '.testcategory1{margin:3rem}';
        $compcatrecord1id = $DB->insert_record('tiny_elements_compcat', $compcatrecord1);
        $compcatrecord2 = new stdClass();
        $compcatrecord2->name = 'testcategory2';
        $compcatrecord2->displayname = '<span lang="de" class="multilang">Kategorie 2</span><span lang="en" class="multilang">' .
            'Category 2</span>';
        $compcatrecord2->css = '.testcategory2{padding:3rem}';
        $compcatrecord2id = $DB->insert_record('tiny_elements_compcat', $compcatrecord2);

        $componentrecord1 = new stdClass();
        $componentrecord1->name = 'testcomponent1';
        $componentrecord1->displayname = '<span lang="de" class="multilang">Komponente 1</span><span lang="en" class="multilang">' .
            'Component 1</span>';

        $componentrecord1->compcat = $compcatrecord1id;
        $componentrecord1->css = 'div.testcomponent1{background-color:red}';
        $DB->insert_record('tiny_elements_component', $componentrecord1);

        $componentrecord2 = new stdClass();
        $componentrecord2->name = 'testcomponent2';
        $componentrecord2->displayname = '<span lang="de" class="multilang">Komponente 2</span><span lang="en" class="multilang">' .
            'Component 2</span>';

        $componentrecord2->compcat = $compcatrecord2id;
        $componentrecord2->css = 'p.testcomponent2{background-color:green}';
        $DB->insert_record('tiny_elements_component', $componentrecord2);

        $flavorrecord1 = new stdClass();
        $flavorrecord1->name = 'testflavor1';
        $flavorrecord1->displayname = '<span lang="de" class="multilang">Geschmack 1</span><span lang="en" class="multilang">' .
            'Flavor 1</span>';
        $flavorrecord1->css = '#testflavor{color:blue}';
        $DB->insert_record('tiny_elements_flavor', $flavorrecord1);

        $flavorrecord2 = new stdClass();
        $flavorrecord2->name = 'testflavor2';
        $flavorrecord2->displayname = '<span lang="de" class="multilang">Geschmack 2</span><span lang="en" class="multilang">' .
            'Flavor 2</span>';
        $flavorrecord2->css = '#testflavor2{color:grey}';
        $DB->insert_record('tiny_elements_flavor', $flavorrecord2);

        $flavorrecord3 = new stdClass();
        $flavorrecord3->name = 'testflavor3';
        $flavorrecord3->displayname = '<span lang="de" class="multilang">Geschmack 3</span><span lang="en" class="multilang">' .
            'Flavor 3</span>';
        $flavorrecord3->css = '#testflavor3{color:red}';
        $flavorrecord3->hideforstudents = 1;
        $flavorrecord3id = $DB->insert_record('tiny_elements_flavor', $flavorrecord3);

        $starttime = time();
        $this->mock_clock_with_frozen($starttime);

        // We need to initially build the cache.
        // This is usually being triggered by the before_http_headers hook.
        $mpage = new \moodle_page();
        $rbase = new \renderer_base($mpage, "/");
        $beforehttpheadershook = new before_http_headers($rbase);
        hook_callbacks::add_elements_data_to_dom($beforehttpheadershook);

        $css = utils::get_css_from_cache();
        $this->assertStringContainsString($compcatrecord1->css, $css);
        $this->assertStringContainsString($compcatrecord2->css, $css);
        $this->assertStringContainsString($componentrecord1->css, $css);
        $this->assertStringContainsString($componentrecord2->css, $css);
        $this->assertStringContainsString($flavorrecord1->css, $css);
        $this->assertStringContainsString($flavorrecord2->css, $css);
        $this->assertStringContainsString($flavorrecord3->css, $css);

        $dbreadsbefore = $DB->perf_get_queries();
        hook_callbacks::add_elements_data_to_dom($beforehttpheadershook);
        $this->assertEquals($dbreadsbefore, $DB->perf_get_queries());
        $this->mock_clock_with_frozen($starttime + 10);
        $css = utils::get_css_from_cache();
        $this->assertStringContainsString($compcatrecord1->css, $css);
        $this->assertStringContainsString($compcatrecord2->css, $css);
        $this->assertStringContainsString($componentrecord1->css, $css);
        $this->assertStringContainsString($componentrecord2->css, $css);
        $this->assertStringContainsString($flavorrecord1->css, $css);
        $this->assertStringContainsString($flavorrecord2->css, $css);
        $this->assertStringContainsString($flavorrecord3->css, $css);

        $this->mock_clock_with_frozen($starttime + 20);
        $compcatrecord1 = $DB->get_record('tiny_elements_compcat', ['id' => $compcatrecord1id]);
        $compcatrecord1->css = 'p{color:pink}';
        $DB->update_record('tiny_elements_compcat', $compcatrecord1);
        $flavorrecord3 = $DB->get_record('tiny_elements_flavor', ['id' => $flavorrecord3id]);
        $flavorrecord3->hideforstudents = 0;
        $DB->update_record('tiny_elements_flavor', $flavorrecord3);
        // This needs to be called from the admin interface whenever there is a change in the configuration.
        utils::purge_css_cache();
        // Now the callback should trigger a cache rebuild.
        $dbreadsbefore = $DB->perf_get_queries();
        hook_callbacks::add_elements_data_to_dom($beforehttpheadershook);
        $this->assertGreaterThan($dbreadsbefore, $DB->perf_get_queries());
        $css = utils::get_css_from_cache();
        $this->assertStringContainsString($compcatrecord1->css, $css);
        $this->assertStringContainsString($compcatrecord2->css, $css);
        $this->assertStringContainsString($componentrecord1->css, $css);
        $this->assertStringContainsString($componentrecord2->css, $css);
        $this->assertStringContainsString($flavorrecord1->css, $css);
        $this->assertStringContainsString($flavorrecord2->css, $css);
        $this->assertStringContainsString($flavorrecord3->css, $css);

        // Check if it also works if we purge all the caches of moodle.
        purge_all_caches();
        // If we purge the moodle caches the hook callback should trigger a cache rebuild.
        $dbreadsbefore = $DB->perf_get_queries();
        hook_callbacks::add_elements_data_to_dom($beforehttpheadershook);
        $this->assertGreaterThan($dbreadsbefore, $DB->perf_get_queries());
        $this->mock_clock_with_frozen($starttime + 30);
        $css = utils::get_css_from_cache();
        $this->assertStringContainsString($compcatrecord1->css, $css);
        $this->assertStringContainsString($compcatrecord2->css, $css);
        $this->assertStringContainsString($componentrecord1->css, $css);
        $this->assertStringContainsString($componentrecord2->css, $css);
        $this->assertStringContainsString($flavorrecord1->css, $css);
        $this->assertStringContainsString($flavorrecord2->css, $css);
        $this->assertStringContainsString($flavorrecord3->css, $css);
    }
}
