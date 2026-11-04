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
 * External function local_chuspasocial_toggle_reaction.
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
use local_chuspasocial\local\reaction_service;

/**
 * Toggles the current user's reaction to a post.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class toggle_reaction extends external_api {

    /**
     * Describes the parameters of the function.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'postid' => new external_value(PARAM_INT, 'Post id.'),
        ]);
    }

    /**
     * Toggles the reaction to a post.
     *
     * @param int $postid Post id.
     * @return array
     */
    public static function execute(int $postid): array {
        global $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'postid' => $postid,
        ]);

        $post = $DB->get_record('local_chuspasocial_post', ['id' => $params['postid']], '*', MUST_EXIST);

        if ((int) $post->courseid > 0) {
            $context = context_course::instance((int) $post->courseid);
        } else {
            $context = context_system::instance();
        }

        self::validate_context($context);
        require_capability('local/chuspasocial:react', $context);

        // Impedir reaccionar a publicaciones ocultas.
        if (!empty($post->hidden)) {
            throw new \invalid_parameter_exception('Cannot react to a hidden post');
        }

        $reacted = reaction_service::toggle((int) $params['postid']);

        return ['reacted' => $reacted];
    }

    /**
     * Describes the return value of the function.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'reacted' => new external_value(PARAM_BOOL, 'Whether the current user reacted to the post.'),
        ]);
    }
}
