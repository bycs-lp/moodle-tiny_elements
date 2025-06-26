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
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Web service to retrieve images.
 *
 * @package    tiny_elements
 * @copyright  2025 ISB Bayern
 * @author     Stefan Hanauska <stefan.hanauska@csg-in.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_images extends external_api {
    /**
     * Describes the parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT, 'Context id', VALUE_REQUIRED),
            'categoryname' => new external_value(PARAM_TEXT, 'Category name', VALUE_DEFAULT, ''),
            'categoryid' => new external_value(PARAM_INT, 'Category id', VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * Retrieve the images.
     * @param int $contextid the context id (currently only system context is supported)
     * @param string $categoryname the category name
     * @param int $categoryid the category id
     * @return array list of images
     */
    public static function execute(int $contextid, string $categoryname, int $categoryid): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'contextid' => $contextid,
            'categoryname' => $categoryname,
            'categoryid' => $categoryid,
        ]);
        $contextid = $params['contextid'];
        $categoryname = $params['categoryname'];
        $categoryid = $params['categoryid'];
        $context = \core\context::instance_by_id($contextid);
        self::validate_context($context);

        require_capability('tiny/elements:manage', $context);

        return \tiny_elements\local\utils::get_all_images($contextid, $categoryid, $categoryname);
    }

    /**
     * Describes the return structure of the service.
     *
     * @return external_multiple_structure the return structure
     */
    public static function execute_returns(): external_multiple_structure {
        return new external_multiple_structure(
            new external_single_structure([
                'url' => new external_value(PARAM_TEXT, 'the url of the image'),
                'name' => new external_value(PARAM_TEXT, 'the name of the image'),
            ], 'images')
        );
    }
}
