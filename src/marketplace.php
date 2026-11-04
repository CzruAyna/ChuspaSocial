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
 * Browse the book marketplace by enrolled courses or by title and author.
 *
 * @package    local_chuspasocial
 * @copyright  2026 SIS-INF
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use core\output\notification;
use local_chuspasocial\local\listing_service;
use local_chuspasocial\local\persistent\listing;

require_login();

$context = context_system::instance();
require_capability('local/chuspasocial:viewmarketplace', $context);

$tab = optional_param('tab', 'courses', PARAM_ALPHA);
if (!in_array($tab, ['courses', 'search'], true)) {
    $tab = 'courses';
}
$search = trim(optional_param('search', '', PARAM_TEXT));
$page = max(0, optional_param('page', 0, PARAM_INT));
$perpage = 20;
$urlparams = ['tab' => $tab];
if ($tab === 'search' && $search !== '') {
    $urlparams['search'] = $search;
}
if ($page) {
    $urlparams['page'] = $page;
}
$pageurl = new moodle_url('/local/chuspasocial/marketplace.php', $urlparams);
$title = get_string('marketplace', 'local_chuspasocial');

$PAGE->set_context($context);
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($title);
$PAGE->set_heading(get_string('pluginname', 'local_chuspasocial'));
$PAGE->navbar->add($title, new moodle_url('/local/chuspasocial/marketplace.php'));

if (!get_config('local_chuspasocial', 'enablemarketplace')) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading($title);
    echo $OUTPUT->notification(get_string('marketplacedisabled', 'local_chuspasocial'), notification::NOTIFY_INFO);
    echo $OUTPUT->footer();
    return;
}

// Keep both tabs on the same service used by the existing get_listings API.
$fetchlistings = static function (int $requestedpage) use ($tab, $search, $perpage): array {
    if ($tab === 'courses') {
        return listing_service::get_for_my_courses($requestedpage, $perpage);
    }
    return $search === '' ? [] : listing_service::search($search, $requestedpage, $perpage);
};

$coursesbylisting = [];
try {
    $records = $fetchlistings($page);
    $hasnext = count($records) === $perpage && !empty($fetchlistings($page + 1));
    if ($records) {
        // Only display course names from the current user's enrolments.
        $mycourses = enrol_get_my_courses(['id', 'fullname']);
        $listingids = array_map(static fn($record) => (int) $record->id, $records);
        $links = $DB->get_records_list('local_chuspasocial_lstcourse', 'listingid', $listingids);
        foreach ($links as $link) {
            if (isset($mycourses[$link->courseid])) {
                $coursesbylisting[$link->listingid][] = ['name' => $mycourses[$link->courseid]->fullname];
            }
        }
    }
} catch (dml_exception $exception) {
    // Do not expose SQL, connection details or database contents to the visitor.
    throw new moodle_exception('error', 'error');
}

echo $OUTPUT->header();
echo $OUTPUT->heading($title);

if (has_capability('local/chuspasocial:createlisting', $context)) {
    echo html_writer::div($OUTPUT->single_button(
        new moodle_url('/local/chuspasocial/listing_edit.php'),
        get_string('publishbook', 'local_chuspasocial'),
        'get'
    ), 'mb-3');
}

$tabs = [
    new tabobject(
        'courses',
        new moodle_url('/local/chuspasocial/marketplace.php', ['tab' => 'courses']),
        get_string('mycourses')
    ),
    new tabobject(
        'search',
        new moodle_url('/local/chuspasocial/marketplace.php', ['tab' => 'search']),
        get_string('search')
    ),
];
echo $OUTPUT->tabtree($tabs, $tab);

if ($tab === 'search') {
    echo html_writer::start_tag('form', [
        'method' => 'get',
        'action' => (new moodle_url('/local/chuspasocial/marketplace.php'))->out(false),
        'class' => 'mb-3',
        'role' => 'search',
    ]);
    echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'tab', 'value' => 'search']);
    echo html_writer::tag('label', get_string('searchbooks', 'local_chuspasocial'), ['for' => 'marketplace-search']);
    echo html_writer::start_div('input-group');
    echo html_writer::empty_tag('input', ['type' => 'search', 'id' => 'marketplace-search', 'name' => 'search',
        'value' => $search, 'class' => 'form-control']);
    echo html_writer::start_div('input-group-append');
    echo html_writer::tag('button', get_string('search'), ['type' => 'submit', 'class' => 'btn btn-secondary']);
    echo html_writer::end_div();
    echo html_writer::end_div();
    echo html_writer::end_tag('form');
}

if (!$records) {
    $message = $tab === 'search' && $search === '' ? 'searchbooks' : 'nobooks';
    echo html_writer::div(get_string($message, 'local_chuspasocial'), 'alert alert-info', ['data-region' => 'marketplace-empty']);
} else {
    echo html_writer::start_div('row', ['data-region' => 'marketplace-listings']);
    foreach ($records as $record) {
        $data = [
            'id' => (int) $record->id,
            'title' => $record->title,
            'author' => $record->author,
            'price' => listing_service::format_price((float) $record->price),
            'available' => $record->status === listing::STATUS_AVAILABLE,
            'courses' => $coursesbylisting[$record->id] ?? [],
            'photourl' => '',
        ];
        echo html_writer::div($OUTPUT->render_from_template('local_chuspasocial/listing', $data), 'col-12 col-md-6 col-xl-4');
    }
    echo html_writer::end_div();
}

if ($page || $hasnext) {
    echo html_writer::start_tag('nav', ['class' => 'd-flex justify-content-between mt-3', 'aria-label' => get_string('page')]);
    if ($page) {
        $previousurl = new moodle_url($pageurl, ['page' => $page - 1]);
        echo html_writer::link($previousurl, get_string('previous'), ['class' => 'btn btn-secondary']);
    }
    if ($hasnext) {
        $nexturl = new moodle_url($pageurl, ['page' => $page + 1]);
        echo html_writer::link($nexturl, get_string('next'), ['class' => 'btn btn-secondary ml-auto']);
    }
    echo html_writer::end_tag('nav');
}

echo $OUTPUT->footer();
