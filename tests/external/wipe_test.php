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

namespace tiny_elements\external;

use externallib_advanced_testcase;
use tiny_elements\local\constants;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/webservice/tests/helpers.php');

/**
 * Tests for the tiny_elements_wipe external function.
 *
 * @package    tiny_elements
 * @copyright  2026 ISB Bayern
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \tiny_elements\external\wipe
 */
final class wipe_test extends externallib_advanced_testcase {
    /**
     * Store one category so the effect of a wipe is observable.
     *
     * @return int id of the new category
     */
    private function create_compcat(): int {
        global $DB;

        return $DB->insert_record(constants::TABLES['compcat'], (object) [
            'name' => 'testcat',
            'displayname' => 'Test category',
            'description' => '',
            'css' => '',
            'timecreated' => time(),
            'timemodified' => time(),
        ]);
    }

    /**
     * A manager scoped to a single course must not be able to wipe the site wide library.
     */
    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    public function test_execute_rejects_course_scoped_manager(): void {
        global $DB;

        $this->resetAfterTest();
        $categoryid = $this->create_compcat();

        $course = $this->getDataGenerator()->create_course();
        $coursecontext = \context_course::instance($course->id);
        $user = $this->getDataGenerator()->create_user();
        // Enrol so the course context itself is reachable and the capability gate is what rejects the call.
        $this->getDataGenerator()->enrol_user($user->id, $course->id);
        $this->setUser($user);
        $this->assignUserCapability('tiny/elements:manage', $coursecontext->id);

        $this->expectException(\required_capability_exception::class);
        try {
            wipe::execute($coursecontext->id);
        } finally {
            $this->assertTrue($DB->record_exists(constants::TABLES['compcat'], ['id' => $categoryid]));
        }
    }

    /**
     * A caller holding the capability at system context still wipes the library.
     */
    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    public function test_execute_allows_system_context_manager(): void {
        global $DB;

        $this->resetAfterTest();
        $categoryid = $this->create_compcat();

        $this->setAdminUser();
        $result = wipe::execute(\context_system::instance()->id);

        $this->assertTrue($result['result']);
        $this->assertFalse($DB->record_exists(constants::TABLES['compcat'], ['id' => $categoryid]));
    }
}
