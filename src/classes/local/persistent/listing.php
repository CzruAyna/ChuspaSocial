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

namespace local_chuspasocial\local\persistent;

use core\persistent;

/**
 * Persistent class for marketplace book listings.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class listing extends persistent {
    /** Database table. */
    const TABLE = 'local_chuspasocial_listing';

    /** Listing status: available. */
    const STATUS_AVAILABLE = 'available';

    /** Listing status: sold. */
    const STATUS_SOLD = 'sold';

    /**
     * Define the properties of this persistent.
     *
     * @return array
     */
    protected static function define_properties(): array {
        return [
            'userid' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'title' => [
                'type' => PARAM_TEXT,
            ],
            'author' => [
                'type' => PARAM_TEXT,
            ],
            'description' => [
                'type' => PARAM_RAW,
                'default' => null,
                'null' => NULL_ALLOWED,
            ],
            'descriptionformat' => [
                'type' => PARAM_INT,
                'default' => FORMAT_HTML,
            ],
            'price' => [
                'type' => PARAM_FLOAT,
                'default' => 0,
            ],
            'externalcontact' => [
                'type' => PARAM_TEXT,
                'default' => null,
                'null' => NULL_ALLOWED,
            ],
            'status' => [
                'type' => PARAM_ALPHA,
                'default' => self::STATUS_AVAILABLE,
                'choices' => [
                    self::STATUS_AVAILABLE,
                    self::STATUS_SOLD,
                ],
            ],
        ];
    }
}
