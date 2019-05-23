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

$string['filtername'] = 'עבודת צוות';
$string['open_filter'] = 'עבודה בצוות';
$string['all_students'] = 'כל התלמידים';
$string['default_name_group'] = 'צוות';
$string['groups'] = 'קבוצות';
$string['students'] = 'תלמידים';
$string['current_users'] = 'משתמשים עכשוויים';
$string['reset'] = 'איפוס';
$string['search'] = 'חיפוש';
$string['available_team'] = 'מנגנון צוותים פעיל / זמין';
$string['choice_by_students'] = 'בחירה על ידי תלמידים';
$string['random_groups'] = 'חלוקה אקראית לצוותים';
$string['how_studens_each_team'] = 'כמה תלמידים בכל צוות';
$string['student_in_each_group'] = 'מספר התלמידים בכל קבוצה';
$string['make_teams'] = 'לסדר צוותים';
$string['error_drag_drop'] = 'שגיאה גרור ושחרר';
$string['you_belong_to_the_team'] = 'אתה שייך לצוות';
$string['students_in_team'] = 'סטודנטים בצוות';
$string['forgroup'] = 'קבוצה: ';
$string['noteams'] = 'לא הוגדר צוותים';
$string['forteam'] = 'צוות: ';
$string['nosharedusers'] = 'לא הוגדרו תלמידים';
$string['teamnumbers'] = 'מספר צוותים מרבי';
$string['teamusernumbers'] = 'מספר חברי צוות';
$string['teamuserenddate'] = 'תאריך וזמן סיום שיבוץ';
$string['header_student_settings'] = 'הגדרות תלמיד';
$string['exceed_teamnumbers_limit'] = 'הגעתה למספר צוותים מרבי';
$string['exceed_student_teams_limit'] = 'לא ניתן להצטרף ליותר מצוות אחד';
$string['exceed_teamusernumbers_limit'] = 'הגעתה למספר משתמשמים מרבי בצוות';
$string['save'] = 'שמירה';
$string['close'] = 'סגור';
$string['month1'] = 'ינואר';
$string['month2'] = 'פברואר';
$string['month3'] = 'מרץ';
$string['month4'] = 'אפריל';
$string['month5'] = 'מאי';
$string['month6'] = 'יוני';
$string['month7'] = 'יולי';
$string['month8'] = 'אוגוסט';
$string['month9'] = 'ספטמבר';
$string['month10'] = 'אוקטובר';
$string['month11'] = 'נובמבר';
$string['month12'] = 'דצמבר';
$string['letsubmitafterteamworkenddate'] = 'המטלה נמצאת במצב: "תלמידים בוחרים צוות", עד התאריך: {$a}.בסיום שלב החלוקה לצוותים, תיפתח המטלה להגשות.';
