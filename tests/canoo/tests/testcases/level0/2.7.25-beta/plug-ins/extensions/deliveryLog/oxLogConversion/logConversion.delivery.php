<?php

/*
+---------------------------------------------------------------------------+
| OpenX v${RELEASE_MAJOR_MINOR}                                                                |
| =======${RELEASE_MAJOR_MINOR_DOUBLE_UNDERLINE}                                                                |
|                                                                           |
| Copyright (c) 2003-2008 OpenX Limited                                     |
| For contact details, see: http://www.openx.org/                           |
|                                                                           |
| This program is free software; you can redistribute it and/or modify      |
| it under the terms of the GNU General Public License as published by      |
| the Free Software Foundation; either version 2 of the License, or         |
| (at your option) any later version.                                       |
|                                                                           |
| This program is distributed in the hope that it will be useful,           |
| but WITHOUT ANY WARRANTY; without even the implied warranty of            |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
| GNU General Public License for more details.                              |
|                                                                           |
| You should have received a copy of the GNU General Public License         |
| along with this program; if not, write to the Free Software               |
| Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA |
+---------------------------------------------------------------------------+
$Id: logConversion.delivery.php 25718 2008-09-15 13:03:39Z andrew.hill $
*/

/**
 * @package    Plugin
 * @subpackage openxDeliveryLog
 */

MAX_Dal_Delivery_Include();

/**
 * A function to log conversions.
 *
 * @param integer $trackerId The ID of the tracker for which the conversion is to be logged.
 * @param array $serverRawIp The "raw IP address" value to use for the conversion.
 * @param array $aConversion An array of the conversion details, as returned from the
 *                           MAX_trackerCheckForValidAction() function.
 * @return array An array...
 */
function Plugin_deliveryLog_oxLogConversion_logConversion_Delivery_logConversion($trackerId, $serverRawIp, $aConversion)
{
    $table = $GLOBALS['_MAX']['CONF']['table']['prefix'] . 'data_bkt_a';

    if (empty($GLOBALS['_MAX']['NOW'])) {
        $GLOBALS['_MAX']['NOW'] = time();
    }
    $time = $GLOBALS['_MAX']['NOW'];

    $aFields = array(
        'server_ip'        => $serverRawIp,
        'tracker_id'       => $trackerId,
        'date_time'        => gmdate('Y-m-d H:i:s', $time),
        'action_date_time' => gmdate('Y-m-d H:i:s', $aConversion['dt']),
        'creative_id'      => $aConversion['cid'],
        'zone_id'          => $aConversion['zid'],
        'ip_address'       => $_SERVER['REMOTE_ADDR'],
        'action'           => $aConversion['action_type'],
        'window'           => $aConversion['window'],
        'status'           => $aConversion['status']
    );

    array_walk($aFields, 'OX_escapeString');

    $query = "
        INSERT INTO
            {$table}
            (" . implode(', ', array_keys($aFields)) . ")
        VALUES
            ('" . implode("', '", $aFields) . "')
    ";
    $result = OA_Dal_Delivery_query($query, 'rawDatabase');
    if (!$result) {
        return false;
    }
    $aResult = array(
        'server_conv_id' => OA_Dal_Delivery_insertId('rawDatabase', $table, 'server_conv_id'),
        'server_raw_ip' => $serverRawIp
    );
    return $aResult;
}

?>