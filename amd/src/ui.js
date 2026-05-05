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
 * Tiny Elements UI.
 *
 * @module      tiny_elements/ui
 * @copyright 2025 ISB Bayern
 * @copyright based on the work of Marc Català <reskit@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {ElementsModal} from 'tiny_elements/modal';
import {
    isStudent,
    showPreview,
    canManage
} from 'tiny_elements/options';
import ModalEvents from 'core/modal_events';
import {
    getVariantsClass,
    getVariantPreferences,
    getVariantsHtml,
    loadVariantPreferences,
    setData as setVariantsData
} from 'tiny_elements/variantslib';
import {
    savePreferences,
    loadPreferences,
    Preferences
} from 'tiny_elements/preferencelib';
import {getContextId} from 'editor_tiny/options';
import Data from 'tiny_elements/data';

let currentFlavor = '';
let currentFlavorId = 0;
let currentCategoryId = 1;
let currentCategoryName = '';
let selectedComponentId = null;
let lastFlavor = [];
let selection = '';
let data = {};

/**
 * Handle action.
 *
 * @param {TinyMCE} editor
 */
export const handleAction = async(editor) => {
    selection = editor.selection.getContent();
    data = new Data(
        getContextId(editor),
        isStudent(editor),
        showPreview(editor),
        canManage(editor)
    );
    await data.loadData();
    setVariantsData(data);

    currentCategoryId = await loadPreferences(Preferences.category);

    lastFlavor = await loadPreferences(Preferences.category_flavors);
    if (lastFlavor === null || lastFlavor === undefined) {
        lastFlavor = [];
    }

    let componentVariants = await loadPreferences(Preferences.component_variants);
    if (componentVariants === null || componentVariants === undefined) {
        componentVariants = {};
    }
    loadVariantPreferences(componentVariants);
    await displayDialogue(editor);
};

/**
 * Display modal.
 *
 * @param  {TinyMCE} editor
 */
const displayDialogue = async(editor) => {
    const templateContext = data.getTemplateContext(editor);
    const modal = await ElementsModal.create({
        type: ElementsModal.TYPE,
        templateContext: templateContext,
        large: true,
    });

    const modalClass = data.getPreviewElements() ? 'elements-modal' : 'elements-modal-no-preview';
    editor.targetElm.closest('body').classList.add(modalClass);

    modal.show();

    const root = modal.getRoot()[0];

    modal.getRoot().on(ModalEvents.hidden, () => {
        handleModalHidden(editor);
    });

    root.querySelectorAll('.elements-rail-item').forEach(node => {
        node.addEventListener('click', (event) => handleRailClick(event, modal));
    });

    root.querySelectorAll('.elements-flavor-chip').forEach(node => {
        node.addEventListener('click', (event) => handleFlavorChipClick(event, modal));
    });

    root.querySelectorAll('.elementst-dialog-button').forEach(node => {
        node.addEventListener('click', (event) => handleCardClick(event, modal));
        if (data.getPreviewElements()) {
            node.addEventListener('mouseenter', (event) => handleCardMouseEvent(event, modal, true));
            node.addEventListener('mouseleave', (event) => handleCardMouseEvent(event, modal, false));
        }
    });

    const insertBtn = root.querySelector('.elements-insert-btn');
    if (insertBtn) {
        insertBtn.addEventListener('click', () => handleInsertClick(editor, modal));
    }

    restoreInitialSelection(modal);
};

/**
 * Restore the previously chosen category + flavor on modal open.
 *
 * @param {obj} modal
 */
const restoreInitialSelection = (modal) => {
    const root = modal.getRoot()[0];
    const railItems = root.querySelectorAll('.elements-rail-item');
    if (railItems.length === 0) {
        return;
    }
    let target = null;
    if (currentCategoryId) {
        target = root.querySelector(`.elements-rail-item[data-categoryid="${currentCategoryId}"]`);
    }
    if (!target) {
        target = railItems[0];
    }
    target.click();

    const savedFlavorId = lastFlavor[target.dataset.categoryid];
    if (savedFlavorId) {
        const flavorChip = root.querySelector(
            `.elements-flavor-chip[data-id="${savedFlavorId}"][data-categoryid="${target.dataset.categoryid}"]`
        );
        if (flavorChip) {
            flavorChip.click();
        }
    }
};

/**
 * Activate a category — switches the rail, the flavor bar set, and filters the grid.
 *
 * @param {MouseEvent} event
 * @param {obj} modal
 */
const handleRailClick = (event, modal) => {
    const item = event.currentTarget;
    currentCategoryId = item.dataset.categoryid;
    currentCategoryName = item.dataset.categoryname;
    currentFlavor = '';
    currentFlavorId = 0;

    const root = modal.getRoot()[0];

    root.querySelectorAll('.elements-rail-item').forEach(node => {
        const isActive = node === item;
        node.classList.toggle('active', isActive);
        node.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });

    root.querySelectorAll('.elements-flavor-list').forEach(set => {
        set.toggleAttribute('hidden', set.dataset.categoryid !== currentCategoryId);
    });
    root.querySelectorAll('.elements-flavor-chip').forEach(chip => chip.classList.remove('active'));
    const noneChip = root.querySelector(
        `.elements-flavor-list[data-categoryid="${currentCategoryId}"] .elements-flavor-none`
    );
    if (noneChip) {
        noneChip.classList.add('active');
    }

    showCategoryButtons(modal, currentCategoryName);
    clearSelection(modal);
};

/**
 * Handle clicks on a flavor chip (incl. the "Kein Flavor" chip).
 *
 * @param {MouseEvent} event
 * @param {obj} modal
 */
const handleFlavorChipClick = (event, modal) => {
    const chip = event.currentTarget;
    currentFlavor = chip.dataset.flavor || '';
    currentFlavorId = chip.dataset.id ? parseInt(chip.dataset.id, 10) : 0;
    currentCategoryId = chip.dataset.categoryid;
    currentCategoryName = chip.dataset.categoryname;

    if (currentFlavorId) {
        lastFlavor[currentCategoryId] = currentFlavorId;
    } else {
        delete lastFlavor[currentCategoryId];
    }

    const root = modal.getRoot()[0];
    root.querySelectorAll(
        `.elements-flavor-list[data-categoryid="${currentCategoryId}"] .elements-flavor-chip`
    ).forEach(c => c.classList.remove('active'));
    chip.classList.add('active');

    const buttons = root.querySelectorAll('.elementst-dialog-button');
    buttons.forEach(button => {
        const matchesCategory = button.dataset.category === currentCategoryName;
        const flavorlist = button.dataset.flavorlist || '';
        const matchesFlavor = !currentFlavor || flavorlist === '' || flavorlist.split(',').includes(currentFlavor);
        const visible = matchesCategory && matchesFlavor;
        button.classList.toggle('elements-hidden', !visible);

        if (visible) {
            button.dataset.flavor = currentFlavor;
            refreshCardDots(button);
        }
    });

    if (selectedComponentId) {
        const stillVisible = root.querySelector(
            `.elementst-dialog-button[data-id="${selectedComponentId}"]:not(.elements-hidden)`
        );
        if (!stillVisible) {
            clearSelection(modal);
        } else {
            updatePreviewSummary(modal);
            refreshSelectedPreview(modal);
        }
    }
};

/**
 * Handle a click on a component card — sets selection, enables Insert.
 *
 * @param {MouseEvent} event
 * @param {obj} modal
 */
const handleCardClick = (event, modal) => {
    const button = event.currentTarget;
    const root = modal.getRoot()[0];

    selectedComponentId = button.dataset.id;

    root.querySelectorAll('.elementst-dialog-button.selected').forEach(node => {
        node.classList.remove('selected');
        node.setAttribute('aria-selected', 'false');
    });
    button.classList.add('selected');
    button.setAttribute('aria-selected', 'true');

    refreshSelectedPreview(modal);
    updatePreviewSummary(modal);

    const insertBtn = root.querySelector('.elements-insert-btn');
    if (insertBtn) {
        insertBtn.disabled = false;
    }
};

/**
 * Hover over a card — show/hide preview (selection takes over on mouseleave).
 *
 * @param {MouseEvent} event
 * @param {obj} modal
 * @param {bool} show
 */
const handleCardMouseEvent = (event, modal, show) => {
    const button = event.currentTarget;
    if (show) {
        showPreviewFor(modal, button.dataset.id);
    } else if (selectedComponentId) {
        showPreviewFor(modal, selectedComponentId);
    } else {
        showPreviewFor(modal, null);
    }
};

/**
 * Render preview for the given component id (or the default hint when null).
 *
 * @param {obj} modal
 * @param {?string} componentId
 */
const showPreviewFor = (modal, componentId) => {
    const root = modal.getRoot()[0];
    const previewBody = root.querySelector('.elements-preview-body');
    if (!previewBody) {
        return;
    }
    previewBody.querySelectorAll('.elements-component-code, .elements-preview-default').forEach(node => {
        node.classList.add('elements-hidden');
    });

    if (!componentId) {
        const defaultHint = previewBody.querySelector('.elements-preview-default');
        if (defaultHint) {
            defaultHint.classList.remove('elements-hidden');
        }
        return;
    }

    const comp = data.getComponentById(componentId);
    if (!comp) {
        return;
    }
    const node = previewBody.querySelector(`div[data-id="code-preview-${componentId}"]`);
    if (!node) {
        return;
    }
    const flavor = comp.flavors.length > 0 ? currentFlavor : '';
    const placeholder = (selection.length > 0 ? selection : comp.text);
    node.innerHTML = updateComponentCode(comp.code, componentId, placeholder, flavor);
    node.classList.remove('elements-hidden');
};

/**
 * Re-render the preview for the currently selected component.
 *
 * @param {obj} modal
 */
const refreshSelectedPreview = (modal) => {
    if (selectedComponentId) {
        showPreviewFor(modal, selectedComponentId);
    } else {
        showPreviewFor(modal, null);
    }
};

/**
 * Sync the dot indicators on a single card to current preferences.
 *
 * @param {HTMLElement} card
 */
const refreshCardDots = (card) => {
    const comp = data.getComponentById(card.dataset.id);
    if (!comp) {
        return;
    }
    const flavor = comp.flavors.length > 0 ? currentFlavor : '';
    const activeClasses = getVariantsClass(comp.name, flavor);
    card.querySelectorAll('.elements-v-dot').forEach(dot => {
        dot.classList.toggle('on', activeClasses.includes(dot.dataset.variantclass));
    });
};

/**
 * Update the summary line in the preview footer.
 *
 * @param {obj} modal
 */
const updatePreviewSummary = (modal) => {
    const root = modal.getRoot()[0];
    const summary = root.querySelector('[data-region="preview-summary"]');
    if (!summary) {
        return;
    }
    if (!selectedComponentId) {
        summary.textContent = '';
        return;
    }
    const comp = data.getComponentById(selectedComponentId);
    if (!comp) {
        summary.textContent = '';
        return;
    }
    const parts = [comp.displayname];
    const cat = data.getCategoryById(currentCategoryId);
    if (cat) {
        parts.push(cat.displayname);
    }
    if (currentFlavor) {
        const chip = root.querySelector(
            `.elements-flavor-chip[data-flavor="${currentFlavor}"][data-categoryid="${currentCategoryId}"]`
        );
        if (chip) {
            parts.push(chip.textContent.trim());
        }
    }
    summary.textContent = parts.join(' · ');
};

/**
 * Reset selection state and disable Insert.
 *
 * @param {obj} modal
 */
const clearSelection = (modal) => {
    selectedComponentId = null;
    const root = modal.getRoot()[0];
    root.querySelectorAll('.elementst-dialog-button.selected').forEach(node => {
        node.classList.remove('selected');
        node.setAttribute('aria-selected', 'false');
    });
    const insertBtn = root.querySelector('.elements-insert-btn');
    if (insertBtn) {
        insertBtn.disabled = true;
    }
    showPreviewFor(modal, null);
    updatePreviewSummary(modal);
};

/**
 * Insert the selected component into the editor.
 *
 * @param {obj} editor
 * @param {obj} modal
 */
const handleInsertClick = (editor, modal) => {
    if (!selectedComponentId) {
        return;
    }
    const comp = data.getComponentById(selectedComponentId);
    if (!comp) {
        return;
    }

    const flavor = comp.flavors.length > 0 ? currentFlavor : '';
    const placeholder = (selection.length > 0 ? selection : comp.text);
    const randomId = generateRandomID();
    const newNode = document.createElement('span');
    newNode.dataset.id = randomId;
    newNode.innerHTML = placeholder;
    const componentCode = updateComponentCode(comp.code, selectedComponentId, newNode.outerHTML, flavor);

    editor.selection.setContent(componentCode);
    const nodeSel = editor.dom.select('span[data-id="' + randomId + '"]');
    if (nodeSel?.[0]) {
        editor.selection.select(nodeSel[0]);
    }

    modal.destroy();
    editor.focus();
    editor.undoManager.add();
};

/**
 * Modal close — persist selections.
 *
 * @param {obj} editor
 */
const handleModalHidden = (editor) => {
    editor.targetElm.closest('body').classList.remove('elements-modal-no-preview');
    if (currentCategoryId) {
        savePreferences([
            {type: Preferences.category, value: currentCategoryId},
            {type: Preferences.category_flavors, value: JSON.stringify(lastFlavor)},
            {type: Preferences.component_variants, value: JSON.stringify(getVariantPreferences())}
        ]);
    }
};

const updateComponentCode = (componentCode, selectedButton, placeholder, flavor = '') => {
    componentCode = componentCode.replace('{{PLACEHOLDER}}', placeholder);
    const comp = data.getComponentById(selectedButton);
    const variants = getVariantsClass(comp.name, flavor);

    if (variants.length > 0) {
        componentCode = componentCode.replace('{{VARIANTS}}', variants.join(' '));
        componentCode = componentCode.replace('{{VARIANTSHTML}}', getVariantsHtml(comp.name, flavor));
    } else {
        componentCode = componentCode.replace('{{VARIANTS}}', '');
        componentCode = componentCode.replace('{{VARIANTSHTML}}', '');
    }

    if (currentFlavor) {
        componentCode = componentCode.replace('{{FLAVOR}}', 'elements-' + currentFlavor + '-flavor');
    } else {
        componentCode = componentCode.replace('{{FLAVOR}}', '');
    }

    componentCode = componentCode.replace('{{COMPONENT}}', 'elements-' + comp.name);
    componentCode = componentCode.replace('{{CATEGORY}}', 'elements-' + data.getCategoryById(currentCategoryId).name);

    componentCode = applyRandomID(componentCode);
    componentCode = applyLangStrings(componentCode);

    return componentCode;
};

/**
 * Show only buttons of the active category.
 *
 * @param  {object} modal
 * @param  {String} context
 */
const showCategoryButtons = (modal, context) => {
    const showNodes = modal.getRoot()[0].querySelectorAll(`button[data-type="${context}"]`);
    const hideNodes = modal.getRoot()[0].querySelectorAll(`button[data-type]:not([data-type="${context}"])`);
    showNodes.forEach(node => {
        node.classList.remove('elements-hidden');
        refreshCardDots(node);
    });
    hideNodes.forEach(node => node.classList.add('elements-hidden'));
};

/**
 * Replace all localized strings.
 *
 * @param  {String} text
 * @return {String}
 */
const applyLangStrings = (text) => {
    const compRegex = /{{#([^}]*)}}/g;
    [...text.matchAll(compRegex)].forEach(strLang => {
        text = text.replace('{{#' + strLang[1] + '}}', data.getLangString(strLang[1]));
    });
    return text;
};

/**
 * Generates a random string.
 * @return {string}
 */
const generateRandomID = () => {
    const timestamp = new Date().getTime();
    return 'R' + Math.round(Math.random() * 100000) + '-' + timestamp;
};

/**
 * Replace all ID tags with a random string.
 * @param  {String} text
 * @return {String}
 */
const applyRandomID = (text) => {
    const compRegex = /{{@ID}}/g;
    if (text.match(compRegex)) {
        text = text.replace(compRegex, generateRandomID());
    }
    return text;
};
