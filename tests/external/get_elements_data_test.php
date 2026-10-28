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
 * Tests for the tiny_elements_get_elements_data external function.
 *
 * @package    tiny_elements
 * @copyright  2026 ISB Bayern
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \tiny_elements\external\get_elements_data
 */
final class get_elements_data_test extends externallib_advanced_testcase {
    /**
     * Enrol a new user with the given role, store a teacher-only component and request the elements data.
     *
     * @param string $role role shortname in the course
     * @return array component names returned by the service
     */
    private function get_component_names_as(string $role): array {
        global $DB;

        $DB->insert_record(constants::TABLES['component'], (object) [
            'name' => 'teacheronlycomponent',
            'displayname' => 'Teacher only',
            'categoryname' => 'testcat',
            'code' => '<div>secret</div>',
            'text' => '',
            'displayorder' => 1,
            'hideforstudents' => 1,
            'timecreated' => time(),
            'timemodified' => time(),
        ]);
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_and_enrol($course, $role);
        $this->setUser($user);

        // Utils::get_all_comp_*() already emit undefined property warnings for the bundled library.
        set_error_handler(fn() => true, E_WARNING);
        try {
            $result = get_elements_data::execute(\context_course::instance($course->id)->id, false);
        } finally {
            restore_error_handler();
        }

        return array_column($result['components'], 'name');
    }

    /**
     * A student must not receive teacher-only components by sending isstudent = false.
     */
    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    public function test_execute_hides_teacher_components_from_student(): void {
        $this->resetAfterTest();

        $this->assertNotContains('teacheronlycomponent', $this->get_component_names_as('student'));
    }

    /**
     * A teacher still receives teacher-only components.
     */
    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    public function test_execute_returns_teacher_components_to_teacher(): void {
        $this->resetAfterTest();

        $this->assertContains('teacheronlycomponent', $this->get_component_names_as('editingteacher'));
    }
}
