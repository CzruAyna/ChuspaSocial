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
 * Event triggered when a listing is created.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chuspasocial\event;

/**
 * Event triggered when a listing is created.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class listing_created extends \core\event\base {
    /**
     * Initialise the event data.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'local_chuspasocial_listing';
    }

    /**
     * Returns the localised name of the event.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventlistingcreated', 'local_chuspasocial');
    }

    /**
     * Returns a non-localised description of the event.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' created the listing with id '$this->objectid'.";
    }

    /**
     * Returns the URL of the created listing.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/local/chuspasocial/listing.php', [
            'id' => $this->objectid,
        ]);
    }

    /**
     * Validates the event data.
     *
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();

        if (empty($this->data['objectid'])) {
            throw new \coding_exception('The listing ID must be set.');
        }
    }
}
