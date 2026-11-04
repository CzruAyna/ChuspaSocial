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
 * External function local_chuspasocial_get_listings.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chuspasocial\external;

use context_system;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use invalid_parameter_exception;
use local_chuspasocial\local\listing_service;

/**
 * Returns the book listings of the marketplace.
 *
 * Without a search text, it returns the available listings linked to the
 * courses of the current user. With a search text, it returns the listings
 * whose title or author match the text.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_listings extends external_api {
    /** @var int Maximum number of listings per page. */
    const MAX_PERPAGE = 50;

    /**
     * Describes the parameters of the function.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'search' => new external_value(
                PARAM_TEXT,
                'Text to search in the title or author. Empty to get the listings of my courses.',
                VALUE_DEFAULT,
                ''
            ),
            'page' => new external_value(PARAM_INT, 'Page number, starting at 0.', VALUE_DEFAULT, 0),
            'perpage' => new external_value(PARAM_INT, 'Listings per page (1 to 50).', VALUE_DEFAULT, 20),
        ]);
    }

    /**
     * Returns the listings of my courses or the listings that match the search.
     *
     * @param string $search Text to search in the title or author.
     * @param int $page Page number, starting at 0.
     * @param int $perpage Listings per page.
     * @return array
     * @throws \invalid_parameter_exception When the page is negative or the perpage is out of range.
     */
    public static function execute(string $search = '', int $page = 0, int $perpage = 20): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'search' => $search,
            'page' => $page,
            'perpage' => $perpage,
        ]);

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/chuspasocial:view', $context);

        if ($params['page'] < 0) {
            throw new invalid_parameter_exception('page must be 0 or greater');
        }
        if ($params['perpage'] < 1 || $params['perpage'] > self::MAX_PERPAGE) {
            throw new invalid_parameter_exception('perpage must be between 1 and ' . self::MAX_PERPAGE);
        }

        $search = trim($params['search']);
        if ($search === '') {
            $records = listing_service::get_for_my_courses($params['page'], $params['perpage']);
        } else {
            $records = listing_service::search($search, $params['page'], $params['perpage']);
        }

        $listings = [];
        foreach ($records as $record) {
            [$description, $descriptionformat] = \core_external\util::format_text(
                $record->description ?? '',
                $record->descriptionformat ?? FORMAT_HTML,
                $context,
                'local_chuspasocial',
                'listing',
                $record->id
            );
            $listings[] = [
                'id' => (int) $record->id,
                'userid' => (int) $record->userid,
                'title' => $record->title,
                'author' => $record->author,
                'description' => $description,
                'descriptionformat' => $descriptionformat,
                'price' => (float) $record->price,
                'externalcontact' => $record->externalcontact ?? '',
                'status' => $record->status,
                'timecreated' => (int) $record->timecreated,
            ];
        }

        return ['listings' => $listings];
    }

    /**
     * Describes the return value of the function.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'listings' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Listing id.'),
                    'userid' => new external_value(PARAM_INT, 'Id of the seller.'),
                    'title' => new external_value(PARAM_TEXT, 'Book title.'),
                    'author' => new external_value(PARAM_TEXT, 'Book author.'),
                    'description' => new external_value(PARAM_RAW, 'Formatted description.'),
                    'descriptionformat' => new external_value(PARAM_INT, 'Description format.'),
                    'price' => new external_value(PARAM_FLOAT, 'Book price.'),
                    'externalcontact' => new external_value(PARAM_TEXT, 'External contact of the seller.'),
                    'status' => new external_value(PARAM_ALPHA, 'Listing status: available or sold.'),
                    'timecreated' => new external_value(PARAM_INT, 'Creation time.'),
                ])
            ),
        ]);
    }
}
