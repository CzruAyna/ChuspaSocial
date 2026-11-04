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
use context_course;
use core_external\external_api;

/**
 * Tests for the add_comment external function.
 *
 * @package    local_chuspasocial
 * @category   test
 * @covers     \local_chuspasocial\external\add_comment
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class add_comment_test extends advanced_testcase {

    /**
     * Creates a post directly in the database.
     *
     * @param int $userid Author user id.
     * @param int $courseid Course id.
     * @return int Post id.
     */
    private function create_post(int $userid, int $courseid): int {
        global $DB;

        return $DB->insert_record('local_chuspasocial_post', (object) [
            'userid' => $userid,
            'courseid' => $courseid,
            'type' => 'post',
            'content' => 'Post de prueba',
            'contentformat' => FORMAT_PLAIN,
            'hidden' => 0,
            'timecreated' => time(),
            'timemodified' => time(),
        ]);
    }

    /**
     * A valid external call creates and returns the comment.
     */
    public function test_execute_valid_call(): void {
        global $DB;

        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $author = $generator->create_user();
        $commenter = $generator->create_user();

        $generator->enrol_user($author->id, $course->id, 'student');
        $generator->enrol_user($commenter->id, $course->id, 'student');

        $postid = $this->create_post($author->id, $course->id);
        $this->setUser($commenter);

        $result = add_comment::execute($postid, 'Buen aporte');

        $this->assertSame($postid, $result['postid']);
        $this->assertSame((int) $commenter->id, $result['userid']);
        $this->assertSame('Buen aporte', $result['content']);
        $this->assertTrue($DB->record_exists('local_chuspasocial_comment', [
            'id' => $result['id'],
            'postid' => $postid,
            'userid' => $commenter->id,
        ]));
    }

    /**
     * A user without the comment capability cannot add a comment.
     */
    public function test_execute_without_permission(): void {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $author = $generator->create_user();
        $commenter = $generator->create_user();

        $generator->enrol_user($author->id, $course->id, 'student');
        $generator->enrol_user($commenter->id, $course->id, 'student');

        $context = context_course::instance($course->id);
        $roles = get_archetype_roles('student');
        $studentrole = reset($roles);
        role_change_permission(
            $studentrole->id,
            $context,
            'local/chuspasocial:comment',
            CAP_PROHIBIT
        );

        $postid = $this->create_post($author->id, $course->id);
        $this->setUser($commenter);

        $this->expectException(\required_capability_exception::class);
        add_comment::execute($postid, 'Comentario bloqueado');
    }

    /**
     * The returned data matches the declared external structure.
     */
    public function test_execute_returnvalue_is_clean(): void {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();

        $generator->enrol_user($user->id, $course->id, 'student');

        $postid = $this->create_post($user->id, $course->id);
        $this->setUser($user);

        $result = add_comment::execute($postid, 'Comentario valido');
        $clean = external_api::clean_returnvalue(add_comment::execute_returns(), $result);

        $this->assertSame($result, $clean);
    }
}
