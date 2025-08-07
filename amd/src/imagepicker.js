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
 * Choose from images for iconurls.
 *
 * @module     tiny_elements/imagepicker
 * @copyright  2025 ISB Bayern
 * @author     Stefan Hanauska <stefan.hanauska@csg-in.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Modal from 'core/modal';
import Ajax from 'core/ajax';
import Templates from 'core/templates';
import {getString} from 'core/str';

export const init = async(clickSelector, targetSelector) => {
    const clickTargets = document.querySelectorAll(clickSelector);

    clickTargets.forEach((element) => {
        let targetElement = element.closest('fieldset, form').querySelector(targetSelector);

        element.addEventListener('click', async() => {
            // Let's see if we can figure out the category.
            let categoryid = 0;
            if (element.dataset.categoryid) {
                categoryid = element.dataset.categoryid;
            } else {
                let categoryidelement = element.closest('form').querySelector('[name="compcat"], [name="categoryid"]');
                if (categoryidelement) {
                    categoryid = categoryidelement.value;
                }
            }

            let categoryname = '';
            if (element.dataset.categoryname) {
                categoryname = element.dataset.categoryname;
            } else {
                let categoryelement = element.closest('form').querySelector('[name="categoryname"]');
                if (categoryelement) {
                    categoryname = categoryelement.value;
                }
            }

            const result = await Ajax.call([{
                methodname: 'tiny_elements_get_images',
                args: {
                    contextid: 1,
                    categoryid: categoryid,
                    categoryname: categoryname,
                },
            }])[0];

            const renderedTemplate = await Templates.render('tiny_elements/imagepicker', {
                images: result,
            });

            const pickerModal = await Modal.create({
                removeOnClose: true,
                large: true,
                body: renderedTemplate,
                returnElement: element,
                title: getString('showprinturls', 'tiny_elements'),
            });

            pickerModal.show();

            const root = pickerModal.getRoot()[0];
            root.querySelectorAll('.tiny_elements_thumbnail').forEach((thumbnail) => {
                thumbnail.addEventListener('click', (event) => {
                    const image = event.target.closest('img');
                    const url = image.src;
                    targetElement.value = url;
                    pickerModal.hide();
                });
            });
        });
    });
};
