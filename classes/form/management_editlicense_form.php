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
 * Form for editing licenses.
 *
 * @package tiny_elements
 * @copyright 2025 ISB Bayern
 * @author 2025 Franziska Hübler <franziska.huebler@schule.bayern.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tiny_elements\form;

use core_form\dynamic_form;
use context;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/licenselib.php');

/**
 * Form for editing licenses.
 *
 * @package tiny_elements
 * @copyright 2025 ISB Bayern
 * @author 2025 Franziska Hübler <franziska.huebler@schule.bayern.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class management_editlicense_form extends dynamic_form {
    /**
     * @var array $areafiles Files of category.
     */
    private $areafiles = [];

    /**
     * Define the form elements.
     */
    public function definition(): void {
        $data = $this->_ajaxformdata;

        $fs = get_file_storage();
        $this->areafiles = $fs->get_area_files(
            \context_system::instance()->id,
            'tiny_elements',
            'images',
            $data['id'],
            "itemid, filepath, filename",
            false
        );
        $count = count($this->areafiles);

        $mform =& $this->_form;

        $group = [];
        // Fileicon.
        $group[] = $mform->createElement('html', \html_writer::img(
            'dummy',
            'tiny_elements_thumbnail',
            ['class'  => 'tiny_elements_thumbnail']
        ));
        // Filename.
        $group[] = $mform->createElement('hidden', 'fileid');
        $group[] = $mform->createElement('static', 'filename', get_string('name', 'repository'));
        // Author.
        $group[] = $mform->createElement('text', 'fileauthor', get_string('author', 'repository'));
        // Source.
        $group[] = $mform->createElement('text', 'filesource', get_string('editlicensesformfileurl', 'tiny_elements'));
        // License.
        $licenses = [];
        // Discard licenses without a name from enabled licenses.
        foreach (\license_manager::get_active_licenses() as $license) {
            if (!empty($license->fullname)) {
                $licenses[$license->shortname] = $license->fullname;
            }
        }
        $group[] = $mform->createElement('select', 'filelicense', get_string('license', 'repository'), $licenses);

        $options = [
            'fileid' => [
                'type' => PARAM_INT,
            ],
            'filename' => [
                'type' => PARAM_TEXT,
            ],
            'fileauthor' => [
                'type' => PARAM_TEXT,
                'helpbutton' => ['editlicensesformfileautor', 'tiny_elements'],
            ],
            'filesource' => [
                'type' => PARAM_TEXT,
                'helpbutton' => ['editlicensesformfileurl', 'tiny_elements'],
            ],
            'filelicense' => [
                'type' => PARAM_TEXT,
                'helpbutton' => ['editlicensesformfilelicense', 'tiny_elements'],
            ],
        ];

        $this->repeat_elements($group, $count, $options, 'itemcount', 'adddummy', 0);

        $mform->removeElement('adddummy');
    }

    /**
     * Returns context where this form is used.
     *
     * @return context Context where this form is used.
     */
    protected function get_context_for_dynamic_submission(): context {
        return \context_system::instance();
    }

    /**
     * Checks if current user has sufficient permissions, otherwise throws exception.
     */
    protected function check_access_for_dynamic_submission(): void {
        require_capability('tiny/elements:manage', $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission, used if form was submitted via AJAX.
     *
     * @return array Returns whether the records were updated.
     */
    public function process_dynamic_submission(): array {
        global $DB;

        $formdata = $this->get_data();
        $result = true;

        for ($i = 0; $i < $formdata->itemcount; $i++) {
            $record = new \stdClass();
            $record->id = $formdata->fileid[$i];
            $record->author = $formdata->fileauthor[$i] ?? '';
            $record->license = $formdata->filelicense[$i] ?? '';
            $record->source = $formdata->filesource[$i] ?? '';
            $result &= $DB->update_record('files', $record, true);
        }

        return [
            'update' => $result,
        ];
    }

    /**
     * Load in existing data as form defaults.
     */
    public function set_data_for_dynamic_submission(): void {
        $data = $this->_ajaxformdata;

        $files = [];
        foreach ($this->areafiles as $file) {
            if ($file->is_directory()) {
                continue;
            }
            $files['fileid'][] = $file->get_id();
            $files['filename'][]  = $file->get_filename();
            $files['fileauthor'][]  = $file->get_author();
            $files['filesource'][]  = $file->get_source();
            $files['filelicense'][]  = $file->get_license();
            $files['fileurl'][]  = \moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename()
            )->out();
        }

         $data['itemcount'] = count($files);
         $this->set_data($files);
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): \moodle_url {
        return new \moodle_url('/lib/editor/tiny/plugins/elements/management.php');
    }

    /**
     * Called when data / defaults are already loaded.
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function definition_after_data(): void {
        $mform = $this->_form;
        $data = $mform->_defaultValues;

        for ($i = 0; $i < count($mform->_elements); $i++) {
            if ($mform->_elements[$i]->_type === 'html') {
                $mform->_elements[$i]->_text = \html_writer::img(
                    $data['fileurl'][intdiv($i, 6)],
                    'tiny_elements_thumbnail',
                    ['class'  => 'tiny_elements_thumbnail']
                );
            }
        }
        $mform->setAttributes(['data-formtype' => 'tiny_elements_editlicense']);
    }
}
