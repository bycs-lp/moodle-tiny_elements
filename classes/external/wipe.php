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

use tiny_elements\manager;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_api;
use core_external\external_value;

/**
 * Implementation of web service tiny_elements_wipe
 *
 * @package    tiny_elements
 * @copyright  2025 ISB Bayern
 * @author     Stefan Hanauska <stefan.hanauska@csg-in.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class wipe extends external_api {
    /**
     * Describes the parameters for tiny_elements_wipe
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT, 'Context id', VALUE_REQUIRED),
        ]);
    }

    /**
     * Implementation of web service tiny_elements_wipe
     *
     * @param int $contextid the context id
     * @return array result array
     */
    public static function execute(int $contextid): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'contextid' => $contextid,
        ]);
        $contextid = $params['contextid'];
        $context = \core\context::instance_by_id($contextid);
        self::validate_context($context);
        require_capability('tiny/elements:manage', $context);

        $manager = new manager($context->id);
        $manager->wipe();

        return [
            'result' => true,
        ];
    }

    /**
     * Describe the return structure for tiny_elements_wipe
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'result' => new external_value(PARAM_BOOL, 'Result of the wipe operation'),
        ]);
    }
}
