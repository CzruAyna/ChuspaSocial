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

namespace local_chuspasocial\external;

use context_course;
use context_system;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use local_chuspasocial\local\post_service;

/**
 * External function that returns one page of the general wall or of a course wall.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_posts extends external_api {
    /**
     * Describes the parameters of execute().
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course id, 0 for the general wall', VALUE_DEFAULT, 0),
            'page' => new external_value(PARAM_INT, 'Page number, starting at 0', VALUE_DEFAULT, 0),
            'perpage' => new external_value(PARAM_INT, 'Posts per page', VALUE_DEFAULT, 20),
        ]);
    }

    /**
     * Returns one page of posts.
     *
     * @param int $courseid Course id, 0 for the general wall.
     * @param int $page Page number, starting at 0.
     * @param int $perpage Posts per page.
     * @return array
     */
    public static function execute(int $courseid = 0, int $page = 0, int $perpage = 20): array {
        global $DB, $PAGE, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'page' => $page,
            'perpage' => $perpage,
        ]);

        if ($params['page'] < 0 || $params['perpage'] < 1 || $params['perpage'] > 50) {
            throw new \invalid_parameter_exception('Invalid page or perpage value');
        }

        $context = $params['courseid'] > 0
            ? context_course::instance($params['courseid'])
            : context_system::instance();
        self::validate_context($context);
        require_capability('local/chuspasocial:view', $context);

        $items = $params['courseid'] > 0
            ? post_service::get_course_feed($params['courseid'], $params['page'], $params['perpage'])
            : post_service::get_general_feed($params['page'], $params['perpage']);

        // The general feed does not filter hidden posts, so they are removed here.
        $records = array_values(array_filter($items, fn($record) => empty($record->hidden)));
        if (empty($records)) {
            return ['posts' => []];
        }

        // Load authors, reaction counts and the reactions of the current user in batch.
        $userids = array_unique(array_map(fn($record) => (int) $record->userid, $records));
        $authors = $DB->get_records_list(
            'user',
            'id',
            $userids,
            '',
            implode(',', \core_user\fields::get_picture_fields())
        );

        $postids = array_map(fn($record) => (int) $record->id, $records);
        [$insql, $inparams] = $DB->get_in_or_equal($postids, SQL_PARAMS_NAMED, 'pid');

        $counts = $DB->get_records_sql_menu(
            "SELECT postid, COUNT(1)
               FROM {local_chuspasocial_reaction}
              WHERE postid $insql
           GROUP BY postid",
            $inparams
        );
        $mine = array_flip($DB->get_fieldset_select(
            'local_chuspasocial_reaction',
            'postid',
            "userid = :userid AND postid $insql",
            $inparams + ['userid' => (int) $USER->id]
        ));

        $output = $PAGE->get_renderer('core');
        $posts = [];
        foreach ($records as $record) {
            if (empty($authors[$record->userid])) {
                continue;
            }

            $postcontext = ((int) $record->courseid > 0)
                ? context_course::instance((int) $record->courseid)
                : context_system::instance();

            $exporter = new post_exporter($record, [
                'context' => $postcontext,
                'author' => $authors[$record->userid],
                'reactions' => (int) ($counts[$record->id] ?? 0),
                'hasreacted' => isset($mine[$record->id]),
            ]);
            $data = (array) $exporter->export($output);

            [$data['content'], $data['contentformat']] = \core_external\util::format_text(
                $data['content'],
                $data['contentformat'],
                $postcontext,
                'local_chuspasocial',
                'post',
                $record->id
            );

            $posts[] = $data;
        }

        return ['posts' => $posts];
    }

    /**
     * Describes the return value of execute().
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'posts' => new external_multiple_structure(post_exporter::get_read_structure()),
        ]);
    }
}