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
$Id: CampaignsOptIn.plg.test.php 46832 2009-11-25 09:24:25Z lukasz.wikierski $
*/

require_once LIB_PATH . '/Plugin/Component.php';
require_once MAX_PATH . '/lib/OA/Dal/DataGenerator.php';

/**
 * A class for testing the CampaignOptIn DAL library
 *
 * @package    OpenXPlugin
 * @subpackage TestSuite
 * @author     Lukasz Wikierski <lukasz.wikierski@openx.org>
 */
class OX_oxMarket_Dal_CampaignsOptInTest extends UnitTestCase
{
    function setUp()
    {
        $oPkgMgr = new OX_PluginManager();
        TestEnv::uninstallPluginPackage('openXMarket', false);
        TestEnv::installPluginPackage('openXMarket', false);
        // OX_oxMarket_Dal_CampaignsOptIn is initialised from plugin directory during plugin installation
        if (!class_exists('OX_oxMarket_Dal_CampaignsOptIn')) {
            require_once dirname(__FILE__).'/../../CampaignsOptIn.php';
        }
    }

    function tearDown()
    {
        DataGenerator::cleanUp();
        TestEnv::uninstallPluginPackage('openXMarket', false);
    }

    
    function testPerformOptIn(){

        $aObjectsIds = $this->_prepare_users();
        // Prepare logged user
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->get($aObjectsIds['managerUserID']);
        $oUser = new OA_Permission_User($doUsers);
        global $session;
        $session['user'] = $oUser;

        $aCampaignsIds = $this->_prepare_campaigns($aObjectsIds);
        $oDalCampaignsOptIn = new OX_oxMarket_Dal_CampaignsOptIn();
        
        // Test selected (update and insert)
        $toOptIn = array($aCampaignsIds[4], $aCampaignsIds[2]);
        $minCpms = array($aCampaignsIds[4] => 1.23, $aCampaignsIds[2] => 0.56);
        $result = $oDalCampaignsOptIn->performOptIn($toOptIn, $minCpms);
        $this->assertEqual(2, $result);

        $doMarketCampaignPref = OA_Dal::factoryDO('ext_market_campaign_pref');
        $doMarketCampaignPref->orderBy('campaignid');
        $aResult = $doMarketCampaignPref->getAll();
        $this->assertEqual(count($aResult),2);
        $this->assertEqual($aResult[0]['campaignid'], $aCampaignsIds[2]);
        $this->assertTrue($aResult[0]['is_enabled']);
        $this->assertEqual($aResult[0]['floor_price'], 0.56);
        $this->assertEqual($aResult[1]['campaignid'], $aCampaignsIds[4]);
        $this->assertTrue($aResult[1]['is_enabled']);
        $this->assertEqual($aResult[1]['floor_price'], 1.23);

        // Security test: try to change other manager campaign
        $toOptIn = array($aCampaignsIds[5]);
        $minCpms = array($aCampaignsIds[5] => 1.23);
        $result = $oDalCampaignsOptIn->performOptIn($toOptIn, $minCpms);
        $this->assertEqual(0, $result);

        $doMarketCampaignPref = OA_Dal::factoryDO('ext_market_campaign_pref');
        $doMarketCampaignPref->orderBy('campaignid');
        $aResult = $doMarketCampaignPref->getAll();
        $this->assertEqual(count($aResult),2);

        // Empty array $toOptIn
        $toOptIn = array();
        $minCpms = array();
        $result = $oDalCampaignsOptIn->performOptIn($toOptIn, $minCpms);
        $this->assertEqual(0, $result);

        $doMarketCampaignPref = OA_Dal::factoryDO('ext_market_campaign_pref');
        $doMarketCampaignPref->orderBy('campaignid');
        $aResult = $doMarketCampaignPref->getAll();
        $this->assertEqual(count($aResult),2);
    }


    function testInsertOrUpdateMarketCampaignPref()
    {
        $oDalCampaignsOptIn = new OX_oxMarket_Dal_CampaignsOptIn();
        $oDalCampaignsOptIn->insertOrUpdateMarketCampaignPref(3, 0.41);

        $doMarketCampaignPref = OA_Dal::factoryDO('ext_market_campaign_pref');
        $doMarketCampaignPref->orderBy('campaignid');
        $aResult = $doMarketCampaignPref->getAll();
        $this->assertEqual(count($aResult),1);
        $this->assertEqual($aResult[0]['campaignid'], 3);
        $this->assertTrue($aResult[0]['is_enabled']);
        $this->assertEqual($aResult[0]['floor_price'], 0.41);

        $oDalCampaignsOptIn->insertOrUpdateMarketCampaignPref(3, 0.51);

        $doMarketCampaignPref = OA_Dal::factoryDO('ext_market_campaign_pref');
        $doMarketCampaignPref->orderBy('campaignid');
        $aResult = $doMarketCampaignPref->getAll();
        $this->assertEqual(count($aResult),1);
        $this->assertEqual($aResult[0]['campaignid'], 3);
        $this->assertTrue($aResult[0]['is_enabled']);
        $this->assertEqual($aResult[0]['floor_price'], 0.51);
    }


    function testGetCampaigns()
    {
        $aObjectsIds = $this->_prepare_users();
        // Prepare logged user
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->get($aObjectsIds['managerUserID']);
        $oUser = new OA_Permission_User($doUsers);
        global $session;
        $session['user'] = $oUser;

        $aCampaignsIds = $this->_prepare_campaigns($aObjectsIds);

        // Define formatCpm (required by getCampaigns)
        function formatCpm($cpm)
        {
            return number_format($cpm, 2, '.', '');
        }

        $oDalCampaignsOptIn = new OX_oxMarket_Dal_CampaignsOptIn();

        // Get remnant campaigns
        $aResult = $oDalCampaignsOptIn->getCampaigns(0.5, 'remnant');

        // Unfortunatelly all campaigns epcm are automatically set to 1.000 in tests
        $this->assertEqual(2, count($aResult));
        $this->assertEqual($aResult[$aCampaignsIds[1]]['campaignid'], $aCampaignsIds[1]);
        $this->assertEqual($aResult[$aCampaignsIds[1]]['campaignname'], 'campaign 1');
        $this->assertEqual($aResult[$aCampaignsIds[1]]['minCpm'], 1);
        $this->assertEqual($aResult[$aCampaignsIds[1]]['priority'], DataObjects_Campaigns::PRIORITY_REMNANT);
        $this->assertFalse($aResult[$aCampaignsIds[1]]['minCpmCalculated']); //REMNANT_CAMPAIGNS are not eCPM enabled
        $this->assertFalse($aResult[$aCampaignsIds[1]]['optin_status']);
        $this->assertEqual($aResult[$aCampaignsIds[1]]['floor_price'], '');
        $this->assertEqual($aResult[$aCampaignsIds[2]]['campaignid'], $aCampaignsIds[2]);
        $this->assertEqual($aResult[$aCampaignsIds[2]]['campaignname'], 'campaign 2');
        $this->assertEqual($aResult[$aCampaignsIds[2]]['minCpm'], 1);
        $this->assertTrue($aResult[$aCampaignsIds[2]]['minCpmCalculated']);
        $this->assertFalse($aResult[$aCampaignsIds[2]]['optin_status']);
        $this->assertEqual($aResult[$aCampaignsIds[2]]['floor_price'], '');

        // Get contract campaigns
        $aResult = $oDalCampaignsOptIn->getCampaigns(0.5, 'contract');
        $this->assertEqual(1, count($aResult));
        $this->assertEqual($aResult[$aCampaignsIds[4]]['campaignid'], $aCampaignsIds[4]);
        $this->assertEqual($aResult[$aCampaignsIds[4]]['campaignname'], 'campaign 4');
        $this->assertEqual($aResult[$aCampaignsIds[4]]['minCpm'], 1);
        $this->assertTrue($aResult[$aCampaignsIds[4]]['minCpmCalculated']);
        $this->assertFalse($aResult[$aCampaignsIds[4]]['optin_status']);
        $this->assertEqual($aResult[$aCampaignsIds[4]]['floor_price'], '');

        // Get all (remnant+contract) campaigns
        $aResult = $oDalCampaignsOptIn->getCampaigns(0.5, 'all');
        $this->assertEqual(3, count($aResult));
        $this->assertEqual($aResult[$aCampaignsIds[1]]['campaignid'], $aCampaignsIds[1]);
        $this->assertEqual($aResult[$aCampaignsIds[2]]['campaignid'], $aCampaignsIds[2]);
        $this->assertEqual($aResult[$aCampaignsIds[4]]['priority'], 7);
        $this->assertEqual($aResult[$aCampaignsIds[4]]['campaignid'], $aCampaignsIds[4]);
        $this->assertFalse($aResult[$aCampaignsIds[1]]['optin_status']);
        $this->assertFalse($aResult[$aCampaignsIds[2]]['optin_status']);
        $this->assertFalse($aResult[$aCampaignsIds[4]]['optin_status']);
        $this->assertEqual($aResult[$aCampaignsIds[1]]['floor_price'], '');
        $this->assertEqual($aResult[$aCampaignsIds[2]]['floor_price'], '');
        $this->assertEqual($aResult[$aCampaignsIds[4]]['floor_price'], '');
        $aExpectedSorting = array(0=>$aCampaignsIds[1], 1=> $aCampaignsIds[2], 2=> $aCampaignsIds[4]);
        $aSorting = $this->_getSorting($aResult);
        $this->assertEqual($aSorting, $aExpectedSorting);

        $aExpected = $aResult;
        //get all campaigns use sorting by name
        $aResult = $oDalCampaignsOptIn->getCampaigns(0.5, 'all', null, null, 'name');
        $this->assertEqual($aResult, $aExpected);
        $aSorting = $this->_getSorting($aResult);
        $this->assertEqual($aSorting, $aExpectedSorting);
        
        //get all campaigns try to put unknown sorting (it should use default)
        $aResult = $oDalCampaignsOptIn->getCampaigns(0.5, 'all', null, null, 'unknown');
        $this->assertEqual($aResult, $aExpected);
        $aSorting = $this->_getSorting($aResult);
        $this->assertEqual($aSorting, $aExpectedSorting);

        $aExpectedSorting = array_reverse($aExpectedSorting);
        //get all campaigns use reverse sorting by name
        $aResult = $oDalCampaignsOptIn->getCampaigns(0.5, 'all', null, null, '-name');
        $this->assertEqual($aResult, $aExpected);
        $aSorting = $this->_getSorting($aResult);
        $this->assertEqual($aSorting, $aExpectedSorting);
        $aResult = $oDalCampaignsOptIn->getCampaigns(0.5, 'all', null, null, '-');
        $this->assertEqual($aResult, $aExpected);
        $aSorting = $this->_getSorting($aResult);
        $this->assertEqual($aSorting, $aExpectedSorting);
        
        //get all campaigns try to put reverse unknown sorting (it should use reverse default)
        $aResult = $oDalCampaignsOptIn->getCampaigns(0.5, 'all', null, null, '-unknown');
        $this->assertEqual($aResult, $aExpected);
        $aSorting = $this->_getSorting($aResult);
        $this->assertEqual($aSorting, $aExpectedSorting);

        // Opt in two campaigns
        $toOptIn = array($aCampaignsIds[4], $aCampaignsIds[2]);
        $minCpms = array($aCampaignsIds[4] => 1.23, $aCampaignsIds[2] => 0.56);
        $result = $oDalCampaignsOptIn->performOptIn($toOptIn, $minCpms);

        // Get remnant campaigns
        $aResult = $oDalCampaignsOptIn->getCampaigns(2, 'remnant');
        $this->assertEqual(2, count($aResult));
        $this->assertEqual($aResult[$aCampaignsIds[1]]['campaignid'], $aCampaignsIds[1]);
        $this->assertEqual($aResult[$aCampaignsIds[2]]['campaignid'], $aCampaignsIds[2]);
        $this->assertFalse($aResult[$aCampaignsIds[1]]['optin_status']);
        $this->assertTrue($aResult[$aCampaignsIds[2]]['optin_status']);
        $this->assertEqual($aResult[$aCampaignsIds[1]]['floor_price'], '');
        $this->assertEqual($aResult[$aCampaignsIds[2]]['floor_price'], '0.56');

        // Get contract campaigns
        $aResult = $oDalCampaignsOptIn->getCampaigns(0.5, 'contract');
        $this->assertEqual(1, count($aResult));
        $this->assertEqual($aResult[$aCampaignsIds[4]]['campaignid'], $aCampaignsIds[4]);
        $this->assertTrue($aResult[$aCampaignsIds[4]]['optin_status']);
        $this->assertEqual($aResult[$aCampaignsIds[4]]['floor_price'], '1.23');

        // Get all (remnant+contract) campaigns
        $aResult = $oDalCampaignsOptIn->getCampaigns(2, 'all');
        $this->assertEqual(3, count($aResult));
        $this->assertEqual($aResult[$aCampaignsIds[1]]['campaignid'], $aCampaignsIds[1]);
        $this->assertEqual($aResult[$aCampaignsIds[2]]['campaignid'], $aCampaignsIds[2]);
        $this->assertEqual($aResult[$aCampaignsIds[4]]['campaignid'], $aCampaignsIds[4]);
        $this->assertFalse($aResult[$aCampaignsIds[1]]['optin_status']);
        $this->assertTrue($aResult[$aCampaignsIds[2]]['optin_status']);
        $this->assertTrue($aResult[$aCampaignsIds[4]]['optin_status']); 
        $this->assertEqual($aResult[$aCampaignsIds[1]]['floor_price'], '');
        $this->assertEqual($aResult[$aCampaignsIds[2]]['floor_price'], '0.56');
        $this->assertEqual($aResult[$aCampaignsIds[4]]['floor_price'], '1.23');

        // Clear optin tables
        $doMarketCampaignPref = OA_Dal::factoryDO('ext_market_campaign_pref');
        $doMarketCampaignPref->whereAdd('1=1');
        $doMarketCampaignPref->delete(DB_DATAOBJECT_WHEREADD_ONLY);
        //Opt in first and last campaign
        $toOptIn = array($aCampaignsIds[4], $aCampaignsIds[1]);
        $minCpms = array($aCampaignsIds[4] => 1.23, $aCampaignsIds[1] => 0.56);
        $result = $oDalCampaignsOptIn->performOptIn($toOptIn, $minCpms);
        
        // sort by optin status
        $aResult = $oDalCampaignsOptIn->getCampaigns(2, 'all', null, null, 'optinstatus');
        $aExpectedSorting = array(0=>$aCampaignsIds[2], 1=> $aCampaignsIds[1], 2=> $aCampaignsIds[4]);
        $aSorting = $this->_getSorting($aResult);
        $this->assertEqual($aSorting, $aExpectedSorting);
        // sort by desc optin status
        $aResult = $oDalCampaignsOptIn->getCampaigns(2, 'all', null, null, '-optinstatus');
        $aExpectedSorting = array(0=>$aCampaignsIds[1], 1=> $aCampaignsIds[4], 2=> $aCampaignsIds[2]);
        $aSorting = $this->_getSorting($aResult);
        $this->assertEqual($aSorting, $aExpectedSorting);
        
        // optout campaing 4
        $oDalCampaignsOptIn->performOptOut(array($aCampaignsIds[4]));
        
        // sort by optin status
        $aResult = $oDalCampaignsOptIn->getCampaigns(2, 'all', null, null, 'optinstatus');
        $aExpectedSorting = array(0=>$aCampaignsIds[2], 1=> $aCampaignsIds[4], 2=> $aCampaignsIds[1]);
        $aSorting = $this->_getSorting($aResult);
        $this->assertEqual($aSorting, $aExpectedSorting);
        // sort by desc optin status
        $aResult = $oDalCampaignsOptIn->getCampaigns(2, 'all', null, null, '-optinstatus');
        $aExpectedSorting = array(0=>$aCampaignsIds[1], 1=> $aCampaignsIds[2], 2=> $aCampaignsIds[4]);
        $aSorting = $this->_getSorting($aResult);
        $this->assertEqual($aSorting, $aExpectedSorting);
        
        
        // optout campaing 1
        $oDalCampaignsOptIn->performOptOut(array($aCampaignsIds[1]));
        
        // sort by optin status (should sort properly unifying 0 and null values as not opted in)
        $aResult = $oDalCampaignsOptIn->getCampaigns(2, 'all', null, null, 'optinstatus');
        $aExpectedSorting = array(0=>$aCampaignsIds[1], 1=> $aCampaignsIds[2], 2=> $aCampaignsIds[4]);
        $aSorting = $this->_getSorting($aResult);
        $this->assertEqual($aSorting, $aExpectedSorting);
        // sort by desc optin status 
        $aResult = $oDalCampaignsOptIn->getCampaigns(2, 'all', null, null, '-optinstatus');
        $aExpectedSorting = array(0=>$aCampaignsIds[1], 1=> $aCampaignsIds[2], 2=> $aCampaignsIds[4]);
        $aSorting = $this->_getSorting($aResult);
        $this->assertEqual($aSorting, $aExpectedSorting);
        
        // test seach phrase
        $aResult = $oDalCampaignsOptIn->getCampaigns(2, 'all', null, 'ign 4');
        $this->assertEqual(1, count($aResult));
        $this->assertEqual($aResult[$aCampaignsIds[4]]['campaignid'], $aCampaignsIds[4]);
        
        $aResult = $oDalCampaignsOptIn->getCampaigns(2, 'all', null, 'camp');
        $this->assertEqual(3, count($aResult));
        $this->assertEqual($aResult[$aCampaignsIds[1]]['campaignid'], $aCampaignsIds[1]);
        $this->assertEqual($aResult[$aCampaignsIds[2]]['campaignid'], $aCampaignsIds[2]);
        $this->assertEqual($aResult[$aCampaignsIds[4]]['campaignid'], $aCampaignsIds[4]);
        
        // test limit
        $aResult = $oDalCampaignsOptIn->getCampaigns(2, 'all', null, null, null, 2);
        $this->assertEqual(2, count($aResult));
        $this->assertEqual($aResult[$aCampaignsIds[1]]['campaignid'], $aCampaignsIds[1]);
        $this->assertEqual($aResult[$aCampaignsIds[2]]['campaignid'], $aCampaignsIds[2]);
        
        // test limit, offset
        $aResult = $oDalCampaignsOptIn->getCampaigns(2, 'all', null, null, null, 2, 1);
        $this->assertEqual(2, count($aResult));
        $this->assertEqual($aResult[$aCampaignsIds[2]]['campaignid'], $aCampaignsIds[2]);
        $this->assertEqual($aResult[$aCampaignsIds[4]]['campaignid'], $aCampaignsIds[4]);
    }
    
    /**
     * Returns array with campaigns ids in same order as given input array
     * 
     * @param array $aCampaigns result of getCampaigns methods
     * @return array array order ordinal number => campaign id
     */
    private function _getSorting($aCampaigns)
    {
        $sorting = array();
        $i = 0;
        foreach ($aCampaigns as $id => $data) {
            $sorting[$i] = $id;
            $i++;
        }
        return $sorting;
    }
    
    
    function testGetCampaignsCount()
    {
        $aObjectsIds = $this->_prepare_users();
        // Prepare logged user
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->get($aObjectsIds['managerUserID']);
        $oUser = new OA_Permission_User($doUsers);
        global $session;
        $session['user'] = $oUser;

        $aCampaignsIds = $this->_prepare_campaigns($aObjectsIds);

        $oDalCampaignsOptIn = new OX_oxMarket_Dal_CampaignsOptIn();

        // Get remnant campaigns
        $this->assertEqual(2,$oDalCampaignsOptIn->getCampaignsCount('remnant'));
        // Get contract campaigns
        $this->assertEqual(1,$oDalCampaignsOptIn->getCampaignsCount('contract'));
        // Get all (remnant+contract) campaigns
        $this->assertEqual(3,$oDalCampaignsOptIn->getCampaignsCount('all'));
        
        // Opt in two campaigns
        $toOptIn = array($aCampaignsIds[4], $aCampaignsIds[2]);
        $minCpms = array($aCampaignsIds[4] => 1.23, $aCampaignsIds[2] => 0.56);
        $result = $oDalCampaignsOptIn->performOptIn($toOptIn, $minCpms);

        // Get remnant campaigns
        $this->assertEqual(2,$oDalCampaignsOptIn->getCampaignsCount('remnant'));
        // Get contract campaigns
        $this->assertEqual(1,$oDalCampaignsOptIn->getCampaignsCount('contract'));
        // Get all (remnant+contract) campaigns
        $this->assertEqual(3,$oDalCampaignsOptIn->getCampaignsCount('all'));
                
        // test seach prhrase
        $this->assertEqual(1,$oDalCampaignsOptIn->getCampaignsCount('all', 'ign 4'));
        $this->assertEqual(3,$oDalCampaignsOptIn->getCampaignsCount('all', 'camp'));
    }


    function testNumberOfOptedCampaigns()
    {
        $aObjectsIds = $this->_prepare_users();
        // Prepare logged user
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->get($aObjectsIds['managerUserID']);
        $oUser = new OA_Permission_User($doUsers);
        global $session;
        $session['user'] = $oUser;

        $aCampaignsIds = $this->_prepare_campaigns($aObjectsIds);

        $oDalCampaignsOptIn = new OX_oxMarket_Dal_CampaignsOptIn();

        // test empty 'remnant', 'contract', 'all' types
        $this->assertEqual(0, $oDalCampaignsOptIn->numberOfOptedCampaigns('remnant'));
        $this->assertEqual(0, $oDalCampaignsOptIn->numberOfOptedCampaigns('contract'));
        $this->assertEqual(0, $oDalCampaignsOptIn->numberOfOptedCampaigns('all'));

        // Opt 1 remnant and 1 contract campaign
        $toOptIn = array($aCampaignsIds[4], $aCampaignsIds[2]);
        $minCpms = array($aCampaignsIds[4] => 1.23, $aCampaignsIds[2] => 0.56);
        $result = $oDalCampaignsOptIn->performOptIn($toOptIn, $minCpms);

        // test 'remnant', 'contract', 'all' types once again
        $this->assertEqual(1, $oDalCampaignsOptIn->numberOfOptedCampaigns('remnant'));
        $this->assertEqual(1, $oDalCampaignsOptIn->numberOfOptedCampaigns('contract'));
        $this->assertEqual(2, $oDalCampaignsOptIn->numberOfOptedCampaigns('all'));
    }


    function testNumberOfRemnantCampaignsToOptIn()
    {
        $aObjectsIds = $this->_prepare_users();
        // Prepare logged user
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->get($aObjectsIds['managerUserID']);
        $oUser = new OA_Permission_User($doUsers);
        global $session;
        $session['user'] = $oUser;

        $oDalCampaignsOptIn = new OX_oxMarket_Dal_CampaignsOptIn();

        // there is no campaign in database
        $this->assertEqual(0, $oDalCampaignsOptIn->numberOfRemnantCampaignsToOptIn());

        $aCampaignsIds = $this->_prepare_campaigns($aObjectsIds);

        // Two matching campaigns
        $this->assertEqual(2, $oDalCampaignsOptIn->numberOfRemnantCampaignsToOptIn());

        // Opt 1 remnant and 1 contract campaign
        $toOptIn = array($aCampaignsIds[4], $aCampaignsIds[2]);
        $minCpms = array($aCampaignsIds[4] => 1.23, $aCampaignsIds[2] => 0.56);
        $oDalCampaignsOptIn->performOptIn($toOptIn, $minCpms);

        // Result is independed from already opted in capmaigns
        $this->assertEqual(2, $oDalCampaignsOptIn->numberOfRemnantCampaignsToOptIn());
    }
    

    function testPerformOptOut()
    {
        $aObjectsIds = $this->_prepare_users();
        // Prepare logged user
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->get($aObjectsIds['managerUserID']);
        $oUser = new OA_Permission_User($doUsers);
        global $session;
        $session['user'] = $oUser;

        $aCampaignsIds = $this->_prepare_campaigns($aObjectsIds);

        $oDalCampaignsOptIn = new OX_oxMarket_Dal_CampaignsOptIn();
        
        // Test invalid input, empty array, random ids where there is no campaigns optedin
        $this->assertEqual(0, $oDalCampaignsOptIn->performOptOut('not array'));
        $this->assertEqual(0, $oDalCampaignsOptIn->performOptOut(array()));
        $this->assertEqual(0, $oDalCampaignsOptIn->performOptOut(array(1,4,5,6)));
        
        $doMarketCampaignPref = OA_Dal::factoryDO('ext_market_campaign_pref');
        $doMarketCampaignPref->orderBy('campaignid');
        $aResult = $doMarketCampaignPref->getAll();
        $this->assertEqual(count($aResult),0);
        
        // Opt in campaigns
        $toOptIn = array($aCampaignsIds[4], $aCampaignsIds[2]);
        $minCpms = array($aCampaignsIds[4] => 1.23, $aCampaignsIds[2] => 0.56);
        $result = $oDalCampaignsOptIn->performOptIn($toOptIn, $minCpms);
        // optin other user campaign
        $oDalCampaignsOptIn->insertOrUpdateMarketCampaignPref($aCampaignsIds[5], 0.66);
        
        $this->assertEqual(0, $oDalCampaignsOptIn->performOptOut(array($aCampaignsIds[5])));
        
        $doMarketCampaignPref = OA_Dal::factoryDO('ext_market_campaign_pref');
        $doMarketCampaignPref->is_enabled = false;
        $doMarketCampaignPref->orderBy('campaignid');
        $aResult = $doMarketCampaignPref->getAll();
        $this->assertEqual(count($aResult),0);
        
        $this->assertEqual(1, $oDalCampaignsOptIn->performOptOut(array($aCampaignsIds[2],$aCampaignsIds[5])));
        
        $doMarketCampaignPref = OA_Dal::factoryDO('ext_market_campaign_pref');
        $doMarketCampaignPref->orderBy('campaignid');
        $aResult = $doMarketCampaignPref->getAll();
        $this->assertEqual(count($aResult),3);
        $this->assertEqual($aResult[0]['campaignid'], $aCampaignsIds[2]);
        $this->assertFalse($aResult[0]['is_enabled']);
        $this->assertEqual($aResult[0]['floor_price'], 0.56);
        $this->assertEqual($aResult[1]['campaignid'], $aCampaignsIds[4]);
        $this->assertTrue($aResult[1]['is_enabled']);
        $this->assertEqual($aResult[1]['floor_price'], 1.23);
        $this->assertEqual($aResult[2]['campaignid'], $aCampaignsIds[5]);
        $this->assertTrue($aResult[2]['is_enabled']);
        $this->assertEqual($aResult[2]['floor_price'], 0.66);
    }
    
    
    function testPerformOptInAll()
    {
        $aObjectsIds = $this->_prepare_users();
        // Prepare logged user
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->get($aObjectsIds['managerUserID']);
        $oUser = new OA_Permission_User($doUsers);
        global $session;
        $session['user'] = $oUser;

        $aCampaignsIds = $this->_prepare_campaigns($aObjectsIds);

        $oDalCampaignsOptIn = new OX_oxMarket_Dal_CampaignsOptIn();
        
        // Test selected (update and insert)
        $minCpms = array($aCampaignsIds[4] => 1.23);
        $defaultMinCpm = 3.45;
        $result = $oDalCampaignsOptIn->performOptInAll($defaultMinCpm, 'remnant', array(), 'ign 4');
        $this->assertEqual(0, $result);
        
        $result = $oDalCampaignsOptIn->performOptInAll($defaultMinCpm, 'all', array(), 'ign 5');
        $this->assertEqual(0, $result);
        
        $result = $oDalCampaignsOptIn->performOptInAll($defaultMinCpm, 'all', array(), 'ign 4');
        $this->assertEqual(1, $result);

        $doMarketCampaignPref = OA_Dal::factoryDO('ext_market_campaign_pref');
        $doMarketCampaignPref->orderBy('campaignid');
        $aResult = $doMarketCampaignPref->getAll();
        $this->assertEqual(count($aResult),1);
        $this->assertEqual($aResult[0]['campaignid'], $aCampaignsIds[4]);
        $this->assertTrue($aResult[0]['is_enabled']);
        $this->assertEqual($aResult[0]['floor_price'], 1.0);  // uses calculated ecpm

        $result = $oDalCampaignsOptIn->performOptInAll($defaultMinCpm, 'contract', $minCpms, 'ign 4');
        $this->assertEqual(1, $result);
        
        $doMarketCampaignPref = OA_Dal::factoryDO('ext_market_campaign_pref');
        $doMarketCampaignPref->orderBy('campaignid');
        $aResult = $doMarketCampaignPref->getAll();
        $this->assertEqual(count($aResult),1);
        $this->assertEqual($aResult[0]['campaignid'], $aCampaignsIds[4]);
        $this->assertTrue($aResult[0]['is_enabled']);
        $this->assertEqual($aResult[0]['floor_price'], 1.23); // uses given ecpm

        $result = $oDalCampaignsOptIn->performOptInAll($defaultMinCpm, 'all', array(), null);
        $this->assertEqual(3, $result);
       
        $doMarketCampaignPref = OA_Dal::factoryDO('ext_market_campaign_pref');
        $doMarketCampaignPref->orderBy('campaignid');
        $aResult = $doMarketCampaignPref->getAll();
        $this->assertEqual(count($aResult),3);
        $this->assertEqual($aResult[0]['campaignid'], $aCampaignsIds[1]);
        $this->assertTrue($aResult[0]['is_enabled']);
        $this->assertEqual($aResult[0]['floor_price'], 1.00);
        $this->assertEqual($aResult[1]['campaignid'], $aCampaignsIds[2]);
        $this->assertTrue($aResult[1]['is_enabled']);
        $this->assertEqual($aResult[1]['floor_price'], 1.00);
        $this->assertEqual($aResult[2]['campaignid'], $aCampaignsIds[4]);
        $this->assertTrue($aResult[2]['is_enabled']);
        $this->assertEqual($aResult[2]['floor_price'], 1.23); // uses last given ecpm
    }
    
    
    function testPerformOptOutAll()
    {
        $aObjectsIds = $this->_prepare_users();
        // Prepare logged user
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->get($aObjectsIds['managerUserID']);
        $oUser = new OA_Permission_User($doUsers);
        global $session;
        $session['user'] = $oUser;

        $aCampaignsIds = $this->_prepare_campaigns($aObjectsIds);

        $oDalCampaignsOptIn = new OX_oxMarket_Dal_CampaignsOptIn();
        
        // Test: no campaigns optedin
        $this->assertEqual(0, $oDalCampaignsOptIn->performOptOutAll('all', null));
        
        $doMarketCampaignPref = OA_Dal::factoryDO('ext_market_campaign_pref');
        $doMarketCampaignPref->orderBy('campaignid');
        $aResult = $doMarketCampaignPref->getAll();
        $this->assertEqual(count($aResult),0);
        
        // Opt in campaigns
        $toOptIn = array($aCampaignsIds[4], $aCampaignsIds[2]);
        $minCpms = array($aCampaignsIds[4] => 1.23, $aCampaignsIds[2] => 0.56);
        $result = $oDalCampaignsOptIn->performOptIn($toOptIn, $minCpms);
        // optin other user campaign
        $oDalCampaignsOptIn->insertOrUpdateMarketCampaignPref($aCampaignsIds[5], 0.66);
        
        $this->assertEqual(0, $oDalCampaignsOptIn->performOptOutAll('all', 'ign 5'));
        
        $doMarketCampaignPref = OA_Dal::factoryDO('ext_market_campaign_pref');
        $doMarketCampaignPref->is_enabled = false;
        $doMarketCampaignPref->orderBy('campaignid');
        $aResult = $doMarketCampaignPref->getAll();
        $this->assertEqual(count($aResult),0);
        
        $this->assertEqual(1, $oDalCampaignsOptIn->performOptOutAll('all', 'aign 2'));
        
        $doMarketCampaignPref = OA_Dal::factoryDO('ext_market_campaign_pref');
        $doMarketCampaignPref->orderBy('campaignid');
        $aResult = $doMarketCampaignPref->getAll();
        $this->assertEqual(count($aResult),3);
        $this->assertEqual($aResult[0]['campaignid'], $aCampaignsIds[2]);
        $this->assertFalse($aResult[0]['is_enabled']);
        $this->assertEqual($aResult[0]['floor_price'], 0.56);
        $this->assertEqual($aResult[1]['campaignid'], $aCampaignsIds[4]);
        $this->assertTrue($aResult[1]['is_enabled']);
        $this->assertEqual($aResult[1]['floor_price'], 1.23);
        $this->assertEqual($aResult[2]['campaignid'], $aCampaignsIds[5]);
        $this->assertTrue($aResult[2]['is_enabled']);
        $this->assertEqual($aResult[2]['floor_price'], 0.66);
        
        $this->assertEqual(0, $oDalCampaignsOptIn->performOptOutAll('remnant'));
        
        $this->assertEqual(1, $oDalCampaignsOptIn->performOptOutAll('contract'));
        
        $doMarketCampaignPref = OA_Dal::factoryDO('ext_market_campaign_pref');
        $doMarketCampaignPref->orderBy('campaignid');
        $aResult = $doMarketCampaignPref->getAll();
        $this->assertEqual(count($aResult),3);
        $this->assertEqual($aResult[0]['campaignid'], $aCampaignsIds[2]);
        $this->assertFalse($aResult[0]['is_enabled']);
        $this->assertEqual($aResult[0]['floor_price'], 0.56);
        $this->assertEqual($aResult[1]['campaignid'], $aCampaignsIds[4]);
        $this->assertFalse($aResult[1]['is_enabled']);
        $this->assertEqual($aResult[1]['floor_price'], 1.23);
        $this->assertEqual($aResult[2]['campaignid'], $aCampaignsIds[5]);
        $this->assertTrue($aResult[2]['is_enabled']);
        $this->assertEqual($aResult[2]['floor_price'], 0.66);
        
    }

    
    function _prepare_users()
    {
        $aObjectsIds = array();

        $doAgency = OA_Dal::factoryDO('agency');
        $aObjectsIds['agencyId'] = DataGenerator::generateOne($doAgency);

        $doAgency = OA_Dal::factoryDO('agency');
        $doAgency->get($aObjectsIds['agencyId']);
        $aObjectsIds['managerAccountId'] = $doAgency->account_id;

        // Create user linked to manager account
        $doUsers = OA_Dal::factoryDO('users');
        $doUsers->default_account_id = $aObjectsIds['managerAccountId'];
        $doUsers->username = 'manager';
        $aObjectsIds['managerUserID'] = DataGenerator::generateOne($doUsers);

        $doAccountsUserAssoc = OA_Dal::factoryDO('account_user_assoc');
        $doAccountsUserAssoc->account_id = $aObjectsIds['managerAccountId'];
        $doAccountsUserAssoc->user_id = $aObjectsIds['managerUserID'];
        DataGenerator::generateOne($doAccountsUserAssoc);

        $doClient = OA_Dal::factoryDO('clients');
        $doClient->agencyid = $aObjectsIds['agencyId'];
        $aObjectsIds['managerClientID'] = DataGenerator::generateOne($doClient);

        return $aObjectsIds;
    }
    

    function _prepare_campaigns($aObjectsIds)
    {
        $oDate = new Date();
        $oDate->setTZbyID('UTC');
        $dateY[0] = $oDate->format("%Y-%m-%d").' 23:59:59';
        $oDate->setYear($oDate->getYear()+1);
        $dateY[1] = $oDate->format("%Y-%m-%d").' 23:59:59';
        $oDate->setYear($oDate->getYear()-2);
        $dateY[-1] = $oDate->format("%Y-%m-%d").' 23:59:59';

        // Prepare some campaigns
        $doCampaigns = OA_Dal::factoryDO('campaigns');
        $doCampaigns->campaignname = 'campaign 1';
        $doCampaigns->expire_time = null;
        $doCampaigns->priority = DataObjects_Campaigns::PRIORITY_REMNANT;
        $doCampaigns->clientid = $aObjectsIds['managerClientID'];
        $aCampaignsIds[1] = DataGenerator::generateOne($doCampaigns);
        $doCampaigns = OA_Dal::factoryDO('campaigns');
        $doCampaigns->campaignname = 'campaign 2';
        $doCampaigns->expire_time = $dateY[1];
        $doCampaigns->priority = DataObjects_Campaigns::PRIORITY_ECPM;
        $doCampaigns->clientid = $aObjectsIds['managerClientID'];
        $aCampaignsIds[2] = DataGenerator::generateOne($doCampaigns);
        $doCampaigns = OA_Dal::factoryDO('campaigns');
        $doCampaigns->campaignname = 'campaign 3';
        $doCampaigns->expire_time = $dateY[-1];
        $doCampaigns->priority = DataObjects_Campaigns::PRIORITY_ECPM;
        $doCampaigns->clientid = $aObjectsIds['managerClientID'];
        $aCampaignsIds[3] = DataGenerator::generateOne($doCampaigns);
        $doCampaigns = OA_Dal::factoryDO('campaigns');
        $doCampaigns->campaignname = 'campaign 4';
        $doCampaigns->expire_time = null;
        $doCampaigns->priority = 7;
        $doCampaigns->clientid = $aObjectsIds['managerClientID'];
        $doCampaigns->ecpm_enabled = true; //contract campaigns with priority 6-9 have this set to true
        
        $aCampaignsIds[4] = DataGenerator::generateOne($doCampaigns);
        $doCampaigns = OA_Dal::factoryDO('campaigns');
        $doCampaigns->campaignname = 'campaign 5';
        $doCampaigns->expire_time = $dateY[1];
        $doCampaigns->priority = DataObjects_Campaigns::PRIORITY_REMNANT;
        $doCampaigns->clientid = $doCampaigns->clientid = $aObjectsIds['managerClientID']-1;
        $aCampaignsIds[5] = DataGenerator::generateOne($doCampaigns);
        $aCampaignsIds[6] = DataGenerator::generateOne($doCampaigns);
        $doCampaigns = OA_Dal::factoryDO('campaigns');
        $doCampaigns->campaignname = 'campaign 6';
        $doCampaigns->expire_time = $dateY[7];
        $doCampaigns->priority = 7;
        $doCampaigns->clientid = $aObjectsIds['managerClientID'];
        $doCampaigns->ecpm_enabled = true; //contract campaigns with priority 6-9 have this set to true
        return $aCampaignsIds;
    }
    
}

?>
