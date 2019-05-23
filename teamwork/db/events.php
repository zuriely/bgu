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
 * This filter provides for teacher sharing students in teams and make groups
 * Submissions in each team just in activity assign
 *
 * @package    filter_teamwork
 * @copyright 2019 onwards - Weizmann institute @author Devlion info@devlion.co
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = array(
    array(
        'eventname' => '\mod_assign\event\submission_graded',
        'callback' => '\filter_teamwork\observer::update_team_members_grades',
        'schedule' => 'instant',
    ),
    array(
        'eventname' => '\assignsubmission_onlinetext\event\submission_updated',
        'callback' => '\filter_teamwork\observer::update_team_memebers_submision_status_updated',
        'schedule' => 'instant',
    ),
    array(
        'eventname' => '\assignsubmission_file\event\submission_updated',
        'callback' => '\filter_teamwork\observer::update_team_memebers_submision_status_updated',
        'schedule' => 'instant',
    ),
    array(
        'eventname' => '\mod_assign\event\submission_created',
        'callback' => '\filter_teamwork\observer::update_team_memebers_submision_status_created',
        'schedule' => 'instant',
    ),
    array(
        'eventname' => '\assignsubmission_file\event\assessable_uploaded',
        'callback' => '\filter_teamwork\observer::update_team_memebers_submitted_files_uploaded',
        'schedule' => 'instant',
    ),
);
