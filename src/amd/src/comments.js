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
import {getString} from 'core/str';

const ERROR_VISIBLE_MS = 5000;

const errorTimers = new WeakMap();

/**
 * Shows a temporary error alert inside the comments section.
 *
 * @param {HTMLElement} section Element with the data-postid attribute.
 * @param {String} message Message to display.
 */
const showError = (section, message) => {
    let alert = section.querySelector('[data-region="comment-error"]');

    if (!alert) {
        alert = document.createElement('div');
        alert.className = 'alert alert-danger mt-2';
        alert.dataset.region = 'comment-error';
        alert.setAttribute('role', 'alert');
        section.append(alert);
    }

    alert.textContent = message;

    const timer = errorTimers.get(alert);

    if (timer) {
        window.clearTimeout(timer);
    }

    errorTimers.set(alert, window.setTimeout(() => {
        alert.remove();
        errorTimers.delete(alert);
    }, ERROR_VISIBLE_MS));
};

/**
 * Adds a rendered comment to the comments list of a post.
 *
 * @param {HTMLElement} section Element with the data-postid attribute.
 * @param {Object} comment Comment returned by local_chuspasocial_add_comment.
 */
const appendComment = (section, comment) => {
    const list = section.querySelector('ul');

    if (!list) {
        return;
    }

    // Remove the "no comments" placeholder, which has no data-commentid.
    const placeholder = list.querySelector(':scope > li:not([data-commentid])');

    if (placeholder) {
        placeholder.remove();
    }

    const item = document.createElement('li');
    item.className = 'border-bottom py-2';
    item.dataset.commentid = comment.id;

    const date = document.createElement('small');
    date.className = 'text-muted';
    date.textContent = new Date(comment.timecreated * 1000).toLocaleString();

    const body = document.createElement('div');
    body.textContent = comment.content;

    item.append(date, body);
    list.append(item);
};

/**
 * Initializes the add comment field without reloading the page.
 *
 * @module     local_chuspasocial/comments
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
export const init = () => {
    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter') {
            return;
        }

        const input = event.target.closest('[data-action="add-comment"]');

        if (!input) {
            return;
        }

        event.preventDefault();

        const section = input.closest('[data-postid]');
        const content = input.value.trim();

        if (!section || content === '' || input.disabled) {
            return;
        }

        const postid = Number(section.dataset.postid);

        input.disabled = true;

        Ajax.call([{
            methodname: 'local_chuspasocial_add_comment',
            args: {
                postid,
                content,
            },
        }])[0].then((comment) => {
            appendComment(section, comment);

            input.value = '';
            input.disabled = false;
            input.focus();

            return null;
        }).catch((error) => {
            input.disabled = false;

            return getString('errorsavingrequest', 'error').then((message) => {
                showError(section, message);

                return null;
            }).catch(() => {
                showError(section, (error && error.message) || '');

                return null;
            });
        });
    });
};