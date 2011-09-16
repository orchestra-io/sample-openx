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
$Id: CampaignsOptIn.php$
*/

/**
 * CampaignsOptIn DAL Library
 *
 * @package    openXMarket
 * @author     Miguel Correa  <miguel.correa@openx.org>
 * @author     Lukasz Wikierski  <lukasz.wikierski@openx.org>
 */
class OX_oxMarket_Dal_CampaignsOptIn
{


    /**
     * Perform Opt In works on selected campaigns, set default CPM or $minCpms if available for $toOptIn campaigns
     *
     * @param array $toOptIn array of campaigns ids 
     * @param array $minCpms Array of min cpms for campaigns (indexed by campaigns ids)
     * @return int campains opted in
     */
    public function performOptIn($toOptIn, $minCpms)
    {
        // For non remnant mode toOptIn have to be non empty array
        if((!is_array($toOptIn) || count($toOptIn)==0)) {
            return 0;
        }

        // Opt-in campaigns based on the submitted values
        $doCampaigns = OA_Dal::factoryDO('campaigns');
        $doClients = OA_Dal::factoryDO('clients');

        // Get campaigns that belong to advertiser of the current agency
        $doClients->agencyid = OA_Permission::getAgencyId();
        $doCampaigns->joinAdd($doClients, 'LEFT');

        // Ignore already ended campaigns
        $doCampaigns->whereAdd(" expire_time >= '" . $this->getTodayDate() .
                               "' OR expire_time IS NULL");
        // add campaigns ids
        $oDbh = OA_DB::singleton();
        foreach ($toOptIn as $k => $campaignId) {
            $toOptIn[$k] = $oDbh->quote($campaignId, 'integer');
        }
        $doCampaigns->whereAdd(' campaignid IN (' . implode(",", $toOptIn) . ')');

        $doCampaigns->find();
        $campaignsOptedIn = $doCampaigns->getRowCount();
        while ($doCampaigns->fetch()) {
            $campaignId = $doCampaigns->campaignid;
            $this->insertOrUpdateMarketCampaignPref($campaignId, $minCpms[$campaignId]);
        }

        return $campaignsOptedIn;
    }


    /**
     * Insert or update MarketCampaignPref entry for given campaingId and cpm
     *
     * @param int $campaignId
     * @param float $minCpm
     */
    public function insertOrUpdateMarketCampaignPref($campaignId, $minCpm)
    {
        $doQueryMarketCampaignPref = OA_Dal::factoryDO('ext_market_campaign_pref');
        $doQueryMarketCampaignPref->campaignid = $campaignId;
        $doQueryMarketCampaignPref->find();
        // prepare object to insert or update
        $doMarketCampaignPref = OA_Dal::factoryDO('ext_market_campaign_pref');
        $doMarketCampaignPref->campaignid = $campaignId;
        $doMarketCampaignPref->is_enabled = true;
        $doMarketCampaignPref->floor_price = $minCpm;
        if ($doQueryMarketCampaignPref->fetch()) {
            $doMarketCampaignPref->update();
        } else {
            $doMarketCampaignPref->insert();
        }
    }

    /**
     * Get campaigns of given type, default CPM have to be provided,
     * min CPMs for campaigns are optional
     *
     * @param float $defaultMinCpm default min CPM
     * @param string $campaignType select campaigns of given type: 'remnant', 'contract', 'all'
     * @param array $minCpms array of campaigns min CPM indexed by campaigns ids
     * @param string $searchPhrase will search for campaigns containing this string 
     * @param string $orderBy orderBy options currently allowed values: { "name", "optinstatus"), to do reverse sorting add prefix '-' e.g. "-name" 
     * @param int $limit limit returned campaings to given number
     * @param int $offset skip first $offset campaigns in result
     * @return array of campaigns info: campaignid, campaignname, type, minCpm, minCpmCalculated, optin_status
     */
    public function getCampaigns($defaultMinCpm, $campaignType = null, $minCpms=array(),
                                 $searchPhrase = null, $orderBy = null, $limit = null, $offset = null)
    {
        $campaigns = array();

        $doCampaigns = $this->prepareCommonCampaingQuery($campaignType, $searchPhrase);
        $doMarketCampaignPref = OA_Dal::factoryDO('ext_market_campaign_pref');

        // Is campaign already optedin ext_market_campaign_pref
        $doCampaigns->joinAdd($doMarketCampaignPref, 'LEFT');
// query below is returning 'f','t' on postgresql and 0, 1 on mysql, but is good for sorting by
        $doCampaigns->selectAdd('('.OA_Dal::getTablePrefix() .'ext_market_campaign_pref.is_enabled IS NOT NULL AND '
                                .OA_Dal::getTablePrefix().'ext_market_campaign_pref.is_enabled <> 0) AS optin_status');
        //orderBy options
        $direction = 'asc';
        if (substr($orderBy,0,1) == '-') {
            $orderBy = substr($orderBy,1);
            $direction = 'desc';
        }
        switch ($orderBy) {
            case ('optinstatus') :
                $doCampaigns->orderBy("optin_status {$direction}, campaignname, campaign_id" );
                break;
            case ('name') : 
            default:
                $doCampaigns->orderBy("campaignname {$direction}, campaign_id {$direction}");
        }
        
        // Add selectAs to create fileds used by MAX_getPlacementName method
        $doCampaigns->selectAs(array('campaignid'), 'placement_id');
        $doCampaigns->selectAs(array('campaignname'), 'name');
        if (!empty($limit)) {
            if (isset($offset)) {
                $doCampaigns->limit($offset, $limit);
            } else {
                $doCampaigns->limit($limit);
            }
        }
        $doCampaigns->find();
        while ($doCampaigns->fetch() && $row_campaigns = $doCampaigns->toArray()) {
            $campaignId = $row_campaigns['campaign_id'];
            $campaigns[$campaignId]['campaignid']   = $campaignId;
            $campaigns[$campaignId]['campaignname'] = MAX_getPlacementName($row_campaigns);
            $campaignTypeAux = OX_Util_Utils::getCampaignType($row_campaigns['priority']);
            // Take ECPM campaigns as remnant campaigns
            $campaigns[$campaignId]['type'] = ($campaignTypeAux == OX_CAMPAIGN_TYPE_ECPM) 
                ? OX_CAMPAIGN_TYPE_REMNANT : $campaignTypeAux;
            $campaigns[$campaignId]['priority'] = $row_campaigns['priority'];
            $campaigns[$campaignId]['revenue'] = $row_campaigns['revenue'];
            $campaigns[$campaignId]['ecpm'] = $row_campaigns['ecpm'];
            $campaigns[$campaignId]['ecpm_enabled'] = $row_campaigns['ecpm_enabled'];
            $campaigns[$campaignId]['optin_status'] = !empty($row_campaigns['is_enabled']);
            $campaigns[$campaignId]['floor_price'] = $row_campaigns['floor_price']; 
            $campaigns[$campaignId]['revenue_type'] = $row_campaigns['revenue_type']; 
            
            $minCpmCalculated = false;
            $minCpmSpecified = false;
            
            $isEcpmAvailable = self::isECPMEnabledCampaign($row_campaigns) && is_numeric($row_campaigns['ecpm']);
            $isCpmAvailable = $row_campaigns['revenue_type'] == MAX_FINANCE_CPM 
                && is_numeric($row_campaigns['revenue']);
            
            if (isset($minCpms[$campaignId])) {
                $campaignMinCpm = $minCpms[$campaignId];
            }
            else if (isset($campaigns[$campaignId]['floor_price']) && $campaigns[$campaignId]['optin_status']) {
                $campaignMinCpm = self::formatCpm($campaigns[$campaignId]['floor_price']);
            }
            else if ($isEcpmAvailable) {
                $campaignMinCpm = self::formatCpm($row_campaigns['ecpm']);
            }
            else if ($isCpmAvailable) {
                $campaignMinCpm = self::formatCpm($row_campaigns['revenue']);
            }
            else {
                $campaignMinCpm = self::formatCpm($defaultMinCpm);
            }
                
            //if user has specified same floor as current eCPM/CPM or have not 
            //touched the proposed eCPM/CPM at all and submitted, we should preserve the ecpm marker
            if ($isEcpmAvailable
                && $campaignMinCpm == $row_campaigns['ecpm']) {
                $minCpmCalculated = true;     
            }
            else if ($row_campaigns['revenue_type'] == MAX_FINANCE_CPM 
                && $campaignMinCpm == $row_campaigns['revenue']) {
                $minCpmSpecified = true;
            }
            
            $campaigns[$campaignId]['minCpm'] = $campaignMinCpm;
            $campaigns[$campaignId]['minCpmCalculated'] = $minCpmCalculated;
            $campaigns[$campaignId]['minCpmSpecified'] = $minCpmSpecified;
            
            if ($isEcpmAvailable) {
                $campaigns[$campaignId]['minRecommendedFloorPrice'] = self::formatCpm($row_campaigns['ecpm']);
            } elseif ($isCpmAvailable) {
                $campaigns[$campaignId]['minRecommendedFloorPrice'] = self::formatCpm($row_campaigns['revenue']);
            }
        }

        return $campaigns;
    }

    public static function formatCpm($cpm)
    {
        return number_format($cpm, 2, '.', '');
    }
    
    
    /**
     * Count all campaigns matching to selected criteria
     *
     * @param string $campaignType select campaigns of given type: 'remnant', 'contract', 'all'
     * @param string $searchPhrase will search for campaigns containing this string
     * @return unknown
     */
    public function getCampaignsCount($campaignType = null, $searchPhrase = null)
    {
        $doCampaigns = $this->prepareCommonCampaingQuery($campaignType, $searchPhrase);
        return $doCampaigns->count();
    }

    /**
     * Get number of opted campaigns
     *
     * @param string $campaignType count campaigns of given type: 'remnant', 'contract', 'all'
     * @return int
     */
    public function numberOfOptedCampaigns($campaignType = null)
    {
        // Get campaigns based on the criteria
        $doCampaigns = $this->prepareCommonCampaingQuery($campaignType);
        $doMarketCampaignPref = OA_Dal::factoryDO('ext_market_campaign_pref');

        $doCampaigns->joinAdd($doMarketCampaignPref, 'LEFT');

        // Ignore campaigns that are already Opt in
        $doCampaigns->whereAdd(OA_Dal::getTablePrefix().'ext_market_campaign_pref.is_enabled = 1');

        $doCampaigns->find();
        $numberOfOptedCampaigns = $doCampaigns->getRowCount();

        return $numberOfOptedCampaigns;
    }


    /**
     * Get number of remrant campaigns
     *
     * @return int
     */
    public function  numberOfRemnantCampaignsToOptIn()
    {
        // Get campaigns based on the criteria
        $doCampaigns = OA_Dal::factoryDO('campaigns');
        $doClients = OA_Dal::factoryDO('clients');

        // Get campaigns that belong to advertiser of the current agency
        $doClients->agencyid = OA_Permission::getAgencyId();
        $doCampaigns->joinAdd($doClients, 'LEFT');

        // Ignore already ended campaigns
        $doCampaigns->whereAdd("expire_time >= '" . $this->getTodayDate() . "' OR expire_time IS NULL");

        $doCampaigns->whereAdd('priority = ' . DataObjects_Campaigns::PRIORITY_REMNANT .
                               ' OR priority = ' . DataObjects_Campaigns::PRIORITY_ECPM);
        $doCampaigns->find();
        $numberOfRemnantCampaignsToOptIn = $doCampaigns->getRowCount();

        return $numberOfRemnantCampaignsToOptIn;
    }


    /**
     * Get today as string formated YYYY-MM-DD
     *
     * @return string
     */
    private function getTodayDate()
    {
        $oDate = new Date();
        $oDate->setTZbyID('UTC');
        return $oDate->format("%Y-%m-%d");
    }


    /**
     * Prepares common query for searching not ended campaigns of given type
     *
     * @param string $campaignType select campaigns of given type: 'remnant', 'contract', 'all'
     * @param string $searchPhrase will search for campaigns containing this string
     * @return DataObjects_Campaigns
     */
    private function prepareCommonCampaingQuery($campaignType, $searchPhrase = null)
    {   
        // Get campaigns based on the criteria
        $doCampaigns = OA_Dal::factoryDO('campaigns');
        $doClients = OA_Dal::factoryDO('clients');

        // Get campaigns that belong to advertiser of the current agency
        $doClients->agencyid = OA_Permission::getAgencyId();
        $doCampaigns->joinAdd($doClients, 'LEFT');
        $doCampaigns->selectAs(array('campaignid'), 'campaign_id');

        // Ignore already ended campaigns
        $doCampaigns->whereAdd(" expire_time >= '" . $this->getTodayDate() . "' OR expire_time IS NULL");
        // allow only default campaigns
        $doCampaigns->type =  DataObjects_Campaigns::CAMPAIGN_TYPE_DEFAULT;

        // If not all campaigns selected set the selected campaign type
        if ($campaignType == 'remnant') {
            $doCampaigns->whereAdd('priority = ' . DataObjects_Campaigns::PRIORITY_REMNANT .
                                   ' OR priority = ' . DataObjects_Campaigns::PRIORITY_ECPM);
        } elseif ($campaignType == 'contract') {
            $doCampaigns->whereAdd('priority > 0');
            $doCampaigns->whereAdd('expire_time IS NULL');
            
        } else { // 'all' or null (or any not matching)
            $doCampaigns->whereAdd('priority = ' . DataObjects_Campaigns::PRIORITY_REMNANT 
                                   .' OR priority = ' . DataObjects_Campaigns::PRIORITY_ECPM 
                                   .' OR (priority > 0 AND expire_time IS NULL)');
        }

        if (isset($searchPhrase)) {
            $doCampaigns->whereAdd('UPPER(campaignname) like(UPPER(' . DBC::makeLiteral('%'.$searchPhrase.'%') . '))'); 
        }

        return $doCampaigns;
    }
    
    
    /**
     * Returns true if campaign is remnant eCPM enabled campaign, or if it is
     * contract campaign with ecpm_enabled
     *
     * @param unknown_type $aCampaign
     * @return unknown
     */    
    public static function isECPMEnabledCampaign($aCampaign)
    {
        $campaignTypeAux = OX_Util_Utils::getCampaignType($aCampaign['priority']);
        return $campaignTypeAux == OX_CAMPAIGN_TYPE_ECPM || $aCampaign['ecpm_enabled'];
    }
    
    
    /**
     * Perform Opt Out works on selected campaigns
     *
     * @param array $toOptOut array of campaigns ids
     * @return int campains opted out
     */
    public function performOptOut($toOptOut)
    {
        if (!is_array($toOptOut) || count($toOptOut)==0) {
            return 0;
        }
        
        // Validate Opt-out campaigns ids first
        $doCampaigns = OA_Dal::factoryDO('campaigns');
        $doClients = OA_Dal::factoryDO('clients');

        // Get campaigns that belong to advertiser of the current agency
        $doClients->agencyid = OA_Permission::getAgencyId();
        $doCampaigns->joinAdd($doClients, 'LEFT');

        // Ignore already ended campaigns
        $doCampaigns->whereAdd(" expire_time >= '" . $this->getTodayDate() .
                               "' OR expire_time IS NULL");

        $oDbh = OA_DB::singleton();
        foreach ($toOptOut as $k => $campaignId) {
            $toOptOut[$k] = $oDbh->quote($campaignId, 'integer');
        }
        $doCampaigns->whereAdd(' campaignid IN (' . implode(",", $toOptOut) . ')');
        $doCampaigns->selectAdd();
        $doCampaigns->selectAdd('campaignid');
        $doCampaigns->find();
        $toOptOut = array(); // clear toOptOut array and get valid campaigns ids 
        while ($doCampaigns->fetch()) {
            $toOptOut[] = (int)$doCampaigns->campaignid;
        }
        
        // Is there any campaign to update?
        if (count($toOptOut)==0) {
            return 0;
        }
        
        // Get campaign preferences and do update for given campaigns (set enabled to false)
        $doCampaignPref = OA_Dal::factoryDO('ext_market_campaign_pref');
        $doCampaignPref->whereAdd(' campaignid IN (' . implode(",", $toOptOut) . ')');
        $doCampaignPref->is_enabled = false;
        $campaignsOptedOut = $doCampaignPref->update();        
        
        return $campaignsOptedOut;
    }
    
    
    /**
     * Opt in all selected campaigns
     * Sets minCpm from $minCpms before database
     *
     * @param float $defaultMinCpm default min CPM
     * @param string $campaignType select campaigns of given type: 'remnant', 'contract', 'all'
     * @param array $minCpms array of campaigns min CPM indexed by campaigns ids
     * @param string $searchPhrase will search for campaigns containing this string 
     * @return int campains opted in
     */
    public function performOptInAll($defaultMinCpm, $campaignType = null, $minCpms=array(),
                                 $searchPhrase = null)
    {
        // get all selected campaign
        $campaigns = $this->getCampaigns($defaultMinCpm, $campaignType, $minCpms, 
                                 $searchPhrase);
        // Create toOptIn array of campaigns
        // and minCpms array from results
        $minCpms = array();
        $toOptIn = array();
        foreach ($campaigns as $campaign) {
            $campaignId = $campaign['campaignid'];
            $toOptIn[] = $campaign['campaignid'];
            $minCpms[$campaignId] = $campaign['minCpm'];
        }
        return $this->performOptIn($toOptIn, $minCpms);
    }
    
    
    /**
     * Perform Opt Out for all selected campaigns
     *
     * @param string $campaignType select campaigns of given type: 'remnant', 'contract', 'all'
     * @param string $searchPhrase will search for campaigns containing this string
     * @return int campains opted out
     */
    public function performOptOutAll($campaignType = null, $searchPhrase = null)
    {
        // Select all campaignids
        $doCampaigns = $this->prepareCommonCampaingQuery($campaignType, $searchPhrase);
        $doMarketCampaignPref = OA_Dal::factoryDO('ext_market_campaign_pref');

        // Select campaigns already optedin ext_market_campaign_pref
        $doCampaigns->joinAdd($doMarketCampaignPref, 'LEFT');
        $doCampaigns->whereAdd('('.OA_Dal::getTablePrefix() .'ext_market_campaign_pref.is_enabled IS NOT NULL AND '
                                .OA_Dal::getTablePrefix().'ext_market_campaign_pref.is_enabled <> 0)');
                                
        $doCampaigns->selectAdd();
        $doCampaigns->selectAdd($doCampaigns->tableName().'.campaignid');
        $doCampaigns->find();
        $toOptOut = array();
        while ($doCampaigns->fetch()) {
            $toOptOut[] = $doCampaigns->campaignid;
        }
        
        // If no campaign to optout exit
        if (count($toOptOut)==0) {
            return 0;
        }
        
        // optout campaigns from list
        $doCampaignPref = OA_Dal::factoryDO('ext_market_campaign_pref');
        $doCampaignPref->whereAdd(' campaignid IN (' . implode(",", $toOptOut) . ')');
        $doCampaignPref->is_enabled = false;
        $campaignsOptedOut = $doCampaignPref->update();
        
        return $campaignsOptedOut;
    }
}
