<?php

/*
+---------------------------------------------------------------------------+
| OpenX v${RELEASE_MAJOR_MINOR}                                                                |
| =======${RELEASE_MAJOR_MINOR_DOUBLE_UNDERLINE}                                                                |
|                                                                           |
| Copyright (c) 2003-2009 OpenX Limited                                     |
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
$Id: logImpression.delivery.php 37975 2009-06-15 12:44:21Z andrew.hill $
*/

/**
 * @package    Plugin
 * @subpackage openxDeliveryLog
 */

function Plugin_deliveryLog_OxLogImpression_LogImpression_Delivery_logImpression($adId = 0, $zoneId = 0, $okToLog = true)
{
    if (!$okToLog) { return false; }
    $aData = $GLOBALS['_MAX']['deliveryData'];
    $aQuery = array(
        'interval_start' => $aData['interval_start'],
        'creative_id'    => $aData['creative_id'],
        'zone_id'        => $aData['zone_id']
    );
    return OX_bucket_updateTable('data_bkt_m', $aQuery);
}

