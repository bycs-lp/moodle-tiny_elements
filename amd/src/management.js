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
 * Management functions for tiny_elements admin backend.
 *
 * @module     tiny_elements/management
 * @copyright  2024 ISB Bayern
 * @author     Tobias Garske
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {PreviewModal} from 'tiny_elements/previewmodal';
import ModalForm from 'core_form/modalform';
import Notification from 'core/notification';
import {get_string as getString} from 'core/str';
import {exception as displayException, deleteCancelPromise} from 'core/notification';
import {call as fetchMany} from 'core/ajax';
import {render as renderTemplate} from 'core/templates';
import Log from 'core/log';

export const init = async(params) => {

    // Add listener to import xml files.
    let importxml = document.getElementById('elements_import');
    importxml.addEventListener('click', async(e) => {
        importModal(e);
    });

    // Add listener for adding a new item.
    let additem = document.getElementsByClassName('add');
    additem.forEach(element => {
        element.addEventListener('click', async(e) => {
            showModal(e, element.dataset.id, element.dataset.table);
        });
    });

    // Add listener to edit items.
    let edititems = document.getElementsByClassName('edit');
    edititems.forEach(element => {
        element.addEventListener('click', async(e) => {
            showModal(e, element.dataset.id, element.dataset.table);
        });
    });

    // Add listener to delete items.
    let deleteitems = document.getElementsByClassName('delete');
    deleteitems.forEach(element => {
        element.addEventListener('click', async(e) => {
            deleteModal(e, element.dataset.id, element.dataset.title, element.dataset.table);
        });
    });

    // Add listener to preview items.
    let previewitems = document.getElementsByClassName('preview-button');
    previewitems.forEach(element => {
        element.addEventListener('click', async(e) => {
            previewModal(e);
        });
    });

    // Add listener to select compcat to show corresponding items.
    let compcats = document.getElementsByClassName('compcat');
    compcats.forEach(element => {
        element.addEventListener('click', async(e) => {
            showItems(e, element.dataset.compcat);
        });
    });

    // Add listener to edit licenses icon.
    let editlicenses = document.getElementsByClassName('editlicenses');
    editlicenses.forEach(element => {
        element.addEventListener('click', async(e) => {
            editlicensesModal(e, element.dataset.id);
        });
    });

    // Add listener to manage component flavor relation.
    let buttonicons = document.querySelectorAll('.buttonicons');
    buttonicons.forEach(element => {
        element.addEventListener('click', async(e) => {
            compflavorModal(e);
        });
    });

    let displaynamesbutton = document.getElementById('elements_displaynames_button');
    displaynamesbutton.addEventListener('click', async(e) => {
        displaynamesModal(e);
    });

    let displaynamesflavorbutton = document.getElementById('elements_displaynames_flavor_button');
    displaynamesflavorbutton.addEventListener('click', async(e) => {
        displaynamesFlavorModal(e);
    });

    let displaynamesvariantbutton = document.getElementById('elements_displaynames_variant_button');
    displaynamesvariantbutton.addEventListener('click', async(e) => {
        displaynamesVariantModal(e);
    });

    // Add listener to duplicate items.
    let duplicateitems = document.getElementsByClassName('duplicate');
    duplicateitems.forEach(element => {
        element.addEventListener('click', async() => {
            duplicateItem(element.dataset.id, element.dataset.table).always(() => reload());
        });
    });

    // Add listener to wipe all items.
    let wipebutton = document.getElementById('elements_wipe');
    if (wipebutton) {
        wipebutton.addEventListener('click', async(e) => {
            wipeModal(e);
        });
    }

    // Add image and text to item setting click area.
    let enlargeItems = document.querySelectorAll(
        '.flavor .card-body > .clickingextended, .component .card-body > .clickingextended, .variant .card-body > .clickingextended'
    );
    enlargeItems.forEach(element => {
        element.addEventListener('click', async(e) => {
            let item = e.target.closest('.item');
            item.querySelector('a.edit').click();
        });
    });

    // After submitting a new item, reset active compcat.
    if (params.compcatactive) {
        let compcat = document.querySelector('.compcat[data-compcat="' + params.compcatactive + '"]');
        if (compcat) {
            showItems(false, params.compcatactive);
            compcat.classList.add('active');
        }
    }
};

/**
 * Show dynamic form to add/edit a source.
 * @param {*} event
 * @param {*} id
 * @param {*} table
 */
const showModal = async(event, id, table) => {
    event.preventDefault();
    let title;
    if (id == 0) {
        title = getString('additem', 'tiny_elements');
    } else {
        title = getString('edititem', 'tiny_elements');
    }

    const modalForm = new ModalForm({
        // Set formclass, depending on component.
        formClass: "tiny_elements\\form\\management_" + table + "_form",
        args: {
            id: id,
            compcat: getActiveCompcatId(),
            categoryname: getActiveCompcatName(),
        },
        modalConfig: {title: title},
        returnFocus: event.target,
    });
    // Conditional reload page after submit.
    modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, () => reload());

    await modalForm.show();
};

/**
 * Show modal to preview css version.
 * @param {*} event
 */
const previewModal = async(event) => {
    event.preventDefault();
    let preview = event.target.closest(".preview-button");
    const modal = await PreviewModal.create({
        templateContext: {
            component: preview.dataset.component,
            flavors: preview.dataset.flavors.trim().split(" "),
            config: M.cfg,
        },
    });
    await modal.show();
};

/**
 * Show dynamic form to import xml backups.
 * @param {*} event
 */
const importModal = async(event) => {
    event.preventDefault();
    let title = getString('import', 'tiny_elements');

    const modalForm = new ModalForm({
        // Load import form.
        formClass: "tiny_elements\\form\\management_import_form",
        args: {},
        modalConfig: {title: title},
    });
    modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, importModalSubmitted);

    await modalForm.show();
};

/**
 * Process import form submit.
 * @param {*} event
 */
const importModalSubmitted = async(event) => {
    // Reload page after submit.
    if (event.detail.update) {
        location.reload();
    } else {
        event.stopPropagation();
        renderTemplate('tiny_elements/management_import_form_result', event.detail).then(async(html) => {
            await Notification.alert(
                getString('import_simulation', 'tiny_elements'),
                html,
                getString('close', 'tiny_elements')
            );
            return true;
        }).catch((error) => {
            displayException(error);
        });
    }
};

/**
 * Load modal to edit icon urls.
 * @param {*} event
 */
const compflavorModal = async(event) => {
    event.preventDefault();
    let title = getString('manage', 'tiny_elements');
    const target = event.target.closest('.buttonicons');
    const component = target.dataset.component ?? '';
    const flavor = target.dataset.flavor ?? '';
    const modalForm = new ModalForm({
        // Load import form.
        formClass: "tiny_elements\\form\\management_comp_flavor_form",
        args: {
            component: component,
            flavor: flavor,
        },
        modalConfig: {title: title},
    });

    await modalForm.show();
};

/**
 * Load modal to edit licenses of icons.
 * @param {*} event
 * @param {*} id
 */
const editlicensesModal = async(event, id) => {
    event.preventDefault();
    let title = getString('editlicenses', 'tiny_elements');
    const modalForm = new ModalForm({
        formClass: "tiny_elements\\form\\management_editlicense_form",
        args: {
            id: id,
        },
        modalConfig: {title: title},
    });
    await modalForm.show();
};

/**
 * Load modal to edit displaynames.
 * @param {*} event
 * @returns {void}
 */
const displaynamesModal = async(event) => {
    event.preventDefault();
    let title = getString('manage', 'tiny_elements');

    const modalForm = new ModalForm({
        // Load displaynames bulk edit form.
        formClass: "tiny_elements\\form\\management_displaynames_form",
        args: {},
        modalConfig: {title: title},
    });

    // Reload page after submit.
    modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, () => location.reload());

    await modalForm.show();
};

/**
 * Load modal to edit displaynames.
 * @param {*} event
 * @returns {void}
 */
const displaynamesFlavorModal = async(event) => {
    event.preventDefault();
    let title = getString('manage', 'tiny_elements');

    const modalForm = new ModalForm({
        // Load displaynames bulk edit form.
        formClass: "tiny_elements\\form\\management_displaynames_flavors_form",
        args: {},
        modalConfig: {title: title},
    });

    // Reload page after submit.
    modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, () => location.reload());

    await modalForm.show();
};

/**
 * Load modal to edit displaynames.
 * @param {*} event
 * @returns {void}
 */
const displaynamesVariantModal = async(event) => {
    event.preventDefault();
    let title = getString('manage', 'tiny_elements');

    const modalForm = new ModalForm({
        // Load displaynames bulk edit form.
        formClass: "tiny_elements\\form\\management_displaynames_variants_form",
        args: {},
        modalConfig: {title: title},
    });

    // Reload page after submit.
    modalForm.addEventListener(modalForm.events.FORM_SUBMITTED, () => location.reload());

    await modalForm.show();
};

/**
 * Show dynamic form to delete a source.
 * @param {*} event
 * @param {*} id
 * @param {*} title
 * @param {*} table
 */
const deleteModal = (event, id, title, table) => {
    event.preventDefault();

    deleteCancelPromise(
        getString('delete', 'tiny_elements', title),
        getString('deletewarning', 'tiny_elements'),
    ).then(async() => {
        if (id !== 0) {
            try {
                const deleted = await deleteItem(id, table);
                if (deleted) {
                    const link = document.querySelector('[data-table="' + table + '"][data-id="' + id + '"]');
                    if (link) {
                        const card = link.closest(".item");
                        card.remove();
                    }
                }
            } catch (error) {
                displayException(error);
            }
        }
        return;
    }).catch((err) => {
        if (err.message) {
            Log.error(err.message);
        }
        return;
    });
};

/**
 * Show a modal to confirm wiping all items.
 * @param {*} event
 */
const wipeModal = (event) => {
    event.preventDefault();

    deleteCancelPromise(
        getString('wipe', 'tiny_elements'),
        getString('wipewarning', 'tiny_elements')
    ).then(async() => {
        try {
            await wipe();
            reload();
            return;
        } catch (error) {
            displayException(error);
            return;
        }
    }).catch((err) => {
        if (err.message) {
            Log.error(err.message);
        }
        return;
    });
};

/**
 * Delete elements items.
 * @param {*} id
 * @param {*} table
 * @returns {mixed}
 */
export const deleteItem = (
    id,
    table,
) => fetchMany(
    [{
        methodname: 'tiny_elements_delete_item',
        args: {
            id,
            table,
        }
    }])[0];

/**
 * Wipe all elements items.
 * @returns {mixed}
 */
export const wipe = () => fetchMany(
    [{
        methodname: 'tiny_elements_wipe',
        args: {
            "contextid": 1,
        }
    }])[0];

/**
 * Show items after clicking a compcat.
 * @param {*} event
 * @param {*} compcat
 */
const showItems = (event, compcat) => {
    // But first hide all items.
    let itemsHide = document.querySelectorAll('.flavor, .component, .variant');
    itemsHide.forEach(element => {
        element.classList.add('hidden');
    });

    // Show component and variants with compcat name and read the flavors.
    let itemsShow = document.querySelectorAll('[data-categoryname="' + compcat + '"]');
    let usedFlavors = [];
    itemsShow.forEach(element => {
        element.classList.remove('hidden');
        // Get all flavors to show if on compcat element.
        if (typeof element.dataset.flavors !== 'undefined') {
            let flavors = element.dataset.flavors.split(' ');
            for (let value of flavors) {
                if (!usedFlavors.includes(value) && value.length != 0) {
                    usedFlavors.push(value);
                }
            }
        }
    });

    // Show the flavors.
    let flavorstring = usedFlavors.map(item => `.${item}`).join(', ');
    if (flavorstring.length) {
        let flavorsShow = document.querySelectorAll(flavorstring);
        flavorsShow.forEach(element => {
            element.classList.remove('hidden');
        });
    }

    // Show add buttons.
    let addsShow = document.getElementsByClassName('addcontainer');
    addsShow.forEach(element => {
        element.classList.remove('hidden');
    });

    // Unmark all and mark clicked compcat.
    if (event) {
        let items = document.getElementsByClassName('compcat');
        items.forEach(element => {
            element.classList.remove('active');
        });
        let item = event.target.closest('.compcat');
        item.classList.add('active');
    }

    // Special case, unassigned items, show all items without connection to compcat.
    if (compcat == 'found-items') {
        let found = document.querySelector('.compcat[data-compcat="found-items"]');
        if (found.dataset.loneflavors.length) {
            let flavorsShow = document.querySelectorAll(found.dataset.loneflavors);
            flavorsShow.forEach(element => {
                element.classList.remove('hidden');
            });
        }
        if (found.dataset.lonevariants.length) {
            let variantsShow = document.querySelectorAll(found.dataset.lonevariants);
            variantsShow.forEach(element => {
                element.classList.remove('hidden');
            });
        }
        if (found.dataset.lonecomponents.length) {
            let componentsShow = document.querySelectorAll(found.dataset.lonecomponents);
            componentsShow.forEach(element => {
                element.classList.remove('hidden');
            });
        }
    }
};

/**
 * Reload page with active compcat.
 */
const reload = () => {
    // Reload page with active compcat.
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('compcat', getActiveCompcatName());
    window.location.href = currentUrl.toString();
    window.location.reload();
};

/**
 * Get the current active compcat.
 * @returns string Name of active compcat.
 */
const getActiveCompcatName = () => {
    const compcat = document.querySelector('.compcat.active');
    if (!compcat) {
        return '';
    }
    return compcat.dataset.compcat ?? '';
};

/**
 * Get the current active compcat.
 * @returns int Id of active compcat.
 */
const getActiveCompcatId = () => {
    const compcat = document.querySelector('.compcat.active');
    if (!compcat) {
        return 0;
    }
    return compcat.dataset.id ?? 0;
};

/**
 * Duplicate elements items.
 * @param {*} id
 * @param {*} table
 * @returns {mixed}
 */
export const duplicateItem = (id, table) => fetchMany(
    [{
        methodname: 'tiny_elements_duplicate_item',
        args: {
            id,
            table,
        }
    }])[0];
