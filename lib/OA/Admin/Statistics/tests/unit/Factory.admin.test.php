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
$Id: Factory.admin.test.php 42121 2009-08-26 01:26:30Z matthieu.aubry $
*/

require_once MAX_PATH . '/lib/OA/Admin/Statistics/Factory.php';

/**
 * A class for testing the OA_Admin_Statistics_Factory class.
 *
 * @package    OpenXAdmin
 * @subpackage TestSuite
 * @author     Andrew Hill <andrew.hill@openx.org>
 */
class Test_OA_Admin_Statistics_Factory extends UnitTestCase
{

    /**
     * An array to store the different possible $controllerType values
     * accepted by the factory method, and the associated expected class
     * types the factory should return.
     *
     * @var unknown_type
     */
    var $testTypesDelivery = array(
        'advertiser-affiliates'         => 'OA_Admin_Statistics_Delivery_Controller_AdvertiserAffiliates',
        'advertiser-affiliate-history'  => 'OA_Admin_Statistics_Delivery_Controller_AdvertiserAffiliateHistory',
        'advertiser-campaigns'          => 'OA_Admin_Statistics_Delivery_Controller_AdvertiserCampaigns',
        //'advertiser-daily'              => 'OA_Admin_Statistics_Delivery_Controller_AdvertiserDaily',
        'advertiser-history'            => 'OA_Admin_Statistics_Delivery_Controller_AdvertiserHistory',
        'advertiser-zone-history'       => 'OA_Admin_Statistics_Delivery_Controller_AdvertiserZoneHistory',

        'affiliate-banner-history'      => 'OA_Admin_Statistics_Delivery_Controller_AffiliateBannerHistory',
        'affiliate-campaigns'           => 'OA_Admin_Statistics_Delivery_Controller_AffiliateCampaigns',
        'affiliate-campaign-history'    => 'OA_Admin_Statistics_Delivery_Controller_AffiliateCampaignHistory',
        //'affiliate-daily'               => 'OA_Admin_Statistics_Delivery_Controller_AffiliateDaily',
        'affiliate-history'             => 'OA_Admin_Statistics_Delivery_Controller_AffiliateHistory',
        'affiliate-zones'               => 'OA_Admin_Statistics_Delivery_Controller_AffiliateZones',

        'banner-affiliates'             => 'OA_Admin_Statistics_Delivery_Controller_BannerAffiliates',
        'banner-affiliate-history'      => 'OA_Admin_Statistics_Delivery_Controller_BannerAffiliateHistory',
        //'banner-daily'                  => 'OA_Admin_Statistics_Delivery_Controller_BannerDaily',
        'banner-history'                => 'OA_Admin_Statistics_Delivery_Controller_BannerHistory',
        'banner-zone-history'           => 'OA_Admin_Statistics_Delivery_Controller_BannerZoneHistory',

        'campaign-affiliates'           => 'OA_Admin_Statistics_Delivery_Controller_CampaignAffiliates',
        'campaign-affiliate-history'    => 'OA_Admin_Statistics_Delivery_Controller_CampaignAffiliateHistory',
        'campaign-banners'              => 'OA_Admin_Statistics_Delivery_Controller_CampaignBanners',
        //'campaign-daily'                => 'OA_Admin_Statistics_Delivery_Controller_CampaignDaily',
        'campaign-history'              => 'OA_Admin_Statistics_Delivery_Controller_CampaignHistory',
        'campaign-zone-history'         => 'OA_Admin_Statistics_Delivery_Controller_CampaignZoneHistory',

        'global-advertiser'             => 'OA_Admin_Statistics_Delivery_Controller_GlobalAdvertiser',
        'global-affiliates'             => 'OA_Admin_Statistics_Delivery_Controller_GlobalAffiliates',
        //'global-daily'                  => 'OA_Admin_Statistics_Delivery_Controller_GlobalDaily',
        'global-history'                => 'OA_Admin_Statistics_Delivery_Controller_GlobalHistory',

        'zone-banner-history'           => 'OA_Admin_Statistics_Delivery_Controller_ZoneBannerHistory',
        'zone-campaigns'                => 'OA_Admin_Statistics_Delivery_Controller_ZoneCampaigns',
        'zone-campaign-history'         => 'OA_Admin_Statistics_Delivery_Controller_ZoneCampaignHistory',
        //'zone-daily'                    => 'OA_Admin_Statistics_Delivery_Controller_ZoneDaily',
        'zone-history'                  => 'OA_Admin_Statistics_Delivery_Controller_ZoneHistory'
    );

    /**
     * The constructor method.
     */
    function Test_OA_Admin_Statistics_Factory()
    {
        $this->UnitTestCase();
    }

    function test_getControllerClass()
    {
        $expectPath = '/Statistics/Delivery/Controller/';
        foreach ($this->testTypesDelivery as $controllerType => $expectedClassName)
        {
            $aType = explode('-',$controllerType);
            $expectFile= '';
            foreach ($aType AS $string)
            {
                $expectFile.= ucfirst($string);
            }
            OA_Admin_Statistics_Factory::_getControllerClass($controllerType, null, $class, $file);
            $this->assertEqual($class, $expectedClassName);
            $this->assertPattern("(\/Statistics\/Delivery\/Controller\/{$expectFile}\.php)",$file);
        }
    }

    function test_instantiateController()
    {
        $file = MAX_PATH.'/lib/OA/Admin/Statistics/Delivery/Common.php';
        $aParams = array();
        $class = 'OA_Admin_Statistics_Delivery_Common';
        $oObject =& OA_Admin_Statistics_Factory::_instantiateController($file, $class, $aParams);
        $this->assertIsA($oObject, $class);
        $this->assertEqual(count($oObject->aPlugins),2);
        $this->assertTrue(isset($oObject->aPlugins['default']));
        $this->assertTrue(isset($oObject->aPlugins['affiliates']));

        $file = MAX_PATH.'/lib/OA/Admin/Statistics/tests/data/TestStatisticsController.php';
        $aParams = array();
        $class = 'OA_Admin_Statistics_Test';
        $oObject =& OA_Admin_Statistics_Factory::_instantiateController($file, $class, $aParams);
        $this->assertIsA($oObject, $class);

        // Disable default error handling
        PEAR::pushErrorHandling(null);

        // Test _instantiateController for not existing controller
        $file = MAX_PATH.'/lib/OA/Admin/Statistics/tests/data/TestNotExists.php';
        $aParams = array();
        $class = 'OA_Admin_Statistics_Test';
        $oObject =& OA_Admin_Statistics_Factory::_instantiateController($file, $class, $aParams);
        $this->assertTrue(PEAR::isError($oObject));
        $this->assertEqual($oObject->getMessage(), 'OA_Admin_Statistics_Factory::_instantiateController() Unable to locate '.basename($file));

        // Test _instantiateController for not existing class
        $file = MAX_PATH.'/lib/OA/Admin/Statistics/tests/data/TestStatisticsController.php';
        $aParams = array();
        $class = 'OA_Admin_not_exists';
        $oObject =& OA_Admin_Statistics_Factory::_instantiateController($file, $class, $aParams);
        $this->assertTrue(PEAR::isError($oObject));
        $this->assertEqual($oObject->getMessage(), 'OA_Admin_Statistics_Factory::_instantiateController() Class '.$class.' doesn\'t exist');

        // Restore default error handling
        PEAR::popErrorHandling();
    }

    /**
     *
     * PROBLEM: if any of the classes use database in their constructor this test will fail
     * the test layer has no database
     *
     * A method to test that the correct class is returned for appropriate
     * $controllerType input values
     */
    function test_GetController()
    {
        /*foreach ($this->testTypes as $controllerType => $expectedClassName) {
            $oController =& OA_Admin_Statistics_Factory::getController($controllerType);
            $this->assertTrue(is_a($oController, $expectedClassName));
        }*/
    }




}

?>
