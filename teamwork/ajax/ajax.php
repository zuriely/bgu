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


require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/filter/teamwork/classes/classAjax.php');

if (!defined('AJAX_SCRIPT')) {
    define('AJAX_SCRIPT', true);
}

require_login();

$context = context_system::instance();
$PAGE->set_context($context);

require_sesskey();

$ajax = new classAjax();
echo $ajax->run();


