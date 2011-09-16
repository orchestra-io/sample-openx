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
$Id: SummariseIntermediateRun.mts.test.php 37535 2009-06-05 13:04:28Z andrew.hill $
*/

require_once MAX_PATH . '/lib/OA/ServiceLocator.php';
require_once LIB_PATH . '/Dal/Maintenance/Statistics/Factory.php';
require_once LIB_PATH . '/Maintenance/Statistics.php';
require_once LIB_PATH . '/Maintenance/Statistics/Task/SummariseIntermediate.php';

/**
 * A class for testing the OX_Maintenance_Statistics_Task_MigrateBucketData class.
 *
 * @package    OpenXMaintenance
 * @subpackage TestSuite
 * @author     Andrew Hill <andrew.hill@openx.org>
 */
class Test_OX_Maintenance_Statistics_Task_MigrateBucketData extends UnitTestCase
{

    /**
     * The constructor method.
     */
    function __construct()
    {
        $this->UnitTestCase();
    }

    /**
     * A method to test the main run() method.
     */
    function testRun()
    {
        $aConf =& $GLOBALS['_MAX']['CONF'];
        $aConf['maintenance']['operationInterval'] = 60;
        $oServiceLocator =& OA_ServiceLocator::instance();

        $oFactory = new OX_Dal_Maintenance_Statistics_Factory();
        $oDalMaintenanceStatsticsClassName = $oFactory->deriveClassName();

        // Test 1: Run, with the migration required but with no plugins installed
        $oNowDate = new Date('2008-08-28 09:01:00');
        $oServiceLocator->register('now', $oNowDate);

        $oMaintenanceStatistics = new OX_Maintenance_Statistics();
        $oMaintenanceStatistics->updateIntermediate        = true;
        $oMaintenanceStatistics->oLastDateIntermediate     = new Date('2008-08-28 07:59:59');
        $oMaintenanceStatistics->oUpdateIntermediateToDate = new Date('2008-08-28 08:59:59');

        Mock::generatePartial(
            $oDalMaintenanceStatsticsClassName,
            'MockOX_Dal_Maintenance_Statistics_Test_1',
            array(
                'summariseBucketsRaw',
                'summariseBucketsRawSupplementary',
                'summariseBucketsAggregate',
                'migrateRawRequests',
                'migrateRawImpressions',
                'migrateRawClicks'
            )
        );
        $oDal = new MockOX_Dal_Maintenance_Statistics_Test_1($this);

        $oDal->expectNever('summariseBucketsRaw');
        $oDal->expectNever('summariseBucketsRawSupplementary');
        $oDal->expectNever('summariseBucketsAggregate');
        $oDal->expectNever('migrateRawRequests');
        $oDal->expectNever('migrateRawImpressions');
        $oDal->expectNever('migrateRawClicks');

        $oDal->OX_Dal_Maintenance_Statistics();

        $oServiceLocator->register('OX_Dal_Maintenance_Statistics', $oDal);
        $oSummariseIntermediate = new OX_Maintenance_Statistics_Task_MigrateBucketData();
        $oSummariseIntermediate->run();
        $oDal =& $oServiceLocator->get('OX_Dal_Maintenance_Statistics');
        $oDal->tally();

        // Create the "application_variable" table required for installing the plugin
        $oTables =& OA_DB_Table_Core::singleton();
        $oTables->createTable('application_variable');

        // Setup the default OpenX delivery logging plugin for the next test
        TestEnv::installPluginPackage('openXDeliveryLog', false);

        // Test 2: Run, with plugins installed, but with the migration not required
        $oNowDate = new Date('2008-08-28 09:01:00');
        $oServiceLocator->register('now', $oNowDate);

        $oMaintenanceStatistics = new OX_Maintenance_Statistics();
        $oMaintenanceStatistics->updateIntermediate = false;

        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);

        Mock::generatePartial(
            $oDalMaintenanceStatsticsClassName,
            'MockOX_Dal_Maintenance_Statistics_Test_2',
            array(
                'summariseBucketsRaw',
                'summariseBucketsRawSupplementary',
                'summariseBucketsAggregate',
                'migrateRawRequests',
                'migrateRawImpressions',
                'migrateRawClicks'
            )
        );
        $oDal = new MockOX_Dal_Maintenance_Statistics_Test_2($this);

        $oDal->expectNever('summariseBucketsRaw');
        $oDal->expectNever('summariseBucketsRawSupplementary');
        $oDal->expectNever('summariseBucketsAggregate');
        $oDal->expectNever('migrateRawRequests');
        $oDal->expectNever('migrateRawImpressions');
        $oDal->expectNever('migrateRawClicks');

        $oDal->OX_Dal_Maintenance_Statistics();

        $oServiceLocator->register('OX_Dal_Maintenance_Statistics', $oDal);
        $oSummariseIntermediate = new OX_Maintenance_Statistics_Task_MigrateBucketData();
        $oSummariseIntermediate->run();
        $oDal =& $oServiceLocator->get('OX_Dal_Maintenance_Statistics');
        $oDal->tally();

        // Test 3: Run, with plugins installed and with the migration required for a single
        //         operation interval
        $oNowDate = new Date('2008-08-28 09:01:00');
        $oServiceLocator->register('now', $oNowDate);

        $oMaintenanceStatistics = new OX_Maintenance_Statistics();
        $oMaintenanceStatistics->updateIntermediate        = true;
        $oMaintenanceStatistics->oLastDateIntermediate     = new Date('2008-08-28 07:59:59');
        $oMaintenanceStatistics->oUpdateIntermediateToDate = new Date('2008-08-28 08:59:59');

        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);

        Mock::generatePartial(
            $oDalMaintenanceStatsticsClassName,
            'MockOX_Dal_Maintenance_Statistics_Test_3',
            array(
                'summariseBucketsRaw',
                'summariseBucketsRawSupplementary',
                'summariseBucketsAggregate',
                'migrateRawRequests',
                'migrateRawImpressions',
                'migrateRawClicks'
            )
        );
        $oDal = new MockOX_Dal_Maintenance_Statistics_Test_3($this);

        $oComponent =& OX_Component::factory('deliveryLog', 'oxLogConversion', 'logConversion');
        $oStartDate = new Date('2008-08-28 07:59:59');
        $oStartDate->addSeconds(1);
        $oEndDate = new Date('2008-08-28 09:00:00');
        $oEndDate->subtractSeconds(1);
        $oDal->expectOnce(
            'summariseBucketsRaw',
            array(
                $aConf['table']['prefix'] . 'data_intermediate_ad_connection',
                $oComponent->getStatisticsMigration(),
                array(
                    'start' => $oStartDate,
                    'end'   => $oEndDate
                )
            )
        );

        $oComponent =& OX_Component::factory('deliveryLog', 'oxLogConversion', 'logConversionVariable');
        $oStartDate = new Date('2008-08-28 07:59:59');
        $oStartDate->addSeconds(1);
        $oEndDate = new Date('2008-08-28 09:00:00');
        $oEndDate->subtractSeconds(1);
        $oDal->expectOnce(
            'summariseBucketsRawSupplementary',
            array(
                $aConf['table']['prefix'] . 'data_intermediate_ad_variable_value',
                $oComponent->getStatisticsMigration(),
                array(
                    'start' => $oStartDate,
                    'end'   => $oEndDate
                )
            )
        );

        $aMap = array();
        $oComponent =& OX_Component::factory('deliveryLog', 'oxLogClick', 'logClick');
        $aMap[get_class($oComponent)] = $oComponent->getStatisticsMigration();
        $oComponent =& OX_Component::factory('deliveryLog', 'oxLogImpression', 'logImpression');
        $aMap[get_class($oComponent)] = $oComponent->getStatisticsMigration();
        $oComponent =& OX_Component::factory('deliveryLog', 'oxLogRequest', 'logRequest');
        $aMap[get_class($oComponent)] = $oComponent->getStatisticsMigration();
        $oStartDate = new Date('2008-08-28 07:59:59');
        $oStartDate->addSeconds(1);
        $oEndDate = new Date('2008-08-28 09:00:00');
        $oEndDate->subtractSeconds(1);
        $oDal->expectOnce(
            'summariseBucketsAggregate',
            array(
                $aConf['table']['prefix'] . 'data_intermediate_ad',
                $aMap,
                array(
                    'start' => $oStartDate,
                    'end'   => $oEndDate
                ),
                array(
                    'operation_interval'    => '60',
                    'operation_interval_id' => OX_OperationInterval::convertDateToOperationIntervalID($oStartDate),
                    'interval_start'        => "'2008-08-28 08:00:00'",
                    'interval_end'          => "'2008-08-28 08:59:59'",
                    'creative_id'           => 0,
                    'updated'               => "'2008-08-28 09:01:00'"
                )
            )
        );

        $oDal->expectNever('migrateRawRequests');
        $oDal->expectNever('migrateRawImpressions');
        $oDal->expectNever('migrateRawClicks');

        $oDal->OX_Dal_Maintenance_Statistics();

        $oServiceLocator->register('OX_Dal_Maintenance_Statistics', $oDal);
        $oSummariseIntermediate = new OX_Maintenance_Statistics_Task_MigrateBucketData();
        $oSummariseIntermediate->run();
        $oDal =& $oServiceLocator->get('OX_Dal_Maintenance_Statistics');
        $oDal->tally();

        // Test 4: Run, with plugins installed and with the migration required for a single
        //         operation interval + migration of raw data set to occur
        $doApplication_variable = OA_Dal::factoryDO('application_variable');
        $doApplication_variable->name  = 'mse_process_raw';
        $doApplication_variable->value = '1';
        $doApplication_variable->insert();

        $oNowDate = new Date('2008-08-28 09:01:00');
        $oServiceLocator->register('now', $oNowDate);

        $oMaintenanceStatistics = new OX_Maintenance_Statistics();
        $oMaintenanceStatistics->updateIntermediate        = true;
        $oMaintenanceStatistics->oLastDateIntermediate     = new Date('2008-08-28 07:59:59');
        $oMaintenanceStatistics->oUpdateIntermediateToDate = new Date('2008-08-28 08:59:59');

        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);

        Mock::generatePartial(
            $oDalMaintenanceStatsticsClassName,
            'MockOX_Dal_Maintenance_Statistics_Test_4',
            array(
                'summariseBucketsRaw',
                'summariseBucketsRawSupplementary',
                'summariseBucketsAggregate',
                'migrateRawRequests',
                'migrateRawImpressions',
                'migrateRawClicks'
            )
        );
        $oDal = new MockOX_Dal_Maintenance_Statistics_Test_4($this);

        $oComponent =& OX_Component::factory('deliveryLog', 'oxLogConversion', 'logConversion');
        $oStartDate = new Date('2008-08-28 07:59:59');
        $oStartDate->addSeconds(1);
        $oEndDate = new Date('2008-08-28 09:00:00');
        $oEndDate->subtractSeconds(1);
        $oDal->expectOnce(
            'summariseBucketsRaw',
            array(
                $aConf['table']['prefix'] . 'data_intermediate_ad_connection',
                $oComponent->getStatisticsMigration(),
                array(
                    'start' => $oStartDate,
                    'end'   => $oEndDate
                )
            )
        );

        $oComponent =& OX_Component::factory('deliveryLog', 'oxLogConversion', 'logConversionVariable');
        $oStartDate = new Date('2008-08-28 07:59:59');
        $oStartDate->addSeconds(1);
        $oEndDate = new Date('2008-08-28 09:00:00');
        $oEndDate->subtractSeconds(1);
        $oDal->expectOnce(
            'summariseBucketsRawSupplementary',
            array(
                $aConf['table']['prefix'] . 'data_intermediate_ad_variable_value',
                $oComponent->getStatisticsMigration(),
                array(
                    'start' => $oStartDate,
                    'end'   => $oEndDate
                )
            )
        );

        $aMap = array();
        $oComponent =& OX_Component::factory('deliveryLog', 'oxLogClick', 'logClick');
        $aMap[get_class($oComponent)] = $oComponent->getStatisticsMigration();
        $oComponent =& OX_Component::factory('deliveryLog', 'oxLogImpression', 'logImpression');
        $aMap[get_class($oComponent)] = $oComponent->getStatisticsMigration();
        $oComponent =& OX_Component::factory('deliveryLog', 'oxLogRequest', 'logRequest');
        $aMap[get_class($oComponent)] = $oComponent->getStatisticsMigration();
        $oStartDate = new Date('2008-08-28 07:59:59');
        $oStartDate->addSeconds(1);
        $oEndDate = new Date('2008-08-28 09:00:00');
        $oEndDate->subtractSeconds(1);
        $oDal->expectOnce(
            'summariseBucketsAggregate',
            array(
                $aConf['table']['prefix'] . 'data_intermediate_ad',
                $aMap,
                array(
                    'start' => $oStartDate,
                    'end'   => $oEndDate
                ),
                array(
                    'operation_interval'    => '60',
                    'operation_interval_id' => OX_OperationInterval::convertDateToOperationIntervalID($oStartDate),
                    'interval_start'        => "'2008-08-28 08:00:00'",
                    'interval_end'          => "'2008-08-28 08:59:59'",
                    'creative_id'           => 0,
                    'updated'               => "'2008-08-28 09:01:00'"
                )
            )
        );

        $oDal->expectOnce(
            'migrateRawRequests',
            array(
                $oStartDate,
                $oEndDate
            )
        );

        $oDal->expectOnce(
            'migrateRawImpressions',
            array(
                $oStartDate,
                $oEndDate
            )
        );

        $oDal->expectOnce(
            'migrateRawClicks',
            array(
                $oStartDate,
                $oEndDate
            )
        );

        $oDal->OX_Dal_Maintenance_Statistics();

        $oServiceLocator->register('OX_Dal_Maintenance_Statistics', $oDal);
        $oSummariseIntermediate = new OX_Maintenance_Statistics_Task_MigrateBucketData();
        $oSummariseIntermediate->run();
        $oDal =& $oServiceLocator->get('OX_Dal_Maintenance_Statistics');
        $oDal->tally();

        $doApplication_variable = OA_Dal::factoryDO('application_variable');
        $doApplication_variable->name  = 'mse_process_raw';
        $doApplication_variable->value = '1';
        $doApplication_variable->find();
        $rows = $doApplication_variable->getRowCount();
        $this->assertEqual($rows, 0);

        // Test 5: Run, with plugins installed and with the migration required for multiple
        //         operation intervals
        $oNowDate = new Date('2008-08-28 11:01:00');
        $oServiceLocator->register('now', $oNowDate);

        $oMaintenanceStatistics = new OX_Maintenance_Statistics();
        $oMaintenanceStatistics->updateIntermediate        = true;
        $oMaintenanceStatistics->oLastDateIntermediate     = new Date('2008-08-28 07:59:59');
        $oMaintenanceStatistics->oUpdateIntermediateToDate = new Date('2008-08-28 10:59:59');
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);

        Mock::generatePartial(
            $oDalMaintenanceStatsticsClassName,
            'MockOX_Dal_Maintenance_Statistics_Test_5',
            array(
                'summariseBucketsRaw',
                'summariseBucketsRawSupplementary',
                'summariseBucketsAggregate',
                'migrateRawRequests',
                'migrateRawImpressions',
                'migrateRawClicks'
            )
        );
        $oDal = new MockOX_Dal_Maintenance_Statistics_Test_5($this);

        $oDal->expectCallCount('summariseBucketsRaw', 3);
        $oDal->expectCallCount('summariseBucketsRawSupplementary', 3);
        $oDal->expectCallCount('summariseBucketsAggregate', 3);

        $oComponent =& OX_Component::factory('deliveryLog', 'oxLogConversion', 'logConversion');
        $oStartDate = new Date('2008-08-28 07:59:59');
        $oStartDate->addSeconds(1);
        $oEndDate = new Date('2008-08-28 09:00:00');
        $oEndDate->subtractSeconds(1);
        $oDal->expectAt(
            0,
            'summariseBucketsRaw',
            array(
                $aConf['table']['prefix'] . 'data_intermediate_ad_connection',
                $oComponent->getStatisticsMigration(),
                array(
                    'start' => $oStartDate,
                    'end'   => $oEndDate
                )
            )
        );
        $oStartDate = new Date('2008-08-28 08:59:59');
        $oStartDate->addSeconds(1);
        $oEndDate = new Date('2008-08-28 10:00:00');
        $oEndDate->subtractSeconds(1);
        $oDal->expectAt(
            1,
            'summariseBucketsRaw',
            array(
                $aConf['table']['prefix'] . 'data_intermediate_ad_connection',
                $oComponent->getStatisticsMigration(),
                array(
                    'start' => $oStartDate,
                    'end'   => $oEndDate
                )
            )
        );
        $oStartDate = new Date('2008-08-28 09:59:59');
        $oStartDate->addSeconds(1);
        $oEndDate = new Date('2008-08-28 11:00:00');
        $oEndDate->subtractSeconds(1);
        $oDal->expectAt(
            2,
            'summariseBucketsRaw',
            array(
                $aConf['table']['prefix'] . 'data_intermediate_ad_connection',
                $oComponent->getStatisticsMigration(),
                array(
                    'start' => $oStartDate,
                    'end'   => $oEndDate
                )
            )
        );

        $oComponent =& OX_Component::factory('deliveryLog', 'oxLogConversion', 'logConversionVariable');
        $oStartDate = new Date('2008-08-28 07:59:59');
        $oStartDate->addSeconds(1);
        $oEndDate = new Date('2008-08-28 09:00:00');
        $oEndDate->subtractSeconds(1);
        $oDal->expectAt(
            0,
            'summariseBucketsRawSupplementary',
            array(
                $aConf['table']['prefix'] . 'data_intermediate_ad_variable_value',
                $oComponent->getStatisticsMigration(),
                array(
                    'start' => $oStartDate,
                    'end'   => $oEndDate
                )
            )
        );
        $oStartDate = new Date('2008-08-28 08:59:59');
        $oStartDate->addSeconds(1);
        $oEndDate = new Date('2008-08-28 10:00:00');
        $oEndDate->subtractSeconds(1);
        $oDal->expectAt(
            1,
            'summariseBucketsRawSupplementary',
            array(
                $aConf['table']['prefix'] . 'data_intermediate_ad_variable_value',
                $oComponent->getStatisticsMigration(),
                array(
                    'start' => $oStartDate,
                    'end'   => $oEndDate
                )
            )
        );
        $oStartDate = new Date('2008-08-28 09:59:59');
        $oStartDate->addSeconds(1);
        $oEndDate = new Date('2008-08-28 11:00:00');
        $oEndDate->subtractSeconds(1);
        $oDal->expectAt(
            2,
            'summariseBucketsRawSupplementary',
            array(
                $aConf['table']['prefix'] . 'data_intermediate_ad_variable_value',
                $oComponent->getStatisticsMigration(),
                array(
                    'start' => $oStartDate,
                    'end'   => $oEndDate
                )
            )
        );

        $aMap = array();
        $oComponent =& OX_Component::factory('deliveryLog', 'oxLogClick', 'logClick');
        $aMap[get_class($oComponent)] = $oComponent->getStatisticsMigration();
        $oComponent =& OX_Component::factory('deliveryLog', 'oxLogImpression', 'logImpression');
        $aMap[get_class($oComponent)] = $oComponent->getStatisticsMigration();
        $oComponent =& OX_Component::factory('deliveryLog', 'oxLogRequest', 'logRequest');
        $aMap[get_class($oComponent)] = $oComponent->getStatisticsMigration();
        $oStartDate = new Date('2008-08-28 07:59:59');
        $oStartDate->addSeconds(1);
        $oEndDate = new Date('2008-08-28 09:00:00');
        $oEndDate->subtractSeconds(1);
        $oDal->expectAt(
            0,
            'summariseBucketsAggregate',
            array(
                $aConf['table']['prefix'] . 'data_intermediate_ad',
                $aMap,
                array(
                    'start' => $oStartDate,
                    'end'   => $oEndDate
                ),
                array(
                    'operation_interval'    => '60',
                    'operation_interval_id' => OX_OperationInterval::convertDateToOperationIntervalID($oStartDate),
                    'interval_start'        => "'2008-08-28 08:00:00'",
                    'interval_end'          => "'2008-08-28 08:59:59'",
                    'creative_id'           => 0,
                    'updated'               => "'2008-08-28 11:01:00'"
                )
            )
        );
        $oStartDate = new Date('2008-08-28 08:59:59');
        $oStartDate->addSeconds(1);
        $oEndDate = new Date('2008-08-28 10:00:00');
        $oEndDate->subtractSeconds(1);
        $oDal->expectAt(
            1,
            'summariseBucketsAggregate',
            array(
                $aConf['table']['prefix'] . 'data_intermediate_ad',
                $aMap,
                array(
                    'start' => $oStartDate,
                    'end'   => $oEndDate
                ),
                array(
                    'operation_interval'    => '60',
                    'operation_interval_id' => OX_OperationInterval::convertDateToOperationIntervalID($oStartDate),
                    'interval_start'        => "'2008-08-28 09:00:00'",
                    'interval_end'          => "'2008-08-28 09:59:59'",
                    'creative_id'           => 0,
                    'updated'               => "'2008-08-28 11:01:00'"
                )
            )
        );
        $oStartDate = new Date('2008-08-28 09:59:59');
        $oStartDate->addSeconds(1);
        $oEndDate = new Date('2008-08-28 11:00:00');
        $oEndDate->subtractSeconds(1);
        $oDal->expectAt(
            2,
            'summariseBucketsAggregate',
            array(
                $aConf['table']['prefix'] . 'data_intermediate_ad',
                $aMap,
                array(
                    'start' => $oStartDate,
                    'end'   => $oEndDate
                ),
                array(
                    'operation_interval'    => '60',
                    'operation_interval_id' => OX_OperationInterval::convertDateToOperationIntervalID($oStartDate),
                    'interval_start'        => "'2008-08-28 10:00:00'",
                    'interval_end'          => "'2008-08-28 10:59:59'",
                    'creative_id'           => 0,
                    'updated'               => "'2008-08-28 11:01:00'"
                )
            )
        );

        $oDal->expectNever('migrateRawRequests');
        $oDal->expectNever('migrateRawImpressions');
        $oDal->expectNever('migrateRawClicks');

        $oDal->OX_Dal_Maintenance_Statistics();

        $oServiceLocator->register('OX_Dal_Maintenance_Statistics', $oDal);
        $oSummariseIntermediate = new OX_Maintenance_Statistics_Task_MigrateBucketData();
        $oSummariseIntermediate->run();
        $oDal =& $oServiceLocator->get('OX_Dal_Maintenance_Statistics');
        $oDal->tally();

        // Test 6: Run, with plugins installed and with the migration required for multiple
        //         operation intervals + migration of raw data set to occur
        $doApplication_variable = OA_Dal::factoryDO('application_variable');
        $doApplication_variable->name  = 'mse_process_raw';
        $doApplication_variable->value = '1';
        $doApplication_variable->insert();

        $oNowDate = new Date('2008-08-28 11:01:00');
        $oServiceLocator->register('now', $oNowDate);

        $oMaintenanceStatistics = new OX_Maintenance_Statistics();
        $oMaintenanceStatistics->updateIntermediate        = true;
        $oMaintenanceStatistics->oLastDateIntermediate     = new Date('2008-08-28 07:59:59');
        $oMaintenanceStatistics->oUpdateIntermediateToDate = new Date('2008-08-28 10:59:59');
        $oServiceLocator->register('Maintenance_Statistics_Controller', $oMaintenanceStatistics);

        Mock::generatePartial(
            $oDalMaintenanceStatsticsClassName,
            'MockOX_Dal_Maintenance_Statistics_Test_6',
            array(
                'summariseBucketsRaw',
                'summariseBucketsRawSupplementary',
                'summariseBucketsAggregate',
                'migrateRawRequests',
                'migrateRawImpressions',
                'migrateRawClicks'
            )
        );
        $oDal = new MockOX_Dal_Maintenance_Statistics_Test_6($this);

        $oDal->expectCallCount('summariseBucketsRaw', 3);
        $oDal->expectCallCount('summariseBucketsRawSupplementary', 3);
        $oDal->expectCallCount('summariseBucketsAggregate', 3);

        $oComponent =& OX_Component::factory('deliveryLog', 'oxLogConversion', 'logConversion');
        $oStartDate = new Date('2008-08-28 07:59:59');
        $oStartDate->addSeconds(1);
        $oEndDate = new Date('2008-08-28 09:00:00');
        $oEndDate->subtractSeconds(1);
        $oDal->expectAt(
            0,
            'summariseBucketsRaw',
            array(
                $aConf['table']['prefix'] . 'data_intermediate_ad_connection',
                $oComponent->getStatisticsMigration(),
                array(
                    'start' => $oStartDate,
                    'end'   => $oEndDate
                )
            )
        );
        $oStartDate = new Date('2008-08-28 08:59:59');
        $oStartDate->addSeconds(1);
        $oEndDate = new Date('2008-08-28 10:00:00');
        $oEndDate->subtractSeconds(1);
        $oDal->expectAt(
            1,
            'summariseBucketsRaw',
            array(
                $aConf['table']['prefix'] . 'data_intermediate_ad_connection',
                $oComponent->getStatisticsMigration(),
                array(
                    'start' => $oStartDate,
                    'end'   => $oEndDate
                )
            )
        );
        $oStartDate = new Date('2008-08-28 09:59:59');
        $oStartDate->addSeconds(1);
        $oEndDate = new Date('2008-08-28 11:00:00');
        $oEndDate->subtractSeconds(1);
        $oDal->expectAt(
            2,
            'summariseBucketsRaw',
            array(
                $aConf['table']['prefix'] . 'data_intermediate_ad_connection',
                $oComponent->getStatisticsMigration(),
                array(
                    'start' => $oStartDate,
                    'end'   => $oEndDate
                )
            )
        );

        $oComponent =& OX_Component::factory('deliveryLog', 'oxLogConversion', 'logConversionVariable');
        $oStartDate = new Date('2008-08-28 07:59:59');
        $oStartDate->addSeconds(1);
        $oEndDate = new Date('2008-08-28 09:00:00');
        $oEndDate->subtractSeconds(1);
        $oDal->expectAt(
            0,
            'summariseBucketsRawSupplementary',
            array(
                $aConf['table']['prefix'] . 'data_intermediate_ad_variable_value',
                $oComponent->getStatisticsMigration(),
                array(
                    'start' => $oStartDate,
                    'end'   => $oEndDate
                )
            )
        );
        $oStartDate = new Date('2008-08-28 08:59:59');
        $oStartDate->addSeconds(1);
        $oEndDate = new Date('2008-08-28 10:00:00');
        $oEndDate->subtractSeconds(1);
        $oDal->expectAt(
            1,
            'summariseBucketsRawSupplementary',
            array(
                $aConf['table']['prefix'] . 'data_intermediate_ad_variable_value',
                $oComponent->getStatisticsMigration(),
                array(
                    'start' => $oStartDate,
                    'end'   => $oEndDate
                )
            )
        );
        $oStartDate = new Date('2008-08-28 09:59:59');
        $oStartDate->addSeconds(1);
        $oEndDate = new Date('2008-08-28 11:00:00');
        $oEndDate->subtractSeconds(1);
        $oDal->expectAt(
            2,
            'summariseBucketsRawSupplementary',
            array(
                $aConf['table']['prefix'] . 'data_intermediate_ad_variable_value',
                $oComponent->getStatisticsMigration(),
                array(
                    'start' => $oStartDate,
                    'end'   => $oEndDate
                )
            )
        );

        $aMap = array();
        $oComponent =& OX_Component::factory('deliveryLog', 'oxLogClick', 'logClick');
        $aMap[get_class($oComponent)] = $oComponent->getStatisticsMigration();
        $oComponent =& OX_Component::factory('deliveryLog', 'oxLogImpression', 'logImpression');
        $aMap[get_class($oComponent)] = $oComponent->getStatisticsMigration();
        $oComponent =& OX_Component::factory('deliveryLog', 'oxLogRequest', 'logRequest');
        $aMap[get_class($oComponent)] = $oComponent->getStatisticsMigration();
        $oStartDate = new Date('2008-08-28 07:59:59');
        $oStartDate->addSeconds(1);
        $oEndDate = new Date('2008-08-28 09:00:00');
        $oEndDate->subtractSeconds(1);
        $oDal->expectAt(
            0,
            'summariseBucketsAggregate',
            array(
                $aConf['table']['prefix'] . 'data_intermediate_ad',
                $aMap,
                array(
                    'start' => $oStartDate,
                    'end'   => $oEndDate
                ),
                array(
                    'operation_interval'    => '60',
                    'operation_interval_id' => OX_OperationInterval::convertDateToOperationIntervalID($oStartDate),
                    'interval_start'        => "'2008-08-28 08:00:00'",
                    'interval_end'          => "'2008-08-28 08:59:59'",
                    'creative_id'           => 0,
                    'updated'               => "'2008-08-28 11:01:00'"
                )
            )
        );
        $oStartDate = new Date('2008-08-28 08:59:59');
        $oStartDate->addSeconds(1);
        $oEndDate = new Date('2008-08-28 10:00:00');
        $oEndDate->subtractSeconds(1);
        $oDal->expectAt(
            1,
            'summariseBucketsAggregate',
            array(
                $aConf['table']['prefix'] . 'data_intermediate_ad',
                $aMap,
                array(
                    'start' => $oStartDate,
                    'end'   => $oEndDate
                ),
                array(
                    'operation_interval'    => '60',
                    'operation_interval_id' => OX_OperationInterval::convertDateToOperationIntervalID($oStartDate),
                    'interval_start'        => "'2008-08-28 09:00:00'",
                    'interval_end'          => "'2008-08-28 09:59:59'",
                    'creative_id'           => 0,
                    'updated'               => "'2008-08-28 11:01:00'"
                )
            )
        );
        $oStartDate = new Date('2008-08-28 09:59:59');
        $oStartDate->addSeconds(1);
        $oEndDate = new Date('2008-08-28 11:00:00');
        $oEndDate->subtractSeconds(1);
        $oDal->expectAt(
            2,
            'summariseBucketsAggregate',
            array(
                $aConf['table']['prefix'] . 'data_intermediate_ad',
                $aMap,
                array(
                    'start' => $oStartDate,
                    'end'   => $oEndDate
                ),
                array(
                    'operation_interval'    => '60',
                    'operation_interval_id' => OX_OperationInterval::convertDateToOperationIntervalID($oStartDate),
                    'interval_start'        => "'2008-08-28 10:00:00'",
                    'interval_end'          => "'2008-08-28 10:59:59'",
                    'creative_id'           => 0,
                    'updated'               => "'2008-08-28 11:01:00'"
                )
            )
        );

        $oStartDate = new Date('2008-08-28 07:59:59');
        $oStartDate->addSeconds(1);
        $oEndDate = new Date('2008-08-28 09:00:00');
        $oEndDate->subtractSeconds(1);
        $oDal->expectAt(
            0,
            'migrateRawRequests',
            array(
                $oStartDate,
                $oEndDate
            )
        );
        $oDal->expectAt(
            0,
            'migrateRawImpressions',
            array(
                $oStartDate,
                $oEndDate
            )
        );
        $oDal->expectAt(
            0,
            'migrateRawClicks',
            array(
                $oStartDate,
                $oEndDate
            )
        );

        $oStartDate = new Date('2008-08-28 08:59:59');
        $oStartDate->addSeconds(1);
        $oEndDate = new Date('2008-08-28 10:00:00');
        $oEndDate->subtractSeconds(1);
        $oDal->expectAt(
            1,
            'migrateRawRequests',
            array(
                $oStartDate,
                $oEndDate
            )
        );
        $oDal->expectAt(
            1,
            'migrateRawImpressions',
            array(
                $oStartDate,
                $oEndDate
            )
        );
        $oDal->expectAt(
            1,
            'migrateRawClicks',
            array(
                $oStartDate,
                $oEndDate
            )
        );

        $oStartDate = new Date('2008-08-28 09:59:59');
        $oStartDate->addSeconds(1);
        $oEndDate = new Date('2008-08-28 11:00:00');
        $oEndDate->subtractSeconds(1);
        $oDal->expectAt(
            2,
            'migrateRawRequests',
            array(
                $oStartDate,
                $oEndDate
            )
        );
        $oDal->expectAt(
            2,
            'migrateRawImpressions',
            array(
                $oStartDate,
                $oEndDate
            )
        );
        $oDal->expectAt(
            2,
            'migrateRawClicks',
            array(
                $oStartDate,
                $oEndDate
            )
        );

        $oDal->OX_Dal_Maintenance_Statistics();

        $oServiceLocator->register('OX_Dal_Maintenance_Statistics', $oDal);
        $oSummariseIntermediate = new OX_Maintenance_Statistics_Task_MigrateBucketData();
        $oSummariseIntermediate->run();
        $oDal =& $oServiceLocator->get('OX_Dal_Maintenance_Statistics');
        $oDal->tally();

        $doApplication_variable = OA_Dal::factoryDO('application_variable');
        $doApplication_variable->name  = 'mse_process_raw';
        $doApplication_variable->value = '1';
        $doApplication_variable->find();
        $rows = $doApplication_variable->getRowCount();
        $this->assertEqual($rows, 0);

        // Uninstall the installed plugins
        TestEnv::uninstallPluginPackage('openXDeliveryLog', false);

        // Reset the testing environment
        TestEnv::restoreEnv();
    }

}

?>
