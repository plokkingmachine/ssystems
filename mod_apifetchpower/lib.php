<?php
// This file is part of Moodle - http://moodle.com/
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
 * Library functions for the local_apifetchpower plugin.
 *
 * @package   local_apifetchpower
 * @copyright 2025 fw
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Fetches JSON data from an external API using curl.
 *
 * @param string $url The API endpoint URL.
 * @param string|null $token Optional authentication token (Bearer header).
 * @return array An array with 'success' (bool), 'data' (decoded JSON or null), and 'error' (string or null).
 */

/* , $token = null */
function local_apifetchpower_fetch_json($url) {
    // Initialize curl for secure HTTP requests.
    $curl = curl_init();

    // Set curl options: URL, return transfer, follow redirects, timeout.
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 30-second timeout for security.

    /* // If token provided, add Bearer authentication header. */
    /* if (!empty($token)) { */
    /*     curl_setopt($curl, CURLOPT_HTTPHEADER, [ */
    /*         'Authorization: Bearer ' . $token, */
    /*         'Content-Type: application/json' // Assume JSON API. */
    /*     ]); */
    /* } */

    // Execute the request.
    $response = curl_exec($curl);
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);

    // Check for errors or non-200 status.
    if ($error || $httpcode !== 200) {
        return [
            'success' => false,
            'data' => null,
            'error' => $error ?: "HTTP $httpcode error"
        ];
    }

    // Decode JSON response.
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'success' => false,
            'data' => null,
            'error' => 'Invalid JSON response'
        ];
    }

    return [
        'success' => true,
        'data' => $data,
        'error' => null
    ];
}


/* MINIMAL IMPLEMENTATIONS */

/**
 * Add instance.
 * @param stdClass $data
 * @param mod_form|null $mform
 * @return int new instance id
 */
function apifetchpower_add_instance(stdClass $data, $mform = null) : int {
    global $DB;

    $data->timemodified = time();
    // Insert into the plugin table named exactly as plugin.
    $id = $DB->insert_record('apifetchpower', $data);
    return $id;
}

/**
 * Update instance.
 * @param stdClass $data
 * @param mod_form $mform
 * @return bool
 */
function apifetchpower_update_instance(stdClass $data, $mform) : bool {
    global $DB;

    $data->timemodified = time();
    $data->id = $data->instance;
    return (bool)$DB->update_record('apifetchpower', $data);
}

/**
 * Delete instance.
 * @param int $id
 * @return bool
 */
function apifetchpower_delete_instance($id) : bool {
    global $DB;

    if (!$record = $DB->get_record('apifetchpower', ['id' => $id])) {
        return false;
    }
    $DB->delete_records('apifetchpower', ['id' => $id]);
    // Also delete related data if any.
    return true;
}

/**
 * Feature support.
 */
function apifetchpower_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS: return true;
        case FEATURE_GROUPINGS: return true;
        case FEATURE_MOD_INTRO: return true;
        case FEATURE_SHOW_DESCRIPTION: return true;
        default: return null;
    }
}
