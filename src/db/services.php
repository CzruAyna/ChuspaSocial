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
 * External service definitions for ChuspaSocial.
 *
 * @package    local_chuspasocial
 * @copyright  2026 sis-inf
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_chuspasocial_toggle_follow' => [
        'classname' => \local_chuspasocial\external\toggle_follow::class,
        'methodname' => 'execute',
        'description' => 'Toggle follow status for a user or tag.',
        'type' => 'write',
        'ajax' => true,
    ],
    'local_chuspasocial_get_listings' => [
        'classname' => \local_chuspasocial\external\get_listings::class,
        'methodname' => 'execute',
        'description' => 'Get the book listings of my courses or the listings that match a search.',
        'type' => 'read',
        'ajax' => true,
    ],
    'local_chuspasocial_report_item' => [
        'classname' => \local_chuspasocial\external\report_item::class,
        'methodname' => 'execute',
        'description' => 'Report an item for moderation.',
        'type' => 'write',
        'ajax' => true,
    ],
    'local_chuspasocial_toggle_reaction' => [
        'classname' => \local_chuspasocial\external\toggle_reaction::class,
        'methodname' => 'execute',
        'description' => 'Toggle reaction status for a post.',
        'type' => 'write',
        'ajax' => true,
    ],
    'local_chuspasocial_add_comment' => [
        'classname' => \local_chuspasocial\external\add_comment::class,
        'methodname' => 'execute',
        'description' => 'Add a comment to a post.',
        'type' => 'write',
        'ajax' => true,
    ],
    'local_chuspasocial_get_posts' => [
        'classname' => \local_chuspasocial\external\get_posts::class,
        'methodname' => 'execute',
        'description' => 'Return the list of posts for a wall.',
        'type' => 'read',
        'ajax' => true,
    ],
];
