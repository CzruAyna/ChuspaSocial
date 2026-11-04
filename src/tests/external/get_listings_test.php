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

defined('MOODLE_INTERNAL') || die();

use advanced_testcase;
use context_system;
use core_external\external_api;
use local_chuspasocial\local\persistent\listing;

/**
 * Tests for the get_listings external function.
 *
 * @package    local_chuspasocial
 * @category   test
 * @covers     \local_chuspasocial\external\get_listings
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class get_listings_test extends advanced_testcase {

    /**
     * Creates a listing directly with the persistent class.
     *
     * @param int $userid Seller user id.
     * @param string $title Book title.
     * @param string $author Book author.
     * @return listing The created listing.
     */
    private function create_listing(int $userid, string $title, string $author): listing {
        $listing = new listing();
        $listing->set('userid', $userid);
        $listing->set('title', $title);
        $listing->set('author', $author);
        $listing->create();

        return $listing;
    }

    /**
     * A valid external call returns the listings that match the search.
     */
    public function test_execute_valid_call(): void {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $seller = $generator->create_user();
        $viewer = $generator->create_user();

        $calculus = $this->create_listing($seller->id, 'Calculus', 'Stewart');
        $this->create_listing($seller->id, 'Physics', 'Halliday');

        $this->setUser($viewer);

        $result = get_listings::execute('calc');

        $this->assertCount(1, $result['listings']);
        $this->assertSame((int) $calculus->get('id'), $result['listings'][0]['id']);
        $this->assertSame('Calculus', $result['listings'][0]['title']);
        $this->assertSame('Stewart', $result['listings'][0]['author']);
        $this->assertSame((int) $seller->id, $result['listings'][0]['userid']);
    }

    /**
     * A user without the view capability cannot get the listings.
     */
    public function test_execute_without_permission(): void {
        global $DB;

        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $seller = $generator->create_user();
        $viewer = $generator->create_user();

        $this->create_listing($seller->id, 'Calculus', 'Stewart');

        $userroleid = $DB->get_field('role', 'id', ['shortname' => 'user'], MUST_EXIST);
        role_change_permission(
            $userroleid,
            context_system::instance(),
            'local/chuspasocial:view',
            CAP_PROHIBIT
        );

        $this->setUser($viewer);

        $this->expectException(\required_capability_exception::class);
        get_listings::execute('calc');
    }

    /**
     * The returned data matches the declared external structure.
     */
    public function test_execute_returnvalue_is_clean(): void {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $seller = $generator->create_user();
        $viewer = $generator->create_user();

        $this->create_listing($seller->id, 'Calculus', 'Stewart');

        $this->setUser($viewer);

        $result = get_listings::execute('calc');
        $clean = external_api::clean_returnvalue(get_listings::execute_returns(), $result);

        $this->assertNotEmpty($result['listings']);
        $this->assertSame($result, $clean);
    }
}