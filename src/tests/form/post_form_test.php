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
 * Tests for the post form.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chuspasocial\form;

use advanced_testcase;

/**
 * Tests for the post form.
 *
 * @covers \local_chuspasocial\form\post_form
 */
final class post_form_test extends advanced_testcase {
    /**
     * Creates a post form with the given custom data.
     *
     * @param array $customdata Custom data of the form.
     * @return \moodleform Instance of the post form.
     */
    private function create_post_form(array $customdata): \moodleform {
        return new post_form('/local/chuspasocial/posts/new.php', $customdata);
    }

    /**
     * Returns the quickform that holds the elements of the given post form.
     *
     * @param \moodleform $form Post form to get the quickform from.
     * @return \MoodleQuickForm Quickform with the elements of the form.
     */
    private function get_post_quickform(\moodleform $form): \MoodleQuickForm {
        $property = new \ReflectionProperty(\moodleform::class, '_form');
        $property->setAccessible(true);

        /** @var \MoodleQuickForm $quickform */
        $quickform = $property->getValue($form);

        return $quickform;
    }

    /**
     * Verifies that validation of an empty content returns an error.
     */
    public function test_validation_content_empty_returns_error(): void {
        $this->resetAfterTest();
        $form = $this->create_post_form(['courseid' => 0]);

        $errors = $form->validation(['content' => ['text' => '']], []);

        $this->assertArrayHasKey('content', $errors);
    }

    /**
     * Verifies that validation of content with only html tags returns an error.
     */
    public function test_validation_content_html_only_returns_error(): void {
        $this->resetAfterTest();
        $form = $this->create_post_form(['courseid' => 0]);

        $errors = $form->validation(['content' => ['text' => '<p></p>']], []);

        $this->assertArrayHasKey('content', $errors);
    }

    /**
     * Verifies that validation of content with only entities and spaces returns an error.
     */
    public function test_validation_content_only_entities_returns_error(): void {
        $this->resetAfterTest();
        $form = $this->create_post_form(['courseid' => 0]);

        $errors = $form->validation(['content' => ['text' => ' &nbsp; ']], []);

        $this->assertArrayHasKey('content', $errors);
    }

    /**
     * Verifies that validation of real content returns no error.
     */
    public function test_validation_content_real_text_returns_no_error(): void {
        $this->resetAfterTest();
        $form = $this->create_post_form(['courseid' => 0]);

        $errors = $form->validation(['content' => ['text' => 'Hola']], []);

        $this->assertArrayNotHasKey('content', $errors);
    }

    /**
     * Verifies that the hidden courseid field takes the value of the custom data.
     */
    public function test_courseid_hidden_field_takes_customdata_value(): void {
        $this->resetAfterTest();
        $form = $this->create_post_form(['courseid' => 42]);

        $quickform = $this->get_post_quickform($form);
        $courseid = $quickform->getElementValue('courseid');

        $this->assertEquals(42, $courseid);
    }

    /**
     * Verifies that the hidden courseid field defaults to zero.
     */
    public function test_courseid_hidden_field_defaults_to_zero(): void {
        $this->resetAfterTest();
        $form = $this->create_post_form(['can_post_announcement' => true]);

        $quickform = $this->get_post_quickform($form);
        $courseid = $quickform->getElementValue('courseid');

        $this->assertEquals(0, $courseid);
    }

    /**
     * Verifies that the announcement checkbox only exists with permission.
     */
    public function test_announcement_checkbox_only_exists_with_permission(): void {
        $this->resetAfterTest();
        $formallowed = $this->create_post_form(['courseid' => 0, 'can_post_announcement' => true]);
        $quickformallowed = $this->get_post_quickform($formallowed);

        $this->assertTrue($quickformallowed->elementExists('announcement'));

        $formdenied = $this->create_post_form(['courseid' => 0, 'can_post_announcement' => false]);
        $quickformdenied = $this->get_post_quickform($formdenied);

        $this->assertFalse($quickformdenied->elementExists('announcement'));
    }
}
