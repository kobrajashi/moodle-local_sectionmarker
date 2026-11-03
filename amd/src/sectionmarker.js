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
 * AMD module for local_sectionmarker – v1.0.2
 *
 * Marks individual course modules (<li id="module-{cmid}" class="activity ...">),
 * not whole sections.
 *
 * @module     local_sectionmarker/sectionmarker
 * @copyright  2025 Tobias Sanders
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import Notification from 'core/notification';
import {get_strings as getStrings} from 'core/str';

const MARKER_CLASSES = ['sectionmarker-info', 'sectionmarker-note', 'sectionmarker-ok'];
const MARKERS = {
    info:    {cssClass: 'sectionmarker-info'},
    note: {cssClass: 'sectionmarker-note'},
    ok:      {cssClass: 'sectionmarker-ok'},
};
const INJECTED_ATTR = 'data-sectionmarker-injected';

// ---------------------------------------------------------------------------
// Find the <li id="module-{cmid}"> element
// ---------------------------------------------------------------------------

const findModuleElement = (cmId) => {
    const id = String(cmId);
    return (
        document.querySelector(`#module-${id}`) ||
        document.querySelector(`li.activity[data-id="${id}"]`) ||
        document.querySelector(`.activity[data-id="${id}"]`)
    );
};

// ---------------------------------------------------------------------------
// Ownership
//
// A subsection is itself an activity and contains further activities. Plain
// querySelector() would therefore hand a subsection the menu and the body of
// its first child, which is why that child used to show the marker items twice
// and why picking one of them coloured the whole section. A node only counts as
// the activity's own if no other activity sits between the two.
// ---------------------------------------------------------------------------

const belongsTo = (activityEl, node) => node.closest('.activity[data-id]') === activityEl;

const findOwn = (activityEl, selectors) => {
    for (const selector of selectors) {
        for (const candidate of activityEl.querySelectorAll(selector)) {
            if (belongsTo(activityEl, candidate)) {
                return candidate;
            }
        }
    }
    return null;
};

/**
 * Whether this element holds other activities instead of being one.
 *
 * Markers belong on activities. Subsections render as a section of their own,
 * so marking one paints the whole block — exactly what we want to avoid.
 *
 * @param  {Element} activityEl
 * @return {boolean}
 */
const isContainer = (activityEl) => (
    activityEl.classList.contains('modtype_subsection') ||
    activityEl.querySelector('.activity[data-id]') !== null
);

// ---------------------------------------------------------------------------
// Apply / remove CSS class on the module element
// ---------------------------------------------------------------------------

const applyModuleMarker = (cmId, marker) => {
    const moduleEl = findModuleElement(cmId);
    if (!moduleEl || isContainer(moduleEl)) {
        return;
    }
    const el = findOwn(moduleEl, ['.activity-item']) || moduleEl;
    el.classList.remove(...MARKER_CLASSES);
    if (marker && MARKERS[marker]) {
        el.classList.add(MARKERS[marker].cssClass);
    }
};

const applyAllMarkers = (markers) => {
    for (const [cmId, marker] of Object.entries(markers)) {
        if (marker) {
            applyModuleMarker(cmId, marker);
        }
    }
};

// ---------------------------------------------------------------------------
// Build Bootstrap 5 dropdown elements
// ---------------------------------------------------------------------------

const buildDivider = () => {
    const li = document.createElement('li');
    li.innerHTML = '<hr class="dropdown-divider sectionmarker-divider">';
    return li;
};

const buildItem = (emoji, label, onClick) => {
    const li = document.createElement('li');
    const a = document.createElement('a');
    a.className = 'dropdown-item sectionmarker-item';
    a.href = '#';
    a.textContent = `${emoji} ${label}`;
    a.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        onClick();
    });
    li.appendChild(a);
    return li;
};

// ---------------------------------------------------------------------------
// Dropdown lookup
// ---------------------------------------------------------------------------

const findDropdownMenu = (activityEl) => findOwn(activityEl, [
    '.activity-actions .dropdown-menu',
    '[data-region="action-menu"] .dropdown-menu',
    '[data-region="dropdown-menu-container"]',
    '.dropdown-menu',
]);

// ---------------------------------------------------------------------------
// Web service calls
// ---------------------------------------------------------------------------

const handleMarkerClick = async(courseId, cmId, marker) => {
    try {
        if (marker) {
            await Ajax.call([{
                methodname: 'local_sectionmarker_set_marker',
                args: {courseid: courseId, cmid: cmId, marker},
            }])[0];
        } else {
            await Ajax.call([{
                methodname: 'local_sectionmarker_remove_marker',
                args: {courseid: courseId, cmid: cmId},
            }])[0];
        }
        applyModuleMarker(cmId, marker || null);
    } catch (err) {
        Notification.exception(err);
    }
};

// ---------------------------------------------------------------------------
// Menu injection
// ---------------------------------------------------------------------------

const injectItemsToActivity = (courseId, activityEl, strings) => {
    if (activityEl.hasAttribute(INJECTED_ATTR) || isContainer(activityEl)) {
        return;
    }
    const dropdownMenu = findDropdownMenu(activityEl);
    if (!dropdownMenu) {
        return;
    }
    // Cmid = the activity element's own data-id: <li data-id="42" id="module-42">
    const cmId = parseInt(activityEl.dataset.id, 10);
    if (!cmId) {
        return;
    }

    activityEl.setAttribute(INJECTED_ATTR, '1');

    // The course editor re-renders activity cards, so the same menu can be
    // offered twice. One set of items per menu is enough.
    if (dropdownMenu.querySelector('.sectionmarker-item')) {
        return;
    }

    dropdownMenu.appendChild(buildDivider());

    [
        {marker: 'info', emoji: '🔵', label: strings[0]},
        {marker: 'note', emoji: '🟡', label: strings[1]},
        {marker: 'ok', emoji: '🟢', label: strings[2]},
        {marker: null, emoji: '❌', label: strings[3]},
    ].forEach(({marker, emoji, label}) =>
        dropdownMenu.appendChild(buildItem(emoji, label, () => handleMarkerClick(courseId, cmId, marker)))
    );
};

const injectAll = (courseId, strings) =>
    document.querySelectorAll('.activity[data-id]').forEach((el) => injectItemsToActivity(courseId, el, strings));

// ---------------------------------------------------------------------------
// MutationObserver
// ---------------------------------------------------------------------------

const observeNewActivities = (courseId, strings) => {
    const root = document.querySelector('.course-content') || document.body;
    new MutationObserver((mutations) => {
        for (const mutation of mutations) {
            for (const node of mutation.addedNodes) {
                if (node.nodeType !== Node.ELEMENT_NODE) {
                    continue;
                }
                const candidates = [];
                if (node.matches?.('.activity[data-id]')) {
                    candidates.push(node);
                }
                node.querySelectorAll?.('.activity[data-id]').forEach((el) => candidates.push(el));
                candidates.forEach((el) => injectItemsToActivity(courseId, el, strings));
            }
        }
    }).observe(root, {childList: true, subtree: true});
};

// ---------------------------------------------------------------------------
// Observer for colour-only updates (always active, not just in edit mode)
// ---------------------------------------------------------------------------

const observeColorUpdates = (markers) => {
    const root = document.querySelector('.course-content') || document.body;
    new MutationObserver((mutations) => {
        for (const mutation of mutations) {
            for (const node of mutation.addedNodes) {
                if (node.nodeType !== Node.ELEMENT_NODE) {
                    continue;
                }
                const candidates = [];
                if (node.matches?.('.activity[data-id]')) {
                    candidates.push(node);
                }
                node.querySelectorAll?.('.activity[data-id]').forEach((el) => candidates.push(el));
                candidates.forEach((el) => {
                    const cmId = String(el.dataset.id);
                    if (markers[cmId]) {
                        applyModuleMarker(cmId, markers[cmId]);
                    }
                });
            }
        }
    }).observe(root, {childList: true, subtree: true});
};

// ---------------------------------------------------------------------------
// Entry point
// ---------------------------------------------------------------------------

export const init = async(courseId, markers, isEditMode) => {
    applyAllMarkers(markers);
    observeColorUpdates(markers); // Re-apply colours when activities load dynamically.
    if (!isEditMode) {
        return;
    }
    let strings;
    try {
        strings = await getStrings([
            {key: 'mark_info', component: 'local_sectionmarker'},
            {key: 'mark_note', component: 'local_sectionmarker'},
            {key: 'mark_ok', component: 'local_sectionmarker'},
            {key: 'remove_mark', component: 'local_sectionmarker'},
        ]);
    } catch (err) {
        Notification.exception(err);
        return;
    }
    injectAll(courseId, strings);
    observeNewActivities(courseId, strings);
};
