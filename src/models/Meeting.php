<?php
// Meeting model logic
class Meeting
{
    public static function createMeeting($type = 'instant')
    {
        if (strtolower($type) === 'instant') {
            // add a 
        } else {

        }
    }
    public static function readMeetingData($meeting_id, $get) {}
    public static function updateMeeting($meeting_id, $data) {}
    public static function endMeeting($meeting_id) {}
    public static function deleteMeeting($meeting_id) {}
    public static function banUserFromMeeting($id) {}
}
