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
 * Unit tests for tag_service::get_feed_by_tag().
 *
 * @package    local_chuspasocial
 * @category   test
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chuspasocial\local;

use advanced_testcase;

/**
 * Pruebas unitarias para tag_service::get_feed_by_tag().
 *
 * @package    local_chuspasocial
 * @category   test
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_chuspasocial\local\tag_service::get_feed_by_tag
 */
class tag_service_test extends advanced_testcase {
    /**
     * Configuracion previa a cada test.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Create a post record in a course for the given user.
     *
     * @param int $userid Author of the post.
     * @param int $courseid Course where the post is published.
     * @return int Id of the new post.
     */
    private function create_test_post(int $userid, int $courseid): int {
        global $DB;

        return (int) $DB->insert_record('local_chuspasocial_post', (object) [
            'userid' => $userid,
            'courseid' => $courseid,
            'type' => 'post',
            'content' => 'Test post',
            'contentformat' => FORMAT_HTML,
            'hidden' => 0,
            'timecreated' => time(),
            'timemodified' => time(),
        ]);
    }

    /**
     * Assign tags to a post, using the context that matches its course.
     *
     * @param int $postid Post id.
     * @param int $courseid Course id of the post.
     * @param array $tags Tag names to assign.
     */
    private function tag_test_post(int $postid, int $courseid, array $tags): void {
        $context = $courseid > 0
            ? \context_course::instance($courseid)
            : \context_system::instance();

        \core_tag_tag::set_item_tags(
            'local_chuspasocial',
            'local_chuspasocial_post',
            $postid,
            $context,
            $tags
        );
    }

    /**
     * Solo devuelve las publicaciones que tienen el tag buscado.
     *
     * @covers \local_chuspasocial\local\tag_service::get_feed_by_tag
     */
    public function test_get_feed_by_tag_filtra(): void {
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();
        $generator->enrol_user($user->id, $course->id, 'student');

        $matchid = $this->create_test_post($user->id, $course->id);
        $this->tag_test_post($matchid, $course->id, ['matematicas']);

        $otherid = $this->create_test_post($user->id, $course->id);
        $this->tag_test_post($otherid, $course->id, ['fisica']);

        $this->setUser($user);
        $result = tag_service::get_feed_by_tag('matematicas', 0, 10);

        $this->assertCount(1, $result);
        $this->assertEquals($matchid, $result[0]->id);
    }

    /**
     * No devuelve publicaciones de materias en las que el usuario no esta inscrito.
     *
     * @covers \local_chuspasocial\local\tag_service::get_feed_by_tag
     */
    public function test_get_feed_by_tag_materias_ajenas(): void {
        $generator = $this->getDataGenerator();
        $mycourse = $generator->create_course();
        $othercourse = $generator->create_course();
        $user = $generator->create_user();
        $otheruser = $generator->create_user();
        $generator->enrol_user($user->id, $mycourse->id, 'student');
        $generator->enrol_user($otheruser->id, $othercourse->id, 'student');

        $mypostid = $this->create_test_post($user->id, $mycourse->id);
        $this->tag_test_post($mypostid, $mycourse->id, ['redes']);

        $otherpostid = $this->create_test_post($otheruser->id, $othercourse->id);
        $this->tag_test_post($otherpostid, $othercourse->id, ['redes']);

        $this->setUser($user);
        $result = tag_service::get_feed_by_tag('redes', 0, 10);

        $this->assertCount(1, $result);
        $this->assertEquals($mypostid, $result[0]->id);
    }

    /**
     * Un tag que no existe no devuelve publicaciones.
     *
     * @covers \local_chuspasocial\local\tag_service::get_feed_by_tag
     */
    public function test_get_feed_by_tag_tag_inexistente(): void {
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();
        $generator->enrol_user($user->id, $course->id, 'student');

        $postid = $this->create_test_post($user->id, $course->id);
        $this->tag_test_post($postid, $course->id, ['quimica']);

        $this->setUser($user);
        $result = tag_service::get_feed_by_tag('tagquenoexiste', 0, 10);

        $this->assertEmpty($result);
    }
}
