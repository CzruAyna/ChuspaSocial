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
 * Page to review the open content reports and hide the reported content.
 *
 * Only users with the local/chuspasocial:moderate capability can access it.
 * The hide action is submitted with a sesskey and handled by report_service.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use core\output\notification;
use local_chuspasocial\local\report_service;

require_login();

$context = context_system::instance();
require_capability('local/chuspasocial:moderate', $context);

$pageurl = new moodle_url('/local/chuspasocial/moderation.php');

$PAGE->set_context($context);
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('moderation', 'local_chuspasocial'));
$PAGE->set_heading(get_string('pluginname', 'local_chuspasocial'));

$reportid = optional_param('hide', 0, PARAM_INT);
if ($reportid) {
    require_sesskey();

    report_service::resolve_and_hide($reportid);

    redirect(
        $pageurl,
        get_string('changessaved'),
        null,
        notification::NOTIFY_SUCCESS
    );
}

$reports = $DB->get_records(
    'local_chuspasocial_report',
    ['status' => 'open'],
    'timecreated ASC'
);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('openreports', 'local_chuspasocial'));

if ($reports) {
    $table = new html_table();
    $table->head = [
        get_string('content'),
        get_string('reportreason', 'local_chuspasocial'),
        get_string('user'),
        get_string('date'),
        get_string('actions'),
    ];

    foreach ($reports as $report) {
        $reporter = core_user::get_user($report->reporterid);
        $reportername = $reporter ? s(fullname($reporter)) : get_string('unknownuser');

        $hidebutton = new single_button(
            new moodle_url($pageurl, ['hide' => $report->id]),
            get_string('hidecontent', 'local_chuspasocial'),
            'post'
        );

        $table->data[] = [
            s($report->itemtype) . ' #' . $report->itemid,
            s($report->reason),
            $reportername,
            userdate($report->timecreated),
            $OUTPUT->render($hidebutton),
        ];
    }

    echo html_writer::table($table);
} else {
    echo $OUTPUT->notification(
        get_string('nothingtodisplay'),
        notification::NOTIFY_INFO
    );
}

echo $OUTPUT->footer();
