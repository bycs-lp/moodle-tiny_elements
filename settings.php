<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Tiny Elements plugin settings.
 *
 * @package     tiny_elements
 * @copyright 2025 ISB Bayern
 * @copyright based on the work of Marc Català <reskit@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$categoryname = 'tiny_elements_settings';

// We overwrite $settings here that is defined in editor_tiny\plugininfo\tiny::load_settings().
$settings = new admin_category($categoryname, new lang_string('pluginname', 'tiny_elements'));

$settingspage = new admin_settingpage('tiny_elements', new lang_string('generalsettings', 'tiny_elements'));

if ($ADMIN->fulltree) {
    // Custom components settings.
    $settingspage->add(
        new admin_setting_heading('tiny_elements/generalsettings', new lang_string('generalsettings', 'tiny_elements'), '')
    );

    // Configure component preview.
    $name = get_string('enablepreview', 'tiny_elements');
    $desc = get_string('enablepreview_desc', 'tiny_elements');
    $default = 1;
    $setting = new admin_setting_configcheckbox('tiny_elements/enablepreview', $name, $desc, $default);
    $settingspage->add($setting);

    $settingspage->add(new admin_setting_configtext(
        'tiny_elements/allowedfilters',
        get_string('allowedfilters', 'tiny_elements'),
        get_string('allowedfilters_desc', 'tiny_elements'),
        'multilang2'
    ));
}

$settings->add($categoryname, $settingspage);

$settings->add($categoryname, new admin_externalpage(
    'tiny_elements_management',
    get_string('elements:manage', 'tiny_elements'),
    new moodle_url('/lib/editor/tiny/plugins/elements/management.php'),
    'tiny/elements:manage'
));

$settings->add($categoryname, new admin_externalpage(
    'tiny_elements_previewall',
    get_string('previewall', 'tiny_elements'),
    new moodle_url('/lib/editor/tiny/plugins/elements/previewall.php'),
    'tiny/elements:manage'
));
