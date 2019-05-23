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

/**
 * Glossary linking filter class.
 *
 * NOTE: multilang glossary entries are not compatible with this filter.
 */
class filter_teamwork extends moodle_text_filter {

    public function filter($text, array $options = array()) {
        global $CFG, $PAGE, $OUTPUT, $DB;
        $pagesworking = array('mod-assign-view');
        if (!in_array($PAGE->pagetype, $pagesworking)) {
            return $text;
        }

        $courseid = $this->context->get_course_context()->instanceid;
        $activityid = $this->context->instanceid;

        // Check if groups submittions is enabled.
        $assign = $DB->get_record('assign', array('id' => $PAGE->cm->instance));

        if (empty($assign) || $assign->teamsubmission == 1) {
            return $text;
        }
        //jacobz 15/04/2019 other filters
	$pattern= '/(class="multilang"|class="text_to_html")/';

        // Search filter placeholder
        
	//jacobz 15/04/2019 match filters
        preg_match_all($pattern, $text, $matches);
        if (!isset($CFG->teamwork_counter)) {

		if (!empty($matches) && count($matches[0]) == 0){                
		    $CFG->teamwork_counter = 1;
		}
        }

        $moduletype = get_module_name($activityid);

        // Default value of select.
        $groups = view_groups_select($courseid);

        if (!empty($groups) && isset($groups[0])) {
            $selectgroupid = array($groups[0]->id);
        } else {
            $selectgroupid = array(0);
        }
        $jsonselectgroupid = json_encode($selectgroupid);
//jacobz 15/04/2019 find only description one
        if ($CFG->teamwork_counter == 1) {
            $isateacher = if_user_teacher_on_course($courseid);

            $teamwork = $DB->get_record('teamwork', array('moduleid' => $activityid, 'type' => get_module_name($activityid)));
	
            if ($isateacher || (if_user_student_on_course($courseid) && if_access_to_student($activityid))) {
//echo 'jacobz2';
                $text .= html_writer::tag('button', get_string('open_filter', 'filter_teamwork'),
                        array('id' => 'open_filter', 'class' => 'btn-primary'));
                if (!$isateacher && $teamwork->teamuserallowenddate) {
                    $text .= '<style>.singlebutton{display:none;}</style>';
                }

                if ($teamwork->teamuserallowenddate) {
                    $text .= html_writer::tag('div',
                            get_string('letsubmitafterteamworkenddate', 'filter_teamwork', userdate($teamwork->teamuserenddate)),
                            ['class' => 'teawmworkenddatemessage']);
                }

                if (!empty($groups) && if_teamwork_enable($activityid)) {
                    $data = array();
                    foreach ($groups as $group) {
                        $tmp['teacherinfo_title'] = get_string('forgroup', 'filter_teamwork') . $group->name;
                        $tmp['teamsharedusers'] = get_cards($activityid, $moduletype, $courseid, $group->id);
                        $data[] = $tmp;
                    }
                    $text .= $OUTPUT->render_from_template('filter_teamwork/teamwork-info', array('data' => $data));
                }
            }
            // Get information for student.
            if (if_user_student_on_course($courseid) && if_teamwork_enable($activityid)) {
                $datastudent = return_data_for_student_to_html($activityid, $moduletype, $courseid, $selectgroupid);
                $text .= $OUTPUT->render_from_template('filter_teamwork/student-info', array('studentCard' => $datastudent));
            }

        }
//jacobz 15/04/2019
        if ($CFG->teamwork_counter == 1) {
        $text .= html_writer::tag('script', '', array('src' => $CFG->wwwroot . '/filter/teamwork/javascript/dragula.js'));

        $PAGE->requires->js_call_amd('filter_teamwork/init', 'init',
                array($courseid, $activityid, $moduletype, $jsonselectgroupid));
}
        return $text;
    }
}
