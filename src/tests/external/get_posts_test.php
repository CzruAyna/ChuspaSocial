<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Tests for the get_posts external function.
 *
 * @package    local_chuspasocial
 * @copyright  2026 ChuspaSocial contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chuspasocial\external;

defined('MOODLE_INTERNAL') || die();

use advanced_testcase;
use core_external\external_api;
use local_chuspasocial\local\persistent\post;

/**
 * Tests for the get_posts external function.
 *
 * @covers \local_chuspasocial\external\get_posts
 */
final class get_posts_test extends advanced_testcase {
    /**
     * Inserts a post in the general wall.
     *
     * @param int $userid Author of the post.
     * @param string $content Content of the post.
     * @param int $timecreated Creation time of the post.
     * @param int $hidden 1 to create the post hidden.
     * @return int Id of the new post.
     */
    private function create_general_post(int $userid, string $content, int $timecreated, int $hidden = 0): int {
        global $DB;

        return $DB->insert_record('local_chuspasocial_post', (object) [
            'userid' => $userid,
            'courseid' => 0,
            'type' => post::TYPE_POST,
            'content' => $content,
            'contentformat' => FORMAT_HTML,
            'hidden' => $hidden,
            'timecreated' => $timecreated,
            'timemodified' => $timecreated,
        ]);
    }

    /**
     * Verifies that a valid call returns the visible posts, newest first,
     * and leaves out the hidden ones.
     */
    public function test_get_posts_returns_visible_posts(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $now = time();
        $this->create_general_post((int) $user->id, 'Post viejo', $now - 100);
        $this->create_general_post((int) $user->id, 'Post nuevo', $now);
        $this->create_general_post((int) $user->id, 'Post oculto', $now - 50, 1);

        $result = get_posts::execute(0);

        $contents = array_column($result['posts'], 'content');
        $this->assertCount(2, $contents);
        $this->assertStringContainsString('Post nuevo', $contents[0]);
        $this->assertStringContainsString('Post viejo', $contents[1]);
    }

    /**
     * Verifies that a user without the view capability cannot read the posts.
     */
    public function test_get_posts_without_permission_throws_exception(): void {
        global $DB;

        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $userroleid = (int) $DB->get_field('role', 'id', ['shortname' => 'user'], MUST_EXIST);
        assign_capability(
            'local/chuspasocial:view',
            CAP_PROHIBIT,
            $userroleid,
            \context_system::instance()->id,
            true
        );
        $this->setUser($user);

        $this->expectException(\required_capability_exception::class);
        get_posts::execute(0);
    }

    /**
     * Verifies that the response matches the structure declared in execute_returns().
     */
    public function test_get_posts_response_matches_returns_structure(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $this->create_general_post((int) $user->id, 'Post de prueba', time());

        $result = get_posts::execute(0);

        // Throws an invalid_response_exception if the response does not match the declared structure.
        $cleaned = external_api::clean_returnvalue(get_posts::execute_returns(), $result);

        $this->assertArrayHasKey('posts', $cleaned);
        $this->assertCount(1, $cleaned['posts']);
    }
}
