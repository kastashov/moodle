<?php

/**
 * This file allows you to download a list of students from Participants list
 */

require_once("../config.php");
require_once($CFG->dirroot .'/notes/lib.php');

$id    = required_param('id', PARAM_INT);              // course id
// $ssort = optional_param('ssort', 'lastaccess', PARAM_ALPHA);          // sort by column

$PAGE->set_url('/user/exportcsv.php', array('id'=>$id));

if (! $course = $DB->get_record('course', array('id'=>$id))) {
    print_error('invalidcourseid');
}

$context = get_context_instance(CONTEXT_COURSE, $id);
require_login($course->id);

$users = Array();

// list hack
foreach ($_POST as $k => $v) {
    if (preg_match('/^user(\d+)$/',$k,$m)) {
        $users[] = intval($m[1]);
    }
}
$usersStr = implode(',',$users);

$DB->execute('CREATE TEMP sequence temp_seq'); // pgsql trick for getting unique row numbers in result to please Moodle

$sql = "
SELECT nextval('temp_seq') as row_number, resrows.* FROM (
    SELECT u.id as id, u.username as username, u.firstname as firstname, u.lastname as lastname, u.email as email, g.name as groupname
    FROM {user_enrolments} uer, {enrol} er, {user} u
        LEFT OUTER JOIN {groups_members} gm ON u.id=gm.userid 
        LEFT OUTER JOIN {groups} g ON ( gm.groupid=g.id AND g.courseid = ? )
    WHERE u.id IN( $usersStr ) AND u.id=uer.userid AND uer.enrolid=er.id AND er.courseid = ?
    ORDER BY lastname ASC, groupname ASC
) as resrows
";
/*
$sql = "
SELECT nextval('temp_seq') as row_number, resrows.* FROM (
    SELECT u.id as id, u.username as username, u.firstname as firstname, u.lastname as lastname, u.email as email, g.name as groupname
    FROM {user} u
        LEFT OUTER JOIN {groups_members} gm ON u.id=gm.userid 
        LEFT OUTER JOIN {groups} g ON ( gm.groupid=g.id AND g.courseid = ? )
    WHERE u.id IN( $usersStr )
    ORDER BY lastname ASC, groupname ASC
) as resrows
";
*/
$rows = $DB->get_records_sql($sql, Array($id, $id));

$csvArr = Array(); //key is user id
$csvArr[0] = Array(get_string('idnumber'), get_string('firstname'), get_string('lastname'), get_string('email'), get_string('group').'1');
$maxcolumns = 0;

//pushing data into array
foreach ($rows as $row) {
        if (!array_key_exists($row->id, $csvArr)) {
            $csvArr[$row->id] = Array($row->username, $row->firstname, $row->lastname, $row->email);
        }
        if (trim($row->groupname)) {
            $csvArr[$row->id][] = $row->groupname;
        }
        if (sizeof($csvArr[$row->id]) > $maxcolumns) {
            $maxcolumns = sizeof($csvArr[$row->id]);
        }
}

// adding necessary 'Group2', 'Group3' etc to header
$counter = 2;
for ($i = sizeof($csvArr[0]); $i < $maxcolumns; $i++) {
    $csvArr[0][$i] = get_string('group').$counter;
    $counter++;
}

//sending the file
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename=userlist.csv');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: no-cache');

foreach ($csvArr as &$row) {
    foreach ($row as &$cell) {
        str_replace('"','""',$cell);
        $cell = '"'.$cell.'"';
    }
    // adding empty cells
    if (sizeof($row) < $maxcolumns) {
        for ($i=sizeof($row); $i < $maxcolumns; $i++ ) {
            $row[$i] = '""';
        }
    }
    echo implode(',', $row) . "\r\n";
}
exit;



