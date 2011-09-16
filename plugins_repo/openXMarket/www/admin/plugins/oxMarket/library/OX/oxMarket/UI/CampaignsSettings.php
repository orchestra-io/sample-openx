<?php

/*
+---------------------------------------------------------------------------+
| OpenX v${RELEASE_MAJOR_MINOR}                                             |
| =======${RELEASE_MAJOR_MINOR_DOUBLE_UNDERLINE}                            |
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
$Id: CampaignsSettings.php 54232 2010-06-01 20:07:44Z chris.nutting $
*/

require_once MAX_PATH .'/lib/OA/Admin/UI/component/Form.php';
require_once MAX_PATH .'/lib/OX/Admin/Redirect.php';
require_once MAX_PATH .'/lib/OA/Admin/UI/component/rule/DecimalPlaces.php';
require_once MAX_PATH .'/lib/pear/HTML/QuickForm/Rule/Regex.php';
require_once MAX_PATH .'/lib/OA/Admin/UI/component/rule/Max.php';
require_once OX_MARKET_LIB_PATH . '/OX/oxMarket/Dal/CampaignsOptIn.php';

/**
 * 
 */
class OX_oxMarket_UI_CampaignsSettings
{
    private static $MAX_ADVERTISER_INFO_SHOW_COUNT = 20;
    
    
    /**
     * @var Plugins_admin_oxMarket_oxMarket
     */
    private $marketComponent;

    /**
     * @var OX_oxMarket_Dal_CampaignsOptIn
     */
    private $campaignsOptInDal;
    
    private $campaigns;

    private $campaignType;
    private $search;
    private $order = 'optinstatus';
    private $descending = false;
    private $minCpms;
    private $defaultMinCpm;
    private $itemsPerPage = 50;
    private $currentPage;
    
    /**
     * Ids of campaigns to opt in.
     */
    private $toOptIn; 
    private $allSelected;
    private $optedCount;
    private $maxCpm;
    
    private $contentKeys;

    /** Indicates whether skip link leading to advertiser index should be shown **/
    private $showSkipLink;
    private $showMarkerAdvertiserInfo;
    
    
    
    /** 
     * Numbers of opted in campaigns for tracking reasons. The numbers are calculated
     * when handling the opt in/out POST requests and displayed on the subsequent campaigns
     * list view.
     */
    private $remnantOptedIn;
    private $contractOptedIn;
    
    public function __construct()
    {
        $this->marketComponent = OX_Component::factory('admin', 'oxMarket');
        $this->campaignsOptInDal = new OX_oxMarket_Dal_CampaignsOptIn();
        
        // Get configuration defaults
        $this->defaultMinCpm = $this->marketComponent->getConfigValue('defaultFloorPrice');
        $this->maxCpm = $this->marketComponent->getMaxFloorPrice(); //used for validation purpose only
        
        $this->contentKeys = $this->marketComponent->retrieveCustomContent('market-quickstart');
        if (!$this->contentKeys) {
            $this->contentKeys = array();
        }
    }

    public function handle()
    {
        $result = $this->handleInternal();
        phpAds_SessionDataStore();
        return $result;
    }
    
    /**
     * A dispatcher method for handling all requests for the market settings screen.
     * 
     * @return a template to display or null if a HTTP redirect has been performed 
     *      after a successful form submission. 
     */
    public function handleInternal()
    {
        // Initial checks
        $this->marketComponent->checkActive();
        if (!$this->marketComponent->isMarketSettingsAlreadyShown()) {
            $this->marketComponent->setMarketSettingsAlreadyShown();
        }
        
        $this->parseRequestParameters();

        if ('POST' != $_SERVER['REQUEST_METHOD']) {
            $this->clearStored();            
        }
        
        // Prepare a list of campaigns involved
        $this->campaigns = $this->campaignsOptInDal->getCampaigns($this->defaultMinCpm, 
            $this->campaignType, $this->getStoredMinCpms(), $this->search, 
            ($this->descending ? '-' : '') . $this->order);
            
        // For POSTs, perform opt in/out and redirect
        if ('POST' == $_SERVER['REQUEST_METHOD']) { 
            if (!empty($_REQUEST['action']) && 'refresh' == $_REQUEST['action']) {
                return $this->displayAjaxList();
            }
            elseif (!empty($this->toOptIn) || $this->allSelected) {
                if (isset($_REQUEST['optInSubmit'])) {
                    $invalidCpmMessages = $this->validateCpms($this->campaigns);
                    if (empty($invalidCpmMessages)) {
                        $this->performOptInAndRedirect();
                        return null;
                    }
                } 
                elseif(isset($_REQUEST['optOutSubmit'])) {
                    $this->performOptOutAndRedirect();
                    return null;
                }
            } 
        }
        
        return $this->displayFullList($invalidCpmMessages);
    }

    
    private function displayFullList($invalidCpmMessages)
    {
        $template = new OA_Plugin_Template('market-campaigns-settings.html', 'oxMarket');
        $this->assignCampaignsListModel($template);
        $this->assignContentStrings($template);
        $this->assignMarketAdvertisersLinks($template);
        
        
        if (!empty($invalidCpmMessages)) {
            $hasCpmRateError = false;
            $hasEcpmRateError = false;
            foreach ($invalidCpmMessages as $message) {
                $hasCpmRateError = $hasCpmRateError || $message[0] == 'compare-rate'; 
                $hasEcpmRateError = $hasEcpmRateError || $message[0] == 'compare-ecpm'; 
            }
            
            $prefix = '';
            if ($hasCpmRateError || $hasEcpmRateError) {
                $model = ($hasCpmRateError ? $this->marketComponent->translate("CPM") : "") . 
                         ($hasCpmRateError && $hasEcpmRateError ? ' / ' : '') .
                         ($hasEcpmRateError ? $this->marketComponent->translate("eCPM") : "");
                $prefix = $this->marketComponent->translate("For your benefit, we require a campaign's floor price to be greater than or equal to that campaign's %s.<br />", array($model));
            } else {
                $prefix = $this->marketComponent->translate("The specified floor prices contain errors. ");
            }
            
            OA_Admin_UI::queueMessage($prefix . $this->marketComponent->translate("To opt in campaigns to %s, please correct the errors below.", array($this->marketComponent->aBranding['name'])), 'local', 'error', 0);
            $template->assign('minCpmsInvalid', $invalidCpmMessages);
        } 
        else {
            // Don't show trackers on validation
            $this->assignTrackers($template);
        }
        
        return $template;
    }

    
    private function displayAjaxList()
    {
        $template = new OA_Plugin_Template('market-campaigns-settings-list.html', 'oxMarket');
        $this->assignCampaignsListModel($template);
        $this->assignContentStrings($template);
        $template->assign('aBranding', $this->marketComponent->aBranding);
        return $template;
    }
    
    
	public function assignCampaignsListModel($template)
    {
        $template->register_function('ox_campaign_type_tag', 
            array($this, 'ox_campaign_type_tag_helper'));

        // Filtering criteria
        $template->assign('campaignType', $this->campaignType);
        $template->assign('search', $this->search);

        // Sorting
        $template->assign('order', $this->order);
        $template->assign('desc', $this->descending);
        
        // Pager
        $bottomPager = OX_buildPager($this->campaigns, $this->itemsPerPage, true, '', 4, $this->currentPage, 
            'market-campaigns-settings-list.php');
        $topPager = OX_buildPager($this->campaigns, $this->itemsPerPage, false, '', 4, $this->currentPage, 
            'market-campaigns-settings-list.php');
        list($itemsFrom, $itemsTo) = $bottomPager->getOffsetByPageId();
        $template->assign('pager', $bottomPager);
        $template->assign('topPager', $topPager);
        $template->assign('page', $bottomPager->getCurrentPageID());

        // Counts
        $template->assign('allSelected', $this->allSelected);
        $template->assign('allMatchingCount', count($this->campaigns));
        $this->campaigns =  array_slice($this->campaigns, $itemsFrom - 1, 
            $this->itemsPerPage, true);
        $template->assign('showingCount', count($this->campaigns));
        
        // Campaigns
        $template->assign('campaigns', $this->campaigns);
        $toOptInMap = self::arrayValuesToKeys($this->toOptIn);
        foreach ($this->campaigns as $campaignId => $campaign) {
            if (!isset($toOptInMap[$campaignId])) {
                $toOptInMap[$campaignId] = false;
            }
        }
        $template->assign('toOptIn', $toOptInMap);
        $template->assign('selection', $this->getStoredSelection());
        
        // For validation
        $template->assign('maxValueLength', 3 + strlen($this->maxCpm)); //two decimal places, point, plus strlen of maxCPM
        
        // For context help
        $template->assign('defaultMinCpm', $this->defaultMinCpm);
        
        // Plugin version for CSS/JS versioning
        $template->assign('pluginVersion', $this->getPluginVersion());
        $template->assign('cookiePath', $this->marketComponent->getCookiePath());
    }

    private function performOptInAndRedirect()
    {
        $beforeCount = $this->campaignsOptInDal->numberOfOptedCampaigns();
        
        if ($this->allSelected) {
            $updated = $this->campaignsOptInDal->performOptInAll(
                $this->defaultMinCpm, $this->campaignType, $this->minCpms, $this->search);
        } else {
            $updated = $this->campaignsOptInDal->performOptIn(
                $this->toOptIn, $this->minCpms);
        }
        $this->prepareAfterStatusChangeCounts();
        
        $afterCount = $this->contractOptedIn + $this->remnantOptedIn;
        $actualOptedCount = $afterCount - $beforeCount;
        $updatedCount = $updated - $actualOptedCount;
    
        $message = '';
        if ($actualOptedCount > 0) {
            $message .= $this->marketComponent->translate("You have successfully opted <b>%s campaigns</b> into %s", array($actualOptedCount, $this->marketComponent->aBranding['name']));
        }
        if ($actualOptedCount > 0 && $updatedCount) {
            $message .= '. ';
        }
        if ($updatedCount > 0) {
            $message .= $this->marketComponent->translate("Floor prices of %s campaigns have been updated.", array($updatedCount));
        }
        
        $this->scheduleMessages($message, 'local', 'confirm', 0);

        $this->redirect();
    }
    
    
    private function performOptOutAndRedirect()
    {
        if ($this->allSelected) {
            $campaignsOptedOut = $this->campaignsOptInDal->performOptOutAll(
                $this->campaignType, $this->search);
        } else {
            $campaignsOptedOut = $this->campaignsOptInDal->performOptOut($this->toOptIn);
        }
        $this->prepareAfterStatusChangeCounts();
        
        $this->scheduleMessages($this->marketComponent->translate("You have successfully opted out <b>%s campaigns</b> of %s", array($campaignsOptedOut, $this->marketComponent->aBranding['name'])));
    
        $this->redirect();
    }
    
    
    private function prepareAfterStatusChangeCounts()
    {
        $this->remnantOptedIn = $this->campaignsOptInDal->numberOfOptedCampaigns('remnant');
        $this->contractOptedIn = $this->campaignsOptInDal->numberOfOptedCampaigns('contract');
    }
    
    
	private function redirect()
    {
        $params = array(
            'campaignType' => $this->campaignType, 
            'search' => $this->search, 
            'order' => $this->order, 
            'desc' => $this->descending, 
            'p' => $this->currentPage,
            'remnantOptedIn' => $this->remnantOptedIn,
            'contractOptedIn' => $this->contractOptedIn
        );
        
        global $session;
        $session['oxMarket-quickstart-params'] = $params;
        
        OX_Admin_Redirect::redirect('plugins/oxMarket/market-campaigns-settings.php');
    }
    
    
    private function scheduleMessages($message) 
    {
        OA_Admin_UI::queueMessage($message, 'local', 'confirm', 0);
        
        $submittedCount = $this->marketComponent->getMarketUserVariable('campaign_settings_submitted_count');
        if (!isset($submittedCount)) {
            $submittedCount = 0;
        }
        
        if ($submittedCount < self::$MAX_ADVERTISER_INFO_SHOW_COUNT) {
            global $session;
            $session['oxMarket-quickstart-params']['showMarkerAdvertiserInfo'] = 1;
            phpAds_SessionDataStore();
            $submittedCount++;
            $this->marketComponent->setMarketUserVariable('campaign_settings_submitted_count', $submittedCount);            
        }
    }
    

    private function parseRequestParameters()
    {
        // Retrieve values passed by the previous request making a redirect after POST
        global $session;
        $fromRedirecingRequest = isset($session['oxMarket-quickstart-params']) 
            ? $session['oxMarket-quickstart-params'] : array();
            
        if (!empty($fromRedirecingRequest)) {
            unset($session['oxMarket-quickstart-params']);
        }
        
        // Merge with params from the current request. We explicitly overwrite current
        // request parameters with the ones from session and on the other way round
        // to make sure that the values we pass through the session are not tampered with.
        $request = array_merge(phpAds_registerGlobalUnslashed('campaignType', 'toOptIn', 
                'search', 'p', 'order', 'desc', 'allSelected', 'showSkip', 'showMarkerAdvertiserInfo'), $fromRedirecingRequest);
        
        $this->campaignType = !empty($request['campaignType']) ? $request['campaignType'] : 'remnant';
        $toOptInMap = isset($request['toOptIn']) ? $request['toOptIn'] : array();
        $this->toOptIn = array();
        foreach ($toOptInMap as $id => $val) {
            if ($val == '1') {
                $this->toOptIn []= $id;
            }
        }
        $this->optedCount = $request['optedCount'];
        $this->search = $request['search'];
        $this->currentPage = $request['p'];
        $this->showSkipLink = $request['showSkip'];
        $this->showMarkerAdvertiserInfo = $request['showMarkerAdvertiserInfo'];
        
        if (!empty($request['order'])) {
            $order = $request['order'];
            if ('optinstatus' == $order || 'name' == $order) {
                $this->order = $order;
            }
        }
        if ($request['desc']) {
            $this->descending = true;
        }
        $this->allSelected = !empty($request['allSelected']) && $request['allSelected'] == 'true';

        $this->remnantOptedIn = $request['remnantOptedIn']; 
        $this->contractOptedIn = $request['contractOptedIn']; 

        $this->minCpms = array();
        foreach ($_REQUEST as $param => $value) {
            if (preg_match("/^cpm\d+$/", $param)) {
                $this->minCpms[substr($param, 3)] = $value;
            }
        }
        $cpms = $this->getStoredMinCpms();
        foreach ($this->minCpms as $id => $val) {
            $cpms[$id] = $val;
        }
        $this->setStoredMinCpms($cpms);

        // Selected campaigns
        $selection = $this->getStoredSelection();
        foreach ($toOptInMap as $id => $val) {
            if ($val == '1') {
                $selection[$id] = '1';
            } else {
                unset($selection[$id]);
            }
        }
        $this->setStoredSelection($selection);
    }
    
    private function getStoredSelection()
    {
        global $session;
        return isset($session['oxMarket-quickstart-selection']) ? 
            $session['oxMarket-quickstart-selection'] : array();
    }
    
    private function setStoredSelection($selection)
    {
        global $session;
        if (null === $selection) {
            unset($session['oxMarket-quickstart-selection']);
        } else {
            $session['oxMarket-quickstart-selection'] = $selection;
        }
    }
    
    private function getStoredMinCpms()
    {
        global $session;
        return isset($session['oxMarket-quickstart-mincpms']) ? 
            $session['oxMarket-quickstart-mincpms'] : array();
    }
    
    private function setStoredMinCpms($minCpms)
    {
        global $session;
        if (null === $minCpms) {
            unset($session['oxMarket-quickstart-mincpms']);
        } else {
            $session['oxMarket-quickstart-mincpms'] = $minCpms;
        }
    }
    
    private function clearStored()
    {
        $this->setStoredSelection(null);
        $this->setStoredMinCpms(null);
    }
    
    private function validateCpms($campaigns)
    {
        $zero = false;
        $decimalValidator = new OA_Admin_UI_Rule_DecimalPlaces();
        $maxValidator = new OA_Admin_UI_Rule_Max();
        
        $invalidCpms = array();
        foreach ($this->toOptIn as $campaignId) {
            $value = $_REQUEST['cpm' . $campaignId];
            //is number
            $valueValid = is_numeric($value);
            
            $message = $valueValid ?  null : $this->getValidationMessage('format');
            
            //is greater than zero
            if ($valueValid) {
                $valueValid = ($value > 0);
                $message = $valueValid ?  null : $this->getValidationMessage('too-small');
            }
            //less than arbitrary maxcpm 
            if ($valueValid) {
                $valueValid = $maxValidator->validate($value, $this->maxCpm);
                $message = $valueValid ?  null : $this->getValidationMessage('too-big');
            }
            //max 2decimal places?
            if ($valueValid) {
                $valueValid = $decimalValidator->validate($value, 2);
                $message = $valueValid ?  null : $this->getValidationMessage('format');                
            }
            //not smaller than eCPM or campaigns CPM ('revenue')
            $validateCpms = false;
            if ($validateCpms && $valueValid) {
                $aCampaign = $campaigns[$campaignId];
                if (OX_oxMarket_Dal_CampaignsOptIn::isECPMEnabledCampaign($aCampaign)) {
                    if (is_numeric($aCampaign['ecpm']) && $value < $aCampaign['ecpm']) {
                        $valueValid = false;
                        $message = $this->getValidationMessage('compare-ecpm', $aCampaign['ecpm']);
                    }
                }
                else {
                    if (is_numeric($aCampaign['revenue']) && $value < $aCampaign['revenue']
                        && $aCampaign['revenue_type'] == MAX_FINANCE_CPM) {
                        $valueValid = false;
                        $message = $this->getValidationMessage('compare-rate', $aCampaign['revenue']);
                    }
                }
            }
            if (!$valueValid) {
                $invalidCpms[$campaignId] = $message;
            }
        }
        return $invalidCpms;
    }


    private function getValidationMessage($cause, $value = null)
    {
        switch($cause) {
            case 'too-small' : {
                $message = $this->marketComponent->translate("Please provide a floor price greater than zero");
                break;
            }
            case 'too-big' : {
                $message = $this->marketComponent->translate("Please provide a floor price smaller than %s", array(self::formatCpm($this->maxCpm))); 
                break;
            }
            case 'compare-ecpm' : {
                $message = $this->marketComponent->translate("Please provide a floor price greater than or equal to %s (your campaign's eCPM).", array(self::formatCpm($value))); 
                break;
            }
            case 'compare-rate' : {
                $message = $this->marketComponent->translate("Please provide a floor price greater than or equal to %s (your campaign's specified CPM).", array(self::formatCpm($value))); 
                break;
            }
            
            case 'format' : 
            default : {
                $message = $this->marketComponent->translate("Please provide a floor price as a decimal number with two digit precision"); 
            }
        }
        
        return array($cause, $message);
    }
    
    
    public static function formatCpm($cpm)
    {
        return number_format($cpm, 2, '.', '');
    }
    

    private function assignContentStrings($template)
    {
        $template->assign('topMessage', $this->getContentKeyValue('top-message'));
        $template->assign('optInSubmitLabel', $this->getContentKeyValue('radio-opt-in-submit-label'));
        $template->assign('optOutSubmitLabel', $this->getContentKeyValue('radio-opt-out-submit-label'));
        $template->assign('skipLinkLabel', $this->getContentKeyValue('link-skip-label'));
        $template->assign('floorPriceColumnContextHelp', $this->getContentKeyValue('floor-price-column-context-help'));
        $template->assign('selectAllExplanation', $this->getContentKeyValue('select-all-explanation'));
    }
    
    
    private function assignMarketAdvertisersLinks($template)
    {
         $template->assign('showSkipLink', $this->showSkipLink);
         $template->assign('showMarkerAdvertiserInfo', $this->showMarkerAdvertiserInfo);
    }
    
    
    private function assignTrackers($template)
    {
        if (isset($this->remnantOptedIn) && isset($this->contractOptedIn)) {
            $this->assignTracker($template, 'tracker-optin-iframe', array(
                '$REMNANT_OPTED_IN' => $this->remnantOptedIn,
                '$CONTRACT_OPTED_IN' => $this->contractOptedIn
            ));
        } else {
            $this->assignTracker($template, 'tracker-view-iframe', array(
                '$REMNANT_ALL' => $this->campaignsOptInDal->getCampaignsCount('remnant'),
                '$CONTRACT_ALL' => $this->campaignsOptInDal->getCampaignsCount('contract')
            ));
        }
    }
    
    
    private function assignTracker($template, $key, $replacements)
    {
        $trackerFrame = $this->getContentKeyValue($key);
        foreach ($replacements as $param => $value)
        {
            $trackerFrame = str_replace($param, $value, $trackerFrame);
        }
        $template->assign('trackerFrame', $trackerFrame);
    }
    
    
    private function getContentKeyValue($key, $default = '')
    {
        return isset($this->contentKeys[$key]) ? $this->contentKeys[$key] : $default; 
    }
    
    
    public function ox_campaign_type_tag_helper($aParams, &$smarty)
    {
        if (isset($aParams['type'])) {
            $type = $aParams['type'];
            $translation = new OX_Translation ();
            
            if ($type == OX_CAMPAIGN_TYPE_CONTRACT_NORMAL) {
                $class = 'tag-contract';
                $text = $translation->translate('Contract');
            } 
            else {
                $class = 'tag-remnant';
                $text = $translation->translate('Remnant');
            }
            $text = strtolower($text);
            
            return '<span class="'.$class.' tag"><span class="t-b"><span class="l-r"><span class="val">'.$text.'</span></span></span></span>';
        } 
        else {
            $smarty->trigger_error("t: missing 'type' parameter");
        }
    }

    /**
     * Same as array_fill_keys(array_keys($array), $valueToFillIn)
     * but compatible with PHP < 5.2
     *
     * @param array $array
     * @param mixed $valueToFillIn
     * @return array
     */
    private static function arrayValuesToKeys($array, $valueToFillIn = true)
    {
        $result = array();
        foreach ($array as $value) {
            $result[$value] = $valueToFillIn;
        }
        return $result;
    }
    
    
    public function getPluginVersion()
    {
        return $this->marketComponent->getPluginVersion();
    }
    
    
    public static function removeSessionCookies($cookiePath)
    {
        // Enable showing the minimum floor price dialog
        setcookie('mqs-floor', '', time() - 1000, $cookiePath);
    }
}
