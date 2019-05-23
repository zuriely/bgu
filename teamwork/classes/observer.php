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

namespace filter_teamwork;
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/filter/teamwork/locallib.php');
require_once($CFG->dirroot . '/completion/completion_aggregation.php');
require_once($CFG->dirroot . '/completion/criteria/completion_criteria.php');
require_once($CFG->dirroot . '/completion/completion_completion.php');
require_once($CFG->dirroot . '/completion/completion_criteria_completion.php');

class observer {

    /**
     * @param \mod_assign\event\submission_graded $event
     * @return bool
     * @throws \dml_exception
     * @throws \file_exception
     * @throws \file_reference_exception
     * @throws \stored_file_creation_exception
     */
    public static function update_team_members_grades(\mod_assign\event\submission_graded $event): bool {
        global $DB, $CFG;

        // Check if filter on.
        $members = get_mod_events_members($event->contextinstanceid, $event->relateduserid, 'assign');
        if ($members == false) {
            return false;
        }

        if (empty($members)) {
            // Team leader user was not found
            // Problem? grade given to a user which is not leading (submitted) the team.
            return false;
        }

        $cm = $DB->get_record('course_modules', array('id' => $event->contextinstanceid));

        require_once($CFG->dirroot . '/mod/assign/lib.php');

        // Get main user's grade (the team member user that submitted the assignment).
        $mainusergrades = $DB->get_record('assign_grades', array('userid' => $event->relateduserid, 'assignment' => $cm->instance));
        $mainusercomments =
                $DB->get_record('assignfeedback_comments', array('grade' => $mainusergrades->id, 'assignment' => $cm->instance));
        $mainuserfile =
                $DB->get_record('assignfeedback_file', array('grade' => $mainusergrades->id, 'assignment' => $cm->instance));

        // Will be used to handle file operations (reading and duplication).
        $fs = get_file_storage();

        if ($mainuserfile) {
            $mainuserfeedbackfile = $DB->get_record_sql("SELECT * FROM {files} WHERE component = 'assignfeedback_file' " .
                    " AND filearea = 'feedback_files' AND itemid = ? AND contextid = ? " .
                    " AND filename != '.' ", array($mainusergrades->id, $event->contextid));

            // Prepare file record object.
            $fileinfo = array(
                    'component' => 'assignfeedback_file',
                    'filearea' => 'feedback_files',
                    'itemid' => $mainuserfeedbackfile->itemid,
                    'contextid' => $mainuserfeedbackfile->contextid,
                    'filepath' => '/',
                    'filename' => $mainuserfeedbackfile->filename);

            // Get file.
            $mainuserfeedbackfile = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
                    $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
        }

        // Get source user submission from assign_submission.
        $obj = array(
                'userid' => $event->relateduserid,
                'assignment' => $event->get_assign()->get_grade_item()->iteminstance
        );

        $sourceusersubmission = $DB->get_record('assign_submission', $obj);

        // Course_modules_completion.
        $obj = array(
                'coursemoduleid' => $event->contextinstanceid,
                'userid' => $event->relateduserid,
                'completionstate' => 1
        );
        $completion = $DB->get_record('course_modules_completion', $obj);

        // Set main user's grade to all other team members.
        foreach ($members as $member) {
            $memberid = $member->userid;

            $grade = new \stdClass();
            $grade->assignment = $cm->instance;
            $grade->userid = $memberid;
            $grade->timecreated = time();
            $grade->timemodified = $grade->timecreated;
            $grade->grader = $event->userid;
            $grade->grade = $mainusergrades->grade;
            $grade->locked = 0;
            $grade->mailed = 0;

            // Check if team member's grade already exists.
            // if so overwrite instead of adding a new one.
            $checkifgraded = $DB->get_record('assign_grades', array('userid' => $memberid, 'assignment' => $grade->assignment));

            if ($checkifgraded) {
                $grade->id = $checkifgraded->id;
                $result = $DB->update_record('assign_grades', $grade);
            } else {
                $result = $DB->insert_record('assign_grades', $grade);
                $grade->id = $result;
            }

            if ($result) {
                $grade2 = new \stdClass();
                $grade2->userid = $grade->userid;
                $grade2->rawgrade = $grade->grade;
                $grade2->usermodified = $grade->grader;
                $grade2->datesubmitted = null;
                $grade2->dategraded = $grade->timemodified;
                $assign = $DB->get_record('assign', array('id' => $cm->instance));
                $assign->cmidnumber = $cm->id;
                assign_grade_item_update($assign, $grade2);
            }

            // Copy teacher feedback comments given to team leader, to all users in the team.
            $checkifcomments =
                    $DB->get_record('assignfeedback_comments', array('grade' => $grade->id, 'assignment' => $cm->instance));
            if ($checkifcomments) {
                $checkifcomments->commenttext = (isset($mainusercomments->commenttext)) ? $mainusercomments->commenttext : '';
                $checkifcomments->commentformat = FORMAT_HTML;
                $usergradeid = $DB->update_record('assignfeedback_comments', $checkifcomments);
            } else {
                $usercomments = new \stdClass();
                $usercomments->grade = $grade->id;
                $usercomments->assignment = $cm->instance;
                $usercomments->commenttext = (isset($mainusercomments->commenttext)) ? $mainusercomments->commenttext : '';
                $usercomments->commentformat = FORMAT_HTML;
                $usergradeid = $DB->insert_record('assignfeedback_comments', $usercomments);
            }

            // Copy teacher feedback file given to team leader, to all users in the team.
            $checkiffiles = $DB->get_record('assignfeedback_file', array('grade' => $grade->id, 'assignment' => $cm->instance));

            if (!$checkiffiles) {
                $userfiles = new \stdClass();
                $userfiles->grade = $grade->id;
                $userfiles->assignment = $cm->instance;
                $userfiles->numfiles = (isset($mainuserfile->numfiles)) ? $mainuserfile->numfiles : '';
                $usergradeid = $DB->insert_record('assignfeedback_file', $userfiles);

                // Duplicate main user feedback file.
                if ($mainuserfeedbackfile) {
                    // Prepare file record object.
                    $newfileinfo = array(
                            'component' => 'assignfeedback_file',
                            'filearea' => 'feedback_files',
                            'itemid' => $grade->id,
                            'contextid' => $mainuserfeedbackfile->contextid,
                            'filepath' => '/',
                            'filename' => $mainuserfeedbackfile->filename);
                    $newfile = $fs->create_file_from_storedfile($newfileinfo, $mainuserfeedbackfile);
                }
            }

            $checkifsubmitted = $DB->get_record('assign_submission', array('assignment' => $cm->instance, 'userid' => $memberid));

            // Update status assign user.
            if ($checkifsubmitted) {
                $checkifsubmitted->status = $sourceusersubmission->status;
                $checkifsubmitted->timemodified = $sourceusersubmission->timemodified;
                $DB->update_record('assign_submission', $checkifsubmitted);
            }

            // Update comments.
            $obj = array(
                    'contextid' => $event->contextid,
                    'component' => 'assignsubmission_comments',
                    'commentarea' => 'submission_comments',
                    'itemid' => $sourceusersubmission->id
            );
            $comments = $DB->get_records('comments', $obj);

            // Check if present comments and delete.
            $obj = array(
                    'contextid' => $event->contextid,
                    'component' => 'assignsubmission_comments',
                    'commentarea' => 'submission_comments',
                    'itemid' => $checkifsubmitted->id
            );
            $row = $DB->get_records('comments', $obj);
            if ($row) {
                $DB->delete_records('comments', $obj);
            }

            // Insert new comments.
            foreach ($comments as $comment) {
                unset($comment->id);
                $comment->itemid = $checkifsubmitted->id;

                $DB->insert_record('comments', $comment);
            }

            // Course_modules_completion add flag.
            if (!empty($completion)) {

                // Check if present course_modules_completion and delete.
                $obj = array(
                        'coursemoduleid' => $event->contextinstanceid,
                        'userid' => $memberid
                );

                $row = $DB->get_records('course_modules_completion', $obj);
                if ($row) {
                    $DB->delete_records('course_modules_completion', $obj);
                }

                // Insert new completion by user.
                unset($completion->id);
                $completion->userid = $memberid;
                $completion->timemodified = time();
                $insertid = $DB->insert_record('course_modules_completion', $completion);
                $data = $DB->get_record('course_modules_completion', array('id' => $insertid));

                // Update cache.
                $completioncache = \cache::make('core', 'completion');
                $course = $DB->get_record('course', array('id' => $cm->course));

                // Update module completion in user's cache.
                if (!($cachedata = $completioncache->get($data->userid . '_' . $cm->course))
                        || $cachedata['cacherev'] != $course->cacherev) {
                    $cachedata = array('cacherev' => $course->cacherev);
                }

                $cachedata[$cm->id] = $data;
                $completioncache->set($data->userid . '_' . $cm->course, $cachedata);

                // Reset modinfo for user (no need to call rebuild_course_cache()).
                get_fast_modinfo($cm->course, 0, true);
            }

        }

        return true;
    }

    /**
     * @param \mod_assign\event\submission_created $event
     * @return bool
     * @throws \dml_exception
     */
    public static function update_team_memebers_submision_status_created(\mod_assign\event\submission_created $event): bool {
        global $DB;

        // Check if filter on.
        $members = get_mod_events_members($event->contextinstanceid, $event->relateduserid, 'assign');
        if ($members == false) {
            return false;
        }

        if (empty($members)) {
            // Team leader user was not found.
            // Problem? grade given to a user which is not leading (submitted) the team.
            return false;
        }

        // Maybe...
        $cm = $DB->get_record('course_modules', array('id' => $event->contextinstanceid));
        $rowexample = $DB->get_record('assign_submission', array('assignment' => $cm->instance, 'userid' => $event->relateduserid));

        foreach ($members as $member) {
            $DB->set_field('assign_submission', 'status', 'submitted',
                    array('userid' => $member->userid, 'assignment' => $cm->instance));
        }

        return true;
    }

    public static function update_team_memebers_submision_status_updated(\mod_assign\event\submission_updated $event): bool {
        global $DB;

        // Check if filter on.
        $members = get_mod_events_members($event->contextinstanceid, $event->relateduserid, 'assign');
        if ($members == false) {
            return false;
        }

        if (empty($members)) {
            // Team leader user was not found.
            // Problem? grade given to a user which is not leading (submitted) the team.
            return false;
        }

        // Maybe...
        $cm = $DB->get_record('course_modules', array('id' => $event->contextinstanceid));
        $rowexample = $DB->get_record('assign_submission', array('assignment' => $cm->instance, 'userid' => $event->relateduserid));

        foreach ($members as $member) {
            $DB->set_field('assign_submission', 'status', 'submitted',
                    array('userid' => $member->userid, 'assignment' => $cm->instance));
        }

        return true;
    }

    /**
     * @param \core\event\assessable_uploaded $event
     * @return bool
     * @throws \dml_exception
     */
    public static function update_team_memebers_submitted_files_uploaded(\core\event\assessable_uploaded $event): bool {
        global $CFG, $DB;

        // Check if filter on.
        $members = get_mod_events_members($event->contextinstanceid, $event->userid, 'assign');

        if ($members == false) {
            return false;
        }

        if (empty($members)) {
            // Team leader user was not found.
            // Problem? grade given to a user which is not leading (submitted) the team.
            return false;
        }

        // SG - get Assign object for this cm.
        require_once($CFG->dirroot . '/mod/assign/locallib.php');
        list ($course, $cm) = get_course_and_cm_from_cmid($event->contextinstanceid, 'assign');
        $context = \context_module::instance($cm->id);
        $assign = new \assign($context, $cm, $course);

        // Get team leader's submission.
        $tlsubm = $assign->get_user_submission($event->userid, false);

        $fs = get_file_storage();

        foreach ($members as $i => $member) {

            // Get or create new submissions for all team members.
            $msubm = $assign->get_user_submission($member->userid, false);
            if (!$msubm) {
                $msubm = $assign->get_user_submission($member->userid, true);
            }

            foreach ($event->other['pathnamehashes'] as $i => $file) {
                $filedata = $DB->get_record('files', array('pathnamehash' => $file));
                $filedata->itemid = $msubm->id;
                $filedata->userid = $member->userid;

                // Copy submitted files to all team members.
                $fs->create_file_from_storedfile($filedata, $filedata->id);
            }

            // Copy the assignsubmission_file record to all team members.
            $filesubmission = $DB->get_record('assignsubmission_file', array('submission' => $msubm->id));
            if (!$filesubmission) {
                $filesubmission = new \stdClass();
                $filesubmission->submission = $msubm->id;
                $filesubmission->assignment = $assign->get_instance()->id;
                $filesubmission->numfiles = 1;

                $DB->insert_record('assignsubmission_file', $filesubmission);
            }
        }

        return true;
    }
}