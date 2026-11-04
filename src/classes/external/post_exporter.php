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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Exporter for the posts of ChuspaSocial.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chuspasocial\external;

use context_system;
use core\external\exporter;
use core_tag_tag;
use moodle_url;
use renderer_base;
use user_picture;

/**
 * Exports a post with the data that the templates and the web services need.
 *
 * Related data:
 * - context: the context of the post, used to format the content and to find its images.
 * - author: the user record of the author of the post.
 * - reactions: number of reactions of the post.
 * - hasreacted: whether the current user has reacted to the post.
 *
 * The reactions and hasreacted values must be calculated before, in batch
 * for all the posts of the page, so the exporter does not run one query per post.
 *
 * @package    local_chuspasocial
 */
class post_exporter extends exporter {
    /**
     * Properties that come from the post record.
     *
     * @return array
     */
    protected static function define_properties(): array {
        return [
            'id' => [
                'type' => PARAM_INT,
                'description' => 'Post id.',
            ],
            'userid' => [
                'type' => PARAM_INT,
                'description' => 'Id of the author.',
            ],
            'content' => [
                'type' => PARAM_RAW,
                'description' => 'Content of the post, formatted with format_text.',
            ],
            'contentformat' => [
                'type' => PARAM_INT,
                'default' => FORMAT_HTML,
                'description' => 'Format of the content.',
            ],
            'timecreated' => [
                'type' => PARAM_INT,
                'description' => 'Date when the post was created.',
            ],
        ];
    }

    /**
     * Related objects that the exporter needs.
     *
     * @return array
     */
    protected static function define_related(): array {
        return [
            'context' => 'context',
            'author' => '\\stdClass',
            'reactions' => 'int',
            'hasreacted' => 'bool',
        ];
    }

    /**
     * Properties that are calculated by the exporter.
     *
     * @return array
     */
    protected static function define_other_properties(): array {
        return [
            'authorfullname' => [
                'type' => PARAM_TEXT,
                'description' => 'Full name of the author.',
            ],
            'authorpictureurl' => [
                'type' => PARAM_URL,
                'description' => 'URL of the profile picture of the author.',
            ],
            'canedit' => [
                'type' => PARAM_BOOL,
                'description' => 'Whether the current user can edit the post (only the author).',
            ],
            'candelete' => [
                'type' => PARAM_BOOL,
                'description' => 'Whether the current user can delete the post (the author or a moderator).',
            ],
            'images' => [
                'type' => [
                    'url' => [
                        'type' => PARAM_URL,
                        'description' => 'URL of the image in the postimage file area.',
                    ],
                    'filename' => [
                        'type' => PARAM_FILE,
                        'description' => 'File name of the image.',
                    ],
                ],
                'multiple' => true,
                'description' => 'Images of the post.',
            ],
            'tags' => [
                'type' => [
                    'name' => [
                        'type' => PARAM_TEXT,
                        'description' => 'Name of the tag.',
                    ],
                    'url' => [
                        'type' => PARAM_URL,
                        'description' => 'URL of tag.php with the posts of the tag.',
                    ],
                ],
                'multiple' => true,
                'description' => 'Tags of the post.',
            ],
            'reactions' => [
                'type' => PARAM_INT,
                'description' => 'Number of reactions of the post.',
            ],
            'hasreacted' => [
                'type' => PARAM_BOOL,
                'description' => 'Whether the current user has reacted to the post.',
            ],
        ];
    }

    /**
     * Calculate the author data and the permissions of the current user.
     *
     * @param renderer_base $output Renderer used to build the picture URL.
     * @return array
     */
    protected function get_other_values(renderer_base $output): array {
        global $USER;

        $author = $this->related['author'];
        $picture = new user_picture($author);
        $picture->size = 1;

        $isauthor = (int) $this->data->userid === (int) $USER->id;

        $ismoderator = false;
        if (get_capability_info('local/chuspasocial:moderate')) {
            $ismoderator = has_capability('local/chuspasocial:moderate', context_system::instance());
        }

        return [
            'authorfullname' => fullname($author),
            'authorpictureurl' => $picture->get_url($output->get_page())->out(false),
            'canedit' => $isauthor,
            'candelete' => $isauthor || $ismoderator,
            'images' => $this->get_images(),
            'tags' => $this->get_tags(),
            'reactions' => $this->related['reactions'],
            'hasreacted' => $this->related['hasreacted'],
        ];
    }

    /**
     * Get the URLs of the images stored in the postimage file area of the post.
     *
     * @return array List of images, each one with its url and filename.
     */
    protected function get_images(): array {
        $fs = get_file_storage();
        $files = $fs->get_area_files(
            $this->related['context']->id,
            'local_chuspasocial',
            'postimage',
            $this->data->id,
            'filename',
            false
        );

        $images = [];
        foreach ($files as $file) {
            $url = moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename()
            );
            $images[] = [
                'url' => $url->out(false),
                'filename' => $file->get_filename(),
            ];
        }

        return $images;
    }

    /**
     * Get the tags of the post, each one with its name and the URL of tag.php.
     *
     * @return array List of tags.
     */
    protected function get_tags(): array {
        $tags = [];
        $items = core_tag_tag::get_item_tags('local_chuspasocial', 'local_chuspasocial_post', $this->data->id);
        foreach ($items as $tag) {
            $url = new moodle_url('/local/chuspasocial/tag.php', ['tag' => $tag->name]);
            $tags[] = [
                'name' => $tag->get_display_name(false),
                'url' => $url->out(false),
            ];
        }

        return $tags;
    }
}
