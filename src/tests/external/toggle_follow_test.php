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

/**
 * Tests for the toggle_follow external function.
 *
 * @package    local_chuspasocial
 * @category   test
 * @covers     \local_chuspasocial\external\toggle_follow
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class toggle_follow_test extends advanced_testcase {

    /**
     * A valid external call follows the user and returns the new state.
     */
    public function test_execute_valid_call(): void {
        global $DB;

        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $follower = $generator->create_user();
        $followed = $generator->create_user();

        $this->setUser($follower);

        $result = toggle_follow::execute('user', $followed->id);

        $this->assertTrue($result['status']);
        $this->assertSame('followed', $result['action']);
        $this->assertTrue($DB->record_exists('local_chuspasocial_follow', [
            'userid' => $follower->id,
            'itemtype' => 'user',
            'itemid' => $followed->id,
        ]));
    }

    /**
     * A user without the follow capability cannot follow.
     */
    public function test_execute_without_permission(): void {
        global $DB;

        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $follower = $generator->create_user();
        $followed = $generator->create_user();

        $userroleid = $DB->get_field('role', 'id', ['shortname' => 'user'], MUST_EXIST);
        role_change_permission(
            $userroleid,
            context_system::instance(),
            'local/chuspasocial:follow',
            CAP_PROHIBIT
        );

        $this->setUser($follower);

        $this->expectException(\required_capability_exception::class);
        toggle_follow::execute('user', $followed->id);
    }

    /**
     * The returned data matches the declared external structure.
     */
    public function test_execute_returnvalue_is_clean(): void {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $follower = $generator->create_user();
        $followed = $generator->create_user();

        $this->setUser($follower);

        $result = toggle_follow::execute('user', $followed->id);
        $clean = external_api::clean_returnvalue(toggle_follow::execute_returns(), $result);

        $this->assertSame($result, $clean);
    }
}