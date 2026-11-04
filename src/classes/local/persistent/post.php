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
 * Persistent class for the posts of ChuspaSocial.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chuspasocial\local\persistent;

use core\persistent;

/**
 * Persistent class for the local_chuspasocial_post table.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class post extends persistent {
    /** Table name. */
    const TABLE = 'local_chuspasocial_post';

    /** Post type: regular post. */
    const TYPE_POST = 'post';

    /** Post type: announcement. */
    const TYPE_ANNOUNCEMENT = 'announcement';

    /** Post type: activity. */
    const TYPE_ACTIVITY = 'activity';

    /**
     * Defines the properties of the post.
     *
     * @return array
     */
    protected static function define_properties(): array {
        return [
            'userid' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'courseid' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'type' => [
                'type' => PARAM_ALPHA,
                'choices' => [self::TYPE_POST, self::TYPE_ANNOUNCEMENT, self::TYPE_ACTIVITY],
                'default' => self::TYPE_POST,
            ],
            'content' => [
                'type' => PARAM_RAW,
            ],
            'contentformat' => [
                'type' => PARAM_INT,
                'default' => FORMAT_MOODLE,
            ],
            'hidden' => [
                'type' => PARAM_BOOL,
                'default' => false,
            ],
            'timecreated' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'timemodified' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
        ];
    }
}
