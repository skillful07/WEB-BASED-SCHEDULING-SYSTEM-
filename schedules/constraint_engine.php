<?php
/**
 * schedules/constraint_engine.php
 * ---------------------------------------------------------------------
 * THE CONSTRAINT ALGORITHM
 * ---------------------------------------------------------------------
 * This file is not a page — it defines validate_schedule(), a reusable
 * function included by create_schedule.php and update_schedule.php.
 *
 * It enforces the HARD constraints described in the project spec:
 *   1. Room Availability      - resource must exist and be 'available'
 *   2. No Room Clash          - resource cannot be double-booked in the
 *                                same time slot
 *   3. No Lecturer Clash      - the assigned user cannot already be
 *                                booked somewhere else in that same slot
 *   4. No Time Slot Conflict  - the (resource, slot) pair as a whole
 *                                must be free (covers equipment/labs too)
 *
 * Workflow (matches the required project workflow):
 *   Input Schedule Data -> Run Constraint Algorithm -> Conflict?
 *       Yes -> Reject (return list of violation messages)
 *       No  -> Approve (caller then INSERTs/UPDATEs the schedule)
 *
 * Returns an array:
 *   [
 *     'valid'  => bool,
 *     'errors' => array of human-readable violation messages
 *   ]
 * ---------------------------------------------------------------------
 */

function validate_schedule($conn, $resource_id, $slot_id, $user_id, $exclude_schedule_id = null) {
    $errors = [];

    // ---- CHECK 1: Room / Resource Availability -------------------------
    $stmt = mysqli_prepare($conn, "SELECT resource_name, status FROM resources WHERE resource_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $resource_id);
    mysqli_stmt_execute($stmt);
    $resource = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$resource) {
        $errors[] = 'Selected resource does not exist.';
    } elseif ($resource['status'] !== 'available') {
        $errors[] = "Resource \"{$resource['resource_name']}\" is currently marked unavailable.";
    }

    // ---- CHECK 2: Time Slot exists --------------------------------------
    $stmt = mysqli_prepare($conn, "SELECT day, start_time, end_time FROM timeslots WHERE slot_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $slot_id);
    mysqli_stmt_execute($stmt);
    $slot = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if (!$slot) {
        $errors[] = 'Selected time slot does not exist.';
    }

    // If basic references are missing, stop here — nothing more to check
    if (!empty($errors)) {
        return ['valid' => false, 'errors' => $errors];
    }

    // ---- CHECK 3: No Room Clash ------------------------------------------
    // Same resource + same slot already booked (approved/pending) by ANY user
    $sql = "SELECT s.schedule_id, u.full_name FROM schedules s
            JOIN users u ON s.user_id = u.user_id
            WHERE s.resource_id = ? AND s.slot_id = ? AND s.status != 'rejected'";
    $params = "ii"; $values = [$resource_id, $slot_id];
    if ($exclude_schedule_id) { $sql .= " AND s.schedule_id != ?"; $params .= "i"; $values[] = $exclude_schedule_id; }

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $params, ...$values);
    mysqli_stmt_execute($stmt);
    $clash = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if ($clash) {
        $errors[] = "Room clash: \"{$resource['resource_name']}\" is already booked in this time slot (by {$clash['full_name']}).";
    }

    // ---- CHECK 4: No Lecturer Clash ---------------------------------------
    // Same user already has a schedule in this exact slot (any resource)
    $sql = "SELECT s.schedule_id, r.resource_name FROM schedules s
            JOIN resources r ON s.resource_id = r.resource_id
            WHERE s.user_id = ? AND s.slot_id = ? AND s.status != 'rejected'";
    $params = "ii"; $values = [$user_id, $slot_id];
    if ($exclude_schedule_id) { $sql .= " AND s.schedule_id != ?"; $params .= "i"; $values[] = $exclude_schedule_id; }

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $params, ...$values);
    mysqli_stmt_execute($stmt);
    $lecturerClash = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if ($lecturerClash) {
        $errors[] = "Lecturer clash: this user already has a schedule in this time slot (in {$lecturerClash['resource_name']}).";
    }

    // ---- CHECK 5: General Time Slot Conflict (safety net) -----------------
    // Belt-and-braces check combining resource+slot+user in one query in case
    // of race conditions between checks 3 and 4.
    $sql = "SELECT schedule_id FROM schedules
            WHERE slot_id = ? AND (resource_id = ? OR user_id = ?) AND status != 'rejected'";
    $params = "iii"; $values = [$slot_id, $resource_id, $user_id];
    if ($exclude_schedule_id) { $sql .= " AND schedule_id != ?"; $params .= "i"; $values[] = $exclude_schedule_id; }
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $params, ...$values);
    mysqli_stmt_execute($stmt);
    $conflictCount = mysqli_num_rows(mysqli_stmt_get_result($stmt));
    if ($conflictCount > 0 && empty($errors)) {
        // Only add this generic message if the specific checks above somehow missed it
        $errors[] = 'Time slot conflict detected for this resource or user.';
    }

    return [
        'valid'  => empty($errors),
        'errors' => $errors,
    ];
}
