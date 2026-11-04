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
 * Tests for the post_exporter external function.
 *
 * @package    local_chuspasocial
 * @copyright  2026 ChuspaSocial contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chuspasocial\external;

use advanced_testcase;

/**
 * Tests for the post_exporter external function.
 *
 * @package    local_chuspasocial
 * @category   test
 * @covers     \local_chuspasocial\external\post_exporter
 * @copyright  2026 ChuspaSocial contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class post_exporter_test extends advanced_testcase {
    /**
     * Creates the data of a post to be exported.
     *
     * @param int $userid Author of the post.
     * @param string $content Content of the post.
     * @param int $timecreated Creation time of the post.
     * @return \stdClass Data of the post.
     */
    private function get_post_data(int $userid, string $content, int $timecreated = 0): \stdClass {
        $data = new \stdClass();
        $data->id = 1;
        $data->userid = $userid;
        $data->content = $content;
        $data->contentformat = FORMAT_HTML;
        $data->timecreated = $timecreated;

        return $data;
    }

    /**
     * Creates an exporter of a post with the given related data.
     *
     * @param \stdClass $data Data of the post.
     * @param \stdClass $author Author of the post.
     * @param int $reactions Number of reactions of the post.
     * @param bool $hasreacted Whether the current user has reacted to the post.
     * @return post_exporter Exporter of the post.
     */
    private function create_post_exporter(
        \stdClass $data,
        \stdClass $author,
        int $reactions = 0,
        bool $hasreacted = false
    ): post_exporter {
        return new post_exporter($data, [
            'context' => \context_system::instance(),
            'author' => $author,
            'reactions' => $reactions,
            'hasreacted' => $hasreacted,
        ]);
    }

    /**
     * Exports a post using the renderer of the current page.
     *
     * @param post_exporter $exporter Exporter to run.
     * @return \stdClass Data exported of the post.
     */
    private function export_post(post_exporter $exporter): \stdClass {
        global $PAGE;

        $renderer = $PAGE->get_renderer('core');

        return $exporter->export($renderer);
    }

    /**
     * The read properties of the exporter expose the fields of the post.
     */
    public function test_define_properties_exposes_post_fields(): void {
        $properties = post_exporter::read_properties_definition();

        foreach (['id', 'userid', 'content', 'contentformat', 'timecreated'] as $property) {
            $this->assertArrayHasKey($property, $properties);
        }
    }

    /**
     * The author of a post can edit and delete it.
     */
    public function test_author_canedit_and_candelete_are_true(): void {
        $author = $this->getDataGenerator()->create_user();
        $this->setUser($author);

        $data = $this->get_post_data((int) $author->id, 'Post del autor');
        $exported = $this->export_post($this->create_post_exporter($data, $author));

        $this->assertTrue($exported->canedit);
        $this->assertTrue($exported->candelete);
    }

    /**
     * Another user without the moderation capability cannot edit or delete the post.
     */
    public function test_other_user_without_moderation_cannot_edit_or_delete(): void {
        $author = $this->getDataGenerator()->create_user();
        $other = $this->getDataGenerator()->create_user();
        $this->setUser($other);

        $data = $this->get_post_data((int) $author->id, 'Post ajeno');
        $exported = $this->export_post($this->create_post_exporter($data, $author));

        $this->assertFalse($exported->canedit);
        $this->assertFalse($exported->candelete);
    }

    /**
     * The fullname of the author of the post is exported.
     */
    public function test_authorfullname_matches_author_fullname(): void {
        $author = $this->getDataGenerator()->create_user(['firstname' => 'Juan', 'lastname' => 'Perez']);
        $this->setUser($author);

        $data = $this->get_post_data((int) $author->id, 'Post con autor');
        $exported = $this->export_post($this->create_post_exporter($data, $author));

        $this->assertEquals(fullname($author), $exported->authorfullname);
    }

    /**
     * A post without tags or images exports empty tags and images arrays.
     */
    public function test_post_without_tags_or_images_exports_empty_arrays(): void {
        $author = $this->getDataGenerator()->create_user();
        $this->setUser($author);

        $data = $this->get_post_data((int) $author->id, 'Post sin etiquetas ni imagenes');
        $exported = $this->export_post($this->create_post_exporter($data, $author));

        $this->assertEmpty($exported->tags);
        $this->assertEmpty($exported->images);
    }

    /**
     * The reactions and the hasreacted flag reflect the related values.
     */
    public function test_reactions_and_hasreacted_reflect_related_values(): void {
        $author = $this->getDataGenerator()->create_user();
        $data = $this->get_post_data((int) $author->id, 'Post con reacciones');
        $exporter = $this->create_post_exporter($data, $author, 3, true);

        $exported = $this->export_post($exporter);

        $this->assertEquals(3, $exported->reactions);
        $this->assertTrue($exported->hasreacted);
    }
}
