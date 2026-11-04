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
 * External function local_chuspasocial_add_comment.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chuspasocial\external;

use context_course;
use context_system;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use local_chuspasocial\local\comment_service;

/**
 * Adds a comment to a post.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class add_comment extends external_api {

    /**
     * Describes the parameters of the function.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'postid' => new external_value(PARAM_INT, 'Post id.'),
            'content' => new external_value(PARAM_RAW_TRIMMED, 'Comment content.'),
        ]);
    }

    /**
     * Adds a comment to a post.
     *
     * @param int $postid Post id.
     * @param string $content Comment content.
     * @return array
     */
    public static function execute(int $postid, string $content): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'postid' => $postid,
            'content' => $content,
        ]);

        $post = $DB->get_record(
            'local_chuspasocial_post',
            ['id' => $params['postid']],
            'id, courseid, hidden',
            MUST_EXIST
        );

        if ((int) $post->courseid > 0) {
            $context = context_course::instance((int) $post->courseid);
        } else {
            $context = context_system::instance();
        }

        self::validate_context($context);
        require_capability('local/chuspasocial:comment', $context);

        // Impedir comentar publicaciones ocultas.
        if (!empty($post->hidden)) {
            throw new \invalid_parameter_exception('Cannot comment on a hidden post');
        }

        $comment = comment_service::add_comment(
            (int) $params['postid'],
            $params['content']
        );

        if ($comment instanceof \core\persistent) {
            $comment = $comment->to_record();
        }

        return [
            'id' => (int) $comment->id,
            'postid' => (int) $comment->postid,
            'userid' => (int) $comment->userid,
            'content' => $comment->content,
            'contentformat' => (int) ($comment->contentformat ?? FORMAT_PLAIN),
            'timecreated' => (int) $comment->timecreated,
        ];
    }

    /**
     * Describes the return value of the function.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'id' => new external_value(PARAM_INT, 'Comment id.'),
            'postid' => new external_value(PARAM_INT, 'Post id.'),
            'userid' => new external_value(PARAM_INT, 'Author user id.'),
            'content' => new external_value(PARAM_RAW, 'Comment content.'),
            'contentformat' => new external_value(PARAM_INT, 'Comment content format.'),
            'timecreated' => new external_value(PARAM_INT, 'Creation time.'),
        ]);
    }
}
