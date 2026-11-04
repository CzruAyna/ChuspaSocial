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

import Ajax from 'core/ajax';
import Notification from 'core/notification';

/**
 * Updates the reaction counter inside the button, if there is one.
 *
 * @param {HTMLElement} button Reaction button.
 * @param {Number} delta Amount to add to the current counter.
 */
const updateCounter = (button, delta) => {
    const counter = button.querySelector('[data-region="reaction-count"]');

    if (!counter || delta === 0) {
        return;
    }

    const current = Number(counter.textContent) || 0;

    counter.textContent = String(Math.max(0, current + delta));
};

/**
 * Initializes the reaction buttons.
 *
 * @module     local_chuspasocial/reactions
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
export const init = () => {
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-action="toggle-reaction"]');

        if (!button || button.disabled) {
            return;
        }

        event.preventDefault();

        const section = button.closest('[data-postid]');

        if (!section) {
            return;
        }

        const postid = Number(section.dataset.postid);
        const wasPressed = button.getAttribute('aria-pressed') === 'true';

        button.disabled = true;

        Ajax.call([{
            methodname: 'local_chuspasocial_toggle_reaction',
            args: {
                postid,
            },
        }])[0].then((response) => {
            button.setAttribute('aria-pressed', String(response.reacted));

            if (response.reacted !== wasPressed) {
                updateCounter(button, response.reacted ? 1 : -1);
            }

            button.disabled = false;

            return null;
        }).catch((error) => {
            button.disabled = false;

            Notification.exception(error);
        });
    });
};