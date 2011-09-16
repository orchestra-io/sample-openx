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
$Id$
*/

/**
 * Zones DAL Library. Wraps plugin DO object
 *
 * @package    openXMarket
 * @author     Bernard Lange  <bernard.lange@openx.org>
 */
class OX_oxMarket_Dal_ZoneOptIn
{
    /**
     * Updates zone's market opt in status to the given one.
     * 
     * @param int $zoneId 
     * @param bool status
     * @package boolean $optedIn indicates whether this zone has market enabled
     */    
    public function updateZoneOptInStatus($zoneId, $optedIn)
    {
        if(empty($zoneId)) {
            return false;
        }
        
        $aConf = $GLOBALS['_MAX']['CONF'];
              
        // Find system campaign to be linked to
        $oCampaign = $this->findSystemCampaignForZoneOptIn($agencyId = OA_Permission::getAgencyId());
        $dalZones = OA_Dal::factoryDAL('zones');
        if ($optedIn) {
            $result = $dalZones->linkZonesToCampaign(
                                    array($zoneId), $oCampaign->campaignid);
        } else { 
            $result = $dalZones->unlinkZonesFromCampaign(
                                    array($zoneId), $oCampaign->campaignid);
        }

        //refresh cache (zone chaining (if any), market banner)
        if(!class_exists('OA_Cache_DeliveryCacheManager')) {
            require_once MAX_PATH . '/lib/OA/Cache/DeliveryCacheManager.php';
        }
        $cacheManager = new OA_Cache_DeliveryCacheManager();
        $cacheManager->invalidateZoneCache($zoneId);

        return ($result != -1); // linking unlinking shouldn't return -1 (parameter error) 
    }
    
    
    /**
     * Checks if given zone is opted in to market.
     * 
     * @param int $zoneId
     * @return boolean true if zone opted in, false if not.  
     */      
    public function isOptedIn($zoneId)
    {
        if (empty($zoneId)) {
            return false;
        }        
        
        // Is market campaign linked to given zone?
        $oCampaign = $this->findSystemCampaignForZoneOptIn($agencyId = OA_Permission::getAgencyId());
        if ($oCampaign !== false) {
            $doPlZoneAssoc = OA_Dal::factoryDO('placement_zone_assoc');
            $doPlZoneAssoc->zone_id = $zoneId;
            $doPlZoneAssoc->placement_id = $oCampaign->campaignid;
            
            return $doPlZoneAssoc->count()>0;
        }
        return false;
    }
    
    
    /**
     * Find market zone opt-in campaign for given zone (same realm)
     * TODO: to be finished after implementing system entities 
     *
     * @param int $agencyid
     * @return DataObjects_Campaigns | false if not found
     */
    protected function findSystemCampaignForZoneOptIn($agencyid)
    {
        // Find system zone opt-in campaign (in same realm as given zone) 
        $doAgency      = OA_Dal::factoryDO('agency');
        $doClients     = OA_Dal::factoryDO('clients');
        $doCampaigns   = OA_Dal::factoryDO('campaigns');
        
        $doAgency->agencyid = $agencyid;
        $doClients->joinAdd($doAgency);
        $doClients->type = DataObjects_Clients::ADVERTISER_TYPE_MARKET;
        $doCampaigns->joinAdd($doClients);
        $doCampaigns->type = DataObjects_Campaigns::CAMPAIGN_TYPE_MARKET_ZONE_OPTIN;
        $doCampaigns->find();
        if ($doCampaigns->fetch()) {
            return $doCampaigns;
        }
        return false;
    }
}
