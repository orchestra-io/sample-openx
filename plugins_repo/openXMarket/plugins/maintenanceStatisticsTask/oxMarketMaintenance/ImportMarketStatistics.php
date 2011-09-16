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
$Id: ImportMarketStatistics.php 49222 2010-02-19 20:26:13Z matthieu.aubry $
*/
require_once LIB_PATH . '/Maintenance/Statistics/Task.php';
require_once LIB_PATH . '/Plugin/Component.php';

/**
 * The MSE process task class that import statistics data from Publisher Console
 * during the MSE run.
 *
 * @package    OpenXPlugin
 * @subpackage oxMarket
 * @author     Lukasz Wikierski <lukasz.wikierski@openx.org>
 */
class Plugins_MaintenaceStatisticsTask_oxMarketMaintenance_ImportMarketStatistics extends OX_Maintenance_Statistics_Task
{
    const LAST_STATISTICS_VERSION_VARIABLE = 'last_statistics_version_api_v2';
    
    const LAST_IMPORT_STATS_DATE = 'last_import_stats_date';

    /**
     * Market plugin
     * @var Plugins_admin_oxMarket_oxMarket
     */
    protected $oMarketComponent;
    
    /**
     * Was script initiated from separate (dedicated) maintenance script?
     * @var bool
     */
    private $calledFromSeparateMaintenace;
    
    
    /**
     * Array of advertiser Ids seen during stats import
     * 
     * @var array
     */
    protected $marketAdvertiserIds = array();
    
    /**
     * The constructor method.
     *
     * @param unknown_type $calledFromSeparateMaintenace
     */
    function __construct($calledFromSeparateMaintenace = false)
    {
        $this->calledFromSeparateMaintenace = $calledFromSeparateMaintenace;
        parent::OX_Maintenance_Statistics_Task();
    }

    /**
     * The implementation of the OA_Task::run() method that 
     * downloads statistics from publisher console
     */
    function run()
    {
        // check if it's allowed to run this task in given maintenance (common, or separate script)
        if (!$this->canRunTask()) {
            return false;
        }
        OA::debug('Started oxMarket_ImportMarketStatistics');
        try {
            $oPublisherConsoleApiClient = $this->getPublisherConsoleApiClient();
            // select only registered and active accounts (skip isPluginActive() method)
            $oAccount = OA_Dal::factoryDO('ext_market_assoc_data');
            $oAccount->status = 0;  
            $oAccount->whereAdd('api_key IS NOT NULL');
            $oAccount->find();
            OA::debug('Starting to download websites Market stats...');
            $countWebsites = 0;
            while ($oAccount->fetch()) {
                try {
                    $accountId = (int)$oAccount->account_id;
                    $oPublisherConsoleApiClient->setWorkAsAccountId($accountId);                
                    $last_update = $this->getLastUpdateVersionNumber($accountId);
                    $aWebsitesIds = $this->getRegisteredWebsitesIds($accountId);
                    
                    if (is_array($aWebsitesIds) && count($aWebsitesIds)>0
                        && !$this->shouldSkipAccount($accountId)) {
                        // Download statistics only if there are registered websites
                        // and account is locally active  
                        do {
                            OA::debug('Fetching stats for accountId = '.$accountId.'...');
                            $data = $oPublisherConsoleApiClient->getStatistics($last_update, $aWebsitesIds);
                            $endOfData = $this->getStatisticFromString($data, $last_update, $accountId);
                            
                            if($countWebsites % 10 == 0 && $countWebsites > 0) { 
                                OA::debug('Downloaded stats for '.$countWebsites . ' websites...');
                                $this->fetchMarketAdvertisers();
                            }
                            $countWebsites++;
                        } while ($endOfData === false);
                        $this->setLastUpdateDate($accountId);
                    }
                } catch (Exception $e) {
                    OA::debug('Following exception occured for account ['. $oAccount->account_id .']: [' 
                              . $e->getCode() .'] '. $e->getMessage());
                }
            }
        } catch (Exception $e) {
            OA::debug('Following exception occured: [' . $e->getCode() .'] '. $e->getMessage());
        }
        $this->fetchMarketAdvertisers();
        OA::debug('Finished, downloaded stats for '.$countWebsites . ' websites. ');
        
        // always clear workAsAccountId
        if (isset($oPublisherConsoleApiClient)) {
            $oPublisherConsoleApiClient->setWorkAsAccountId(null);
        }
        OA::debug('Finished oxMarket_ImportMarketStatistics');
        return true;
    }
       
    protected function fetchMarketAdvertisers()
    {
        if(!empty($this->marketAdvertiserIds)) {
            OA::debug('Fetching '.$count.' market advertiser IDs...');
            $count = count($this->marketAdvertiserIds);
            $advertiserIds = array_unique($this->marketAdvertiserIds);
            sort($advertiserIds);
            $oPublisherConsoleApiClient = $this->getPublisherConsoleApiClient();
            $oPublisherConsoleApiClient->getAdvertiserInfos($advertiserIds);
            $this->marketAdvertiserIds = array();
        }
        return;
    }
    
    /**
     * Get LastUpdate version number for given account
     *
     * @param int $accountId
     * @return string
     */
    function getLastUpdateVersionNumber($accountId)
    {
        $value = OA_Dal::factoryDO('ext_market_general_pref')
                 ->findAndGetValue($accountId, self::LAST_STATISTICS_VERSION_VARIABLE);
        return (isset($value)) ? $value : '0';
    }
    
    /**
     * Get array of website_id stored in database 
     * associated with given account
     *
     * @param int $accountId
     * @return array of strings (website Ids)
     */
    function getRegisteredWebsitesIds($accountId)
    {
        $oWebsites = OA_Dal::factoryDO('ext_market_website_pref');
        return $oWebsites->getRegisteredWebsitesIds($accountId);
    }
    
    /**
     * Returns the website_id (affiliateid) from the website UUID 
     * 
     * @param $uuid
     * @return int or false
     */
    protected function getWebsiteIdFromUUID($uuid)
    {
        static $UUIDToWebsiteId = null;
        if(is_null($UUIDToWebsiteId)) {
            $oWebsites = OA_Dal::factoryDO('ext_market_website_pref');
            $return = $oWebsites->getAll();
            foreach($return as $row ) {
                $UUIDToWebsiteId[$row['website_id']] = $row['affiliateid'];
            }
        }
        if(isset($UUIDToWebsiteId[$uuid])) {
            return $UUIDToWebsiteId[$uuid];
        }
        return false;
    }
    
    const NON_EMPTY_CHANNEL_EXPECTED = 'import-stats-non-empty-channel-expected-account-';
    
    protected function getMarketBannerIdFromWebsiteId($websiteId)
    {
        static $accountToBannerId = array();
        if(!isset($accountToBannerId[$websiteId])) {
            $aConf = $GLOBALS['_MAX']['CONF'];
            $query = "
            SELECT 
                a.clientid as client_id,
                c.campaignid as placement_id,
                b.bannerid as ad_id
            FROM 
            	{$aConf['table']['prefix']}{$aConf['table']['affiliates']} affiliates
                INNER JOIN {$aConf['table']['prefix']}{$aConf['table']['clients']} a ON affiliates.agencyid = a.agencyid
                INNER JOIN {$aConf['table']['prefix']}{$aConf['table']['campaigns']} c ON a.clientid = c.clientid
                INNER JOIN {$aConf['table']['prefix']}{$aConf['table']['banners']} b ON c.campaignid = b.campaignid
            WHERE
                affiliates.affiliateid = {$websiteId} AND a.type = 1 AND c.type = 1";
            $oDbh = OA_DB::singleton();
            $return = $oDbh->query($query);
            $return = $return->fetchRow();
            $accountToBannerId[$websiteId] = $return['ad_id'];
        }
        return $accountToBannerId[$websiteId];
    }
    
    /**
     * Expects a channel at format: 
     * oxpv1:$LOCAL_ADVERTISER_ID-$LOCAL_CAMPAIGN_ID-$LOCAL_BANNER_ID-$LOCAL_WEBSITE_ID-$LOCAL_ZONE_ID
     * Returns an array of IDs, or false if the channel wasn't correctly formated
     * 
     * @return array or false 
     */
    protected function parseChannel($channel)
    {
        if(empty($channel)) {
            return false;
        }
        $expected = 'oxpv1:';
        if(substr($channel, 0, strlen($expected)) != $expected) {
            return false;
        }
        $channel = substr($channel, strlen($expected));
        $channel = explode("-", $channel);
        if(count($channel) != 5) {
            return false;
        }
        return $channel;
    }
    
    /**
     * Insert data from getStatistics to database 
     *
     * @param string $data
     * @param int $last_update
     * @param int $accountId
     * @return bool Is end of data from Pub Console (always true for oxmStatistics)
     */
    protected function getStatisticFromString($data, &$last_update, $accountId)
    {
        if (!empty($data)) {
            try {
                $oDB = OA_DB::singleton(); 
                $supports_transactions = $oDB->supports('transactions');
                if ($supports_transactions) {
                    $oDB->beginTransaction();
                }
                 
                $aLines = explode("\n", $data);
                $aFirstRow = explode("\t", $aLines[0]); 
                $last_update = $aFirstRow[0];
                $endOfData = true;
                if (array_key_exists(1,$aFirstRow)) {
                    $endOfData = (intval($aFirstRow[1])==1);
                }
                $count = count($aLines) - 1;
                
                $nonEmptyChannelExpected = (bool)$this->oMarketComponent->getMarketUserVariable(self::NON_EMPTY_CHANNEL_EXPECTED.$accountId);
                for ($i = 1; $i < $count; $i++) {
                    $aRow = explode("\t", $aLines[$i]);
                    if (count($aRow) > 8) {
                        $UUID = $aRow[0];
                        $websiteId = $this->getWebsiteIdFromUUID($UUID);
                        if($websiteId == false) {
                            continue;
                        }
                        $channel = $aRow[8];
                        
                        $adId = null;
                        if(empty($channel)) {
                            if($nonEmptyChannelExpected) {
                                // channel was empty, but we were expecting a non-empty channel string
                                continue;
                            }
                            $zoneId = 0;
                        } else {
                            $channel = $this->parseChannel($channel);
                            if($channel == false) {
                                continue;
                            }
                            if(!$nonEmptyChannelExpected) {
                                // channel was defined, but we were expecting an empty channel
                                // we will, from now on, expect only non empty channels
                                $this->oMarketComponent->setMarketUserVariable(self::NON_EMPTY_CHANNEL_EXPECTED.$accountId, '1');
                                $nonEmptyChannelExpected = true;
                            }
                            $zoneId = $channel[4];
                            $websiteIdFromChannel = $channel[3];
                            if($websiteIdFromChannel != $websiteId) {
                                continue;
                            }
                            $adId = $channel[2];
                        }
                        $impressions = $aRow[4];
                        
                        // we do not download stats that don't have impressions but have requests 
                        if($impressions <= 0) {
                            continue;
                        }
                        if(empty($adId)) {
                            $adId = $this->getMarketBannerIdFromWebsiteId($websiteId);
                        }
                        $marketAdvertiserId = $aRow[9];
                        if(!empty($marketAdvertiserId)) {
                            $this->marketAdvertiserIds[] = $marketAdvertiserId;
                            $this->marketAdvertiserIds = array_unique($this->marketAdvertiserIds);
                        }
                        if( empty($marketAdvertiserId)) {
                                $marketAdvertiserId = '';
                        }
                        $marketStatsRow = array(
                            'website_id' => $websiteId, 
                        	'ad_height' => $aRow[1], 
                            'ad_width' => $aRow[2], 
                            'date_time' => $aRow[3], 
                            'impressions' => $impressions, 
                            'revenue' => $aRow[5],
                            'requests' => 0,
                            'clicks' => empty($aRow[7]) ? 0 : $aRow[7],
                            'market_advertiser_id' => $marketAdvertiserId,
                            'website_id' => $websiteId,
                            'zone_id' => $zoneId,
                            'ad_id' => $adId
                        );
                        $marketStatsToRecord[] = $marketStatsRow;
                    }
                    else {
                        throw new Exception('Invalid amount of statistics items returned: ' . $aLines[$i]);
                    }
                }
                
                if(!empty($marketStatsToRecord)) {
                    $dal = new OA_Dal();
                    $primaryKey = array('date_time', 'website_id', 'zone_id', 'ad_id','ad_width', 'ad_height', 'market_advertiser_id');
                    $dal->batchInsert($GLOBALS['_MAX']['CONF']['table']['prefix'].'ext_market_stats', array_keys($marketStatsRow), $marketStatsToRecord, $replace = true, $primaryKey);
                }
                // Update last statistics version serial number in same DB transaction
                $oPluginSettings = OA_Dal::factoryDO('ext_market_general_pref');
                $result = $oPluginSettings->insertOrUpdateValue($accountId, 
                            self::LAST_STATISTICS_VERSION_VARIABLE, $last_update);
                if ($result===false) {
                    throw new Exception('Cannot save last statistics version variable for account: '.$accountId);
                }
                if ($supports_transactions) {
                    $oDB->commit();
                }
                return $endOfData;
            } 
            catch (Exception $e) {
                if ($supports_transactions) {
                    $oDB->rollback();
                }
                OA::debug('Following exception occured ' . $e->getMessage());
                throw $e;
            }
        }
        return true;
    }

    /**
     * get Publisher Console Api Client from Market Plugin
     * used in tests to set client mockup 
     *
     * @return Plugins_admin_oxMarket_PublisherConsoleMarketPluginClient
     */
    protected function getPublisherConsoleApiClient()
    {
        $this->initMarketComponent();
        return $this->oMarketComponent->getPublisherConsoleApiClient();
    }
    
    /**
     * Check if plugin is active (registered to the market)
     *
     * @return boolean
     */
    protected function isPluginActive()
    {
        $this->initMarketComponent();
        return $this->oMarketComponent->isActive();
    }
    
    /**
     * Initialize market component if needed
     */
    protected function initMarketComponent()
    {
        if (!isset($this->oMarketComponent)) {
            $this->oMarketComponent = OX_Component::factory('admin', 'oxMarket');
        }
    }
    
    /**
     * Check if it is allowed to run task in maintenance or separate script
     *
     * @return bool true if it's allowed
     */
    public function canRunTask()
    {
        return ($this->calledFromSeparateMaintenace ==
                (bool)$GLOBALS['_MAX']['CONF']['oxMarket']['separateImportStatsScript']);
    }
    
    
    /**
     * Check if given account could be ommited during statistics update (
     *
     * @param int $accountId
     * @return bool true if account doesn't have local stats and can be skipped
     */
    protected function shouldSkipAccount($accountId)
    {
        // in 2.8.4, we force download of stats for all accounts
        return false;
    }
    
    
    /**
     * Set last import statistics update date to now
     *
     * @param int $accountId
     */
    protected function setLastUpdateDate($accountId)
    {
        $oCurrDate = new Date();
        $oPluginSettings = OA_Dal::factoryDO('ext_market_general_pref');
        $oPluginSettings->insertOrUpdateValue($accountId, 
                            self::LAST_IMPORT_STATS_DATE, $oCurrDate->getDate());
    }
}
?>