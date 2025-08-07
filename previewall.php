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
 * Creates a preview for all elements components in all flavors and variants.
 *
 * @package    tiny_elements
 * @copyright  2024 Tobias Garske, ISB Bayern
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../../../config.php');

require_login();

$url = new moodle_url('/lib/editor/tiny/plugins/elements/previewall.php', []);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();

// Iterate over every category.
$categorydata = $DB->get_records('tiny_elements_compcat');
$mustachedata = ['results' => []];
$seenelements = [];
foreach ($categorydata as $category) {
    $elements = [];
    $category = $category->name;
    $componentdata = $DB->get_records('tiny_elements_component', ['categoryname' => $category]);
    // Iterate over every component.
    foreach ($componentdata as $component) {
        // Select corresponding flavors and variants.
        $sql = "SELECT * FROM {tiny_elements_flavor} fla
                JOIN {tiny_elements_comp_flavor} comfla ON comfla.flavorname = fla.name
                WHERE fla.categoryname = :categoryname AND comfla.componentname = :componentname";
        $allflavors = $DB->get_records_sql($sql, ['categoryname' => $component->categoryname, 'componentname' => $component->name]);
        $sql = "SELECT * FROM {tiny_elements_variant} var
                JOIN {tiny_elements_comp_variant} covar ON var.name = covar.variant
                WHERE covar.componentname = :componentname";
        $allvariants = $DB->get_records_sql($sql, ['componentname' => $component->name]);
        // Add default "variant".
        array_unshift($allvariants, (object) ['name' => '']);
        foreach ($allflavors as $flavordata) {
            foreach ($allvariants as $variantdata) {
                $tmpcomponent = clone $component;
                $variant = '';
                if (strlen($variantdata->name) > 0) {
                    $variant = 'elements-' . $variantdata->name . '-variant';
                }
                $varianthtml = '';
                // Build elements by replacing placeholders.
                $tmpcomponent->code = str_replace('{{CATEGORY}}', 'elements-' . $category, $tmpcomponent->code);
                $tmpcomponent->code = str_replace('{{COMPONENT}}', 'elements-' . $tmpcomponent->name, $tmpcomponent->code);
                $tmpcomponent->code = str_replace(
                    '{{FLAVOR}}',
                    'elements-' . $flavordata->name . '-flavor',
                    $tmpcomponent->code
                );
                $tmpcomponent->code = str_replace('{{VARIANTS}}', $variant, $tmpcomponent->code);
                $tmpcomponent->code = str_replace('{{VARIANTSHTML}}', $varianthtml, $tmpcomponent->code);
                $tmpcomponent->code = str_replace(
                    '{{PLACEHOLDER}}',
                    $flavordata->name . ' ' . $variantdata->name,
                    $tmpcomponent->code
                );
                // Collect element data.
                $tmpcomponent->code = tiny_elements\local\utils::replace_pluginfile_urls($tmpcomponent->code, true);
                $elements[] = [
                    'name' => $component->name,
                    'code' => $tmpcomponent->code,
                    'show' => in_array($component->name, $seenelements) ? false : true,
                ];
                $seenelements[] = $component->name;
            }
        }
    }
    $mustachedata['results'][] = [
        'category' => $category,
        'elements' => $elements,
    ];
}

// Create button to copy elements to clipboard.
echo "<button id='elements_to_clipboard' type='button' class='btn btn-primary mb-3'>"
    . get_string('copyasstring', 'tiny_elements') . "</button>";
$PAGE->requires->js_call_amd('tiny_elements/previewall', 'init');
echo $OUTPUT->render_from_template('tiny_elements/previewall', $mustachedata);

echo $OUTPUT->footer();
