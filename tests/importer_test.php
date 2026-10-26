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

namespace tiny_elements;

use advanced_testcase;
use tiny_elements\local\constants;

/**
 * Tests for the tiny_elements importer.
 *
 * @package    tiny_elements
 * @copyright  2026 ISB Bayern
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \tiny_elements\importer
 */
final class importer_test extends advanced_testcase {
    /**
     * Build the record of an exported component carrying javascript.
     *
     * @param string $name
     * @param string $js
     * @return array
     */
    private function get_component_record(string $name, string $js): array {
        return [
            'id' => 1,
            'name' => $name,
            'displayname' => $name,
            'code' => '<p>code</p>',
            'text' => '',
            'css' => '',
            'js' => $js,
            'iconurl' => '',
            'timecreated' => time(),
            'timemodified' => time(),
        ];
    }

    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    /**
     * A holder of the manage capability who is not a site administrator must not be able to store javascript.
     */
    public function test_import_component_ignores_js_for_non_admin(): void {
        global $DB;

        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $roleid = $this->getDataGenerator()->create_role();
        assign_capability('tiny/elements:manage', CAP_ALLOW, $roleid, SYSCONTEXTID);
        role_assign($roleid, $user->id, SYSCONTEXTID);
        $this->setUser($user);

        $importer = new importer();
        $importer->import_component($this->get_component_record('newcomponent', 'alert(1);'), []);

        $this->assertSame('', (string) $DB->get_field(constants::TABLES['component'], 'js', ['name' => 'newcomponent']));
    }

    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    /**
     * A site administrator can still import components with javascript.
     */
    public function test_import_component_keeps_js_for_admin(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $importer = new importer();
        $importer->import_component($this->get_component_record('newcomponent', 'alert(1);'), []);

        $this->assertSame('alert(1);', $DB->get_field(constants::TABLES['component'], 'js', ['name' => 'newcomponent']));
    }
}
