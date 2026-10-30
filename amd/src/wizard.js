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
const TYPING = 'coursewizard-typing-active';
const APPEAR = 'coursewizard-appear';
const VANISH = 'coursewizard-vanish';
const MAXANSWER = 160;
const TYPINGDELAY = 400;
const VANISHDELAY = 400;

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
 * Returns the answers of a step, each with the label of its field.
 *
 * @param {Object} state
 * @param {Object} step
 * @param {boolean} skipped Skipped with "Later".
 * @returns {Array} Objects with label and value.
 */
const getStepAnswer = (state, step, skipped) => step.elements.map((element) => {
    const value = skipped ? state.config.strings.later
        : getAnswer(state.form, element, state.config.strings) || state.config.strings.none;
    return {
        label: element.label,
        value: value.length > MAXANSWER ? value.substring(0, MAXANSWER - 1) + '…' : value,
    };
});

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
 * Returns the choices of an answered step for the history, empty if the step has none.
 *
 * @param {Object} state
 * @param {Object} step
 * @param {number} index Index of the step.
 * @returns {Array} Objects with index, name, value, label, icon, selected.
 */
const getChoices = (state, step, index) => {
    const element = step.elements.length === 1 && step.elements[0].choices ? step.elements[0] : null;
    return element === null ? [] : exportChoices(state, element, String(index));
};

/**
 * Returns the choices of an element for local_coursewizard/choices.
 *
 * @param {Object} state
 * @param {Object} element Element with choices: value, label, icon, image, url.
 * @param {string} step Index of the step in the history, empty for the current step.
 * @returns {Array}
 */
const exportChoices = (state, element, step) => {
    const current = state.form.elements[element.name].value;
    // With images (templates) all choices are cards, so they line up.
    const card = element.choices.some((choice) => choice.image !== '');
    return element.choices.map((choice) => ({...choice, step: step, name: element.name, selected: choice.value === current,
        card: card}));
};

/**
 * Shows the choices of a select as buttons, the current value is pressed.
 *
 * @param {Object} state
 * @param {Object|null} element Element with choices: value, label, icon. Null hides the buttons.
 */
const showChoices = async(state, element) => {
    state.quick.hidden = element === null;
    if (element === null) {
        state.quick.replaceChildren();
        return;
    }
    const {html, js} = await Templates.renderForPromise('local_coursewizard/choices',
        {choices: exportChoices(state, element, '')});
    Templates.replaceNodeContents(state.quick, html, js);
};

/**
 * Shows the current step: chat history, its fields and the navigation.
 *
 * @param {Object} state
 * @param {boolean} focus Move the focus to the first field of the step.
 */
const show = async(state, focus) => {
    // Only after an answer: the answer slides in, then the reply follows after a short "typing" pause.
    const animate = state.animate !== false && !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    // After "What is this for?" only the explanation follows, no answer and no new input.
    const explaining = state.animate === 'explain';
    state.animate = false;
    const render = ++state.renders;
    const steps = state.config.steps;
    const isdone = state.current >= steps.length;
    const hasoptional = state.lastrequired < steps.length - 1;
    // After the last required step: create the course now or add the optional details.
    const atdecision = hasoptional && state.current === state.lastrequired + 1 && !state.decided;
    const context = {
        answered: steps.slice(0, state.current).map((step, index) => ({
            index: index,
            question: step.question,
            answers: getStepAnswer(state, step, state.skipped.has(index)),
            // Choices stay buttons in the history, the current value is pressed and can still be changed.
            choices: getChoices(state, step, index),
            explanation: state.explained.has(index) ? step.explanation : '',
            fresh: animate && !explaining && index === state.answered,
            // Only while it is open: once decided, the offer no longer applies and disappears.
            decision: index === state.lastrequired && hasoptional && !state.decided ? {text: state.config.decision} : null,
        })),
        question: atdecision ? '' : (isdone ? state.config.done : steps[state.current].question),
        explain: !atdecision && !isdone && !state.explained.has(state.current) && steps[state.current].explanation !== '',
        explanation: !atdecision && !isdone && state.explained.has(state.current) ? steps[state.current].explanation : '',
    };
    const {html, js} = await Templates.renderForPromise('local_coursewizard/history', context);
    if (render !== state.renders) {
        return;
    }
    Templates.replaceNodeContents(state.history, html, js);
    // The reply of the assistant: the next question or the decision box.
    const reply = animate ? state.history.lastElementChild : null;
    if (reply && !reply.classList.contains('coursewizard-user')) {
        reply.hidden = true;
        state.root.classList.toggle(TYPING, !explaining);
        state.history.insertAdjacentHTML('beforeend', '<div class="coursewizard-bot coursewizard-typing" aria-hidden="true">' +
            '<span></span><span></span><span></span></div>');
    }

    state.form.querySelectorAll('.' + ACTIVE).forEach((node) => node.classList.remove(ACTIVE));
    let names = isdone ? ['buttonar'] : steps[state.current].elements.map((element) => element.name);
    // A single select with choices is answered with buttons in the chat, its dropdown stays hidden.
    const choice = !isdone && !atdecision && steps[state.current].elements.length === 1 &&
        steps[state.current].elements[0].choices ? steps[state.current].elements[0] : null;
    showChoices(state, choice);
    if (atdecision || choice) {
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
    state.nav.querySelector('[data-action="next"]').hidden = choice !== null;
    // Choices always need a decision, so there is no "Later" next to them.
    state.nav.querySelector('[data-action="later"]').hidden = isdone || atdecision || steps[state.current].required ||
        choice !== null;
    state.nav.hidden = isdone || choice !== null;
    // Visible on every optional step, so it is always clear the course can be created now.
    state.createnow.hidden = !hasoptional || isdone || atdecision || state.current <= state.lastrequired;

    if (reply && reply.hidden) {
        await new Promise((resolve) => setTimeout(resolve, TYPINGDELAY));
        if (render !== state.renders) {
            return;
        }
        state.history.querySelector('.coursewizard-typing')?.remove();
        reply.hidden = false;
        reply.classList.add(APPEAR);
        if (!explaining) {
            state.root.classList.remove(TYPING);
            state.input.classList.add(APPEAR);
            state.input.addEventListener('animationend', () => state.input.classList.remove(APPEAR), {once: true});
        }
    }

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
    goOn(state);
};

/**
 * Goes to the next step, or after editing an earlier answer back to the step reached before.
 *
 * @param {Object} state
 */
const goOn = (state) => {
    state.answered = state.current;
    state.current = Math.max(state.current + 1, state.reached);
    state.reached = state.current;
    // Back past the decision and forward again: the optional details were chosen before.
    if (state.current > state.lastrequired + 1) {
        state.decided = true;
    }
    state.animate = true;
    show(state, true);
};

/**
 * Skips an optional step, its fields keep their defaults. It can be answered later via "Edit".
 *
 * @param {Object} state
 */
const later = (state) => {
    state.skipped.add(state.current);
    goOn(state);
};

/**
 * Adds the optional details: the offer dissolves, then the first optional question follows.
 *
 * @param {Object} state
 * @param {HTMLElement|null} box The green box with the offer.
 */
const more = async(state, box) => {
    if (box && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        box.classList.add(VANISH);
        await new Promise((resolve) => setTimeout(resolve, VANISHDELAY));
    }
    state.decided = true;
    state.answered = null;
    state.animate = true;
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
        root: root,
        form: root.querySelector('form'),
        history: root.querySelector('[data-region="history"]'),
        nav: root.querySelector('[data-region="nav"]'),
        back: root.querySelector('[data-action="back"]'),
        createnow: root.querySelector('[data-region="createnow"]'),
        input: root.querySelector('[data-region="input"]'),
        quick: root.querySelector('[data-region="quick"]'),
        current: config.errorstep === null ? 0 : config.errorstep,
        // Required steps come first (course_form::get_wizard_config()).
        lastrequired: config.steps.map((step) => step.required).lastIndexOf(true),
    };
    // A validation error in an optional step means the details were chosen before.
    state.decided = state.current > state.lastrequired;
    // Optional steps skipped with "Later".
    state.skipped = new Set();
    // Furthest step so far: after editing an earlier answer, "Next" returns there.
    state.reached = state.current;
    // Step just answered, its answer slides in (null: "more details" chosen).
    state.answered = null;
    // Steps whose explanation was asked for, it stays in the history.
    state.explained = new Set();
    // Set before show() to animate the answer and the reply (true) or the explanation ('explain'),
    // the counter drops outdated renders.
    state.animate = false;
    state.renders = 0;
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
        } else if (button.dataset.action === 'choose') {
            const select = state.form.elements[button.dataset.name];
            select.value = button.dataset.value;
            select.dispatchEvent(new Event('change', {bubbles: true}));
            // In the history only the value changes, the dialog stays at the current step.
            if (button.dataset.step !== '' && Number(button.dataset.step) !== state.current) {
                show(state, false);
            } else {
                next(state);
            }
        } else if (button.dataset.action === 'explain') {
            state.explained.add(state.current);
            state.animate = 'explain';
            show(state, true);
        } else if (button.dataset.action === 'more') {
            more(state, button.closest('.coursewizard-decision'));
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

    // Enter in an autocomplete (e.g. category) with a closed list and no search text goes to the next step.
    // Capture phase: checked before Moodle's autocomplete reacts, otherwise choosing an entry would also go on.
    state.form.addEventListener('keydown', (event) => {
        const target = event.target;
        if (event.key === 'Enter' && target.matches('input[role="combobox"]') &&
                target.getAttribute('aria-expanded') !== 'true' && target.value.trim() === '' &&
                state.current < config.steps.length) {
            event.preventDefault();
            event.stopPropagation();
            next(state);
        }
    }, true);

    root.classList.add('coursewizard-ready');
    show(state, config.errorstep !== null);
};
