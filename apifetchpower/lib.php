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
function local_apifetchpower_fetch_json($url, $token = null) {
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);

    // NOT NEEDED ANYMORE (Discogs API -> energyCharts)
    if (!empty($token)) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
    }

    $response = curl_exec($curl);
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);

    if ($error || $httpcode !== 200) {
        return [
            'success' => false,
            'data' => null,
            'error' => $error ?: "HTTP $httpcode error"
        ];
    }

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
