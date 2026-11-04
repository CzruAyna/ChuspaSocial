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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

/**
 * Service for retrieving posts by tag.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chuspasocial\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Service for retrieving posts filtered by tag.
 */
class tag_service {

    /**
     * Get visible posts associated with a tag, newest first.
     *
     * General posts are visible to all users. Course posts are restricted
     * to courses in which the current user is enrolled.
     *
     * @param string $tag Tag name.
     * @param int $page Page number, starting at 0.
     * @param int $perpage Number of posts per page.
     * @return array List of visible post records, newest first.
     */
    public static function get_feed_by_tag(string $tag, int $page, int $perpage): array {
        global $DB;

        $tag = trim($tag);
        if ($tag === '' || $perpage <= 0 || $page < 0) {
            return [];
        }

        $courseids = array_keys(enrol_get_my_courses('id'));

        $params = [
            'component' => 'local_chuspasocial',
            'itemtype' => 'local_chuspasocial_post',
            'tagname' => $tag,
            'tagrawname' => $tag,
        ];

        // General posts are visible even when the user has no enrolled courses.
        $coursecondition = 'p.courseid = 0';

        if (!empty($courseids)) {
            [$insql, $courseparams] = $DB->get_in_or_equal(
                $courseids,
                SQL_PARAMS_NAMED,
                'courseid'
            );

            $coursecondition .= " OR p.courseid $insql";
            $params = array_merge($params, $courseparams);
        }

        $sql = "SELECT DISTINCT p.*
                  FROM {local_chuspasocial_post} p
                  JOIN {tag_instance} ti
                    ON ti.itemid = p.id
                   AND ti.component = :component
                   AND ti.itemtype = :itemtype
                  JOIN {tag} t
                    ON t.id = ti.tagid
                 WHERE p.hidden = 0
                   AND (t.name = :tagname OR t.rawname = :tagrawname)
                   AND ($coursecondition)
              ORDER BY p.timecreated DESC, p.id DESC";

        return array_values($DB->get_records_sql(
            $sql,
            $params,
            $page * $perpage,
            $perpage
        ));
    }
}
