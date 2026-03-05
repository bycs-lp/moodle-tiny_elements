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

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Web service to fitler strings (for preview).
 *
 * @package    tiny_elements
 * @copyright  2026 ISB Bayern
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filter_string extends external_api {
    /**
     * Describes the parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT, 'Context id', VALUE_REQUIRED),
            'text' => new external_value(PARAM_RAW, 'Text to filter', VALUE_REQUIRED),
        ]);
    }

    /**
     * Retrieve the filtered string.
     * @param int $contextid the context id (currently only system context is supported)
     * @param string $text the text to filter
     * @return array with key text that contains the filtered text
     */
    public static function execute(int $contextid, string $text): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'contextid' => $contextid,
            'text' => $text,
        ]);
        $contextid = $params['contextid'];
        $text = $params['text'];
        $context = \core\context::instance_by_id($contextid);
        self::validate_context($context);

        require_capability('tiny/elements:viewplugin', $context);

        return [
            'text' => \tiny_elements\local\utils::filter_string($text)
        ];
    }

    /**
     * Describes the return structure of the service.
     *
     * @return external_single_structure the return structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'text' => new external_value(PARAM_RAW, 'the filtered text'),
        ]);
    }
}
