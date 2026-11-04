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
 * Wall page of ChuspaSocial.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use local_chuspasocial\external\post_exporter;
use local_chuspasocial\local\persistent\post;
use local_chuspasocial\local\post_service;

require_login();

$context = context_system::instance();
require_capability('local/chuspasocial:view', $context);

$page = optional_param('page', 0, PARAM_INT);
$perpage = 20;

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/chuspasocial/index.php'));
$PAGE->set_title(get_string('pluginname', 'local_chuspasocial'));
$PAGE->set_heading(get_string('pluginname', 'local_chuspasocial'));

// Get the posts of the general feed and turn each one into a plain record.
$records = [];
foreach (post_service::get_general_feed($page, $perpage) as $item) {
    $record = ($item instanceof \core\persistent) ? $item->to_record() : (object) $item;
    if (empty($record->hidden)) {
        $records[] = $record;
    }
}

// Most recent posts first.
usort($records, fn($a, $b) => (int) $b->timecreated <=> (int) $a->timecreated);

// Export each post with the data that the wall template needs.
$posts = [];
foreach ($records as $record) {
    $author = core_user::get_user($record->userid);
    if (!$author) {
        continue;
    }

    if ((int) $record->courseid > 0) {
        $postcontext = context_course::instance((int) $record->courseid);
    } else {
        $postcontext = $context;
    }

    $exporter = new post_exporter($record, ['context' => $postcontext, 'author' => $author]);
    $data = (array) $exporter->export($OUTPUT);

    // The exporter gives the raw content: format it for the template.
    $data['content'] = format_text($data['content'], $data['contentformat'], ['context' => $postcontext]);

    // The post template uses authorname and isannouncement.
    $data['authorname'] = $data['authorfullname'];
    $data['isannouncement'] = ($record->type === post::TYPE_ANNOUNCEMENT);

    $posts[] = $data;
}

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_chuspasocial/wall', ['posts' => $posts]);
echo $OUTPUT->footer();
