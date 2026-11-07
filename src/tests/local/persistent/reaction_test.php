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
 * Unit tests for the reaction persistent class.
 *
 * @package    local_chuspasocial
 * @category   test
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chuspasocial\local\persistent;

/**
 * Unit tests for the reaction persistent class.
 *
 * @package    local_chuspasocial
 * @category   test
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_chuspasocial\local\persistent\reaction
 */
final class reaction_test extends \advanced_testcase {
    /**
     * A reaction with valid data is saved in the database.
     */
    public function test_create_valid_reaction(): void {
        global $DB;

        $this->resetAfterTest();

        $reaction = new reaction(0, (object) ['postid' => 1, 'userid' => 2]);
        $reaction->create();

        $this->assertGreaterThan(0, $reaction->get('id'));

        $record = $DB->get_record(reaction::TABLE, ['id' => $reaction->get('id')], '*', MUST_EXIST);
        $this->assertEquals(1, $record->postid);
        $this->assertEquals(2, $record->userid);
    }

    /**
     * The reaction type defaults to like.
     */
    public function test_type_defaults_to_like(): void {
        $this->resetAfterTest();

        $reaction = new reaction();

        $this->assertSame(reaction::TYPE_LIKE, $reaction->get('type'));
    }

    /**
     * A type outside the allowed choices is rejected.
     */
    public function test_invalid_type_is_rejected(): void {
        $this->resetAfterTest();

        $reaction = new reaction(0, (object) ['postid' => 1, 'userid' => 2, 'type' => 'dislike']);

        $this->assertFalse($reaction->is_valid());
        $this->assertArrayHasKey('type', $reaction->get_errors());

        $this->expectException(\core\invalid_persistent_exception::class);
        $reaction->create();
    }
}
