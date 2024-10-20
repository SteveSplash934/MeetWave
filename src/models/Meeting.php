<?php

use function PHPSTORM_META\type;

require_once('Database.php');
require_once(__DIR__ . '/../utils/helpers.php');


// Meeting model logic
class Meeting
{
    private $db;

    public function __construct($db)
    {
        // Pass the Database connection to the Meeting model
        $this->db = $db;
    }

    // Create a new meeting
    public function createMeeting($data, $type = 'instant')
    {
        try {
            if (strtolower($type) === 'instant') {
                $data['status'] = 'active'; // Set status to active for instant meetings
            }
            $data['total_users'] = 1;
            // Insert meeting into the meetings table
            $setup_meeting = $this->db->insert('meetings', $data);

            // now setup meeting_participant table too
            $mpt_data = [
                'meeting_id' => $data['meeting_id'],
                'user_id' => json_decode($data['meeting_hosts'], true)[0] # default user
            ];
            $setup_mpt = $this->db->insert('meeting_participants', $mpt_data);

            return ($setup_meeting && $setup_mpt) ? true : false;
        } catch (Exception $e) {
            // Handle exception
            logError($e->getMessage());
            return false;
        }
    }

    // Retrieve meeting data
    public function readMeetingData($meeting_id)
    {
        try {
            $result = $this->db->read('meetings', ['meeting_id' => $meeting_id]);
            return !empty($result) ? $result : null;
        } catch (Exception $e) {
            // Handle exception
            logError($e->getMessage());
            return null;
        }
    }

    // Update meeting data
    public function updateMeeting($meeting_id, $data)
    {
        try {
            $success = $this->db->update('meetings', $data, ['meeting_id' => $meeting_id]);
            return $success;
        } catch (Exception $e) {
            // Handle exception
            logError($e->getMessage());
            return false;
        }
    }

    // End a meeting (set status to 'ended')
    public function endMeeting($meeting_id)
    {
        try {
            $success = $this->db->update('meetings', ['status' => 'ended'], ['meeting_id' => $meeting_id]);
            return $success;
        } catch (Exception $e) {
            // Handle exception
            logError($e->getMessage());
            return false;
        }
    }

    // Delete a meeting
    public function deleteMeeting($meeting_id)
    {
        try {
            $success = $this->db->delete('meetings', ['meeting_id' => $meeting_id]);
            return $success;
        } catch (Exception $e) {
            // Handle exception
            logError($e->getMessage());
            return false;
        }
    }

    // Helper function to check if a user is in a specific meeting
    public function isUserInMeeting($meetingId, $userId)
    {
        try {
            // Prepare the query
            $query = $this->db->prepare("SELECT * FROM meeting_participants WHERE user_id = ? AND meeting_id = ?");

            // Bind parameters (assuming both are strings)
            $query->bind_param("ss", $userId, $meetingId);

            // Execute the query
            $query->execute();

            // Get the result
            $query->store_result(); // Store result for num_rows

            // Check if any rows were returned
            if ($query->num_rows != 0) {
                return true;  // User is in the meeting
            } else {
                return false; // User is not in the meeting
            }
        } catch (Exception $e) {
            logError($e->getMessage());
            return false; // Return false if an error occurs
        }
    }

    // Private method to add a user to the meeting participants table
    private function addUserToMeetingParticipants($meetingId, $userId)
    {
        try {
            // Prepare the insert query
            $query = $this->db->prepare(
                "INSERT INTO meeting_participants (meeting_id, user_id, joined_at) VALUES (?, ?, ?)"
            );

            // Get the current timestamp for the 'joined_at' field
            $joinedAt = date("Y-m-d H:i:s");

            // Bind parameters (assuming both meeting_id and user_id are strings)
            $query->bind_param("sss", $meetingId, $userId, $joinedAt);

            // Execute the query
            if ($query->execute()) {
                return true; // Successfully inserted
            } else {
                return false; // Failed to insert
            }
        } catch (Exception $e) {
            logError($e->getMessage());
            return false;
        }
    }

    // Public function example usage
    public function addUserToMeeting($meetingId, $userId)
    {
        // get the previous "total meeting members number" and "max_users" preferences limit
        $totalUserInMeeting = (int) $this->getMeetingTotalUsers($meetingId);
        $meetingUsersLimit = (int) $this->getMeetingPreferences($meetingId, "max_users");

        // check if limit hasn't been reached
        if ($totalUserInMeeting  ===  $meetingUsersLimit) {
            return "Maximum limit reached for this meeting";
        }

        // check if user already in meeting
        if ($this->isUserInMeeting($meetingId, $userId)) {
            return "User is already in the meeting.";
        } else {
            $data = ['total_users' => ($totalUserInMeeting + 1)];
            if ($this->addUserToMeetingParticipants($meetingId, $userId) && $this->updateMeeting($meetingId, $data)) {
                // update total users value
                // ;
                return true; # "User added to the meeting.";
            } else {
                return false; # "Failed to add user to the meeting.";
            }
        }
    }

    public function getMeetingTotalUsers($meeting_id)
    {
        return (int) $this->readMeetingData($meeting_id)['total_users'];
    }

    public function getMeetingPreferences($meeting_id, $prefType = 'all')
    {
        $meetingPref = json_decode($this->readMeetingData($meeting_id)['preferences'], true);
        if (strtolower($prefType) == "all") {
            return $meetingPref;
        } else {
            return $meetingPref[strtolower($prefType)];
        }
    }
    public function addUserToMeetingHost($meetingId, $hostId)
    {
        // check if user already in metting
        $userInMeeting = ($this->isUserInMeeting($meetingId, $hostId)) ? true : false;
        if (!$userInMeeting) {
            return "This user is not in this meeting";
        }

        // get the max host length, meeting host len
        $maxHostLen = (int) $this->getMeetingPreferences($meetingId, $prefType = 'max_hosts');
        $meetingHost = json_decode($this->readMeetingData($meetingId)['meeting_hosts'], true);
        $meetingHostLen = (int) count($meetingHost);

        // get te
        if ($meetingHostLen === $maxHostLen) {
            return "Maximum host limit reached!";
        } elseif (in_array($hostId, $meetingHost)) {
            return "User already in host!";
        } else {
            $meetingHost[] = $hostId;
            // now update the host on the DB
            $data = ['meeting_hosts' => json_encode($meetingHost)];
            // return $data;
            $this->updateMeeting($meetingId, $data);
            return true;
        }
    }
    public function isHostInMeeting($meetingId, $hostId)
    {
        $meetingHost = json_decode($this->readMeetingData($meetingId)['meeting_hosts'], true);
        return (in_array($hostId, $meetingHost)) ? true : false;
    }
    // Ban a user from the meeting (add user ID to removed_users array)
    public function banUserFromMeeting($meeting_id, $host_id, $user_id)
    {
        try {
            // Check if the host is a participant in the meeting
            $isHostInMeeting = $this->isHostInMeeting($meeting_id, $host_id);

            // Check if the user to be banned is a participant in the meeting
            $isUserInMeeting = $this->isUserInMeeting($meeting_id, $user_id);

            if (!$isHostInMeeting) {
                return 'Host not found in meeting.';
            }
            if (!$isUserInMeeting) {
                return 'User not found in meeting.';
            }
            if (!$isHostInMeeting || !$isUserInMeeting) {
                return 'Both the host and the user must be in the same meeting to ban.';
            }
            // Retrieve the meeting data
            $meeting = $this->readMeetingData($meeting_id);
            if (!$meeting) {
                throw new Exception('Meeting not found.');
            }

            // Decode the removed_users JSON into an array
            $removed_users = @json_decode($meeting['removed_users'], true) ?? [];

            // Add the new user to the list if they are not already banned
            if (in_array($user_id, $removed_users)) {
                return 'User already banned.';
            }

            if (!in_array($user_id, $removed_users)) {
                $removed_users[] = $user_id;
            }

            // Update the removed_users field with the updated list
            $this->db->update('meetings', ['removed_users' => json_encode($removed_users)], ['meeting_id' => $meeting_id]);

            // remove user from host or users
            $userToBan = $this->isUserInMeeting($meeting_id, $user_id);
            if ($userToBan) {
                $this->db->delete("meeting_participants", ['user_id' => $user_id]); # meeting_participants deletion

                # deleting from hosts if user is in host
                $meetingHosts = json_decode($this->readMeetingData($meeting_id)['meeting_hosts'], true);

                if (in_array($user_id, $meetingHosts)) {
                    $this->removeHost($meeting_id, $user_id);
                }
            }

            // update meeting participants count
            $meetingParticipants = (int) $this->countMeetingParticipants($meeting_id);

            // update on the db
            $this->updateMeeting($meeting_id, ['total_users' => $meetingParticipants]);
            return true;
        } catch (Exception $e) {
            // Handle exceptions, e.g., meeting not found
            logError($e->getMessage());
            return false;
        }
    }

    public function countMeetingParticipants($meeting_id)
    {
        try {
            $participant_count = null;

            // SQL query to count the participants of the given meeting
            $query = "SELECT COUNT(*) as participant_count FROM meeting_participants WHERE meeting_id = ?";

            // Prepare the statement
            $stmt = $this->db->prepare($query);

            // Bind the meeting_id to the query
            $stmt->bind_param('s', $meeting_id);

            // Execute the query
            $stmt->execute();

            // Store the result
            $stmt->store_result();

            // Bind the result to a variable
            $stmt->bind_result($participant_count);

            // Fetch the result
            $stmt->fetch();

            // Return the count as an integer
            return (int) $participant_count;
        } catch (Exception $e) {
            logError($e->getMessage());
            return 0; // Return 0 if there's an error
        }
    }


    public function removeHost($meeting_id, $host_id)
    {
        try {
            // Retrieve the current meeting data
            $meeting = $this->readMeetingData($meeting_id);

            if (!$meeting) {
                throw new Exception('Meeting not found.');
            }

            // Decode the 'meeting_hosts' JSON to an array
            $hosts = json_decode($meeting['meeting_hosts'], true) ?? [];

            // Check if the host_id exists in the hosts array
            if (!in_array($host_id, $hosts)) {
                return [
                    'success' => false,
                    'message' => 'Host not found in the meeting.'
                ];
            }

            // Remove the host from the array
            $hosts = array_diff($hosts, [$host_id]);

            // Update the 'meeting_hosts' field in the database
            $updateSuccess = $this->db->update('meetings', ['meeting_hosts' => json_encode(array_values($hosts))], ['meeting_id' => $meeting_id]);

            if ($updateSuccess) {
                return [
                    'success' => true,
                    'message' => 'Host removed successfully.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update the meeting hosts.'
                ];
            }
        } catch (Exception $e) {
            logError($e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while removing the host.'
            ];
        }
    }
}

$meeting = new Meeting($db);


// - deleting user
// - ending meeting