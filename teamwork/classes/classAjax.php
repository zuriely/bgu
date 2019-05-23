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

global $CFG;
require_once($CFG->dirroot . '/filter/teamwork/locallib.php');

class classAjax {

    private $method;

    public function __construct() {
        $this->method = required_param('method', PARAM_TEXT);
    }

    public function run() {
        // Call ajax metod.
        if (method_exists($this, $this->method)) {
            $method = $this->method;
            return $this->$method();
        } else {
            return 'Wrong method';
        }
    }

    // Main popup.
    private function render_teamwork_html() {
        global $CFG, $USER, $PAGE, $OUTPUT, $DB;

        $courseid = optional_param('courseid', '', PARAM_INT);
        $activityid = optional_param('activityid', '', PARAM_INT);
        $moduletype = optional_param('moduletype', '', PARAM_TEXT);
        $selectgroupid = optional_param('selectgroupid', '', PARAM_TEXT);
        $arrgroupid = json_decode($selectgroupid);

        $data = array();
        $data['courseid'] = $courseid;
        $data['activityid'] = $activityid;
        $data['moduletype'] = $moduletype;

        $data['teamwork_enable'] = if_teamwork_enable($activityid);
        $data['students_button_status'] = students_button_status($activityid);
        $data['allow_add_teams'] = allow_add_teams($courseid, $activityid, $arrgroupid[0]);
        $data['groups'] = view_groups_select($courseid);

        // Set default groups.
        foreach ($data['groups'] as $group) {
            if ($group->id == $arrgroupid[0]) {
                $group->firstelement = true;
                $data['group_name_select'] = $group->name;
            }
        }

        $data['list_students'] = get_students_by_select($selectgroupid, $courseid, $activityid, $moduletype);
        $data['count_all_students'] = count(get_students_course($courseid));
        $data['cards'] = get_cards($activityid, $moduletype, $courseid, $arrgroupid[0]);
        $data['if_user_teacher'] = if_user_teacher_on_course($courseid);
        $data['if_user_student'] = if_user_student_on_course($courseid);

        $html = $OUTPUT->render_from_template('filter_teamwork/main', $data);

        $arrcontent = array(
                'shadow' => (if_teamwork_enable($activityid)) ? 'skin_hide' : 'skin_show',
                'content' => $html
        );
        return json_encode($arrcontent);
    }

    // Set teamwork enable/disable.
    public function set_teamwork_enable() {
        global $USER, $DB, $OUTPUT;

        $activityid = optional_param('activityid', '', PARAM_INT);
        $moduletype = optional_param('moduletype', '', PARAM_TEXT);

        $teamwork = $DB->get_record('teamwork', array('moduleid' => $activityid, 'type' => $moduletype));
        if (!empty($teamwork)) {
            switch ($teamwork->active) {
                case 0:
                    $teamwork->active = 1;
                    break;
                case 1:
                    $teamwork->active = 0;
                    break;
                default:
                    $teamwork->active = 1;
            }
            $DB->update_record('teamwork', $teamwork, $bulk = false);
        } else {
            $dataobject = new stdClass();
            $dataobject->creatorid = $USER->id;
            $dataobject->moduleid = $activityid;
            $dataobject->type = $moduletype;
            $dataobject->studentediting = 1;
            $dataobject->active = 1;
            $dataobject->timecreated = time();
            $dataobject->timemodified = time();
            $DB->insert_record('teamwork', $dataobject);
        }

        return json_encode(array());
    }

    // Set access to student.
    public function set_access_to_student() {
        global $USER, $DB, $OUTPUT;

        $accesstosdudent = optional_param('access', '', PARAM_INT);
        $activityid = optional_param('activityid', '', PARAM_INT);
        $moduletype = optional_param('moduletype', '', PARAM_TEXT);

        $teamwork = $DB->get_record('teamwork', array('moduleid' => $activityid, 'type' => $moduletype));
        if (!empty($teamwork)) {
            switch ($teamwork->studentediting) {
                case 0:
                    $teamwork->studentediting = 1;
                    break;
                case 1:
                    $teamwork->studentediting = 0;
                    break;
                default:
                    $teamwork->studentediting = 0;
            }
            $DB->update_record('teamwork', $teamwork, $bulk = false);
        }

        return json_encode(array());
    }

    // Add new card.
    public function add_new_card() {
        global $USER, $DB, $OUTPUT;

        $courseid = optional_param('courseid', '', PARAM_INT);
        $activityid = optional_param('activityid', '', PARAM_INT);
        $moduletype = optional_param('moduletype', '', PARAM_TEXT);
        $selectgroupid = optional_param('selectgroupid', '', PARAM_TEXT);
        $arrgroupid = json_decode($selectgroupid);
        foreach ($arrgroupid as $id) {
            $result = add_new_card($activityid, $moduletype, $id, array(), $courseid);
        }

        return json_encode($result);
    }

    // Delete card.
    public function delete_card() {
        global $USER, $DB, $OUTPUT;

        $teamid = optional_param('teamid', '', PARAM_TEXT);

        $DB->delete_records('teamwork_groups', array('id' => $teamid));
        $DB->delete_records('teamwork_members', array('teamworkgroupid' => $teamid));

        return json_encode(array());
    }

    // Show random popup.
    public function show_random_popup() {
        global $USER, $DB, $OUTPUT;

        $data = array('num_students' => 10);
        $html = $OUTPUT->render_from_template('filter_teamwork/popup-team-selection', $data);

        $arrcontent = array(
                'content' => $html,
                'header' => get_string('random_groups', 'filter_teamwork'),
        );

        return json_encode($arrcontent);
    }

    // Set random team.
    public function set_random_team() {
        global $USER, $DB, $OUTPUT;

        $numberofstudent = optional_param('numberOfStudent', '', PARAM_INT);
        $courseid = optional_param('courseid', '', PARAM_INT);
        $activityid = optional_param('activityid', '', PARAM_INT);
        $moduletype = optional_param('moduletype', '', PARAM_TEXT);
        $selectgroupid = optional_param('selectgroupid', '', PARAM_TEXT);
        $arrselectid = json_decode($selectgroupid);

        $teamwork = $DB->get_record('teamwork', array('moduleid' => $activityid, 'type' => $moduletype));
        if (!empty($teamwork)) {

            // Delete all cards from tables.
            $teams = $DB->get_records('teamwork_groups', array('teamworkid' => $teamwork->id, 'groupid' => $arrselectid[0]));
            foreach ($teams as $team) {
                $DB->delete_records('teamwork_members', array('teamworkgroupid' => $team->id));
                $DB->delete_records('teamwork_groups', array('id' => $team->id));
            }

            // Insert new teams.
            $students = get_students_by_select($selectgroupid, $courseid, $activityid, $moduletype);
            shuffle($students);
            $chunk = array_chunk($students, $numberofstudent);

            foreach ($chunk as $item) {
                add_new_card($activityid, $moduletype, $arrselectid[0], $item, $courseid);
            }
        }

        return json_encode(array());
    }

    // Set name card.
    public function set_new_team_name() {
        global $USER, $DB, $OUTPUT;

        $cardid = optional_param('cardid', '', PARAM_INT);
        $cardname = optional_param('cardname', '', PARAM_TEXT);

        $team = $DB->get_record('teamwork_groups', array('id' => $cardid));
        if (!empty($team)) {
            $team->name = $cardname;
            $DB->update_record('teamwork_groups', $team, $bulk = false);
        }

        return json_encode(array());
    }

    // Drag student to/from card.
    public function drag_student_card() {
        global $USER, $DB, $OUTPUT;

        $courseid = optional_param('courseid', '', PARAM_INT);
        $activityid = optional_param('activityid', '', PARAM_INT);
        $moduletype = optional_param('moduletype', '', PARAM_TEXT);
        $newteamspost = optional_param('newTeams', '', PARAM_TEXT);
        $draguserid = optional_param('draguserid', '', PARAM_INT);
        $selectgroupid = optional_param('selectgroupid', '', PARAM_TEXT);

        $newteams = json_decode($newteamspost);

        $teamwork = $DB->get_record('teamwork', array('moduleid' => $activityid, 'type' => $moduletype));

        // Validate drag and drop.
        if (if_user_student_on_course($courseid) && $draguserid != $USER->id) {
            $students = get_students_by_select($selectgroupid, $courseid, $activityid, $moduletype);

            $flag = 0;
            foreach ($students as $student) {
                if ($student->userid == $draguserid) {
                    $flag = 1;
                }
            }

            if (!$flag) {
                return json_encode(array('error' => 1, 'errormsg' => get_string('error_drag_drop', 'filter_teamwork')));
            }
        }

        foreach ($newteams as $team) {
            if (!empty($team->teamid)) {

                // If action is done by student - apply some filters, limits or additional actions.
                if (if_user_student_on_course($courseid)) {

                    // SF - #753 - Validate number of the team users. Do not add new team member if limit is exceeded.
                    if (count($team->studentid) > $teamwork->teamusernumbers && !empty($teamwork->teamusernumbers)) {
                        continue;
                    }

                    // SG - #754 - don't let user drag another, if he/she doesn/t belong to this team.
                    if (!in_array($USER->id, $team->studentid) && $draguserid != $USER->id) {
                        continue;
                    }

                    // SG - #855 - remove card, if empty team after dragging self out of team.
                    if (empty($team->studentid) && $draguserid == $USER->id) {
                        $DB->delete_records('teamwork_groups', array('id' => $team->teamid));
                        $DB->delete_records('teamwork_members', array('teamworkgroupid' => $team->teamid));
                    }
                }

                // Step 1.
                $arrstudentsrequest = array();
                foreach ($team->studentid as $studentid) {
                    if (!empty($studentid)) {
                        $arrstudentsrequest[] = $studentid;
                        $obj = $DB->get_record('teamwork_members',
                                array('teamworkgroupid' => $team->teamid, 'userid' => $studentid));
                        if (empty($obj)) {
                            $dataobject = new stdClass();
                            $dataobject->teamworkgroupid = $team->teamid;
                            $dataobject->userid = $studentid;
                            $dataobject->timecreated = time();
                            $dataobject->timemodified = time();
                            $DB->insert_record('teamwork_members', $dataobject);
                        }
                    }
                }

                // Step 2.
                $obj = $DB->get_records('teamwork_members', array('teamworkgroupid' => $team->teamid));
                foreach ($obj as $item) {
                    if (!in_array($item->userid, $arrstudentsrequest)) {
                        $DB->delete_records('teamwork_members', array('id' => $item->id));
                    }
                }

            }
        }

        return json_encode(array());
    }

    // Render ajax.

    public function render_teams_card() {
        global $USER, $DB, $OUTPUT;

        $activityid = optional_param('activityid', '', PARAM_INT);
        $courseid = optional_param('courseid', '', PARAM_INT);
        $moduletype = optional_param('moduletype', '', PARAM_TEXT);
        $selectgroupid = optional_param('selectgroupid', '', PARAM_TEXT);
        $arrgroupid = json_decode($selectgroupid);

        $data = array();
        $data['cards'] = get_cards($activityid, $moduletype, $courseid, $arrgroupid[0]);
        $data['if_user_teacher'] = if_user_teacher_on_course($courseid);
        $data['allow_add_teams'] = allow_add_teams($courseid, $activityid, $arrgroupid[0]);

        $html = $OUTPUT->render_from_template('filter_teamwork/teams-card', $data);

        $arrcontent = array(
                'content' => $html,
                'header' => ''
        );
        return json_encode($arrcontent);
    }

    public function render_student_list() {
        global $USER, $DB, $OUTPUT;

        $selectgroupid = optional_param('selectgroupid', '', PARAM_TEXT);
        $activityid = optional_param('activityid', '', PARAM_INT);
        $courseid = optional_param('courseid', '', PARAM_INT);
        $moduletype = optional_param('moduletype', '', PARAM_TEXT);

        $data = array();
        $data['list_students'] = get_students_by_select($selectgroupid, $courseid, $activityid, $moduletype);

        $html = $OUTPUT->render_from_template('filter_teamwork/students', $data);

        $arrcontent = array(
                'content' => $html,
                'header' => ''
        );

        return json_encode($arrcontent);
    }

    public function render_student_settings_popup() {
        global $USER, $DB, $OUTPUT;
        $activityid = optional_param('activityid', '', PARAM_INT);
        $moduletype = optional_param('moduletype', '', PARAM_TEXT);

        // Gether saved data for popup.
        $teamworkdata = new stdClass();

        // Get data from DB.
        $teamworkdata = $DB->get_record('teamwork', array('moduleid' => $activityid, 'type' => $moduletype));
        if ($teamworkdata) {
            // Decodede and parse unixdate for separate values.
            if (!empty($teamworkdata->teamuserenddate)) {
                $teamuserenddate = new DateTime("now", core_date::get_server_timezone_object());
                $teamuserenddate->setTimestamp($teamworkdata->teamuserenddate);
            } else {
                $teamuserenddate = new DateTime("7 days",
                        core_date::get_server_timezone_object());
            }
            $teamworkdata->endday = $teamuserenddate->format('d');
            $teamworkdata->endmonth = $teamuserenddate->format('m');
            $teamworkdata->endyear = $teamuserenddate->format('Y');
            $teamworkdata->endhour = $teamuserenddate->format('H');
            $teamworkdata->endmin = $teamuserenddate->format('i');

            // Create months array for select tag.
            $monthselect = array();
            for ($i = 1; $i <= 12; $i++) {
                $monthselect[$i - 1]['mnum'] = $i;
                $monthselect[$i - 1]['mname'] = get_string('month' . $i, 'filter_teamwork');
                if ($monthselect[$i - 1]['mnum'] == $teamworkdata->endmonth) {
                    $monthselect[$i - 1]['selected'] = 'selected';
                }
            }
            $teamworkdata->monthselect = $monthselect;

            if ($teamworkdata->teamuserallowenddate == 1) {
                $teamworkdata->userenddateallowchecked = "checked";
                $teamworkdata->userenddateallowvalue = "1";
            } else {
                $teamworkdata->userenddateallowvalue = "0";
                $teamworkdata->userenddatedisabled = 'disabled="disabled"';
            }

            // Render the popup.
            $html = $OUTPUT->render_from_template('filter_teamwork/student_settings', $teamworkdata);
        }

        $arrcontent = array(
                'content' => $html,
                'header' => get_string('header_student_settings', 'filter_teamwork')
        );

        return json_encode($arrcontent);
    }

    public function student_settings_popup_data() {
        global $USER, $DB;

        $courseid = optional_param('courseid', '', PARAM_INT);
        $activityid = optional_param('activityid', '', PARAM_INT);
        $moduletype = optional_param('moduletype', '', PARAM_TEXT);

        $teamnumbers = optional_param('teamNumbers', 10, PARAM_INT);
        $teamnumbers = (empty($teamnumbers)) ? 10 : $teamnumbers;
        $teamusernumbers = optional_param('teamUserNumbers', 3, PARAM_INT);
        $teamusernumbers = (empty($teamusernumbers)) ? 3 : $teamusernumbers;

        $teamuserallowenddate = optional_param('teamuserallowenddate', '', PARAM_INT);
        $teamuserenddate = optional_param('teamUserendDate', '', PARAM_INT);
        $teamuserendmonth = optional_param('teamUserendMonth', '', PARAM_INT);
        $teamuserendyear = optional_param('teamUserendYear', '', PARAM_INT);
        $teamuserendhour = optional_param('teamUserendHour', '', PARAM_INT);
        $teamuserendminute = optional_param('teamUserenMinute', '', PARAM_INT);
        $teamuserenddatestring =
                $teamuserendyear . '-' . $teamuserendmonth . '-' . $teamuserenddate . 'T' . $teamuserendhour . ':' .
                $teamuserendminute . ':00';
        $teamuserenddate = new DateTime($teamuserenddatestring, core_date::get_server_timezone_object());

        if ($teamuserenddate) {
            $teamuserenddateunix = $teamuserenddate->getTimestamp();
        } else {
            $teamuserenddateunix = null;
        }

        // Update students limits in DB.
        $teamworkdata = $DB->get_record('teamwork', array('moduleid' => $activityid, 'type' => $moduletype));
        if (!empty($teamworkdata)) {
            $teamworkdata->teamnumbers = $teamnumbers;
            $teamworkdata->teamusernumbers = $teamusernumbers;
            $teamworkdata->teamuserallowenddate = $teamuserallowenddate;
            if ($teamuserallowenddate == "0") {
                $teamworkdata->teamuserenddate = null;
            } else if ($teamuserallowenddate == "1") {
                $teamworkdata->teamuserenddate = $teamuserenddateunix;
            }
            $result = $DB->update_record('teamwork', $teamworkdata);
        } else {
            return json_encode(array('error' => 2,
                    'errormsg' => get_string('error_no_db_entry', 'filter_teamwork')));
        }

        return 'OK';
    }
}
