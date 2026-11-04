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
 * Persistent class for the comments of ChuspaSocial.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chuspasocial\local\persistent;

use core\persistent;
use lang_string;

/**
 * Persistent class for the local_chuspasocial_comment table.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class comment extends persistent {
    /** Table name. */
    const TABLE = 'local_chuspasocial_comment';

    /**
     * Defines the properties of the comment.
     *
     * @return array
     */
    protected static function define_properties(): array {
        return [
            'postid' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'userid' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'content' => [
                'type' => PARAM_RAW,
            ],
            'contentformat' => [
                'type' => PARAM_INT,
                'default' => FORMAT_MOODLE,
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

    /**
     * Validates that the comment is linked to a post.
     *
     * @param int $value Post ID.
     * @return true|lang_string
     */
    protected function validate_postid($value) {
        if ($value <= 0) {
            return new lang_string('invaliddata', 'error');
        }
        return true;
    }

    /**
     * Validates that the comment belongs to a user.
     *
     * @param int $value User ID.
     * @return true|lang_string
     */
    protected function validate_userid($value) {
        if ($value <= 0) {
            return new lang_string('invaliddata', 'error');
        }
        return true;
    }
}
