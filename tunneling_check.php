<?php
/*
 * Copyright (C) 2011 Daniel Richman
 * This is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Adapted from:
 *   https://gist.github.com/23aefaba269484eb8be4
 */

@error_reporting(E_ALL);
Header("Content-type: text/plain");

$anapnean_ips = Array("46.21.207.188", "127.0.0.1");
$anapnea_main_ip = $anapnean_ips[0];

/* Get info */
$remote_ip = $_SERVER["REMOTE_ADDR"];
$remote_port = $_SERVER["REMOTE_PORT"];
$local_ip = $_SERVER["SERVER_ADDR"];
$local_port = $_SERVER["SERVER_PORT"];

if (!in_array($remote_ip, $anapnean_ips))
{
    echo "Your IP address is $remote_ip - You are not tunneling with anapnea.net.";
    exit;
}

$user = get_ident($local_port, $remote_ip, $remote_port, 1);

echo "Congratulations, $user, you are tunneling with anapnea.net! Your IP address will appear, to the outside world, as $anapnea_main_ip.";

/* Functions */
function get_ident($local_port, $remote_ip, $remote_port, $timeout)
{
	$query = "$remote_port, $local_port\n";

	$errno = 0;
	$errstr = '';
        $socket = @fsockopen($remote_ip, 113, $errno, $errstr, $timeout);
        if (!$socket)   return false;

        $result = @socket_set_timeout($socket, $timeout);
        if (!$result)   { @fclose($socket); return false; }

        $result = @fwrite($socket, $query);
        if (!$result)   { @fclose($socket); return false; }

        $response = @fread($socket, 1024);
        @fclose($socket);

	if (!$response || strlen($response) == 0)	return false;

	$response_array = @explode(':', $response);
	if (count($response_array) < 3)			return false;

	$response_port_array = explode(',', $response_array[0]);
	if (trim($response_port_array[0]) != $remote_port ||
	    trim($response_port_array[1]) != $local_port)
		return false;

	array_shift($response_array);
	foreach ($response_array as $key => $value)
		$response_array[$key] = trim($value);

	return $response_array[2];
}
?>
