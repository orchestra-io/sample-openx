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
$Id: oxPublisherConsoleMarketPluginClient.plg.test.php 46468 2009-11-17 16:27:23Z lukasz.wikierski $
*/
require_once MAX_PATH . '/lib/OA/Dal/DataGenerator.php';

/**
 * A class for testing the Publisher Console Market Plugin Client
 *
 * @package    OpenXPlugin
 * @subpackage TestSuite
 * @author     Lukasz Wikierski <lukasz.wikierski@openx.org>
 */
class Plugins_admin_oxMarket_PublisherConsoleMarketPluginClientTest extends UnitTestCase
{
    
    function setUp()
    {
        $oPkgMgr = new OX_PluginManager();
        TestEnv::uninstallPluginPackage('openXMarket',false);
        TestEnv::installPluginPackage('openXMarket',false);
        // We can mockup classes after plugin is installed 
        require_once dirname(dirname(__FILE__)) . '/util/PublisherConsoleMarketPluginTestClient.php';
        require_once dirname(dirname(__FILE__)) . '/util/PublisherConsoleTestClient.php';
        if (!class_exists('PartialMockPublisherConsoleClient'))
        {
            Mock::generatePartial(
                'Plugins_admin_oxMarket_PublisherConsoleClient',
                'PartialMockPublisherConsoleClient',
                array('createAccount',
                      'isSsoUserNameAvailable',
                      'getStatistics',
                      'getApiKey',
                      'getApiKeyByM2MCred',
                      'linkHostedAccount',
                      'newWebsite',
                      'updateWebsite',
                      'getAdvertiserInfos')
            );
        }
        
    }

    function tearDown()
    {
        TestEnv::uninstallPluginPackage('openXMarket',false);
        DataGenerator::cleanUp();
    }

    function testCreateAccount()
    {
        $email = 'email@test.org';
        $username = 'testUsername';
        $password = 'test';
        $captcha = 'captcha';
        $captcha_random = 'captcha_random';
        $captcha_ph = 'captcha_ph';

        $response = array('accountUuid' => 'pub-acc-id', 'apiKey' => 'api-key');

        OA_Dal_ApplicationVariables::set('platform_hash', $captcha_ph);
        $callArgs = array($email, $username, md5($password), $captcha, $captcha_random, $captcha_ph);
    
        // Create mockup for PubConsoleClient
        $PubConsoleClient = new PartialMockPublisherConsoleClient($this);
        $PubConsoleClient->expect('createAccount', $callArgs);
        $PubConsoleClient->setReturnValue('createAccount', $response);
        
        $oPCMarketPluginClient = new PublisherConsoleMarketPluginTestClient();
        $oPCMarketPluginClient->setPublisherConsoleClient($PubConsoleClient);

        // Try create account - when there is no admin account set
        try {
            $result = $oPCMarketPluginClient->createAccount($email, $username, $password, $captcha, $captcha_random);
            $this->fail('Should have thrown exception');
        } catch (Plugins_admin_oxMarket_PublisherConsoleClientException $e) {
            $this->assertEqual($e->getMessage(),
                                'There is no admin account id in database');
        }
        
        // Create admin account
        $doAccounts = OA_Dal::factoryDO('accounts');
        $doAccounts->account_type = OA_ACCOUNT_ADMIN;
        $adminAccountId = DataGenerator::generateOne($doAccounts);
        
        // Test valid use
        $result = $oPCMarketPluginClient->createAccount($email, $username, $password, $captcha, $captcha_random);
        
        $this->assertTrue($result);
        
        $doMarketAssoc = OA_DAL::factoryDO('ext_market_assoc_data');
        $doMarketAssoc->account_id = DataObjects_Accounts::getAdminAccountId();
        $doMarketAssoc->find();
        $this->assertTrue($doMarketAssoc->fetch());
        $this->assertEqual($response['accountUuid'], $doMarketAssoc->publisher_account_id);
        $this->assertEqual($response['apiKey'], $doMarketAssoc->api_key);
        $this->assertEqual(Plugins_admin_oxMarket_PublisherConsoleMarketPluginClient::LINK_IS_VALID_STATUS,
            $doMarketAssoc->status);
        $this->assertFalse($doMarketAssoc->fetch()); // only one entry
        
        // Try to call this method once again
        try {
            $result = $oPCMarketPluginClient->createAccount($email, $username, $password, $captcha, $captcha_random);
            $this->fail('Should have thrown exception');
        } catch (Plugins_admin_oxMarket_PublisherConsoleClientException $e) {
            $this->assertEqual($e->getMessage(),
                                'There is already publisher_account_id on the OXP');
        }
    }
    
    
    function testLinkHostedAccount() 
    {
//        Mock::generatePartial(
//            'Plugins_admin_oxMarket_PublisherConsoleClient',
//            'PartialMockPublisherConsoleClient',
//            array('linkHostedAccount')
//        );
        $thoriumrAccountId = '11112222-3333-4444-5555-666677778888';
        $apiKey = 'api-key-231423452342345';
        $user_sso_id = 1234;
        $accountName = 'test';
        $email = 'email@test.com';
        
        // Prepare test data
        // manager account
        $doAgency  = OA_DAL::factoryDO('agency');
        $agencyId  = DataGenerator::generateOne($doAgency);
        $doAgency  = OA_Dal::staticGetDO('agency', $agencyId);
        $managerAccountId = $doAgency->account_id;
        $doAccount = OA_Dal::staticGetDO('accounts', $managerAccountId);
        $doAccount->account_name = $accountName;
        $doAccount->update();
        // publisher account
        $doAffiliates = OA_DAL::factoryDo('affiliates');
        $affiliateId = DataGenerator::generateOne($doAffiliates);
        $doAffiliates = OA_Dal::staticGetDO('affiliates', $affiliateId);
        $publisherAccountId = $doAffiliates->account_id;
        
        // user
        $doUsers  = OA_Dal::factoryDO('users');
        $doUsers->default_account_id = $managerAccountId;
        $doUsers->sso_user_id = $user_sso_id;
        $doUsers->username = 'user1';
        $doUsers->email_address = $email;
        $userId = DataGenerator::generateOne($doUsers);

        // Create mockup for PubConsoleClient
        $PubConsoleClient = new PartialMockPublisherConsoleClient($this);
        $PubConsoleClient->expectOnce('linkHostedAccount');
        $PubConsoleClient->expectArguments('linkHostedAccount', array($user_sso_id, $accountName, $email));
        $PubConsoleClient->setReturnValue('linkHostedAccount', array('accountUuid'=>$thoriumrAccountId,
                                                                     'apiKey'=>$apiKey));

        $oPCMarketPluginClient = new PublisherConsoleMarketPluginTestClient();
        $oPCMarketPluginClient->setPublisherConsoleClient($PubConsoleClient);
        
        // Test invalid sso
        try {
            $oPCMarketPluginClient->linkHostedAccount(null, $managerAccountId);
            $this->fail('Should have thrown exception');
        } catch (Plugins_admin_oxMarket_PublisherConsoleClientException $e) {
             $this->assertEqual('Invalid user_sso_id', $e->getMessage());
        }
        try {
            $oPCMarketPluginClient->linkHostedAccount('', $managerAccountId);
            $this->fail('Should have thrown exception');
        } catch (Plugins_admin_oxMarket_PublisherConsoleClientException $e) {
             $this->assertEqual('Invalid user_sso_id', $e->getMessage());
        }
        
        // Test account not linked
        try {
            $result = $oPCMarketPluginClient->linkHostedAccount($user_sso_id, $managerAccountId);
            $this->fail('Should have thrown exception');
        } catch (Plugins_admin_oxMarket_PublisherConsoleClientException $e) {
            $this->assertEqual('User user1 is not linked to manager account id: #' . $managerAccountId,
                 $e->getMessage());
        }
        
        // link user to publisher and manager accounts
        $doAccountUserAssoc = OA_Dal::factoryDO('account_user_assoc');
        $doAccountUserAssoc->user_id = $userId;
        $doAccountUserAssoc->account_id = $publisherAccountId;
        $doAccountUserAssoc->insert();
        $doAccountUserAssoc = OA_Dal::factoryDO('account_user_assoc');
        $doAccountUserAssoc->user_id = $userId;
        $doAccountUserAssoc->account_id = $managerAccountId;
        $doAccountUserAssoc->insert();
        
        // Test trying to link not manager account
        try {
            $result = $oPCMarketPluginClient->linkHostedAccount($user_sso_id, $publisherAccountId);
            $this->fail('Should have thrown exception');
        } catch (Plugins_admin_oxMarket_PublisherConsoleClientException $e) {
            $this->assertEqual('Account id: #' . $publisherAccountId . ' is not MANAGER account type',
                 $e->getMessage());
        }
        
        // Test proper linking
        $result = $oPCMarketPluginClient->linkHostedAccount($user_sso_id, $managerAccountId);
        $this->assertTrue($result);
        
        $doMarketAssoc = OA_DAL::factoryDO('ext_market_assoc_data');
        $doMarketAssoc->account_id = $manager_account_id;
        $doMarketAssoc->find();
        $this->assertTrue($doMarketAssoc->fetch());
        $this->assertEqual($thoriumrAccountId, $doMarketAssoc->publisher_account_id);
        $this->assertEqual(Plugins_admin_oxMarket_PublisherConsoleMarketPluginClient::LINK_IS_VALID_STATUS,
            $doMarketAssoc->status);
        $this->assertFalse($doMarketAssoc->fetch()); // only one entry
        
        // Try to link again
        try {
            $result = $oPCMarketPluginClient->linkHostedAccount($user_sso_id, $managerAccountId);
            $this->fail('Should have thrown exception');
        } catch (Plugins_admin_oxMarket_PublisherConsoleClientException $e) {
            $this->assertEqual('Manager account (accout id: #' . 
                               $managerAccountId . ') is already linked to the Market',
                               $e->getMessage());
        }
    }

    function testGetStatistics()
    {
        $lastUpdate1 = '0';
        $lastUpdate2 = '1';
        $aWebsitesIds2 = array( 'w_id1', 'w_id2');
        
        $callArgs1 = array($lastUpdate1, array()); // Empty array is expected for null $aWebsitesIds parameter
        $response1 = "1\t0\n";
        $callArgs2 = array($lastUpdate2, $aWebsitesIds2);
        $response2 = "2\t0\n";
        
        // Create mockup for PubConsoleClient
        $PubConsoleClient = new PartialMockPublisherConsoleClient($this);
        $PubConsoleClient->expectAt(0, 'getStatistics', $callArgs1);
        $PubConsoleClient->setReturnValueAt(0, 'getStatistics', $response1);
        $PubConsoleClient->expectAt(1, 'getStatistics', $callArgs2);
        $PubConsoleClient->setReturnValueAt(1, 'getStatistics', $response2);
        $PubConsoleClient->expectCallCount('getStatistics', 2);
        
        $oPCMarketPluginClient = new PublisherConsoleMarketPluginTestClient();
        $oPCMarketPluginClient->setPublisherConsoleClient($PubConsoleClient);

        // Try to call method when plugin is not registered
        try {
            $result = $oPCMarketPluginClient->getStatistics($lastUpdate1);
            $this->fail('Should have thrown exception');
        } catch (Plugins_admin_oxMarket_PublisherConsoleClientException $e) {
            $this->assertEqual($e->getMessage(),
                                'There is no association between PC and OXP accounts');
        }
        
        $doExtMarket = $this->createAdminAccountWithMarketAssoc();
        
        // Test null value for aWebsitesIds
        $result = $oPCMarketPluginClient->getStatistics($lastUpdate1);
        $this->assertEqual($response1, $result);
        
        // Test not empty aWebsitesIds
        $result = $oPCMarketPluginClient->getStatistics($lastUpdate2, $aWebsitesIds2);
        $this->assertEqual($response2, $result);
        
        // Clear account association
        $doExtMarket->delete();
    }
    
    function testGetApiKey()
    {
        $username = 'testUsername';
        $password = 'test';
        
        $response = 'newApiKey';
        $callArgs = array($username, $password);
    
        // Create mockup for PubConsoleClient
        $PubConsoleClient = new PartialMockPublisherConsoleClient($this);
        $PubConsoleClient->expect('getApiKey', $callArgs);
        $PubConsoleClient->setReturnValue('getApiKey', $response);
        
        $oPCMarketPluginClient = new PublisherConsoleMarketPluginTestClient();
        $oPCMarketPluginClient->setPublisherConsoleClient($PubConsoleClient);

        // Try to call method when plugin wasn't registered
        $result = $oPCMarketPluginClient->getApiKey($username, $password);
        $this->assertFalse($result);
        
        // Create account and set publisher account association data
        $doAccounts = OA_Dal::factoryDO('accounts');
        $doAccounts->account_type = OA_ACCOUNT_ADMIN;
        $adminAccountId = DataGenerator::generateOne($doAccounts);
        $doExtMarket = OA_DAL::factoryDO('ext_market_assoc_data');
        $doExtMarket->account_id = $adminAccountId;
        $doExtMarket->publisher_account_id = 'publisher_account_id';
        $doExtMarket->api_key = null;
        $doExtMarket->status = 
            Plugins_admin_oxMarket_PublisherConsoleMarketPluginClient::LINK_IS_VALID_STATUS;
        $doExtMarket->insert();
        
        // Test valid use
        $result = $oPCMarketPluginClient->getApiKey($username, $password);
        $this->assertTrue($result);
        
        $doMarketAssoc = OA_DAL::factoryDO('ext_market_assoc_data');
        $doMarketAssoc->account_id = DataObjects_Accounts::getAdminAccountId();
        $doMarketAssoc->find();
        $this->assertTrue($doMarketAssoc->fetch());
        $this->assertEqual($response, $doMarketAssoc->api_key);
        $this->assertFalse($doMarketAssoc->fetch()); // only one entry
    }
    
    function testGetApiKeyByM2MCred()
    {
        $response = 'newApiKey';
        $callArgs = array();
        $publisher_account_id = 'publisher_account_id';
    
        // Create mockup for PubConsoleClient
        $PubConsoleClient = new PartialMockPublisherConsoleClient($this);
        $PubConsoleClient->expect('getApiKeyByM2MCred', $callArgs);
        $PubConsoleClient->setReturnValue('getApiKeyByM2MCred', $response);
        
        $oPCMarketPluginClient = new PublisherConsoleMarketPluginTestClient();
        $oPCMarketPluginClient->setPublisherConsoleClient($PubConsoleClient);

        // Try to call method when plugin wasn't registered
        try {
            $result = $oPCMarketPluginClient->getApiKeyByM2MCred();
            $this->fail('Should have thrown exception');
        } catch (Plugins_admin_oxMarket_PublisherConsoleClientException $e) {
            $this->assertEqual($e->getMessage(),
                                'publisher_account_id can not be null');
        }
        
        
        // Create account and set publisher account association data
        $doAccounts = OA_Dal::factoryDO('accounts');
        $doAccounts->account_type = OA_ACCOUNT_ADMIN;
        $adminAccountId = DataGenerator::generateOne($doAccounts);
        $doExtMarket = OA_DAL::factoryDO('ext_market_assoc_data');
        $doExtMarket->account_id = $adminAccountId;
        $doExtMarket->publisher_account_id = 'publisher_account_id';
        $doExtMarket->api_key = null;
        $doExtMarket->status = 
            Plugins_admin_oxMarket_PublisherConsoleMarketPluginClient::LINK_IS_VALID_STATUS;
        $doExtMarket->insert();
        
        // Test valid use
        $result = $oPCMarketPluginClient->getApiKeyByM2MCred();
        $this->assertTrue($result);
        
        $doMarketAssoc = OA_DAL::factoryDO('ext_market_assoc_data');
        $doMarketAssoc->account_id = DataObjects_Accounts::getAdminAccountId();
        $doMarketAssoc->find();
        $this->assertTrue($doMarketAssoc->fetch());
        $this->assertEqual($response, $doMarketAssoc->api_key);
        $this->assertFalse($doMarketAssoc->fetch()); // only one entry
    }
    
    function testIsSsoUserNameAvailable()
    {
        $testName1 = 'ada';
        $testName2 = 'adam';

        // Create mockup for PubConsoleClient
        $PubConsoleClient = new PartialMockPublisherConsoleClient($this);
        $PubConsoleClient->expectArgumentsAt(0, 'isSsoUserNameAvailable', array($testName1));
        $PubConsoleClient->setReturnValueAt(0, 'isSsoUserNameAvailable', false);
        $PubConsoleClient->expectArgumentsAt(1, 'isSsoUserNameAvailable', array($testName2));
        $PubConsoleClient->setReturnValueAt(1, 'isSsoUserNameAvailable', true);

        $oPCMarketPluginClient = new PublisherConsoleMarketPluginTestClient();
        $oPCMarketPluginClient->setPublisherConsoleClient($PubConsoleClient);
        
        $result = $oPCMarketPluginClient->isSsoUserNameAvailable($testName1);
        $this->assertFalse($result);
        $result = $oPCMarketPluginClient->isSsoUserNameAvailable($testName2);
        $this->assertTrue($result);
    }

    
   function testGetAdvertiserInfos()
   {
        $response1 = array( 
            '5f7d1600-d2aa-11de-8a39-0800200c9a66' => 'Advertiser 1',
            '02d2db84-572d-42a1-be8b-77daab4d8f32' => 'Advertiser 2');
        $response2 = array( 
            '02d2db84-572d-42a1-be8b-77daab4d8f32' => 'Advertiser 2 updated',
            '90036c71-e489-4dba-b50f-c13a72d16aaa' => 'Advertiser 3');
        $response3a = array( 
            '90036c71-e489-4dba-b50f-c13a72d16aaa' => 'Advertiser 3 updated');
        $response3b = array( 
            '35769231-ea89-c3ba-6004-aaaa7145aa56' => 'Advertiser 4');
        $advUuids1 = array_keys($response1); 
        $advUuids2 = array_keys($response2);
        $advUuids3 = array_merge(array_keys($response3a),array_keys($response3b));
        
        $PubConsoleClient = new PartialMockPublisherConsoleClient($this);
        $PubConsoleClient->expectArgumentsAt(0, 'getAdvertiserInfos', array($advUuids1));
        $PubConsoleClient->setReturnValueAt(0, 'getAdvertiserInfos', $response1);
        $PubConsoleClient->expectArgumentsAt(1, 'getAdvertiserInfos', array($advUuids2));
        $PubConsoleClient->setReturnValueAt(1, 'getAdvertiserInfos', $response2);
        $PubConsoleClient->expectArgumentsAt(2, 'getAdvertiserInfos', array(array_keys($response3a)));
        $PubConsoleClient->setReturnValueAt(2, 'getAdvertiserInfos', $response3a);
        $PubConsoleClient->expectArgumentsAt(3, 'getAdvertiserInfos', array(array_keys($response3b)));
        $PubConsoleClient->setReturnValueAt(3, 'getAdvertiserInfos', $response3b);
        
        $oPCMarketPluginClient = new PublisherConsoleMarketPluginTestClient();
        $oPCMarketPluginClient->setPublisherConsoleClient($PubConsoleClient);

        $doAdvertiser = OA_DAL::factoryDO('ext_market_advertiser');
        $this->assertEqual($doAdvertiser->count(),0);
        
        $result = $oPCMarketPluginClient->getAdvertiserInfos($advUuids1);
        $this->assertEqual($result, $response1);
        
        $doAdvertiser = OA_DAL::factoryDO('ext_market_advertiser');
        $doAdvertiser->orderBy('name');
        $result = $doAdvertiser->getAll();
        $expected = array(
            array( 'market_advertiser_id' => '5f7d1600-d2aa-11de-8a39-0800200c9a66',
                   'name' => 'Advertiser 1'),
            array( 'market_advertiser_id' => '02d2db84-572d-42a1-be8b-77daab4d8f32',
                   'name' => 'Advertiser 2')
        );
        $this->assertEqual($result, $expected);
        
        $result = $oPCMarketPluginClient->getAdvertiserInfos($advUuids2);
        $this->assertEqual($result, $response2);
        
        $doAdvertiser = OA_DAL::factoryDO('ext_market_advertiser');
        $doAdvertiser->orderBy('name');
        $result = $doAdvertiser->getAll();
        $expected = array(
            array( 'market_advertiser_id' => '5f7d1600-d2aa-11de-8a39-0800200c9a66',
                   'name' => 'Advertiser 1'),
            array( 'market_advertiser_id' => '02d2db84-572d-42a1-be8b-77daab4d8f32',
                   'name' => 'Advertiser 2 updated'),
            array( 'market_advertiser_id' => '90036c71-e489-4dba-b50f-c13a72d16aaa',
                   'name' => 'Advertiser 3')
        );
        $this->assertEqual($result, $expected);
        
        $result = $oPCMarketPluginClient->getAdvertiserInfos($advUuids3,1);
        $this->assertEqual($result, $response3a+$response3b);
        
        $doAdvertiser = OA_DAL::factoryDO('ext_market_advertiser');
        $doAdvertiser->orderBy('name');
        $result = $doAdvertiser->getAll();
        $expected = array(
            array( 'market_advertiser_id' => '5f7d1600-d2aa-11de-8a39-0800200c9a66',
                   'name' => 'Advertiser 1'),
            array( 'market_advertiser_id' => '02d2db84-572d-42a1-be8b-77daab4d8f32',
                   'name' => 'Advertiser 2 updated'),
            array( 'market_advertiser_id' => '90036c71-e489-4dba-b50f-c13a72d16aaa',
                   'name' => 'Advertiser 3 updated'),
            array( 'market_advertiser_id' => '35769231-ea89-c3ba-6004-aaaa7145aa56',
                   'name' => 'Advertiser 4')
        );
        $this->assertEqual($result, $expected);
   }
    
    function testGetDictionaryData()
    {
        $GLOBALS['_MAX']['CONF']['oxMarket']['dictionaryCacheLifeTime'] = 3;
        
        // Clear var/cache
        $oCache = new OX_oxMarket_Common_Cache('DictionaryData', 'oxMarket', 
            $GLOBALS['_MAX']['CONF']['oxMarket']['dictionaryCacheLifeTime']);
        $oCache->setFileNameProtection(false);
        $oCache->clear();
        
        // Prepare test client
        $PubConsoleClient = new PublisherConsoleTestClient();
        $oPCMarketPluginClient = new PublisherConsoleMarketPluginTestClient();
        $oPCMarketPluginClient->setPublisherConsoleClient($PubConsoleClient);
        
        // (5) Test no cache, exception from PCclient, no var/data/dictionary cached data
        // should return empty array
        $PubConsoleClient->dictionaryData = new Exception('testException1');
        $result = $oPCMarketPluginClient->getDictionaryData('DictionaryData','testGetDictionaryData');
        $this->assertEqual(array(), $result);
        
        // Cache file shouldn't be created
        $this->assertFalse($oCache->load(true));
        
        // (4) Test no cache / exception from PC client / var/data/dictionary exists
        // to get var/data/dictionray file we have to get DefaultRestrictions permament cache
        $oCache2 = new OX_oxMarket_Common_Cache('DefaultRestrictions', 'oxMarket', 
            $GLOBALS['_MAX']['CONF']['oxMarket']['dictionaryCacheLifeTime']);
        $oCache2->setFileNameProtection(false);
        $oCache2->clear();
        $PubConsoleClient->dictionaryData = new Exception('testException2');
        $result = $oPCMarketPluginClient->getDictionaryData('DefaultRestrictions','testGetDictionaryData');
        $this->assertTrue(is_array($result['attribute']));
        $this->assertTrue(is_array($result['category']));
        $this->assertTrue(is_array($result['type']));
        
        // Cache file shouldn't be created
        $this->assertFalse($oCache->load(true));
        $this->assertFalse($oCache2->load(true));
        
        // (2) Test no cache / recieved data from PC client
        $data1 = array('1' => 'test');
        $PubConsoleClient->dictionaryData = $data1;
        $result = $oPCMarketPluginClient->getDictionaryData('DictionaryData','testGetDictionaryData');
        $this->assertEqual($result, $data1);
        // cache is created and is valid
        $this->assertTrue($oCache->load(false));
        
        // (1) Test there is valid cache (from previous test case)
        $data2 = array('2' => 'test2');
        $PubConsoleClient->dictionaryData = $data2;
        $result = $oPCMarketPluginClient->getDictionaryData('DictionaryData','testGetDictionaryData');
        $this->assertEqual($result, $data1);
        
        // Prepare for next test case
        // Wait 5 seconds 
        sleep(5);
        // Check cache file, should exists but should be invalid
        $oCache = new OX_oxMarket_Common_Cache('DictionaryData', 'oxMarket', 
            $GLOBALS['_MAX']['CONF']['oxMarket']['dictionaryCacheLifeTime']);
        $oCache->setFileNameProtection(false);
        $this->assertTrue($oCache->load(true));
        $this->assertFalse($oCache->load(false));
        
        // (3) Test there is invalid cache and exception is thrown from PC client
        $PubConsoleClient->dictionaryData = new Exception('testException3');
        $result = $oPCMarketPluginClient->getDictionaryData('DictionaryData','testGetDictionaryData');
        $this->assertEqual($result, $data1);
        
        // Cache shouldn't be changed (remains invalid)
        $this->assertTrue($oCache->load(true));
        $this->assertFalse($oCache->load(false));
        
        // (2) Test invalid cache, valid response from PC client
        $data3 = array('3' => 'test3'); 
        $PubConsoleClient->dictionaryData = $data3;
        $result = $oPCMarketPluginClient->getDictionaryData('DictionaryData','testGetDictionaryData');
        $this->assertEqual($result, $data3);
        // cache is created and is valid
        $this->assertTrue($oCache->load(false));
    }
    
    function testGetDefaultRestrictions()
    {
        $GLOBALS['_MAX']['CONF']['oxMarket']['dictionaryCacheLifeTime'] = 60;

        // Clear var/cache
        $oCache = new OX_oxMarket_Common_Cache('DefaultRestrictions', 'oxMarket', 
            $GLOBALS['_MAX']['CONF']['oxMarket']['dictionaryCacheLifeTime']);
        $oCache->setFileNameProtection(false);
        $oCache->clear();
        
        // Prepare test client
        $PubConsoleClient = new PublisherConsoleTestClient();
        $oPCMarketPluginClient = new PublisherConsoleMarketPluginTestClient();
        $oPCMarketPluginClient->setPublisherConsoleClient($PubConsoleClient);
        
        // Test bundled var/data/dictionary cache
        $PubConsoleClient->dictionaryData = new Exception('testException');
        $result = $oPCMarketPluginClient->getDefaultRestrictions();
        $this->assertTrue(is_array($result['attribute']));
        $this->assertTrue(is_array($result['category']));
        $this->assertTrue(is_array($result['type']));
        // Cache file shouldn't be created
        $this->assertFalse($oCache->load(true));
        
        // Test creating own cache file
        $data = array( '1' => 'test');
        $PubConsoleClient->dictionaryData = $data;
        $result = $oPCMarketPluginClient->getDefaultRestrictions();
        $this->assertEqual($result, $data);
        // Cache file is created
        $this->assertTrue($oCache->load(false));
        
        // clear cache
        $oCache->clear();
    }
    
    function testGetAdCategories()
    {
        $GLOBALS['_MAX']['CONF']['oxMarket']['dictionaryCacheLifeTime'] = 60;

        // Clear var/cache
        $oCache = new OX_oxMarket_Common_Cache('AdCategories', 'oxMarket', 
            $GLOBALS['_MAX']['CONF']['oxMarket']['dictionaryCacheLifeTime']);
        $oCache->setFileNameProtection(false);
        $oCache->clear();
        
        // Prepare test client
        $PubConsoleClient = new PublisherConsoleTestClient();
        $oPCMarketPluginClient = new PublisherConsoleMarketPluginTestClient();
        $oPCMarketPluginClient->setPublisherConsoleClient($PubConsoleClient);
        
        // Test bundled var/data/dictionary cache
        $PubConsoleClient->dictionaryData = new Exception('testException');
        $result = $oPCMarketPluginClient->getAdCategories();
        $this->assertTrue(is_array($result));
        // There should be at least 30 categories
        $this->assertTrue(count($result)>=25); 
        // All categories have id and description
        foreach ($result as $k => $v) {
            $this->assertTrue(is_int($k));
            $this->assertTrue(is_string($v));
        }
        // Test few categories (shouldn't change)
        $this->assertEqual($result[1], 'Adult');
        $this->assertEqual($result[11], 'Food and Drink');
        $this->assertEqual($result[30], 'Personal Finance');

        // Cache file shouldn't be created
        $this->assertFalse($oCache->load(true));
        
        // Test creating own cache file
        $data = array( '1' => 'category1');
        $PubConsoleClient->dictionaryData = $data;
        $result = $oPCMarketPluginClient->getAdCategories();
        $this->assertEqual($result, $data);
        // Cache file is created
        $this->assertTrue($oCache->load(false));
        
        // clear cache
        $oCache->clear();
    }
    
    function testGetCreativeTypes()
    {
        $GLOBALS['_MAX']['CONF']['oxMarket']['dictionaryCacheLifeTime'] = 60;

        // Clear var/cache
        $oCache = new OX_oxMarket_Common_Cache('CreativeTypes', 'oxMarket', 
            $GLOBALS['_MAX']['CONF']['oxMarket']['dictionaryCacheLifeTime']);
        $oCache->setFileNameProtection(false);
        $oCache->clear();
        
        // Prepare test client
        $PubConsoleClient = new PublisherConsoleTestClient();
        $oPCMarketPluginClient = new PublisherConsoleMarketPluginTestClient();
        $oPCMarketPluginClient->setPublisherConsoleClient($PubConsoleClient);
        
        // Test bundled var/data/dictionary cache
        $PubConsoleClient->dictionaryData = new Exception('testException');
        $result = $oPCMarketPluginClient->getCreativeTypes();
        $this->assertTrue(is_array($result));
        // There should be at least 30 categories
        $this->assertTrue(count($result)>=9); 
        // All categories have id and description
        foreach ($result as $k => $v) {
            $this->assertTrue(is_int($k));
            $this->assertTrue(is_string($v));
        }
        // Test few categories (shouldn't change)
        $this->assertEqual($result[1], 'Image');
        $this->assertEqual($result[4], 'Video');
        $this->assertEqual($result[9], 'Pop-Under');
        
        // Cache file shouldn't be created
        $this->assertFalse($oCache->load(true));
        
        // Test creating own cache file
        $data = array( '1' => 'creativeType1');
        $PubConsoleClient->dictionaryData = $data;
        $result = $oPCMarketPluginClient->getCreativeTypes();
        $this->assertEqual($result, $data);
        // Cache file is created
        $this->assertTrue($oCache->load(false));
        
        // clear cache
        $oCache->clear();
    }
    
    function testGetCreativeAttributes()
    {
        $GLOBALS['_MAX']['CONF']['oxMarket']['dictionaryCacheLifeTime'] = 60;

        // Clear var/cache
        $oCache = new OX_oxMarket_Common_Cache('CreativeAttributes', 'oxMarket', 
            $GLOBALS['_MAX']['CONF']['oxMarket']['dictionaryCacheLifeTime']);
        $oCache->setFileNameProtection(false);
        $oCache->clear();
        
        // Prepare test client
        $PubConsoleClient = new PublisherConsoleTestClient();
        $oPCMarketPluginClient = new PublisherConsoleMarketPluginTestClient();
        $oPCMarketPluginClient->setPublisherConsoleClient($PubConsoleClient);
        
        // Test bundled var/data/dictionary cache
        $PubConsoleClient->dictionaryData = new Exception('testException');
        $result = $oPCMarketPluginClient->getCreativeAttributes();
        $this->assertTrue(is_array($result));
        // There should be at least 15 attributes
        $this->assertTrue(count($result)>=15); 
        // All categories have id and description
        foreach ($result as $k => $v) {
            $this->assertTrue(is_int($k));
            $this->assertTrue(is_string($v));
        }
        // Test few categories (shouldn't change)
        $this->assertEqual($result[1], 'Alcohol');
        $this->assertEqual($result[7], 'Excessive Animation');
        $this->assertEqual($result[15], 'Tobacco');
        
        // Cache file shouldn't be created
        $this->assertFalse($oCache->load(true));
        
        // Test creating own cache file
        $data = array( '1' => 'creativeAttributes1');
        $PubConsoleClient->dictionaryData = $data;
        $result = $oPCMarketPluginClient->getCreativeAttributes();
        $this->assertEqual($result, $data);
        // Cache file is created
        $this->assertTrue($oCache->load(false));
        
        // clear cache
        $oCache->clear();
    }
    
    
    function testGetCreativeSizes()
    {
        $GLOBALS['_MAX']['CONF']['oxMarket']['dictionaryCacheLifeTime'] = 60;

        // Clear var/cache
        $oCache = new OX_oxMarket_Common_Cache('CreativeSizes', 'oxMarket', 
            $GLOBALS['_MAX']['CONF']['oxMarket']['dictionaryCacheLifeTime']);
        $oCache->setFileNameProtection(false);
        $oCache->clear();
        
        // Prepare test client
        $PubConsoleClient = new PublisherConsoleTestClient();
        $oPCMarketPluginClient = new PublisherConsoleMarketPluginTestClient();
        $oPCMarketPluginClient->setPublisherConsoleClient($PubConsoleClient);
        
        // Test bundled var/data/dictionary cache
        $PubConsoleClient->dictionaryData = new Exception('testException');
        $result = $oPCMarketPluginClient->getCreativeSizes();
        $this->assertTrue(is_array($result));
        // There should be at least 15 sizes
        $this->assertTrue(count($result)>=15); 
        // All Sizes id and 
        foreach ($result as $k => $v) {
            $this->assertTrue(is_string($k));
            $this->assertTrue(is_numeric($v['size_id']));
            $this->assertTrue(is_string($v['name']));
            $this->assertTrue(is_numeric($v['height']));
            $this->assertTrue(is_numeric($v['width']));
            $this->assertEqual(count($v),4);
            $this->assertEqual($k, $v['width'].'x'.$v['height']);
        }
        // Test few sizes (shouldn't change)
        $aSizes = array (
            '468x60' => array( 'size_id' => 1,
                               'name' => 'IAB Full Banner',
                               'width' => 468,
                               'height' => 60),
            '125x125' => array( 'size_id' => 8,
                               'name' => 'IAB Square Button',
                               'width' => 125,
                               'height' => 125),
        );
        $this->assertEqual($result['468x60'], $aSizes['468x60']);
        $this->assertEqual($result['125x125'], $aSizes['125x125']);
        
        // Cache file shouldn't be created
        $this->assertFalse($oCache->load(true));
        
        // Test creating own cache file
        $aSizes['777x33'] = array( 'size_id' => 77,
                               'name' => 'Test Banner Size',
                               'width' => 777,
                               'height' => 33);
        $PubConsoleClient->dictionaryData = $aSizes;
        $result = $oPCMarketPluginClient->getCreativeSizes();
        $this->assertEqual($result, $aSizes);
        // Cache file is created
        $this->assertTrue($oCache->load(false));
        
        // clear cache
        $oCache->clear();
    }
    
    function testNewWebsite()
    {
        $websiteUrl = 'http:\\test.url';
        $websiteName = 'test website';
        $website_id = 'thorium_website_id';
        
        // Create mockup for PubConsoleClient
        $PubConsoleClient = new PartialMockPublisherConsoleClient($this);
        $PubConsoleClient->expectOnce('newWebsite', array($websiteUrl, $websiteName));
        $PubConsoleClient->setReturnValue('newWebsite', $website_id);

        $oPCMarketPluginClient = new PublisherConsoleMarketPluginTestClient();
        $oPCMarketPluginClient->setPublisherConsoleClient($PubConsoleClient);
        
        // Try to call method when plugin is not registered
        try {
            $result = $oPCMarketPluginClient->newWebsite($websiteUrl, $websiteName);
            $this->fail('Should have thrown exception');
        } catch (Plugins_admin_oxMarket_PublisherConsoleClientException $e) {
            $this->assertEqual($e->getMessage(),
                                'There is no association between PC and OXP accounts');
        }
        
        $doExtMarket = $this->createAdminAccountWithMarketAssoc();
        
        $result = $oPCMarketPluginClient->newWebsite($websiteUrl, $websiteName);
        $this->assertEqual($result, $website_id);
        
        // clean up
        $doExtMarket->delete();
    }
    
    
    function testUpdateWebsite()
    {
        $websiteUrl = 'http:\\test.url';
        $websiteName = 'web name';
        $website_id = 'thorium_website_id';
        $att_ex = array(1,2); 
        $cat_ex = null;
        $typ_ex = array(3);
    
        // Create mockup for PubConsoleClient
        $PubConsoleClient = new PartialMockPublisherConsoleClient($this);
        $call1 = array($website_id, $websiteUrl, $att_ex, array(), $typ_ex, null);
        $call2 = array( $website_id, $websiteUrl, $att_ex, array(), $typ_ex, $websiteName);

        $PubConsoleClient->expectAt(0, 'updateWebsite', $call1);
        $PubConsoleClient->expectAt(1, 'updateWebsite', $call2);
        $PubConsoleClient->setReturnValue('updateWebsite', $website_id);
        $PubConsoleClient->expectCallCount('updateWebsite', 2);
        
        $oPCMarketPluginClient = new PublisherConsoleMarketPluginTestClient();
        $oPCMarketPluginClient->setPublisherConsoleClient($PubConsoleClient);
        
        // Try to call method when plugin is not registered
        try {
            $result = $oPCMarketPluginClient->updateWebsite(
                $website_id, $websiteUrl, $att_ex, $cat_ex, $typ_ex);
            $this->fail('Should have thrown exception');
        } catch (Plugins_admin_oxMarket_PublisherConsoleClientException $e) {
            $this->assertEqual($e->getMessage(),
                                'There is no association between PC and OXP accounts');
        }
        
        // Use methods without sending websiteName
        $doExtMarket = $this->createAdminAccountWithMarketAssoc();
        $result = $oPCMarketPluginClient->updateWebsite(
                    $website_id, $websiteUrl, $att_ex, $cat_ex, $typ_ex);
        $this->assertEqual($result, $website_id);
        
        // Send website name
        $this->createAdminAccountWithMarketAssoc();
        $result = $oPCMarketPluginClient->updateWebsite(
                    $website_id, $websiteUrl, $att_ex, $cat_ex, $typ_ex, $websiteName);
        $this->assertEqual($result, $website_id);
        
        // clean up
        $doExtMarket->delete();
    }

    
    /**
     * Create admin account and add association to the market
     *
     * @return DataObjects_Ext_market_assoc_data
     */
    private function createAdminAccountWithMarketAssoc()
    {
        // Create account and set publisher account association data
        $doAccounts = OA_Dal::factoryDO('accounts');
        $doAccounts->account_type = OA_ACCOUNT_ADMIN;
        $adminAccountId = DataGenerator::generateOne($doAccounts);
        $doExtMarket = OA_DAL::factoryDO('ext_market_assoc_data');
        $doExtMarket->account_id = $adminAccountId;
        $doExtMarket->publisher_account_id = 'publisher_account_id';
        $doExtMarket->api_key = 'api_key';
        $doExtMarket->status = 
            Plugins_admin_oxMarket_PublisherConsoleMarketPluginClient::LINK_IS_VALID_STATUS;
        $doExtMarket->insert();
        return $doExtMarket;
    }

    
    /**
     * Can be run as test (simple rename method to testGenerateDictionaryFiles
     * Will call PubConsole for dictionary data.
     * 
     */
    function generateDictionaryFiles()
    {
        // Clear var/cache
        $oCache = new OX_oxMarket_Common_Cache('AdCategories', 'oxMarket', 60);
        $oCache->setFileNameProtection(false);
        $oCache->clear();
        $oCache = new OX_oxMarket_Common_Cache('CreativeTypes', 'oxMarket', 60);
        $oCache->setFileNameProtection(false);
        $oCache->clear();
        $oCache = new OX_oxMarket_Common_Cache('CreativeAttributes', 'oxMarket', 60);
        $oCache->setFileNameProtection(false);
        $oCache->clear();
        $oCache = new OX_oxMarket_Common_Cache('CreativeSizes', 'oxMarket', 60);
        $oCache->setFileNameProtection(false);
        $oCache->clear();
        
        // call all dictionary methods
        $oPCMarketPluginClient = new Plugins_admin_oxMarket_PublisherConsoleMarketPluginClient(false);
        $oPCMarketPluginClient->getAdCategories();
        $oPCMarketPluginClient->getCreativeTypes();
        $oPCMarketPluginClient->getCreativeAttributes();
        $oPCMarketPluginClient->getDefaultRestrictions();
        $oPCMarketPluginClient->getCreativeSizes();
        
    }
}