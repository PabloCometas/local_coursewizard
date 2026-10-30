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
 * Guided dialog for creating a new course.
 *
 * Moodle's course form stays on the page and is submitted as usual. The dialog
 * only shows the fields of the current step and lists the answered steps as a
 * chat history. Steps come from \local_coursewizard\form\course_form.
 *
 * @module     local_coursewizard/wizard
 * @copyright  2026 cometas
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Templates from 'core/templates';
import {getInstanceForElementId} from 'editor_tiny/editor';

const ACTIVE = 'coursewizard-active';
const SINGLE = 'coursewizard-single';
const COMPOSE = 'coursewizard-compose';
const MAXANSWER = 160;

/**
 * Returns the form item (label and field) of an element.
 *
 * @param {string} name Element name.
 * @returns {HTMLElement|null}
 */
const getItem = (name) => document.getElementById('fitem_id_' + name) || document.getElementById('fgroup_id_' + name);

/**
 * Returns the plain text of HTML.
 *
 * @param {string} html
 * @returns {string}
 */
const htmlToText = (html) => new DOMParser().parseFromString(html, 'text/html').body.textContent.trim();

/**
 * Returns the current value of an element as text for the chat history.
 *
 * @param {HTMLFormElement} form
 * @param {Object} element Element from the step config: name, type.
 * @param {Object} strings Strings none, yes, no.
 * @returns {string} Empty if there is no value.
 */
const getAnswer = (form, element, strings) => {
    const name = element.name;
    const item = getItem(name);
    switch (element.type) {
        case 'editor': {
            const editor = getInstanceForElementId('id_' + name);
            return editor ? editor.getContent({format: 'text'}).trim() : htmlToText(form.elements[name + '[text]'].value);
        }
        case 'filemanager':
            return item ? [...item.querySelectorAll('.fp-filename')].map((node) => node.textContent.trim())
                .filter((text) => text !== '').join(', ') : '';
        case 'date_selector':
        case 'date_time_selector': {
            const enabled = form.elements[name + '[enabled]'];
            if (enabled && !enabled.checked) {
                return '';
            }
            const part = (key) => form.elements[name + '[' + key + ']'] ? Number(form.elements[name + '[' + key + ']'].value) : 0;
            const date = new Date(part('year'), part('month') - 1, part('day'), part('hour'), part('minute'));
            const options = element.type === 'date_selector' ? {dateStyle: 'long'} : {dateStyle: 'long', timeStyle: 'short'};
            return date.toLocaleString(document.documentElement.lang || undefined, options);
        }
        case 'checkbox':
        case 'advcheckbox':
            return item && item.querySelector('input[type="checkbox"]').checked ? strings.yes : strings.no;
        default: {
            const field = form.elements[name] || form.elements[name + '[]'];
            if (!field) {
                return '';
            }
            if (field instanceof HTMLSelectElement) {
                return [...field.selectedOptions].map((option) => option.textContent.trim()).join(', ');
            }
            return field.value.trim();
        }
    }
};

/**
 * Returns the answer of a step as one text.
 *
 * @param {Object} state
 * @param {Object} step
 * @returns {string}
 */
const getStepAnswer = (state, step) => {
    const answers = step.elements.map((element) => getAnswer(state.form, element, state.config.strings))
        .filter((answer) => answer !== '');
    const text = answers.length ? answers.join('\n') : state.config.strings.none;
    return text.length > MAXANSWER ? text.substring(0, MAXANSWER - 1) + '…' : text;
};

/**
 * Shows or hides Moodle's error message of an element.
 *
 * @param {string} name Element name.
 * @param {string} message Empty to hide.
 */
const setError = (name, message) => {
    const feedback = document.getElementById('id_error_' + name);
    const field = document.getElementById('id_' + name);
    if (feedback) {
        feedback.textContent = message;
        feedback.classList.toggle('d-block', message !== '');
    }
    if (field) {
        field.classList.toggle('is-invalid', message !== '');
    }
};

/**
 * Checks the required elements of the current step, shows Moodle's messages.
 * Everything else is validated by Moodle when the form is submitted.
 *
 * @param {Object} state
 * @param {Object} step
 * @returns {boolean} True if all required elements have a value.
 */
const validateStep = (state, step) => {
    let valid = true;
    step.elements.forEach((element) => {
        if (element.requiredmessage === '' || element.type === 'filemanager') {
            return;
        }
        const missing = getAnswer(state.form, element, state.config.strings) === '';
        setError(element.name, missing ? element.requiredmessage : '');
        valid = valid && !missing;
    });
    return valid;
};

/**
 * Shows the current step: chat history, its fields and the navigation.
 *
 * @param {Object} state
 * @param {boolean} focus Move the focus to the first field of the step.
 */
const show = async(state, focus) => {
    const steps = state.config.steps;
    const isdone = state.current >= steps.length;
    const hasoptional = state.lastrequired < steps.length - 1;
    // After the last required step: create the course now or add the optional details.
    const atdecision = hasoptional && state.current === state.lastrequired + 1 && !state.decided;
    const context = {
        answered: steps.slice(0, state.current).map((step, index) => ({
            index: index,
            question: step.question,
            answer: state.skipped.has(index) ? state.config.strings.later : getStepAnswer(state, step),
            decision: index === state.lastrequired && hasoptional ? {
                text: state.config.decision,
                decided: state.decided,
                answer: state.config.strings.moredetails,
            } : null,
        })),
        question: atdecision ? '' : (isdone ? state.config.done : steps[state.current].question),
    };
    const {html, js} = await Templates.renderForPromise('local_coursewizard/history', context);
    Templates.replaceNodeContents(state.history, html, js);

    state.form.querySelectorAll('.' + ACTIVE).forEach((node) => node.classList.remove(ACTIVE));
    let names = isdone ? ['buttonar'] : steps[state.current].elements.map((element) => element.name);
    if (atdecision) {
        names = [];
    }
    const items = names.map(getItem).filter((item) => item !== null);
    items.forEach((item) => item.classList.add(ACTIVE));
    (isdone || atdecision ? [] : steps[state.current].elements).filter((element) => element.placeholder)
        .forEach((element) => {
            const input = getItem(element.name)?.querySelector('input[role="combobox"]');
            if (input) {
                input.placeholder = element.placeholder;
            }
        });
    // A single field needs no label next to the question; text fields show it as placeholder.
    state.form.classList.toggle(SINGLE, items.length === 1 && !isdone);
    if (items.length === 1 && !isdone) {
        const label = items[0].querySelector('label');
        items[0].querySelectorAll('input[type="text"]:not([placeholder])').forEach((input) => {
            input.placeholder = label ? label.textContent.trim() : '';
        });
    }

    // Only a single text field looks like a chat composer with a round send arrow,
    // selections and other fields get a labelled "Next" button below them.
    const elements = isdone || atdecision ? [] : steps[state.current].elements;
    state.input.classList.toggle(COMPOSE, elements.length === 1 && elements[0].type === 'text');
    state.input.hidden = atdecision;
    state.back.hidden = state.current === 0;
    state.nav.hidden = isdone;
    state.nav.querySelector('[data-action="later"]').hidden = isdone || atdecision || steps[state.current].required;
    // Visible on every optional step, so it is always clear the course can be created now.
    state.createnow.hidden = !hasoptional || isdone || atdecision || state.current <= state.lastrequired;

    if (focus && atdecision) {
        state.history.querySelector('[data-action="create"]').focus();
    }
    if (focus && items.length) {
        // Visible fields only: an autocomplete keeps its original select hidden.
        const field = [...items[0].querySelectorAll('input:not([type="hidden"]), select, textarea, button')]
            .find((node) => node.offsetParent !== null);
        if (field) {
            field.focus();
        }
        items[0].scrollIntoView({block: 'nearest', behavior: 'smooth'});
    }
};

/**
 * Checks the current step and fills in the short name.
 *
 * @param {Object} state
 * @returns {boolean} True if the current step is complete.
 */
const completeStep = (state) => {
    if (!validateStep(state, state.config.steps[state.current])) {
        return false;
    }
    // The short name is never asked. Moodle's own browser check needs a value,
    // the server replaces it with a unique one (course_form::unique_shortname()).
    const {fullname, shortname} = state.form.elements;
    if (fullname && shortname) {
        shortname.value = fullname.value.trim().substring(0, 100);
    }
    return true;
};

/**
 * Goes to the next step if the current one is complete.
 *
 * @param {Object} state
 */
const next = (state) => {
    if (!completeStep(state)) {
        return;
    }
    state.skipped.delete(state.current);
    state.current++;
    show(state, true);
};

/**
 * Skips an optional step, its fields keep their defaults. It can be answered later via "Edit".
 *
 * @param {Object} state
 */
const later = (state) => {
    state.skipped.add(state.current);
    state.current++;
    show(state, true);
};

/**
 * Creates the course right away, the remaining steps keep their defaults.
 *
 * @param {Object} state
 */
const create = (state) => {
    if (!completeStep(state)) {
        return;
    }
    state.form.requestSubmit(state.form.elements.saveanddisplay);
};

/**
 * Initialises the dialog.
 *
 * @param {string} selector Selector of the dialog container.
 */
export const init = (selector) => {
    const root = document.querySelector(selector);
    if (!root) {
        return;
    }
    const config = JSON.parse(root.dataset.config);
    const state = {
        config: config,
        form: root.querySelector('form'),
        history: root.querySelector('[data-region="history"]'),
        nav: root.querySelector('[data-region="nav"]'),
        back: root.querySelector('[data-action="back"]'),
        createnow: root.querySelector('[data-region="createnow"]'),
        input: root.querySelector('[data-region="input"]'),
        current: config.errorstep === null ? 0 : config.errorstep,
        // Required steps come first (course_form::get_wizard_config()).
        lastrequired: config.steps.map((step) => step.required).lastIndexOf(true),
    };
    // A validation error in an optional step means the details were chosen before.
    state.decided = state.current > state.lastrequired;
    // Optional steps skipped with "Later".
    state.skipped = new Set();
    if (!state.form || !config.steps.length) {
        return;
    }

    root.addEventListener('click', (event) => {
        const button = event.target.closest('[data-action]');
        if (!button) {
            return;
        }
        if (button.dataset.action === 'next') {
            next(state);
        } else if (button.dataset.action === 'create') {
            create(state);
        } else if (button.dataset.action === 'later') {
            later(state);
        } else if (button.dataset.action === 'more') {
            state.decided = true;
            show(state, true);
        } else if (button.dataset.action === 'back') {
            // From the first optional step back to the decision, from there to the last required step.
            if (state.current === state.lastrequired + 1 && state.decided) {
                state.decided = false;
            } else {
                state.current--;
            }
            show(state, true);
        } else if (button.dataset.action === 'edit') {
            state.current = Number(button.dataset.step);
            show(state, true);
        }
    });

    // Enter in a text field goes to the next step instead of submitting the form.
    state.form.addEventListener('keydown', (event) => {
        const target = event.target;
        if (event.key === 'Enter' && target.matches('input[type="text"]:not([role="combobox"])') &&
                state.current < config.steps.length) {
            event.preventDefault();
            next(state);
        }
    });

    root.classList.add('coursewizard-ready');
    show(state, config.errorstep !== null);
};
