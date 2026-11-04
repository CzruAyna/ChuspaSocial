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
 * Tests for the new assignments observer.
 *
 * @package    local_chuspasocial
 * @copyright  2026 ChuspaSocial contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chuspasocial\local;

defined('MOODLE_INTERNAL') || die();

use advanced_testcase;
use moodle_url;

/**
 * Tests for the new assignments observer.
 *
 * @covers \local_chuspasocial\local\observer::assign_created
 */
final class observer_assign_test extends advanced_testcase {
    /**
     * Verifies that creating an assignment publishes one activity post
     * in the course wall, linking to the assignment.
     */
    public function test_assign_created_publishes_activity_post(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $assign = $this->getDataGenerator()->create_module('assign', [
            'course' => $course->id,
            'name' => 'Tarea de prueba',
        ]);

        $posts = $DB->get_records('local_chuspasocial_post', ['courseid' => $course->id]);
        $this->assertCount(1, $posts);

        $post = reset($posts);
        $url = new moodle_url('/mod/assign/view.php', ['id' => $assign->cmid]);
        $this->assertEquals('activity', $post->type);
        $this->assertEquals(get_admin()->id, $post->userid);
        $this->assertEquals(0, $post->hidden);
        $this->assertStringContainsString($url->out(false), $post->content);
        $this->assertStringContainsString('Tarea de prueba', $post->content);
    }
}
