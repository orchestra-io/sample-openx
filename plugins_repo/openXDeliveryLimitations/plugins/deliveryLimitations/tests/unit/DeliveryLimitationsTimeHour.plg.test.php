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
$Id: DeliveryLimitationsTimeHour.plg.test.php 39260 2009-07-03 14:20:27Z matteo.beccati $
*/

require_once MAX_PATH . '/lib/max/Plugin.php';
// Using multi-dirname so that the tests can run from either plugins or plugins_repo
require_once dirname(dirname(dirname(__FILE__))) . '/Time/Hour.delivery.php';

/**
 * A class for testing the Plugins_DeliveryLimitations_Time_Hour class.
 *
 * @package    OpenXPlugin
 * @subpackage TestSuite
 * @author     Andrew Hill <andrew@m3.net>
 */
class Plugins_TestOfPlugins_DeliveryLimitations_Time_Hour extends UnitTestCase
{
    function testCheckTimeHour()
    {
        OA_setTimeZoneUTC();

        // =~ and !~ - Single value
        $this->assertTrue(MAX_checkTime_Hour('3',      '=~', array('timestamp' => mktime(3, 0, 0, 7, 1, 2009))));
        $this->assertTrue(MAX_checkTime_Hour('3',      '!~', array('timestamp' => mktime(4, 0, 0, 7, 1, 2009))));

        // =~ and !~ - Multiple value
        $this->assertTrue(MAX_checkTime_Hour('1,3,4',  '=~', array('timestamp' => mktime(1, 0, 0, 7, 1, 2009))));
        $this->assertTrue(MAX_checkTime_Hour('1,3,4',  '=~', array('timestamp' => mktime(4, 0, 0, 7, 1, 2009))));
        $this->assertTrue(MAX_checkTime_Hour('1,3,4',  '!~', array('timestamp' => mktime(5, 0, 0, 7, 1, 2009))));
        $this->assertFalse(MAX_checkTime_Hour('1,3,4', '!~', array('timestamp' => mktime(3, 0, 0, 7, 1, 2009))));

        // =~ and !~ - Single value with TZ
        $this->assertTrue(MAX_checkTime_Hour('5@Europe/Rome',      '=~', array('timestamp' => mktime(3, 0, 0, 7, 1, 2009))));
        $this->assertTrue(MAX_checkTime_Hour('5@Europe/Rome',      '!~', array('timestamp' => mktime(4, 0, 0, 7, 1, 2009))));

        // =~ and !~ - Multiple value with TZ
        $this->assertTrue(MAX_checkTime_Hour('3,5,6@Europe/Rome',  '=~', array('timestamp' => mktime(1, 0, 0, 7, 1, 2009))));
        $this->assertTrue(MAX_checkTime_Hour('3,5,6@Europe/Rome',  '=~', array('timestamp' => mktime(4, 0, 0, 7, 1, 2009))));
        $this->assertTrue(MAX_checkTime_Hour('3,5,6@Europe/Rome',  '!~', array('timestamp' => mktime(5, 0, 0, 7, 1, 2009))));
        $this->assertFalse(MAX_checkTime_Hour('3,5,6@Europe/Rome', '!~', array('timestamp' => mktime(3, 0, 0, 7, 1, 2009))));

        OA_setTimeZoneLocal();
    }
}

?>
