<?php
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
 * Service to toggle the reactions of the ChuspaSocial posts.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chuspasocial\local;

use context_course;
use context_system;
use local_chuspasocial\local\persistent\post;
use local_chuspasocial\local\persistent\reaction;
use moodle_exception;

/**
 * Service to toggle the reactions of the ChuspaSocial posts.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class reaction_service {
    /**
     * Toggles the current user's reaction to a post.
     *
     * @param int $postid ID of the post to react to.
     * @return bool True if the reaction was added, false if it was removed.
     * @throws moodle_exception If the user cannot view the post.
     */
    public static function toggle(int $postid): bool {
        global $DB, $USER;

        $post = new post($postid);

        if (!post_service::can_view($post, (int) $USER->id)) {
            throw new moodle_exception('nopermissions', 'error', '', 'view this post');
        }

        // Hidden posts cannot receive reactions, even from their author.
        if ((int) $post->get('hidden') === 1) {
            throw new moodle_exception('nopermissions', 'error', '', 'react to this post');
        }

        if ((int) $post->get('courseid') > 0) {
            $context = context_course::instance((int) $post->get('courseid'));
        } else {
            $context = context_system::instance();
        }

        // The capability may not be defined yet (issue #256).
        if (get_capability_info('local/chuspasocial:react')) {
            require_capability('local/chuspasocial:react', $context);
        }

        $params = [
            'postid' => $postid,
            'userid' => (int) $USER->id,
        ];

        $existing = $DB->get_record('local_chuspasocial_reaction', $params);
        if ($existing) {
            $DB->delete_records('local_chuspasocial_reaction', ['id' => $existing->id]);
            return false;
        }

        $record = (object) [
            'postid' => $postid,
            'userid' => (int) $USER->id,
            'type' => reaction::TYPE_LIKE,
            'timecreated' => time(),
        ];
        $reactionid = $DB->insert_record('local_chuspasocial_reaction', $record);

        // Trigger the reaction_added event once the reaction is saved.
        \local_chuspasocial\event\reaction_added::create([
            'objectid' => $reactionid,
            'context' => $context,
            'userid' => (int) $USER->id,
            'other' => ['reactiontype' => $record->type],
        ])->trigger();

        return true;
    }
}
