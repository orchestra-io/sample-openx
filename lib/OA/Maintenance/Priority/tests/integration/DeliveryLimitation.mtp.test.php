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
$Id: DeliveryLimitation.mtp.test.php 42110 2009-08-25 21:51:30Z matthieu.aubry $
*/

require_once MAX_PATH . '/lib/OA/Maintenance/Priority/DeliveryLimitation.php';
require_once MAX_PATH . '/lib/pear/Date.php';

/**
 * A class for testing the OA_Maintenance_Priority_DeliveryLimitation class.
 *
 * @package    OpenXMaintenance
 * @subpackage TestSuite
 * @author     Andrew Hill <andrew.hill@openx.org>
 */
class Test_OA_Maintenance_Priority_DeliveryLimitation extends UnitTestCase
{

    /**
     * The constructor method.
     */
    function Test_OA_Maintenance_Priority_DeliveryLimitation()
    {
        $this->UnitTestCase();
        Mock::generatePartial(
            'OA_Maintenance_Priority_DeliveryLimitation',
            'Partial_MockOA_Maintenance_Priority_DeliveryLimitation',
            array('_getOperationInterval')
        );

        // Install the openXDeliveryLog plugin
        TestEnv::uninstallPluginPackage('openXDeliveryLimitations', false);
        TestEnv::installPluginPackage('openXDeliveryLimitations', false);

        OA_setTimeZoneUTC();
    }

    /**
     * A method to test the OA_Maintenance_Priority_DeliveryLimitation
     * constructor method.
     */
    function testConstructor()
    {
        $aDeliveryLimitations = array(
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Hour',
                'comparison'     => '=~',
                'data'           => '1,7,18,23',
                'executionorder' => 1
            ),
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Day',
                'comparison'     => '=~',
                'data'           => '1',
                'executionorder' => 3
            ),
            array(
                'ad_id'          => 1,
                'logical'        => 'or',
                'type'           => 'deliveryLimitations:Time:Date',
                'comparison'     => '==',
                'data'           => '2005:10:10 00:00:00',
                'executionorder' => 2
            ),
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Client:IP',
                'comparison'     => '==',
                'data'           => '192.168.0.1',
                'executionorder' => 0
            ),
        );
        $oDeliveryLimitationManager = new Partial_MockOA_Maintenance_Priority_DeliveryLimitation($this);
        $oDeliveryLimitationManager->setReturnValue('_getOperationInterval', true);
        $oDeliveryLimitationManager->OA_Maintenance_Priority_DeliveryLimitation($aDeliveryLimitations);
        // Test that the different limitations have been set correctly
        $this->assertEqual(count($oDeliveryLimitationManager->aRules), 4);
        $this->assertEqual($oDeliveryLimitationManager->aRules[0]->type, 'deliveryLimitations:Client:IP');
        $this->assertEqual($oDeliveryLimitationManager->aRules[1]->type, 'deliveryLimitations:Time:Hour');
        $this->assertEqual($oDeliveryLimitationManager->aRules[2]->type, 'deliveryLimitations:Time:Date');
        $this->assertEqual($oDeliveryLimitationManager->aRules[3]->type, 'deliveryLimitations:Time:Day');
        $this->assertEqual(count($oDeliveryLimitationManager->aOperationGroups), 2);
        $this->assertEqual(count($oDeliveryLimitationManager->aOperationGroups[0]), 1);
        $this->assertEqual($oDeliveryLimitationManager->aOperationGroups[0][0]->type, 'deliveryLimitations:Time:Hour');
        $this->assertEqual(count($oDeliveryLimitationManager->aOperationGroups[1]), 2);
        $this->assertEqual($oDeliveryLimitationManager->aOperationGroups[1][0]->type, 'deliveryLimitations:Time:Date');
        $this->assertEqual($oDeliveryLimitationManager->aOperationGroups[1][1]->type, 'deliveryLimitations:Time:Day');
    }

    /**
     * A method to test the _getOperationGroupCount() method.
     */
    function test_getOperationGroupCount()
    {
        $aDeliveryLimitations = array(
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Hour',
                'comparison'     => '=~',
                'data'           => '1,7,18,23',
                'executionorder' => 1
            ),
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Day',
                'comparison'     => '=~',
                'data'           => '1',
                'executionorder' => 3
            ),
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Date',
                'comparison'     => '==',
                'data'           => '2005:10:10 00:00:00',
                'executionorder' => 2
            ),
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Client:IP',
                'comparison'     => '==',
                'data'           => '192.168.0.1',
                'executionorder' => 0
            ),
        );
        $oDeliveryLimitationManager = new Partial_MockOA_Maintenance_Priority_DeliveryLimitation($this);
        $oDeliveryLimitationManager->setReturnValue('_getOperationInterval', true);
        $oDeliveryLimitationManager->OA_Maintenance_Priority_DeliveryLimitation($aDeliveryLimitations);
        $this->assertEqual($oDeliveryLimitationManager->_getOperationGroupCount(), 1);

        $aDeliveryLimitations = array(
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Hour',
                'comparison'     => '=~',
                'data'           => '1,7,18,23',
                'executionorder' => 1
            ),
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Day',
                'comparison'     => '=~',
                'data'           => '1',
                'executionorder' => 3
            ),
            array(
                'ad_id'          => 1,
                'logical'        => 'or',
                'type'           => 'deliveryLimitations:Time:Date',
                'comparison'     => '==',
                'data'           => '2005:10:10 00:00:00',
                'executionorder' => 2
            ),
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Client:IP',
                'comparison'     => '==',
                'data'           => '192.168.0.1',
                'executionorder' => 0
            ),
        );
        $oDeliveryLimitationManager = new Partial_MockOA_Maintenance_Priority_DeliveryLimitation($this);
        $oDeliveryLimitationManager->setReturnValue('_getOperationInterval', true);
        $oDeliveryLimitationManager->OA_Maintenance_Priority_DeliveryLimitation($aDeliveryLimitations);
        $this->assertEqual($oDeliveryLimitationManager->_getOperationGroupCount(), 2);
    }

    /**
     * A method to test the deliveryBlocked() method.
     *
     * Test 1: Test a simple, single delivery limitation case, where delivery is NOT blocked.
     * Test 2: Test a simple, single delivery limitation case, where delivery IS blocked.
     *
     * Test 3: Test a simple, dual AND delivery limitation case, is NOT blocked in either limitation.
     * Test 4: Test a simple, dual AND delivery limitation case, is IS blocked in ONE limitation.
     * Test 5: Test a simple, dual AND delivery limitation case, is IS blocked in BOTH limitations.
     *
     * Test 6: Test a simple, dual OR delivery limitation case, is NOT blocked in either limitation.
     * Test 7: Test a simple, dual OR delivery limitation case, is IS blocked in ONE limitation.
     * Test 8: Test a simple, dual OR delivery limitation case, is IS blocked in BOTH limitations.
     *
     * Test 9: Test several complex, multi-delivery limitation cases.
     */
    function testDeliveryBlocked()
    {
        // Test 1
        $oDate = new Date('2006-02-08 07:05:00');
        $aDeliveryLimitations = array(
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Hour',
                'comparison'     => '=~',
                'data'           => '1,7,18,23',
                'executionorder' => 0
            )
        );
        $oDeliveryLimitationManager = new Partial_MockOA_Maintenance_Priority_DeliveryLimitation($this);
        $oDeliveryLimitationManager->setReturnValue('_getOperationInterval', true);
        $oDeliveryLimitationManager->OA_Maintenance_Priority_DeliveryLimitation($aDeliveryLimitations);
        $result = $oDeliveryLimitationManager->deliveryBlocked($oDate);
        $this->assertFalse($result);

        // Test 2
        $oDate = new Date('2006-02-08 10:05:00');
        $aDeliveryLimitations = array(
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Hour',
                'comparison'     => '=~',
                'data'           => '1,7,18,23',
                'executionorder' => 0
            )
        );
        $oDeliveryLimitationManager = new Partial_MockOA_Maintenance_Priority_DeliveryLimitation($this);
        $oDeliveryLimitationManager->setReturnValue('_getOperationInterval', true);
        $oDeliveryLimitationManager->OA_Maintenance_Priority_DeliveryLimitation($aDeliveryLimitations);
        $result = $oDeliveryLimitationManager->deliveryBlocked($oDate);
        $this->assertTrue($result);

        // Test 3
        $oDate = new Date('2006-02-08 07:05:00');
        $aDeliveryLimitations = array(
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Hour',
                'comparison'     => '=~',
                'data'           => '1,7,18,23',
                'executionorder' => 0
            ),
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Date',
                'comparison'     => '==',
                'data'           => '20060208',
                'executionorder' => 1
            )
        );
        $oDeliveryLimitationManager = new Partial_MockOA_Maintenance_Priority_DeliveryLimitation($this);
        $oDeliveryLimitationManager->setReturnValue('_getOperationInterval', true);
        $oDeliveryLimitationManager->OA_Maintenance_Priority_DeliveryLimitation($aDeliveryLimitations);
        $result = $oDeliveryLimitationManager->deliveryBlocked($oDate);
        $this->assertFalse($result);

        // Test 4
        $oDate = new Date('2006-02-08 10:05:00');
        $aDeliveryLimitations = array(
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Hour',
                'comparison'     => '=~',
                'data'           => '1,7,18,23',
                'executionorder' => 0
            ),
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Date',
                'comparison'     => '==',
                'data'           => '20060208',
                'executionorder' => 1
            )
        );
        $oDeliveryLimitationManager = new Partial_MockOA_Maintenance_Priority_DeliveryLimitation($this);
        $oDeliveryLimitationManager->setReturnValue('_getOperationInterval', true);
        $oDeliveryLimitationManager->OA_Maintenance_Priority_DeliveryLimitation($aDeliveryLimitations);
        $result = $oDeliveryLimitationManager->deliveryBlocked($oDate);
        $this->assertTrue($result);

        // Test 5
        $oDate = new Date('2006-02-09 10:05:00');
        $aDeliveryLimitations = array(
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Hour',
                'comparison'     => '==',
                'data'           => '1,7,18,23',
                'executionorder' => 0
            ),
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Date',
                'comparison'     => '==',
                'data'           => '20060208',
                'executionorder' => 1
            )
        );
        $oDeliveryLimitationManager = new Partial_MockOA_Maintenance_Priority_DeliveryLimitation($this);
        $oDeliveryLimitationManager->setReturnValue('_getOperationInterval', true);
        $oDeliveryLimitationManager->OA_Maintenance_Priority_DeliveryLimitation($aDeliveryLimitations);
        $result = $oDeliveryLimitationManager->deliveryBlocked($oDate);
        $this->assertTrue($result);

        // Test 6
        $oDate = new Date('2006-02-08 07:05:00');
        $aDeliveryLimitations = array(
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Hour',
                'comparison'     => '=~',
                'data'           => '1,7,18,23',
                'executionorder' => 0
            ),
            array(
                'ad_id'          => 1,
                'logical'        => 'or',
                'type'           => 'deliveryLimitations:Time:Date',
                'comparison'     => '==',
                'data'           => '20060208',
                'executionorder' => 1
            )
        );
        $oDeliveryLimitationManager = new Partial_MockOA_Maintenance_Priority_DeliveryLimitation($this);
        $oDeliveryLimitationManager->setReturnValue('_getOperationInterval', true);
        $oDeliveryLimitationManager->OA_Maintenance_Priority_DeliveryLimitation($aDeliveryLimitations);
        $result = $oDeliveryLimitationManager->deliveryBlocked($oDate);
        $this->assertFalse($result);

        // Test 7
        $oDate = new Date('2006-02-08 10:05:00');
        $aDeliveryLimitations = array(
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Hour',
                'comparison'     => '=~',
                'data'           => '1,7,18,23',
                'executionorder' => 0
            ),
            array(
                'ad_id'          => 1,
                'logical'        => 'or',
                'type'           => 'deliveryLimitations:Time:Date',
                'comparison'     => '==',
                'data'           => '20060208',
                'executionorder' => 1
            )
        );
        $oDeliveryLimitationManager = new Partial_MockOA_Maintenance_Priority_DeliveryLimitation($this);
        $oDeliveryLimitationManager->setReturnValue('_getOperationInterval', true);
        $oDeliveryLimitationManager->OA_Maintenance_Priority_DeliveryLimitation($aDeliveryLimitations);
        $result = $oDeliveryLimitationManager->deliveryBlocked($oDate);
        $this->assertFalse($result);

        // Test 8
        $oDate = new Date('2006-02-09 10:05:00');
        $aDeliveryLimitations = array(
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Hour',
                'comparison'     => '=~',
                'data'           => '1,7,18,23',
                'executionorder' => 0
            ),
            array(
                'ad_id'          => 1,
                'logical'        => 'or',
                'type'           => 'deliveryLimitations:Time:Date',
                'comparison'     => '==',
                'data'           => '20060208',
                'executionorder' => 1
            )
        );
        $oDeliveryLimitationManager = new Partial_MockOA_Maintenance_Priority_DeliveryLimitation($this);
        $oDeliveryLimitationManager->setReturnValue('_getOperationInterval', true);
        $oDeliveryLimitationManager->OA_Maintenance_Priority_DeliveryLimitation($aDeliveryLimitations);
        $result = $oDeliveryLimitationManager->deliveryBlocked($oDate);
        $this->assertTrue($result);

        // Test 9
        $oDate = new Date('2006-02-08 10:05:00');
        $aDeliveryLimitations = array(
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Hour',
                'comparison'     => '=~',
                'data'           => '1,7,18,23',
                'executionorder' => 0
            ),
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Date',
                'comparison'     => '==',
                'data'           => '20060208',
                'executionorder' => 1
            ),
            array(
                'ad_id'          => 1,
                'logical'        => 'or',
                'type'           => 'deliveryLimitations:Time:Date',
                'comparison'     => '==',
                'data'           => '20060209',
                'executionorder' => 2
            ),
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Client:IP',
                'comparison'     => '==',
                'data'           => '192.168.0.1',
                'executionorder' => 3
            )
        );
        $oDeliveryLimitationManager = new Partial_MockOA_Maintenance_Priority_DeliveryLimitation($this);
        $oDeliveryLimitationManager->setReturnValue('_getOperationInterval', true);
        $oDeliveryLimitationManager->OA_Maintenance_Priority_DeliveryLimitation($aDeliveryLimitations);
        $result = $oDeliveryLimitationManager->deliveryBlocked($oDate);
        $this->assertTrue($result);

        $oDate = new Date('2006-02-09 10:05:00');
        $aDeliveryLimitations = array(
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Hour',
                'comparison'     => '=~',
                'data'           => '1,7,18,23',
                'executionorder' => 0
            ),
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Date',
                'comparison'     => '==',
                'data'           => '20060208',
                'executionorder' => 1
            ),
            array(
                'ad_id'          => 1,
                'logical'        => 'or',
                'type'           => 'deliveryLimitations:Time:Date',
                'comparison'     => '==',
                'data'           => '20060209',
                'executionorder' => 2
            ),
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Client:IP',
                'comparison'     => '==',
                'data'           => '192.168.0.1',
                'executionorder' => 3
            )
        );
        $oDeliveryLimitationManager = new Partial_MockOA_Maintenance_Priority_DeliveryLimitation($this);
        $oDeliveryLimitationManager->setReturnValue('_getOperationInterval', true);
        $oDeliveryLimitationManager->OA_Maintenance_Priority_DeliveryLimitation($aDeliveryLimitations);
        $result = $oDeliveryLimitationManager->deliveryBlocked($oDate);
        $this->assertFalse($result);

        $oDate = new Date('2006-02-10 23:05:00');
        $aDeliveryLimitations = array(
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Hour',
                'comparison'     => '=~',
                'data'           => '1,7,18,23',
                'executionorder' => 0
            ),
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Date',
                'comparison'     => '==',
                'data'           => '20060208',
                'executionorder' => 1
            ),
            array(
                'ad_id'          => 1,
                'logical'        => 'or',
                'type'           => 'deliveryLimitations:Time:Date',
                'comparison'     => '==',
                'data'           => '20060209',
                'executionorder' => 2
            ),
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Client:IP',
                'comparison'     => '==',
                'data'           => '192.168.0.1',
                'executionorder' => 3
            )
        );
        $oDeliveryLimitationManager = new Partial_MockOA_Maintenance_Priority_DeliveryLimitation($this);
        $oDeliveryLimitationManager->setReturnValue('_getOperationInterval', true);
        $oDeliveryLimitationManager->OA_Maintenance_Priority_DeliveryLimitation($aDeliveryLimitations);
        $result = $oDeliveryLimitationManager->deliveryBlocked($oDate);
        $this->assertTrue($result);

        $oDate = new Date('2006-02-10 23:05:00');
        $aDeliveryLimitations = array(
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Hour',
                'comparison'     => '=~',
                'data'           => '1,7,18,23',
                'executionorder' => 0
            ),
            array(
                'ad_id'          => 1,
                'logical'        => 'or',
                'type'           => 'deliveryLimitations:Time:Date',
                'comparison'     => '==',
                'data'           => '20060208',
                'executionorder' => 1
            ),
            array(
                'ad_id'          => 1,
                'logical'        => 'or',
                'type'           => 'deliveryLimitations:Time:Date',
                'comparison'     => '==',
                'data'           => '20060209',
                'executionorder' => 2
            ),
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Client:IP',
                'comparison'     => '==',
                'data'           => '192.168.0.1',
                'executionorder' => 3
            )
        );
        $oDeliveryLimitationManager = new Partial_MockOA_Maintenance_Priority_DeliveryLimitation($this);
        $oDeliveryLimitationManager->setReturnValue('_getOperationInterval', true);
        $oDeliveryLimitationManager->OA_Maintenance_Priority_DeliveryLimitation($aDeliveryLimitations);
        $result = $oDeliveryLimitationManager->deliveryBlocked($oDate);
        $this->assertFalse($result);
    }

    /**
     * A method to test the getBlockedOperationIntervalCount() method.
     *
     * Test 1: Test with an equal blocking limitation.
     * Test 2: Test with a non-equal blocking limitation.
     */
    function testGetBlockedOperationIntervalCount()
    {
        $conf =& $GLOBALS['_MAX']['CONF'];
        $conf['maintenance']['operationInterval'] = 60;
        $oNowDate = new Date('2006-02-08 07:05:00');
        $oPlacementEndDate = new Date('2006-02-10');

        // Test 1
        $aDeliveryLimitations = array(
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Hour',
                'comparison'     => '=~',
                'data'           => '1,7,18,23',
                'executionorder' => 0
            )
        );
        $oDeliveryLimitationManager = new OA_Maintenance_Priority_DeliveryLimitation($aDeliveryLimitations);
        $result = $oDeliveryLimitationManager->getBlockedOperationIntervalCount($oNowDate, $oPlacementEndDate);
        $this->assertEqual($result, 54);

        // Test 2
        $aDeliveryLimitations = array(
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Hour',
                'comparison'     => '!~',
                'data'           => '1,7,18,23',
                'executionorder' => 0
            )
        );
        $oDeliveryLimitationManager = new OA_Maintenance_Priority_DeliveryLimitation($aDeliveryLimitations);
        $result = $oDeliveryLimitationManager->getBlockedOperationIntervalCount($oNowDate, $oPlacementEndDate);
        $this->assertEqual($result, 11);

        TestEnv::restoreConfig();
    }

    /**
     * A method to test the getActiveAdOperationIntervals() method.
     *
     * Test 1: Test with an equal blocking limitation.
     * Test 2: Test with a non-equal blocking limitation.
     */
    function testGetActiveAdOperationIntervals()
    {
        $conf =& $GLOBALS['_MAX']['CONF'];
        $conf['maintenance']['operationInterval'] = 60;
        $oNowDate = new Date('2006-02-08 07:05:00');
        $oPlacementEndDate = new Date('2006-02-10');

        // Test 1
        $aDeliveryLimitations = array(
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Hour',
                'comparison'     => '=~',
                'data'           => '1,7,18,23',
                'executionorder' => 0
            )
        );
        $oDeliveryLimitationManager = new OA_Maintenance_Priority_DeliveryLimitation($aDeliveryLimitations);
        $result = $oDeliveryLimitationManager->getActiveAdOperationIntervals(65, $oNowDate, $oPlacementEndDate);
        $this->assertEqual($result, 11);

        // Test 2
        $aDeliveryLimitations = array(
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Hour',
                'comparison'     => '!~',
                'data'           => '1,7,18,23',
                'executionorder' => 0
            )
        );
        $oDeliveryLimitationManager = new OA_Maintenance_Priority_DeliveryLimitation($aDeliveryLimitations);
        $result = $oDeliveryLimitationManager->getActiveAdOperationIntervals(65, $oNowDate, $oPlacementEndDate);
        $this->assertEqual($result, 54);

        TestEnv::restoreConfig();
    }

    /**
     * A method to test the getAdLifetimeZoneImpressionsRemaining() method.
     *
     * Test 1: Test with invalid parameters, and ensure that zero is returned.
     * Test 2: Test with equal start and end dates, and ensure just that OI's
     *         data is returned.
     * Test 3: Test with a small range of dates in one week, that the correct
     *         sum is returned.
     * Test 4: Test with a small range of dates over three days, covering two
     *         weeks, and ensure that the correct result is returned.
     * Test 5: Test with a limitation that blocks less than 50% of the remaining
     *         range, and ensure that the correct result is returned.
     * Test 6: Test with a limitation that blocks more than 50% of the remaining
     *         range, and ensure that the correct result is returned.
     */
    function testGetAdLifetimeZoneImpressionsRemaining()
    {
        $aConf =& $GLOBALS['_MAX']['CONF'];
        $aConf['maintenance']['operationInterval'] = 60;

        $aDeliveryLimitations = array();
        $oDeliveryLimitationManager = new OA_Maintenance_Priority_DeliveryLimitation($aDeliveryLimitations);

        // Test 1
        $oDate = new Date('2006-02-15 11:07:15');
        $aCumulativeZoneForecast = array();
        $aCumulativeZoneForecast = $this->_fillForecastArray($aCumulativeZoneForecast);
        $result = $oDeliveryLimitationManager->getAdLifetimeZoneImpressionsRemaining('foo', $oDate, $aCumulativeZoneForecast);
        $this->assertEqual($result, 0);
        $result = $oDeliveryLimitationManager->getAdLifetimeZoneImpressionsRemaining($oDate, 'foo', $aCumulativeZoneForecast);
        $this->assertEqual($result, 0);
        $result = $oDeliveryLimitationManager->getAdLifetimeZoneImpressionsRemaining($oDate, $oDate, 'foo');
        $this->assertEqual($result, 0);

        // Test 2
        $oDate = new Date('2006-02-15 23:07:15');
        $aCumulativeZoneForecast = array();
        $aCumulativeZoneForecast = $this->_fillForecastArray($aCumulativeZoneForecast);
        $result = $oDeliveryLimitationManager->getAdLifetimeZoneImpressionsRemaining($oDate, $oDate, $aCumulativeZoneForecast);
        $this->assertEqual($result, 1);
        $aDates = OX_OperationInterval::convertDateToOperationIntervalStartAndEndDates($oDate);
        $operationIntervalID = OX_OperationInterval::convertDateToOperationIntervalID($aDates['start']);
        $aCumulativeZoneForecast[$operationIntervalID] = 50;
        $previousOperationIntervalId = OX_OperationInterval::previousOperationIntervalID($operationIntervalID);
        $aCumulativeZoneForecast[$previousOperationIntervalId] = 5;
        $nextOperationIntervalId = OX_OperationInterval::nextOperationIntervalID($operationIntervalID);
        $aCumulativeZoneForecast[$nextOperationIntervalId] = 7;
        $aCumulativeZoneForecast = $this->_fillForecastArray($aCumulativeZoneForecast);
        $result = $oDeliveryLimitationManager->getAdLifetimeZoneImpressionsRemaining($oDate, $oDate, $aCumulativeZoneForecast);
        $this->assertEqual($result, 50);

        // Test 3
        $oStartDate = new Date('2006-02-15 11:07:15');
        $oEndDate = new Date('2006-02-15 23:59:59');

        $aCumulativeZoneForecast = array();
        $operationIntervalID = OX_OperationInterval::convertDateToOperationIntervalID(new Date('2006-02-15 10:00:01'));
        $aCumulativeZoneForecast[$operationIntervalID] = 1;
        $operationIntervalID = OX_OperationInterval::convertDateToOperationIntervalID(new Date('2006-02-15 11:00:01'));
        $aCumulativeZoneForecast[$operationIntervalID] = 10;
        $operationIntervalID = OX_OperationInterval::convertDateToOperationIntervalID(new Date('2006-02-15 12:00:01'));
        $aCumulativeZoneForecast[$operationIntervalID] = 100;
        $operationIntervalID = OX_OperationInterval::convertDateToOperationIntervalID(new Date('2006-02-15 13:00:01'));
        $aCumulativeZoneForecast[$operationIntervalID] = 1000;
        $operationIntervalID = OX_OperationInterval::convertDateToOperationIntervalID(new Date('2006-02-15 14:00:01'));
        $aCumulativeZoneForecast[$operationIntervalID] = 10000;
        $operationIntervalID = OX_OperationInterval::convertDateToOperationIntervalID(new Date('2006-02-15 15:00:01'));
        $aCumulativeZoneForecast[$operationIntervalID] = 100000;
        $operationIntervalID = OX_OperationInterval::convertDateToOperationIntervalID(new Date('2006-02-15 16:00:01'));
        $aCumulativeZoneForecast[$operationIntervalID] = 1000000;
        $aCumulativeZoneForecast = $this->_fillForecastArray($aCumulativeZoneForecast);

        $result = $oDeliveryLimitationManager->getAdLifetimeZoneImpressionsRemaining($oStartDate, $oEndDate, $aCumulativeZoneForecast);
        $this->assertEqual($result, 1111110 + 7);

        // Test 4
        $oStartDate = new Date('2006-02-18 22:07:15');
        $oEndDate = new Date('2006-02-20 23:59:59');

        $aCumulativeZoneForecast = array();
        $operationIntervalID = OX_OperationInterval::convertDateToOperationIntervalID(new Date('2006-02-18 21:00:01'));
        $aCumulativeZoneForecast[$operationIntervalID] = 1;
        $operationIntervalID = OX_OperationInterval::convertDateToOperationIntervalID(new Date('2006-02-18 22:00:01'));
        $aCumulativeZoneForecast[$operationIntervalID] = 10;
        $operationIntervalID = OX_OperationInterval::convertDateToOperationIntervalID(new Date('2006-02-18 23:00:01'));
        $aCumulativeZoneForecast[$operationIntervalID] = 100;
        $operationIntervalID = OX_OperationInterval::convertDateToOperationIntervalID(new Date('2006-02-19 00:00:01'));
        $aCumulativeZoneForecast[$operationIntervalID] = 1000;
        $operationIntervalID = OX_OperationInterval::convertDateToOperationIntervalID(new Date('2006-02-19 01:00:01'));
        $aCumulativeZoneForecast[$operationIntervalID] = 10000;
        $operationIntervalID = OX_OperationInterval::convertDateToOperationIntervalID(new Date('2006-02-19 02:00:01'));
        $aCumulativeZoneForecast[$operationIntervalID] = 100000;
        $operationIntervalID = OX_OperationInterval::convertDateToOperationIntervalID(new Date('2006-02-19 03:00:01'));
        $aCumulativeZoneForecast[$operationIntervalID] = 1000000;
        $operationIntervalID = OX_OperationInterval::convertDateToOperationIntervalID(new Date('2006-02-19 04:00:01'));
        $aCumulativeZoneForecast[$operationIntervalID] = 10000000;
        $aCumulativeZoneForecast = $this->_fillForecastArray($aCumulativeZoneForecast);

        $result = $oDeliveryLimitationManager->getAdLifetimeZoneImpressionsRemaining($oStartDate, $oEndDate, $aCumulativeZoneForecast);
        $this->assertEqual($result, 110 + 11111000 + 19 + 24);

        // Test 5
        $oStartDate = new Date('2006-02-07 12:07:15');
        $oEndDate = new Date('2006-02-07 23:59:59');

        $aDeliveryLimitations = array(
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Hour',
                'comparison'     => '!~',
                'data'           => '23',
                'executionorder' => 0
            )
        );
        $oDeliveryLimitationManager = new OA_Maintenance_Priority_DeliveryLimitation($aDeliveryLimitations);
        $oDeliveryLimitationManager->getActiveAdOperationIntervals(12, $oStartDate, $oEndDate);

        $aCumulativeZoneForecast = array();
        $aCumulativeZoneForecast = $this->_fillForecastArray($aCumulativeZoneForecast);

        $result = $oDeliveryLimitationManager->getAdLifetimeZoneImpressionsRemaining($oStartDate, $oEndDate, $aCumulativeZoneForecast);
        $this->assertEqual($result, 11);

        // Test 6
        $oStartDate = new Date('2006-02-07 12:07:15');
        $oEndDate = new Date('2006-02-08 23:59:59');

        $aDeliveryLimitations = array(
            array(
                'ad_id'          => 1,
                'logical'        => 'and',
                'type'           => 'deliveryLimitations:Time:Hour',
                'comparison'     => '=~',
                'data'           => '22',
                'executionorder' => 0
            )
        );
        $oDeliveryLimitationManager = new OA_Maintenance_Priority_DeliveryLimitation($aDeliveryLimitations);
        $oDeliveryLimitationManager->getActiveAdOperationIntervals(12, $oStartDate, $oEndDate);

        $aCumulativeZoneForecast = array();
        $aCumulativeZoneForecast = $this->_fillForecastArray($aCumulativeZoneForecast);

        $result = $oDeliveryLimitationManager->getAdLifetimeZoneImpressionsRemaining($oStartDate, $oEndDate, $aCumulativeZoneForecast);
        $this->assertEqual($result, 2);

        TestEnv::restoreConfig();
    }

    /**
     * This MUST be the last test!!!
     *
     */
    function testCleanUp()
    {
        // Uninstall the openXDeliveryLog plugin
        TestEnv::uninstallPluginPackage('openXDeliveryLimitations', false);
    }

    /**
     * A private method to fill an array with 1 as the default forecast
     * for and operation interval that is not yet set.
     *
     * @param array $aArray
     */
    function _fillForecastArray($aArray)
    {
        $intervalsPerWeek = OX_OperationInterval::operationIntervalsPerWeek();
        for ($counter = 0; $counter < $intervalsPerWeek; $counter++) {
            if (empty($aArray[$counter])) {
                $aArray[$counter] = 1;
            }
        }
        return $aArray;
    }

}

?>
