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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/xml/xml_writer.class.php');
require_once($CFG->dirroot . '/backup/util/xml/output/xml_output.class.php');
require_once($CFG->dirroot . '/backup/util/xml/output/memory_xml_output.class.php');
require_once($CFG->libdir . '/licenselib.php');

use tiny_elements\local\utils;
use tiny_elements\local\constants;
use core\exception\moodle_exception;
use memory_xml_output;
use xml_writer;

/**
 * Class exporter
 *
 * @package    tiny_elements
 * @author     Stefan Hanauska <stefan.hanauska@csg-in.de>
 * @copyright  2025 ISB Bayern
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class exporter {
    /** @var int $contextid */
    protected int $contextid;

    /**
     * Constructor.
     *
     * @param int $contextid
     */
    public function __construct(int $contextid = SYSCONTEXTID) {
        $this->contextid = $contextid;
    }

    /**
     * Export.
     *
     * @param int $compcatid
     * @return stored_file
     * @throws moodle_exception
     */
    public function export($compcatid = 0): \stored_file {
        global $DB;
        $fs = get_file_storage();
        $fp = get_file_packer('application/zip');

        $exportfiles = $this->export_files($compcatid);

        $filerecord = [
            'contextid' => $this->contextid,
            'component' => 'tiny_elements',
            'filearea' => 'export',
            'itemid' => time(),
            'filepath' => '/',
            'filename' => constants::FILE_NAME_EXPORT,
        ];
        $exportxmlfile = $fs->create_file_from_string($filerecord, $this->exportxml($compcatid));
        $exportfiles[constants::FILE_NAME_EXPORT] = $exportxmlfile;

        $exporttag = 'full';
        if (!empty($compcatid)) {
            $compcatname = $DB->get_field(constants::TABLES['compcat'], 'name', ['id' => $compcatid], MUST_EXIST);
            $exporttag = $compcatname;
        }

        $filename = 'tiny_elements_export_' . $exporttag . '_' . time() . '.zip';
        $exportfile = $fp->archive_to_storage($exportfiles, $this->contextid, 'tiny_elements', 'export', 0, '/', $filename);
        if (!$exportfile) {
            throw new moodle_exception(get_string('error_export', 'tiny_elements'));
        }
        return $exportfile;
    }

    /**
     * Export files.
     *
     * @param int $compcatid
     * @return array
     */
    public function export_files(int $compcatid = 0): array {
        global $DB;
        $fs = get_file_storage();

        $conditions = [];
        $exportfiles = [];

        if (!empty($compcatid)) {
            $conditions['id'] = $compcatid;
        }

        $compcats = $DB->get_records('tiny_elements_compcat', $conditions);

        // It is necessary to get the files for each compcat separately to avoid mixing up files from
        // different categories.
        foreach ($compcats as $compcat) {
            $files = $fs->get_area_files($this->contextid, 'tiny_elements', 'images', $compcat->id, 'itemid', false);
            foreach ($files as $file) {
                $exportfiles[$compcat->name . '/' . $file->get_filepath() . $file->get_filename()] = $file;
            }

            // Write xml file with metadata.
            $filerecord = [
                'contextid' => $this->contextid,
                'component' => 'tiny_elements',
                'filearea' => 'export',
                'itemid' => time(),
                'filepath' => '/',
                'filename' => constants::FILE_NAME_METADATA . '_' . $compcat->name . '.xml',
            ];
            $exportxmlfile = $fs->create_file_from_string($filerecord, $this->exportxml_filemetadata($compcat->id));
            $exportfiles[$compcat->name . '/' . $filerecord['filename']] = $exportxmlfile;
        }

        return $exportfiles;
    }

    /**
     * Export XML.
     * @param int $compcatid Category ID.
     * @return string
     */
    public function exportxml(int $compcatid = 0): string {
        global $DB;
        // Start.
        $xmloutput = new memory_xml_output();
        $xmlwriter = new xml_writer($xmloutput);
        $xmlwriter->start();
        $xmlwriter->begin_tag('elements');

        $categoryname = '';
        if (!empty($compcatid)) {
            $categoryname = $DB->get_field(constants::TABLES['compcat'], 'name', ['id' => $compcatid], MUST_EXIST);
        }

        $result = $this->export_categories_and_components($xmlwriter, $categoryname);

        $this->export_flavors_and_variants($xmlwriter, $categoryname, $result['componentnames']);

        // End.
        $xmlwriter->end_tag('elements');
        $xmlwriter->stop();
        $xmlstr = $xmloutput->get_allcontents();

        // This is just here for compatibility reasons.
        $xmlstr = utils::replace_pluginfile_urls($xmlstr);

        return $xmlstr;
    }

    /**
     * Export categories.
     *
     * @param xml_writer $xmlwriter
     * @param string $categoryname
     * @return array
     */
    public function export_categories_and_components(xml_writer $xmlwriter, string $categoryname): array {
        global $DB;

        $conditionscategories = [];
        $conditionscomponents = [];

        if (!empty($categoryname)) {
            $conditionscategories['name'] = $categoryname;
            $conditionscomponents['categoryname'] = $categoryname;
        }

        $compcats = $DB->get_records(constants::TABLES['compcat'], $conditionscategories);
        $this->write_elements($xmlwriter, constants::TABLES['compcat'], $compcats);

        $components = $DB->get_records(constants::TABLES['component'], $conditionscomponents);
        $this->write_elements($xmlwriter, constants::TABLES['component'], $components);

        return [
            'componentnames' => array_column($components, 'name'),
        ];
    }

    /**
     * Export flavors and variants.
     *
     * @param xml_writer $xmlwriter
     * @param string $categoryname
     * @param array $componentnames
     */
    public function export_flavors_and_variants(
        xml_writer $xmlwriter,
        string $categoryname = '',
        array $componentnames = []
    ): void {
        global $DB;

        $sql = ' = componentname';
        $params = [];

        if (!empty($categoryname)) {
            [$sql, $params] = $DB->get_in_or_equal($componentnames, SQL_PARAMS_QM, 'param', true, '');
        }
        $compflavors = $DB->get_records_sql(
            "SELECT * FROM {" . constants::TABLES['compflavor'] . "} WHERE componentname " . $sql,
            $params
        );
        $this->write_elements($xmlwriter, constants::TABLES['compflavor'], $compflavors);
        $flavornames = array_unique(array_column($compflavors, 'flavorname'));

        $sql = ' = name';
        if (!empty($categoryname)) {
            [$sql, $params] = $DB->get_in_or_equal($flavornames, SQL_PARAMS_QM, 'param', true, '');
        }
        $flavors = $DB->get_records_sql("SELECT * FROM {" . constants::TABLES['flavor'] . "} WHERE name " . $sql, $params);
        $this->write_elements($xmlwriter, constants::TABLES['flavor'], $flavors);

        $sql = ' = componentname';
        if (!empty($categoryname)) {
            [$sql, $params] = $DB->get_in_or_equal($componentnames, SQL_PARAMS_QM, 'param', true, '0');
        }
        $compvariants = $DB->get_records_sql(
            "SELECT * FROM {" . constants::TABLES['compvariant'] . "} WHERE componentname " . $sql,
            $params
        );
        $this->write_elements($xmlwriter, constants::TABLES['compvariant'], $compvariants);
        $variantnames = array_unique(array_column($compvariants, 'variant'));

        $sql = ' = name';
        if (!empty($categoryname)) {
            [$sql, $params] = $DB->get_in_or_equal($variantnames, SQL_PARAMS_QM, 'param', true, '');
        }
        $variants = $DB->get_records_sql("SELECT * FROM {" . constants::TABLES['variant'] . "} WHERE name " . $sql, $params);
        $this->write_elements($xmlwriter, constants::TABLES['variant'], $variants);
    }

    /**
     * Write elements.
     *
     * @param xml_writer $xmlwriter
     * @param string $table
     * @param array $data
     */
    public function write_elements(xml_writer $xmlwriter, string $table, array $data): void {
        global $DB;

        // Get columns.
        $columns = $DB->get_columns($table);

        $xmlwriter->begin_tag($table);
        foreach ($data as $value) {
            $xmlwriter->begin_tag(constants::ITEMNAME);
            foreach ($columns as $column) {
                $name = $column->name;
                $xmlwriter->full_tag($name, $value->$name ?? '');
            }
            $xmlwriter->end_tag(constants::ITEMNAME);
        }
        $xmlwriter->end_tag($table);
    }

    /**
     * Export files metadata to XML.
     *
     * @param int $compcatid Category ID.
     * @return string XML string.
     */
    public function exportxml_filemetadata(int $compcatid): string {
        global $DB;

        // Start.
        $xmloutput = new memory_xml_output();
        $xmlwriter = new xml_writer($xmloutput);
        $xmlwriter->start();
        $xmlwriter->begin_tag('tiny_elements_files_with_license');
        $compcatname = $DB->get_record(constants::TABLES['compcat'], ['id' => $compcatid], 'name');
        $xmlwriter->begin_tag($compcatname->name, ['id' => $compcatid]);

        // Get licenses.
        $licenses = \license_manager::get_active_licenses();
        // Get files.
        $fs = get_file_storage();
        $files = $fs->get_area_files(
            \context_system::instance()->id,
            'tiny_elements',
            'images',
            $compcatid,
            "itemid, filepath, filename",
            false
        );

        foreach ($files as $file) {
            $xmlwriter->begin_tag(constants::ITEMNAME);

            $xmlwriter->full_tag('id', $file->get_id() ?? '');
            $xmlwriter->full_tag('filename', $file->get_filename() ?? '');
            $xmlwriter->full_tag('source', $file->get_source() ?? '');
            $xmlwriter->full_tag('author', $file->get_author() ?? '');
            $xmlwriter->begin_tag('license');
                $xmlwriter->full_tag('shortname', $file->get_license() ?? '');
                $xmlwriter->full_tag('fullname', $licenses[$file->get_license()]->fullname ?? '');
                $xmlwriter->full_tag('source', $licenses[$file->get_license()]->source ?? '');
            $xmlwriter->end_tag('license');

            $xmlwriter->end_tag(constants::ITEMNAME);
        }

        // End.
        $xmlwriter->end_tag($compcatname->name);
        $xmlwriter->end_tag('tiny_elements_files_with_license');
        $xmlwriter->stop();
        $xmlstr = $xmloutput->get_allcontents();

        return $xmlstr;
    }
}
