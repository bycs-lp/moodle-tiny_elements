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
 * Elements preview all.
 *
 * @module      tiny_elements/previewall
 * @copyright   2025 Tobias Garske
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {add as addToast} from 'core/toast';
import {get_string as getString} from "core/str";

export const init = async() => {
    // Add listener to fill input and copy all elements as string.
    const allelements = document.getElementById("results_of_previewall");
    const toclipboard = document.getElementById("elements_to_clipboard");
    toclipboard.addEventListener("click", () => {
        navigator.clipboard.writeText(allelements.innerHTML)
        .then(() => {
            addToast(getString('copysuccess', 'tiny_elements'), {
                type: 'info',
            });
            return;
        })
        .catch(() => {
            addToast(getString('copyfail', 'tiny_elements'), {
                type: 'warning',
            });
            return;
        });
    });
};
