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
$Id: DeliveryLimitationsTimeDay.plg.test.php 39260 2009-07-03 14:20:27Z matteo.beccati $
*/

require_once MAX_PATH . '/lib/max/Plugin.php';
// Using multi-dirname so that the tests can run from either plugins or plugins_repo
require_once dirname(dirname(dirname(__FILE__))) . '/Time/Day.delivery.php';

/**
 * A class for testing the Plugins_DeliveryLimitations_Time_Day class.
 *
 * @package    OpenXPlugin
 * @subpackage TestSuite
 * @author     Andrew Hill <andrew@m3.net>
 */
class Plugins_TestOfPlugins_DeliveryLimitations_Time_Day extends UnitTestCase
{
    function testCheckTimeDay()
    {
        OA_setTimeZoneUTC();

        // =~ and !~ - Single value
        $this->assertTrue(MAX_checkTime_Day('3',      '=~', array('timestamp' => mktime(0, 0, 0, 7, 1, 2009)))); // Wed
        $this->assertTrue(MAX_checkTime_Day('3',      '!~', array('timestamp' => mktime(0, 0, 0, 7, 2, 2009)))); // Thu

        // =~ and !~ - Multiple value
        $this->assertTrue(MAX_checkTime_Day('1,3,4',  '=~', array('timestamp' => mktime(0, 0, 0, 7, 6, 2009)))); // Mon
        $this->assertTrue(MAX_checkTime_Day('1,3,4',  '=~', array('timestamp' => mktime(0, 0, 0, 7, 2, 2009)))); // Thu
        $this->assertTrue(MAX_checkTime_Day('1,3,4',  '!~', array('timestamp' => mktime(0, 0, 0, 7, 3, 2009)))); // Fri
        $this->assertFalse(MAX_checkTime_Day('1,3,4', '!~', array('timestamp' => mktime(0, 0, 0, 7, 1, 2009)))); // Wed

        // =~ and !~ - Single value with TZ
        $this->assertTrue(MAX_checkTime_Day('2@America/New_York',      '=~', array('timestamp' => mktime(0, 0, 0, 7, 1, 2009)))); // Wed
        $this->assertTrue(MAX_checkTime_Day('2@America/New_York',      '!~', array('timestamp' => mktime(0, 0, 0, 7, 2, 2009)))); // Thu

        // =~ and !~ - Multiple value with TZ
        $this->assertTrue(MAX_checkTime_Day('0,2,3@America/New_York',  '=~', array('timestamp' => mktime(0, 0, 0, 7, 6, 2009)))); // Mon
        $this->assertTrue(MAX_checkTime_Day('0,2,3@America/New_York',  '=~', array('timestamp' => mktime(0, 0, 0, 7, 2, 2009)))); // Thu
        $this->assertTrue(MAX_checkTime_Day('0,2,3@America/New_York',  '!~', array('timestamp' => mktime(0, 0, 0, 7, 3, 2009)))); // Fri
        $this->assertFalse(MAX_checkTime_Day('0,2,3@America/New_York', '!~', array('timestamp' => mktime(0, 0, 0, 7, 1, 2009)))); // Wed

        OA_setTimeZoneLocal();
    }
}

?>
