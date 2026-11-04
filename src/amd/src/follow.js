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
import {getString} from 'core/str';

/**
 * Initializes follow/unfollow buttons.
 *
 * @module     local_chuspasocial/follow
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
export const init = () => {
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-action="toggle-follow"]');

        if (!button || button.disabled) {
            return;
        }

        event.preventDefault();

        const component = button.dataset.component;
        const itemid = Number(button.dataset.itemid);

        button.disabled = true;

        Ajax.call([{
            methodname: 'local_chuspasocial_toggle_follow',
            args: {
                component,
                itemid,
            },
        }])[0].then((response) => {
            const following = response.action === 'followed';

            button.setAttribute('aria-pressed', String(following));

            return getString(following ? 'following' : 'follow', 'local_chuspasocial');
        }).then((label) => {
            button.textContent = label;
            button.disabled = false;

            return null;
        }).catch((error) => {
            button.disabled = false;

            Notification.exception(error);
        });
    });
};
