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
$Id: Data_bkt_a.php 33995 2009-03-18 23:04:15Z chris.nutting $
*/

require_once MAX_PATH.'/lib/max/Dal/DataObjects/DB_DataObjectCommon.php';

/**
 * DB_DataObject for data_bkt_r
 *
 * @package    Plugin
 * @subpackage openxDeliveryLog
 */
class DataObjects_Data_bkt_a extends DB_DataObjectCommon
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'data_bkt_a';             // table name
    public $server_conv_id;                  // BIGINT(20) => openads_mediumint => 129
    public $server_ip;                       // VARCHAR(16) => openads_varchar => 130
    public $tracker_id;                      // MEDIUMINT(9) => openads_mediumint => 129
    public $date_time;                       // DATETIME() => openads_datetime => 142
    public $action_date_time;                // DATETIME() => openads_datetime => 142
    public $creative_id;                     // MEDIUMINT(9) => openads_mediumint => 129
    public $zone_id;                         // MEDIUMINT(9) => openads_mediumint => 129
    public $ip_address;                      // VARCHAR(16) => openads_varchar => 130
    public $action;                          // INT(11) => openads_int => 1
    public $window;                          // INT(11) => openads_int => 1
    public $status;                          // INT(11) => openads_int => 1

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_Data_bkt_a',$k,$v); }

    var $defaultValues = array(
                'server_ip' => '',
                'ip_address' => '',
                );

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
?>