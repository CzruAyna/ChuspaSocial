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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

/**
 * Post form for ChuspaSocial.
 *
 * @package    local_chuspasocial
 * @copyright  2026 sis-inf
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_chuspasocial\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form for creating and editing posts.
 *
 * Custom data:
 * - courseid: id of the course of the post, 0 for the general wall.
 * - can_post_announcement: whether the user can mark the post as an announcement.
 */
class post_form extends \moodleform {
    /**
     * Defines the form fields.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement(
            'editor',
            'content',
            get_string('postcontent', 'local_chuspasocial'),
            null,
            ['maxfiles' => 0]
        );
        $mform->setType('content', PARAM_RAW);

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        $mform->setDefault('courseid', $this->_customdata['courseid'] ?? 0);

        if (!empty($this->_customdata['can_post_announcement'])) {
            $mform->addElement(
                'advcheckbox',
                'announcement',
                get_string('announcement', 'local_chuspasocial')
            );
            $mform->setType('announcement', PARAM_BOOL);
        }

        // Selector de im├ígenes (filemanager).
        $mform->addElement(
            'filemanager',
            'images',
            get_string('postimages', 'local_chuspasocial'),
            null,
            [
                'accepted_types' => ['jpg', 'png', 'webp'],
                'maxfiles' => (int) get_config('local_chuspasocial', 'maxpostimages') ?: 5,
                'subdirs' => false,
                'return_types' => FILE_INTERNAL | FILE_EXTERNAL,
            ]
        );

        // Campo de tags con autocompletado.
        $mform->addElement(
            'tags',
            'tags',
            get_string('tags', 'local_chuspasocial'),
            ['itemtype' => 'local_chuspasocial_post', 'component' => 'local_chuspasocial']
        );

        $this->add_action_buttons(true, get_string('publish', 'local_chuspasocial'));
    }

    /**
     * Validates that the content of the post is not empty.
     *
     * @param array $data Submitted data.
     * @param array $files Submitted files.
     * @return array Errors, indexed by the name of the field.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $text = $data['content']['text'] ?? '';
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8');
        if (trim($text, " \t\n\r\0\x0B\xC2\xA0") === '') {
            $errors['content'] = get_string('errorpostempty', 'local_chuspasocial');
        }

        return $errors;
    }
}
