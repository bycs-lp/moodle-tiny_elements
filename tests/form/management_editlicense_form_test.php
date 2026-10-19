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

namespace tiny_elements\form;

/**
 * Tests for the license editing form.
 *
 * @package    tiny_elements
 * @copyright  2026 ISB Bayern
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \tiny_elements\form\management_editlicense_form
 */
final class management_editlicense_form_test extends \advanced_testcase {
    /**
     * Submit the form like the modal does, which sends no category id.
     *
     * @param int $fileid submitted file id
     */
    private function submit(int $fileid): void {
        $ajaxdata = management_editlicense_form::mock_ajax_submit([
            'itemcount' => 1,
            'fileid' => [$fileid],
            'fileauthor' => ['Changed author'],
            'filesource' => ['https://example.com'],
            'filelicense' => ['public'],
        ]);
        // The form already emits warnings and debugging for this request, which are not under test.
        set_error_handler(fn() => true, E_WARNING);
        try {
            $form = new management_editlicense_form(null, null, 'post', '', null, true, $ajaxdata);
            $form->set_data_for_dynamic_submission();
            $validated = $form->is_validated();
        } finally {
            restore_error_handler();
        }
        $this->resetDebugging();
        $this->assertTrue($validated);
        $form->process_dynamic_submission();
    }

    /**
     * Create a file with a known author and license.
     *
     * @param array $filerecord context, component, area and item of the file
     * @return int file id
     */
    private function create_file(array $filerecord): int {
        $filerecord += [
            'filepath' => '/',
            'filename' => 'image.png',
            'author' => 'Original author',
            'license' => 'allrightsreserved',
        ];
        return (int) get_file_storage()->create_file_from_string($filerecord, 'content')->get_id();
    }

    /**
     * A file outside the plugin's image area must not be modified.
     */
    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    public function test_process_ignores_file_outside_image_area(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        $user = $this->getDataGenerator()->create_user();
        $foreignid = $this->create_file([
            'contextid' => \context_user::instance($user->id)->id,
            'component' => 'user',
            'filearea' => 'private',
            'itemid' => 0,
        ]);

        $this->submit($foreignid);

        $record = $DB->get_record('files', ['id' => $foreignid]);
        $this->assertSame('Original author', $record->author);
        $this->assertSame('allrightsreserved', $record->license);
    }

    /**
     * A file of the plugin's image area is still updated.
     */
    #[\PHPUnit\Framework\Attributes\Group('baseline')]
    public function test_process_updates_file_in_image_area(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        $fileid = $this->create_file([
            'contextid' => \context_system::instance()->id,
            'component' => 'tiny_elements',
            'filearea' => 'images',
            'itemid' => 5,
        ]);

        $this->submit($fileid);

        $record = $DB->get_record('files', ['id' => $fileid]);
        $this->assertSame('Changed author', $record->author);
        $this->assertSame('public', $record->license);
    }
}
