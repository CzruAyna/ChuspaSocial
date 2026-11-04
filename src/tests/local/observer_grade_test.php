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
 * Tests for the published grades observer.
 *
 * @package    local_chuspasocial
 * @copyright  2026 ChuspaSocial contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chuspasocial\local;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/gradelib.php');

use advanced_testcase;

/**
 * Tests for the grade observer.
 *
 * @covers \local_chuspasocial\local\observer::user_graded
 */
final class observer_grade_test extends advanced_testcase {
    /**
     * Verifies that only the student receives a message
     * and no publication is created.
     */
    public function test_observer_redirects_messages_and_creates_no_post(): void {
        global $DB;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_user();

        $this->getDataGenerator()->enrol_user(
            $student->id,
            $course->id,
            'student'
        );

        $itemrecord = $this->getDataGenerator()->create_grade_item([
            'courseid' => $course->id,
        ]);

        $item = \grade_item::fetch(['id' => $itemrecord->id]);

        $sink = $this->redirectMessages();

        $item->update_final_grade($student->id, 80, 'test');

        $messages = $sink->get_messages();
        $sink->close();

        $this->assertCount(1, $messages);
        $this->assertEquals($student->id, $messages[0]->useridto);
        $this->assertEquals(
            0,
            $DB->count_records('local_chuspasocial_post')
        );
    }
}