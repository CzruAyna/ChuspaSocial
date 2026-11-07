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

    /** @var int Default number of listings returned per request. */
    private const DEFAULT_LIMIT = 20;

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
            'perpage' => new external_value(PARAM_INT, 'Listings per page (1 to 50).', VALUE_DEFAULT, self::DEFAULT_LIMIT),
            'categoryid' => new external_value(PARAM_INT, 'Course category id. Zero means no filter.', VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Maximum listings to return (1 to 50).', VALUE_DEFAULT, self::DEFAULT_LIMIT),
            'offset' => new external_value(PARAM_INT, 'Number of matching listings to skip.', VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * Returns the listings of my courses or the listings that match the search.
     *
     * Uses page and perpage when limit and offset have their default values.
     *
     * @param string $search Text to search in the title or author.
     * @param int $page Page number, starting at 0.
     * @param int $perpage Listings per page.
     * @param int $categoryid Course category id. Zero means no filter.
     * @param int $limit Maximum listings to return.
     * @param int $offset Number of matching listings to skip.
     * @return array
     * @throws \invalid_parameter_exception When a filter or pagination parameter is invalid.
     */
    public static function execute(
        string $search = '',
        int $page = 0,
        int $perpage = self::DEFAULT_LIMIT,
        int $categoryid = 0,
        int $limit = self::DEFAULT_LIMIT,
        int $offset = 0
    ): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'search' => $search,
            'page' => $page,
            'perpage' => $perpage,
            'categoryid' => $categoryid,
            'limit' => $limit,
            'offset' => $offset,
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

        if ($params['categoryid'] < 0) {
            throw new invalid_parameter_exception('categoryid must be 0 or greater');
        }
        if ($params['limit'] < 1 || $params['limit'] > self::MAX_PERPAGE) {
            throw new invalid_parameter_exception('limit must be between 1 and ' . self::MAX_PERPAGE);
        }
        if ($params['offset'] < 0) {
            throw new invalid_parameter_exception('offset must be 0 or greater');
        }

        $search = trim($params['search']);
        $uselegacy = $params['limit'] === self::DEFAULT_LIMIT && $params['offset'] === 0;
        if ($uselegacy && $params['page'] > intdiv(PHP_INT_MAX, $params['perpage'])) {
            throw new invalid_parameter_exception('page is too large for the requested perpage');
        }

        if ($uselegacy && $params['categoryid'] === 0) {
            $records = $search === ''
                ? listing_service::get_for_my_courses($params['page'], $params['perpage'])
                : listing_service::search($search, $params['page'], $params['perpage']);
        } else {
            $records = self::get_filtered_records(
                $search,
                $params['categoryid'],
                $uselegacy ? $params['perpage'] : $params['limit'],
                $uselegacy ? $params['page'] * $params['perpage'] : $params['offset']
            );
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
                'descriptionformat' => (int) $descriptionformat,
                'price' => (float) $record->price,
                'externalcontact' => $record->externalcontact ?? '',
                'status' => $record->status,
                'timecreated' => (int) $record->timecreated,
            ];
        }

        return ['listings' => $listings];
    }

    /**
     * Filters listings before applying the requested offset and limit.
     *
     * @param string $search Text to search in the title or author.
     * @param int $categoryid Course category id. Zero means no filter.
     * @param int $limit Maximum listings to return.
     * @param int $offset Number of matching listings to skip.
     * @return array Listing records sorted from newest to oldest.
     */
    private static function get_filtered_records(string $search, int $categoryid, int $limit, int $offset): array {
        global $DB;

        $conditions = [];
        $params = [];
        $courseconditions = ['lc.listingid = l.id'];
        if ($search === '') {
            $courseids = array_keys(enrol_get_my_courses('id'));
            if (!$courseids) {
                return [];
            }
            [$insql, $params] = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED, 'course');
            $courseconditions[] = "lc.courseid $insql";
            $conditions[] = 'l.status = :status';
            $params['status'] = 'available';
        } else {
            $titlelike = $DB->sql_like('l.title', ':title', false);
            $authorlike = $DB->sql_like('l.author', ':author', false);
            $pattern = '%' . $DB->sql_like_escape($search) . '%';
            $conditions[] = "($titlelike OR $authorlike)";
            $params = ['title' => $pattern, 'author' => $pattern];
        }

        if ($categoryid > 0) {
            $courseconditions[] = 'c.category = :categoryid';
            $params['categoryid'] = $categoryid;
        }
        if ($search === '' || $categoryid > 0) {
            $join = $categoryid > 0 ? 'JOIN {course} c ON c.id = lc.courseid' : '';
            $coursewhere = implode(' AND ', $courseconditions);
            $conditions[] = "EXISTS (
                SELECT 1
                  FROM {local_chuspasocial_lstcourse} lc
                  $join
                 WHERE $coursewhere
            )";
        }

        $where = implode(' AND ', $conditions);
        $sql = "SELECT l.*
                  FROM {local_chuspasocial_listing} l
                 WHERE $where
              ORDER BY l.timecreated DESC, l.id DESC";

        return array_values($DB->get_records_sql($sql, $params, $offset, $limit));
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
