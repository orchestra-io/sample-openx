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
$Id:$
*/

require_once MAX_PATH . '/lib/OA/Dal/Statistics/Campaign.php';
require_once MAX_PATH . '/lib/OA/Dal/Statistics/tests/util/DalStatisticsUnitTestCase.php';

/**
 * A class for testing DAL Statistic Advertiser methods
 *
 * @package    OpenXDal
 * @subpackage TestSuite
 * @author     Andriy Petlyovanyy <apetlyovanyy@lohika.com>
 *
 */


class OA_Dal_Statistics_CampaignTest extends DalStatisticsUnitTestCase
{
    /**
     * Object for generate Campaign Statistics.
     *
     * @var OA_Dal_Statistics_Campaign $_dalCampaignStatistics
     */
    var $_dalCampaignStatistics;

    /**
     * The constructor method.
     */
    function OA_Dal_Statistics_CampaignTest()
    {
        $this->UnitTestCase();
    }

    function setUp()
    {
        $this->_dalCampaignStatistics = new OA_Dal_Statistics_Campaign();
    }

    function tearDown()
    {
        DataGenerator::cleanUp();
    }

    /**
     * Test campaign daily statistics.
     *
     */
    function testGetCampaignDailyStatistics()
    {
        $doAgency     = OA_Dal::factoryDO('agency');
        $doAdvertiser = OA_Dal::factoryDO('clients');
        $doCampaign   = OA_Dal::factoryDO('campaigns');
        $doBanner     = OA_Dal::factoryDO('banners');
        $this->generateBannerWithParents($doAgency, $doAdvertiser, $doCampaign, $doBanner);

        $doDataSummaryAdHourly                = OA_Dal::factoryDO('data_summary_ad_hourly');
        $doDataSummaryAdHourly->impressions   = 1;
        $doDataSummaryAdHourly->requests      = 2;
        $doDataSummaryAdHourly->total_revenue = 3;
        $doDataSummaryAdHourly->clicks        = 4;
        $doDataSummaryAdHourly->date_time     = '2007-08-08';
        $this->generateDataSummaryAdHourlyForBanner($doDataSummaryAdHourly, $doBanner);

        $doDataSummaryAdHourly                = OA_Dal::factoryDO('data_summary_ad_hourly');
        $doDataSummaryAdHourly->impressions   = 11;
        $doDataSummaryAdHourly->requests      = 12;
        $doDataSummaryAdHourly->total_revenue = 13;
        $doDataSummaryAdHourly->clicks        = 14;
        $doDataSummaryAdHourly->date_time     = '2007-08-08';
        $this->generateDataSummaryAdHourlyForBanner($doDataSummaryAdHourly, $doBanner);

        $doDataSummaryAdHourly                = OA_Dal::factoryDO('data_summary_ad_hourly');
        $doDataSummaryAdHourly->impressions   = 1;
        $doDataSummaryAdHourly->requests      = 0;
        $doDataSummaryAdHourly->total_revenue = 0;
        $doDataSummaryAdHourly->clicks        = 0;
        $doDataSummaryAdHourly->date_time     = '2007-08-12';
        $dayForRecord2                        = $doDataSummaryAdHourly->date_time;
        $this->generateDataSummaryAdHourlyForBanner($doDataSummaryAdHourly, $doBanner);

        // 1. Get data existing range
        $aData = $this->_dalCampaignStatistics->getCampaignDailyStatistics(
            $doCampaign->campaignid, new Date('2007-08-01'),  new Date('2007-08-20'));

        $this->assertEqual(count($aData), 2, '2 records should be returned');
        $aRow1 = current($aData);
        $aRow2 = next($aData);

        $this->ensureRowSequence($aRow2, $aRow1, 'day', $dayForRecord2);

        // 2. Check return fields names
        $this->assertFieldExists($aRow1, 'day');
        $this->assertFieldExists($aRow1, 'requests');
        $this->assertFieldExists($aRow1, 'impressions');
        $this->assertFieldExists($aRow1, 'clicks');
        $this->assertFieldExists($aRow1, 'revenue');

        // 3. Check return fields value
        $this->assertFieldEqual($aRow1, 'impressions', 12);
        $this->assertFieldEqual($aRow1, 'requests', 14);
        $this->assertFieldEqual($aRow1, 'revenue', 16);
        $this->assertFieldEqual($aRow1, 'clicks', 18);
        $this->assertFieldEqual($aRow2, 'requests', 0);
        $this->assertFieldEqual($aRow2, 'day', $dayForRecord2);

        // 4. Get data in not existing range
        $aData = $this->_dalCampaignStatistics->getCampaignDailyStatistics(
            $doCampaign->campaignid, new Date('2007-01-01'),  new Date('2007-01-20'));

        $this->assertEqual(count($aData), 0, 'Recordset should be empty');
    }

    /**
     * Test campaign banner statistics.
     *
     */
    function testGetCampaignBannerStatistics()
    {
        $doAgency     = OA_Dal::factoryDO('agency');
        $doAdvertiser = OA_Dal::factoryDO('clients');
        $doCampaign   = OA_Dal::factoryDO('campaigns');
        $doBanner1    = OA_Dal::factoryDO('banners');

        $doAdvertiser->clientname = "Advertiser name";
        $doCampaign->campaignname = "Campaign Name";
        $doBanner1->description   = "Banner Name 1";
        $this->generateBannerWithParents($doAgency, $doAdvertiser, $doCampaign, $doBanner1);

        $doBanner2              = OA_Dal::factoryDO('banners');
        $doBanner2->description = "Banner name 2";
        $this->generateBannerForCampaign($doCampaign, $doBanner2);

        $doDataSummaryAdHourly                = OA_Dal::factoryDO('data_summary_ad_hourly');
        $doDataSummaryAdHourly->impressions   = 1;
        $doDataSummaryAdHourly->requests      = 0;
        $doDataSummaryAdHourly->total_revenue = 0;
        $doDataSummaryAdHourly->clicks        = 0;
        $doDataSummaryAdHourly->date_time     = '2007-01-01';
        $this->generateDataSummaryAdHourlyForBanner($doDataSummaryAdHourly, $doBanner1);

        $doDataSummaryAdHourly->impressions   = 0;
        $doDataSummaryAdHourly->requests      = 4;
        $doDataSummaryAdHourly->total_revenue = 6;
        $doDataSummaryAdHourly->clicks        = 7;
        $doDataSummaryAdHourly->date_time     = '2007-02-01';
        $this->generateDataSummaryAdHourlyForBanner($doDataSummaryAdHourly, $doBanner1);

        $doDataSummaryAdHourly->impressions   = 0;
        $doDataSummaryAdHourly->requests      = 16;
        $doDataSummaryAdHourly->total_revenue = 4;
        $doDataSummaryAdHourly->clicks        = 33;
        $doDataSummaryAdHourly->date_time     = '2007-04-01';
        $this->generateDataSummaryAdHourlyForBanner($doDataSummaryAdHourly, $doBanner2);

        // 1. Get data existing range
        $rsCampaignStatistics = $this->_dalCampaignStatistics->getCampaignBannerStatistics(
            $doCampaign->campaignid, new Date('2006-07-07'),  new Date('2007-09-12'));

        $rsCampaignStatistics->find();
        $this->assertTrue($rsCampaignStatistics->getRowCount() == 2,
            '2 records should be returned');

        $rsCampaignStatistics->fetch();
        $aRow1 = $rsCampaignStatistics->toArray();

        $rsCampaignStatistics->fetch();
        $aRow2 = $rsCampaignStatistics->toArray();

        $this->ensureRowSequence($aRow1, $aRow2, 'bannerid', $doBanner1->bannerid);

        // 2. Check return fields names
        $this->assertFieldExists($aRow1, 'campaignid');
        $this->assertFieldExists($aRow1, 'campaignname');
        $this->assertFieldExists($aRow1, 'bannerid');
        $this->assertFieldExists($aRow1, 'bannername');
        $this->assertFieldExists($aRow1, 'requests');
        $this->assertFieldExists($aRow1, 'impressions');
        $this->assertFieldExists($aRow1, 'clicks');
        $this->assertFieldExists($aRow1, 'revenue');

        // 3. Check return fields value
        $this->assertFieldEqual($aRow1, 'impressions', 1);
        $this->assertFieldEqual($aRow1, 'requests', 4);
        $this->assertFieldEqual($aRow1, 'revenue', 6);
        $this->assertFieldEqual($aRow1, 'clicks', 7);
        $this->assertFieldEqual($aRow1, 'campaignname', $doCampaign->campaignname);
        $this->assertFieldEqual($aRow1, 'bannername', $doBanner1->description);

        $this->assertFieldEqual($aRow2, 'impressions', 0);
        $this->assertFieldEqual($aRow2, 'requests', 16);
        $this->assertFieldEqual($aRow2, 'revenue', 4);
        $this->assertFieldEqual($aRow2, 'clicks', 33);
        $this->assertFieldEqual($aRow2, 'campaignname', $doCampaign->campaignname);

        // 4. Get data in not existing range
        $rsCampaignStatistics = $this->_dalCampaignStatistics->getCampaignBannerStatistics(
            $doCampaign->campaignid, new Date('2001-07-07'),  new Date('2001-09-12'));

        $rsCampaignStatistics->find();
        $this->assertTrue($rsCampaignStatistics->getRowCount() == 0,
            'Recordset should be empty');

    }

    /**
     * Test campaign publisher statistics.
     *
     */
    function testGetCampaignPublisherStatistics()
    {
        $doAgency     = OA_Dal::factoryDO('agency');
        $doAdvertiser = OA_Dal::factoryDO('clients');
        $doCampaign   = OA_Dal::factoryDO('campaigns');
        $doBanner     = OA_Dal::factoryDO('banners');
        $this->generateBannerWithParents($doAgency, $doAdvertiser, $doCampaign, $doBanner);

        $doAgency           = OA_Dal::factoryDO('agency');
        $doPublisher1       = OA_Dal::factoryDO('affiliates');
        $doZone1            = OA_Dal::factoryDO('zones');
        $doPublisher1->name = "Test publisher name 1";
        $this->generateZoneWithParents($doAgency, $doPublisher1, $doZone1);

        $doZone2           = OA_Dal::factoryDO('zones');
        $this->generateZoneForPublisher($doPublisher1, $doZone2);

        $doAgency          = OA_Dal::factoryDO('agency');
        $doPublisher2      = OA_Dal::factoryDO('affiliates');
        $doZone3           = OA_Dal::factoryDO('zones');
        $this->generateZoneWithParents($doAgency, $doPublisher2, $doZone3);

        $doDataSummaryAdHourly                = OA_Dal::factoryDO('data_summary_ad_hourly');
        $doDataSummaryAdHourly->impressions   = 11;
        $doDataSummaryAdHourly->requests      = 22;
        $doDataSummaryAdHourly->total_revenue = 33;
        $doDataSummaryAdHourly->clicks        = 44;
        $doDataSummaryAdHourly->conversions   = 55;
        $doDataSummaryAdHourly->date_time     = '1986-04-08';
        $this->generateDataSummaryAdHourlyForBannerAndZone($doDataSummaryAdHourly, $doBanner, $doZone1);

        $doDataSummaryAdHourly                = OA_Dal::factoryDO('data_summary_ad_hourly');
        $doDataSummaryAdHourly->impressions   = 10;
        $doDataSummaryAdHourly->requests      = 20;
        $doDataSummaryAdHourly->total_revenue = 30;
        $doDataSummaryAdHourly->clicks        = 40;
        $doDataSummaryAdHourly->conversions   = 50;
        $doDataSummaryAdHourly->date_time     = '2007-09-13';
        $this->generateDataSummaryAdHourlyForBannerAndZone($doDataSummaryAdHourly, $doBanner, $doZone2);

        $doDataSummaryAdHourly                = OA_Dal::factoryDO('data_summary_ad_hourly');
        $doDataSummaryAdHourly->impressions   = 10;
        $doDataSummaryAdHourly->requests      = 20;
        $doDataSummaryAdHourly->total_revenue = 30;
        $doDataSummaryAdHourly->clicks        = 40;
        $doDataSummaryAdHourly->conversions   = 50;
        $doDataSummaryAdHourly->date_time     = '2007-09-13';
        $this->generateDataSummaryAdHourlyForBannerAndZone($doDataSummaryAdHourly, $doBanner, $doZone3);

        // 1. Get data existing range
        $rsCampaignStatistics = $this->_dalCampaignStatistics->getCampaignPublisherStatistics(
            $doCampaign->campaignid, new Date('1984-01-01'),  new Date('2007-09-18'));

        $rsCampaignStatistics->find();
        $this->assertTrue($rsCampaignStatistics->getRowCount() == 2,
            '2 records should be returned');

        $rsCampaignStatistics->fetch();
        $aRow1 = $rsCampaignStatistics->toArray();
        $rsCampaignStatistics->fetch();
        $aRow2 = $rsCampaignStatistics->toArray();

        $this->ensureRowSequence($aRow1, $aRow2, 'publisherid', $doPublisher1->affiliateid);

        // 2. Check return fields names
        $this->assertFieldExists($aRow1, 'publisherid');
        $this->assertFieldExists($aRow1, 'publishername');
        $this->assertFieldExists($aRow1, 'requests');
        $this->assertFieldExists($aRow1, 'impressions');
        $this->assertFieldExists($aRow1, 'clicks');
        $this->assertFieldExists($aRow1, 'revenue');

        // 3. Check return fields value
        $this->assertFieldEqual($aRow1, 'impressions', 21);
        $this->assertFieldEqual($aRow1, 'requests', 42);
        $this->assertFieldEqual($aRow1, 'revenue', 63);
        $this->assertFieldEqual($aRow1, 'clicks', 84);
        $this->assertFieldEqual($aRow1, 'conversions', 105);
        $this->assertFieldEqual($aRow1, 'publishername', $doPublisher1->name);
        $this->assertFieldEqual($aRow2, 'impressions', 10);
        $this->assertFieldEqual($aRow2, 'requests', 20);
        $this->assertFieldEqual($aRow2, 'revenue', 30);
        $this->assertFieldEqual($aRow2, 'clicks', 40);
        $this->assertFieldEqual($aRow2, 'conversions', 50);

        // 4. Get data in not existing range
        $rsCampaignStatistics = $this->_dalCampaignStatistics->getCampaignPublisherStatistics(
            $doCampaign->campaignid, new Date('2007-09-21'),  new Date('2007-09-21'));

        $rsCampaignStatistics->find();
        $this->assertTrue($rsCampaignStatistics->getRowCount() == 0,
            'Recordset should be empty');

        // 5. Get 1 row
        $rsCampaignStatistics = $this->_dalCampaignStatistics->getCampaignPublisherStatistics(
            $doCampaign->campaignid, new Date('1986-01-01'),  new Date('1986-04-09'));
        $rsCampaignStatistics->find();
        $this->assertTrue($rsCampaignStatistics->getRowCount() == 1,
            '1 records should be returned');
    }


    /**
     * Test campaign zone statistics.
     *
     */
    function testGetCampaignZoneStatistics()
    {
        $doAgency     = OA_Dal::factoryDO('agency');
        $doAdvertiser = OA_Dal::factoryDO('clients');
        $doCampaign   = OA_Dal::factoryDO('campaigns');
        $doBanner     = OA_Dal::factoryDO('banners');
        $this->generateBannerWithParents($doAgency, $doAdvertiser, $doCampaign, $doBanner);

        $doAgency          = OA_Dal::factoryDO('agency');
        $doPublisher       = OA_Dal::factoryDO('affiliates');
        $doZone1           = OA_Dal::factoryDO('zones');
        $doZone1->zonename = 'Test zone name 1';
        $doPublisher->name = "Test publisher name";
        $this->generateZoneWithParents($doAgency, $doPublisher, $doZone1);

        $doZone2           = OA_Dal::factoryDO('zones');
        $this->generateZoneForPublisher($doPublisher, $doZone2);

        $doDataSummaryAdHourly                = OA_Dal::factoryDO('data_summary_ad_hourly');
        $doDataSummaryAdHourly->impressions   = 11;
        $doDataSummaryAdHourly->requests      = 22;
        $doDataSummaryAdHourly->total_revenue = 33;
        $doDataSummaryAdHourly->clicks        = 44;
        $doDataSummaryAdHourly->conversions   = 55;
        $doDataSummaryAdHourly->date_time     = '1986-04-08';
        $this->generateDataSummaryAdHourlyForBannerAndZone($doDataSummaryAdHourly, $doBanner, $doZone1);

        $doDataSummaryAdHourly                = OA_Dal::factoryDO('data_summary_ad_hourly');
        $doDataSummaryAdHourly->impressions   = 10;
        $doDataSummaryAdHourly->requests      = 20;
        $doDataSummaryAdHourly->total_revenue = 30;
        $doDataSummaryAdHourly->clicks        = 40;
        $doDataSummaryAdHourly->conversions   = 50;
        $doDataSummaryAdHourly->date_time     = '2007-09-13';
        $this->generateDataSummaryAdHourlyForBannerAndZone($doDataSummaryAdHourly, $doBanner, $doZone1);

        $doDataSummaryAdHourly                = OA_Dal::factoryDO('data_summary_ad_hourly');
        $doDataSummaryAdHourly->impressions   = 10;
        $doDataSummaryAdHourly->requests      = 20;
        $doDataSummaryAdHourly->total_revenue = 30;
        $doDataSummaryAdHourly->clicks        = 40;
        $doDataSummaryAdHourly->conversions   = 50;
        $doDataSummaryAdHourly->date_time     = '2007-09-13';
        $this->generateDataSummaryAdHourlyForBannerAndZone($doDataSummaryAdHourly, $doBanner, $doZone2);

        // 1. Get data existing range
        $rsCampaignStatistics = $this->_dalCampaignStatistics->getCampaignZoneStatistics(
            $doCampaign->campaignid, new Date('1984-01-01'),  new Date('2007-09-18'));

        $rsCampaignStatistics->find();
        $this->assertTrue($rsCampaignStatistics->getRowCount() == 2,
            '2 records should be returned');

        $rsCampaignStatistics->fetch();
        $aRow1 = $rsCampaignStatistics->toArray();
        $rsCampaignStatistics->fetch();
        $aRow2 = $rsCampaignStatistics->toArray();

        $this->ensureRowSequence($aRow1, $aRow2, 'zoneid', $doZone1->zoneid);

        // 2. Check return fields names
        $this->assertFieldExists($aRow1, 'publisherid');
        $this->assertFieldExists($aRow1, 'publishername');
        $this->assertFieldExists($aRow1, 'zoneid');
        $this->assertFieldExists($aRow1, 'zonename');
        $this->assertFieldExists($aRow1, 'requests');
        $this->assertFieldExists($aRow1, 'impressions');
        $this->assertFieldExists($aRow1, 'clicks');
        $this->assertFieldExists($aRow1, 'revenue');

        // 3. Check return fields value
        $this->assertFieldEqual($aRow1, 'impressions', 21);
        $this->assertFieldEqual($aRow1, 'requests', 42);
        $this->assertFieldEqual($aRow1, 'revenue', 63);
        $this->assertFieldEqual($aRow1, 'clicks', 84);
        $this->assertFieldEqual($aRow1, 'conversions', 105);
        $this->assertFieldEqual($aRow1, 'publishername', $doPublisher->name);
        $this->assertFieldEqual($aRow1, 'zonename', $doZone1->zonename);
        $this->assertFieldEqual($aRow2, 'impressions', 10);
        $this->assertFieldEqual($aRow2, 'requests', 20);
        $this->assertFieldEqual($aRow2, 'revenue', 30);
        $this->assertFieldEqual($aRow2, 'clicks', 40);
        $this->assertFieldEqual($aRow2, 'conversions', 50);

        // 4. Get data in not existing range
        $rsCampaignStatistics = $this->_dalCampaignStatistics->getCampaignZoneStatistics(
            $doCampaign->campaignid, new Date('2007-09-21'),  new Date('2007-09-21'));

        $rsCampaignStatistics->find();
        $this->assertTrue($rsCampaignStatistics->getRowCount() == 0,
            'Recordset should be empty');

        // 5. Get 1 row
        $rsCampaignStatistics = $this->_dalCampaignStatistics->getCampaignZoneStatistics(
            $doCampaign->campaignid, new Date('1986-01-01'),  new Date('1986-04-09'));
        $rsCampaignStatistics->find();
        $this->assertTrue($rsCampaignStatistics->getRowCount() == 1,
            '1 records should be returned');

    }


/**
 *  $aResult = array(
                         array('campaignid' => '25',
                               'trackerid' => '200',
                               'bannerid' => '33',
                               'conversiontime' => '2009-10-22 10:00:20',
                               'conversionstatus' => '1',
                               'userip' => '192.168.0.8',
                               'action' => '1',
                               'window' => '30',
                               'variables' => array('variableName' => 'variableValue',
                                                    'variableName2' => 'variableValue2')
                              ),
                         array('campaignid' => '25',
                               'trackerid' => '200',
                               'bannerid' => '33',
                               'conversiontime' => '2009-10-22 15:08:27',
                               'conversionstatus' => '1',
                               'userip' => '192.168.0.5',
                               'action' => '0',
                               'window' => '30',
                               'variables' => array('variableName' => 'variableValue',
                                                    'variableName2' => 'variableValue2')));
 */
    /**
     * Test tGetCampaignConversionStatistics.
     *
     */
    function testGetCampaignConversionStatistics()
    {       
        $doBanner = OA_Dal::factoryDO('banners');
        $doCampaign = OA_Dal::factoryDO('campaigns');
        $campaignId = DataGenerator::generateOne($doCampaign);
        $doBanner->campaignid = $campaignId;
        $bannerId = DataGenerator::generateOne($doBanner);

        // Test 1: Test with no data
        $oStartDate = new Date('2004-06-06 12:00:00');
        $oEndDate= new Date('2004-06-06 12:59:59');
        $aResult = $this->_dalCampaignStatistics->getCampaignConversionStatistics($campaignId, $oStartDate, $oEndDate);
        $this->assertEmpty($aResult, 'No records should be returned');

        // Test 2: Test with data that is outside the range to manage
        $doData_intermediate_ad_connection = OA_Dal::factoryDO('data_intermediate_ad_connection');
        $doData_intermediate_ad_connection->tracker_date_time = '2004-06-05 11:59:59';
        $doData_intermediate_ad_connection->tracker_id = 501;
        $doData_intermediate_ad_connection->ad_id = $bannerId;
        $doData_intermediate_ad_connection->tracker_ip_address = '127.0.0.1';
        $doData_intermediate_ad_connection->connection_action  = MAX_CONNECTION_AD_CLICK;
        $doData_intermediate_ad_connection->connection_window = 3600;
        $doData_intermediate_ad_connection->connection_status = MAX_CONNECTION_STATUS_APPROVED;
        $connectionId1 = DataGenerator::generateOne($doData_intermediate_ad_connection);
        
        $doData_intermediate_ad_variable_value = OA_Dal::factoryDO('data_intermediate_ad_variable_value');
        $doData_intermediate_ad_variable_value->data_intermediate_ad_connection_id = $connectionId1;
        $doData_intermediate_ad_variable_value->tracker_variable_id = 1;
        $doData_intermediate_ad_variable_value->value = 'test_value1';
        $conecctionVariableValueId = DataGenerator::generateOne($doData_intermediate_ad_variable_value);

        $doData_intermediate_ad_variable_value = OA_Dal::factoryDO('data_intermediate_ad_variable_value');
        $doData_intermediate_ad_variable_value->data_intermediate_ad_connection_id = $connectionId1;
        $doData_intermediate_ad_variable_value->tracker_variable_id = 2;
        $doData_intermediate_ad_variable_value->value = 'test_value2';
        $conecctionVariableValueId2 = DataGenerator::generateOne($doData_intermediate_ad_variable_value);

        $doVariables = OA_Dal::factoryDO('variables');
        $doVariables->variableid = 1;
        $doVariables->trackerid = 501;
        $doVariables->name = 'test_variable1_name';
        DataGenerator::generateOne($doVariables);
        
        $doVariables = OA_Dal::factoryDO('variables');
        $doVariables->variableid = 2;
        $doVariables->trackerid = 501;
        $doVariables->name = 'test_variable2_name';
        DataGenerator::generateOne($doVariables);
        
        $aResult = $this->_dalCampaignStatistics->getCampaignConversionStatistics($campaignId, $oStartDate, $oEndDate);

        // Get 0 Row
        $this->assertEmpty($aResult, '0 records should be returned');

        // Test 3: Test with data that is inside the range to manage,
        //         with corresponding data_intermediate_ad_connection rows        
        $doData_intermediate_ad_connection = OA_Dal::factoryDO('data_intermediate_ad_connection');        
        $doData_intermediate_ad_connection->tracker_date_time = '2004-06-06 12:15:00';
        $doData_intermediate_ad_connection->connection_date_time = '2004-06-06 12:14:58';
        $doData_intermediate_ad_connection->tracker_id = 501;
        $doData_intermediate_ad_connection->ad_id = $bannerId;        
        $doData_intermediate_ad_connection->tracker_ip_address = '127.0.0.1';
        $doData_intermediate_ad_connection->connection_action = MAX_CONNECTION_AD_CLICK;
        $doData_intermediate_ad_connection->connection_window = 3600;
        $doData_intermediate_ad_connection->connection_status = MAX_CONNECTION_STATUS_APPROVED;
        $connectionId2 = DataGenerator::generateOne($doData_intermediate_ad_connection);
        
        $aResult = $this->_dalCampaignStatistics->getCampaignConversionStatistics($campaignId, $oStartDate, $oEndDate);
        
        // Get 1 Row
        $this->assertEqual(1, count($aResult), '1 records should be returned');

        // Check return fields names
        $aConversion = current($aResult);
        $this->assertFieldExists($aConversion, 'campaignID');
        $this->assertFieldExists($aConversion, 'trackerID');
        $this->assertFieldExists($aConversion, 'bannerID');
        $this->assertFieldExists($aConversion, 'conversionTime');
        $this->assertFieldExists($aConversion, 'conversionStatus');
        $this->assertFieldExists($aConversion, 'userIp');
        $this->assertFieldExists($aConversion, 'action');
        $this->assertFieldExists($aConversion, 'window');
        $this->assertFieldExists($aConversion, 'variables');

        // Check return fields value
        $this->assertFieldEqual($aConversion, 'campaignID', $campaignId);
        $this->assertFieldEqual($aConversion, 'trackerID', 501);
        $this->assertFieldEqual($aConversion, 'bannerID', $bannerId);
        $this->assertFieldEqual($aConversion, 'conversionTime', '2004-06-06 12:15:00');
        $this->assertFieldEqual($aConversion, 'conversionStatus', MAX_CONNECTION_STATUS_APPROVED);
        $this->assertFieldEqual($aConversion, 'userIp', '127.0.0.1');
        $this->assertFieldEqual($aConversion, 'action', MAX_CONNECTION_AD_CLICK);
        $this->assertFieldEqual($aConversion, 'window', '2');
        // Conversion without variables
        $this->assertEmpty($aConversion['variables']);        

        // Test 4: Test with data that is inside the range to manage and with
        //         2 conversions
        $doData_intermediate_ad_variable_value = OA_Dal::factoryDO('data_intermediate_ad_variable_value');
        $doData_intermediate_ad_variable_value->data_intermediate_ad_connection_id = $connectionId2;
        $doData_intermediate_ad_variable_value->tracker_variable_id = 1;
        $doData_intermediate_ad_variable_value->value = 'test_value3';
        $conecctionVariableValueId = DataGenerator::generateOne($doData_intermediate_ad_variable_value);

        $doData_intermediate_ad_variable_value = OA_Dal::factoryDO('data_intermediate_ad_variable_value');
        $doData_intermediate_ad_variable_value->data_intermediate_ad_connection_id = $connectionId2;
        $doData_intermediate_ad_variable_value->tracker_variable_id = 2;
        $doData_intermediate_ad_variable_value->value = 'test_value4';
        $conecctionVariableValueId2 = DataGenerator::generateOne($doData_intermediate_ad_variable_value);

        $doData_intermediate_ad_connection = OA_Dal::factoryDO('data_intermediate_ad_connection');
        $doData_intermediate_ad_connection->tracker_date_time = '2004-06-06 12:20:00';
        $doData_intermediate_ad_connection->connection_date_time = '2004-06-06 12:19:57';
        $doData_intermediate_ad_connection->tracker_id = 501;
        $doData_intermediate_ad_connection->ad_id = $bannerId;
        $doData_intermediate_ad_connection->tracker_ip_address = '127.0.0.2';
        $doData_intermediate_ad_connection->connection_action = MAX_CONNECTION_AD_IMPRESSION;
        $doData_intermediate_ad_connection->connection_window = 3600;
        $doData_intermediate_ad_connection->connection_status = MAX_CONNECTION_STATUS_APPROVED;
        $connectionId3 = DataGenerator::generateOne($doData_intermediate_ad_connection);

        $doData_intermediate_ad_variable_value = OA_Dal::factoryDO('data_intermediate_ad_variable_value');
        $doData_intermediate_ad_variable_value->data_intermediate_ad_connection_id = $connectionId3;
        $doData_intermediate_ad_variable_value->tracker_variable_id = 1;
        $doData_intermediate_ad_variable_value->value = 'test_value5';
        $conecctionVariableValueId = DataGenerator::generateOne($doData_intermediate_ad_variable_value);

        $doData_intermediate_ad_variable_value = OA_Dal::factoryDO('data_intermediate_ad_variable_value');
        $doData_intermediate_ad_variable_value->data_intermediate_ad_connection_id = $connectionId3;
        $doData_intermediate_ad_variable_value->tracker_variable_id = 2;
        $doData_intermediate_ad_variable_value->value = 'test_value6';
        $conecctionVariableValueId2 = DataGenerator::generateOne($doData_intermediate_ad_variable_value);

        $aResult = $this->_dalCampaignStatistics->getCampaignConversionStatistics($campaignId, $oStartDate, $oEndDate);

        // Get 2 Row
        $this->assertEqual(2, count($aResult), '2 records should be returned');

        $aConversion = current($aResult);
        // Check return fields value
        $this->assertFieldEqual($aConversion, 'campaignID', $campaignId);
        $this->assertFieldEqual($aConversion, 'trackerID', '501');
        $this->assertFieldEqual($aConversion, 'bannerID', $bannerId);
        $this->assertFieldEqual($aConversion, 'conversionTime', '2004-06-06 12:15:00');
        $this->assertFieldEqual($aConversion, 'conversionStatus', MAX_CONNECTION_STATUS_APPROVED);
        $this->assertFieldEqual($aConversion, 'userIp', '127.0.0.1');
        $this->assertFieldEqual($aConversion, 'action', MAX_CONNECTION_AD_CLICK);
        $this->assertFieldEqual($aConversion, 'window', '2');
        $aVariables = $aConversion['variables'];
        $this->assertFieldEqual($aVariables, 'test_variable1_name', 'test_value3');
        $this->assertFieldEqual($aVariables, 'test_variable2_name', 'test_value4');        

        $aConversion = next($aResult);
        // Check return fields value
        $this->assertFieldEqual($aConversion, 'campaignID', $campaignId);
        $this->assertFieldEqual($aConversion, 'trackerID', '501');
        $this->assertFieldEqual($aConversion, 'bannerID', $bannerId);
        $this->assertFieldEqual($aConversion, 'conversionTime', '2004-06-06 12:20:00');
        $this->assertFieldEqual($aConversion, 'conversionStatus', MAX_CONNECTION_STATUS_APPROVED);
        $this->assertFieldEqual($aConversion, 'userIp', '127.0.0.2');
        $this->assertFieldEqual($aConversion, 'action', MAX_CONNECTION_AD_IMPRESSION);
        $this->assertFieldEqual($aConversion, 'window', '3');
        $aVariables = $aConversion['variables'];
        $this->assertFieldEqual($aVariables, 'test_variable1_name', 'test_value5');
        $this->assertFieldEqual($aVariables, 'test_variable2_name', 'test_value6');


        // Test 5: Test with data that is inside the range to manage but doesn't
        //         belong to the requested campaign
        $doBanner2 = OA_Dal::factoryDO('banners');
        $doCampaign2 = OA_Dal::factoryDO('campaigns');
        $campaignId2 = DataGenerator::generateOne($doCampaign2);
        $doBanner2->campaignid = $campaignId2;
        $bannerId2 = DataGenerator::generateOne($doBanner2);        

        $doData_intermediate_ad_connection = OA_Dal::factoryDO('data_intermediate_ad_connection');
        $doData_intermediate_ad_connection->tracker_date_time = '2004-06-06 12:20:00';
        $doData_intermediate_ad_connection->tracker_id = 501;
        $doData_intermediate_ad_connection->ad_id = $bannerId2;
        $doData_intermediate_ad_connection->tracker_ip_address = '127.0.0.2';
        $doData_intermediate_ad_connection->connection_action = MAX_CONNECTION_AD_IMPRESSION;
        $doData_intermediate_ad_connection->connection_window = 3600;
        $doData_intermediate_ad_connection->connection_status = MAX_CONNECTION_STATUS_APPROVED;
        $connectionId4 = DataGenerator::generateOne($doData_intermediate_ad_connection);

        $doData_intermediate_ad_variable_value = OA_Dal::factoryDO('data_intermediate_ad_variable_value');
        $doData_intermediate_ad_variable_value->data_intermediate_ad_connection_id = $connectionId4;
        $doData_intermediate_ad_variable_value->tracker_variable_id = 1;
        $doData_intermediate_ad_variable_value->value = 'test_value7';
        $conecctionVariableValueId = DataGenerator::generateOne($doData_intermediate_ad_variable_value);
        $aResult = $this->_dalCampaignStatistics->getCampaignConversionStatistics($campaignId, $oStartDate, $oEndDate);

        // Get 2 Row
        $this->assertEqual(2, count($aResult), '2 records should be returned');
        
        $aConversion = current($aResult);
        // Check return fields value
        $this->assertFieldEqual($aConversion, 'campaignID', $campaignId);
        $this->assertFieldEqual($aConversion, 'trackerID', '501');
        $this->assertFieldEqual($aConversion, 'bannerID', $bannerId);
        $this->assertFieldEqual($aConversion, 'conversionTime', '2004-06-06 12:15:00');
        $this->assertFieldEqual($aConversion, 'conversionStatus', MAX_CONNECTION_STATUS_APPROVED);
        $this->assertFieldEqual($aConversion, 'userIp', '127.0.0.1');
        $this->assertFieldEqual($aConversion, 'action', MAX_CONNECTION_AD_CLICK);
        $this->assertFieldEqual($aConversion, 'window', '2');
        $aVariables = $aConversion['variables'];
        $this->assertFieldEqual($aVariables, 'test_variable1_name', 'test_value3');
        $this->assertFieldEqual($aVariables, 'test_variable2_name', 'test_value4');

        $aConversion = next($aResult);
        // Check return fields value
        $this->assertFieldEqual($aConversion, 'campaignID', $campaignId);
        $this->assertFieldEqual($aConversion, 'trackerID', '501');
        $this->assertFieldEqual($aConversion, 'bannerID', $bannerId);
        $this->assertFieldEqual($aConversion, 'conversionTime', '2004-06-06 12:20:00');
        $this->assertFieldEqual($aConversion, 'conversionStatus', MAX_CONNECTION_STATUS_APPROVED);
        $this->assertFieldEqual($aConversion, 'userIp', '127.0.0.2');
        $this->assertFieldEqual($aConversion, 'action', MAX_CONNECTION_AD_IMPRESSION);
        $this->assertFieldEqual($aConversion, 'window', '3');
        $aVariables = $aConversion['variables'];
        $this->assertFieldEqual($aVariables, 'test_variable1_name', 'test_value5');
        $this->assertFieldEqual($aVariables, 'test_variable2_name', 'test_value6');
        
        // Clean Up
        DataGenerator::cleanUp();       
    }

}
?>