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
 * Persistent class for the reactions of ChuspaSocial.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chuspasocial\local\persistent;

use core\persistent;

/**
 * Persistent class for the local_chuspasocial_reaction table.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class reaction extends persistent {
    /** Table name. */
    const TABLE = 'local_chuspasocial_reaction';

    /** Reaction type: like. */
    const TYPE_LIKE = 'like';

    /**
     * Defines the properties of the reaction.
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
            'type' => [
                'type' => PARAM_ALPHA,
                'choices' => [self::TYPE_LIKE],
                'default' => self::TYPE_LIKE,
            ],
            'timecreated' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
        ];
    }
}
