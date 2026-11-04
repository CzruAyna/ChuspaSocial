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
 * Service to query the comments of the ChuspaSocial posts.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chuspasocial\local;

/**
 * Service to query the comments of the ChuspaSocial posts.
 *
 * @package    local_chuspasocial
 */
class comment_service {
    /**
     * Get the comments of a post, oldest first.
     *
     * Returns an empty array when the post does not exist or is hidden.
     *
     * @param int $postid ID of the post to get the comments from.
     * @return array List of comment records, oldest first.
     */
    public static function get_comments(int $postid): array {
        global $DB;

        // The comments are only visible when the post is visible.
        $post = $DB->get_record('local_chuspasocial_post', ['id' => $postid], 'id, hidden');
        if (!$post || !empty($post->hidden)) {
            return [];
        }

        return array_values($DB->get_records(
            'local_chuspasocial_comment',
            ['postid' => $postid],
            'timecreated ASC, id ASC'
        ));
    }
    
    /**
     * Add a comment to a visible post.
     *
     * @param int $postid ID of the post being commented on.
     * @param string $content Text content of the comment.
     * @return \local_chuspasocial\local\persistent\comment The created comment.
     * @throws \moodle_exception If the post is not visible or the user cannot comment.
     */
    public static function add_comment(int $postid, string $content): \local_chuspasocial\local\persistent\comment {
        global $USER;

        // Load the post.
        $post = new \local_chuspasocial\local\persistent\post($postid);

        // Check whether the current user can view the post.
        if (!post_service::can_view($post, (int) $USER->id)) {
            throw new \moodle_exception('nopermissions', 'error', '', 'view this post');
        }

        // Determine the context of the post.
        $courseid = (int) $post->get('courseid');
        if ($courseid > 0) {
            $context = \context_course::instance($courseid);
        } else {
            $context = \context_system::instance();
        }

        // Check whether the current user can comment.
        require_capability('local/chuspasocial:comment', $context);

        // Create and return the comment.
        $comment = new \local_chuspasocial\local\persistent\comment(0, (object) [
            'postid' => $postid,
            'userid' => (int) $USER->id,
            'content' => $content,
            'contentformat' => FORMAT_PLAIN,
            'timecreated' => time(),
            'timemodified' => time(),
        ]);

        $comment->create();

        // Trigger the comment_created event once the comment is saved.
        \local_chuspasocial\event\comment_created::create([
            'objectid' => $comment->get('id'),
            'context' => $context,
            'userid' => (int) $USER->id,
            'other' => ['postid' => $postid],
        ])->trigger();

        return $comment;
    }
}
