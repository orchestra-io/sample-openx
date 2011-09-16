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
$Id: spcjs.php 49127 2010-02-18 01:55:43Z chris.nutting $
*/

// Require the initialisation file
require_once '../../init-delivery.php';

// Required files
require_once MAX_PATH . '/lib/max/Delivery/cache.php';
require_once MAX_PATH . '/lib/max/Delivery/javascript.php';
require_once MAX_PATH . '/lib/max/Delivery/flash.php';

// Get the affiliateid from the querystring if present
MAX_commonRegisterGlobalsArray(array('id'));

// Get JS
$output = OA_SPCGetJavaScript($id);

//OX_Delivery_logMessage('output: ' . $output, 7);

// Output JS
MAX_commonSendContentTypeHeader("application/x-javascript");
header("Content-Size: ".strlen($output));
header("Expires: ".gmdate('r', time() + 86400));

// Flush cookies
MAX_cookieFlush();

echo $output;

function OA_SPCGetJavaScript($affiliateid)
{
    $aConf = $GLOBALS['_MAX']['CONF'];
    $varprefix = $aConf['var']['prefix'];
    $aZones = OA_cacheGetPublisherZones($affiliateid);
    foreach ($aZones as $zoneid => $aZone) {
        $zones[$aZone['type']][] = "            '" . addslashes($aZone['name']) . "' : {$zoneid}";
    }
    $additionalParams = '';
    $magic_quotes_gpc = ini_get('magic_quotes_gpc');

    foreach ($_GET as $key => $value) {
        if ($key == 'id') { continue; }
        if ($magic_quotes_gpc) { $value = stripslashes($value); }
        $additionalParams .= htmlspecialchars('&'.urlencode($key).'='.urlencode($value), ENT_QUOTES);
    }
    $script = "
    if (typeof({$varprefix}zones) != 'undefined') {
        var {$varprefix}zoneids = '';
        for (var zonename in {$varprefix}zones) {$varprefix}zoneids += escape(zonename+'=' + {$varprefix}zones[zonename] + \"|\");
        {$varprefix}zoneids += '&amp;nz=1';
    } else {
        var {$varprefix}zoneids = escape('" . implode('|', array_keys($aZones)) . "');
    }

    if (typeof({$varprefix}source) == 'undefined') { {$varprefix}source = ''; }
    var {$varprefix}p=location.protocol=='https:'?'".
        MAX_commonConstructSecureDeliveryUrl($aConf['file']['singlepagecall'], true).
        "':'".
        MAX_commonConstructDeliveryUrl($aConf['file']['singlepagecall'])."';
    var {$varprefix}r=Math.floor(Math.random()*99999999);
    {$varprefix}output = new Array();

    var {$varprefix}spc=\"<\"+\"script type='text/javascript' \";
    {$varprefix}spc+=\"src='\"+{$varprefix}p+\"?zones=\"+{$varprefix}zoneids;
    {$varprefix}spc+=\"&amp;source=\"+escape({$varprefix}source)+\"&amp;r=\"+{$varprefix}r;" .
    ((!empty($additionalParams)) ? "\n    {$varprefix}spc+=\"{$additionalParams}\";" : '') . "
    ";
    if (empty($_GET['charset'])) {
        $script .= "{$varprefix}spc+=(document.charset ? '&amp;charset='+document.charset : (document.characterSet ? '&amp;charset='+document.characterSet : ''));\n";
    }
    $script .= "
    if (window.location) {$varprefix}spc+=\"&amp;loc=\"+escape(window.location);
    if (document.referrer) {$varprefix}spc+=\"&amp;referer=\"+escape(document.referrer);
    {$varprefix}spc+=\"'><\"+\"/script>\";
    document.write({$varprefix}spc);

    function {$varprefix}show(name) {
        if (typeof({$varprefix}output[name]) == 'undefined') {
            return;
        } else {
            document.write({$varprefix}output[name]);
        }
    }

    function {$varprefix}showpop(name) {
        zones = window.{$varprefix}zones ? window.{$varprefix}zones : false;
        var zoneid = name;
        if (typeof(window.{$varprefix}zones) != 'undefined') {
            if (typeof(zones[name]) == 'undefined') {
                return;
            }
            zoneid = zones[name];
        }

        {$varprefix}p=location.protocol=='https:'?'".
        MAX_commonConstructSecureDeliveryUrl($aConf['file']['popup'], true).
        "':'".
        MAX_commonConstructDeliveryUrl($aConf['file']['popup'])."';

        var {$varprefix}pop=\"<\"+\"script type='text/javascript' \";
        {$varprefix}pop+=\"src='\"+{$varprefix}p+\"?zoneid=\"+zoneid;
        {$varprefix}pop+=\"&amp;source=\"+escape({$varprefix}source)+\"&amp;r=\"+{$varprefix}r;" .
        ((!empty($additionalParams)) ? "\n        {$varprefix}spc+=\"{$additionalParams}\";" : '') . "
        if (window.location) {$varprefix}pop+=\"&amp;loc=\"+escape(window.location);
        if (document.referrer) {$varprefix}pop+=\"&amp;referer=\"+escape(document.referrer);
        {$varprefix}pop+=\"'><\"+\"/script>\";

        document.write({$varprefix}pop);
    }
";

    // Add the FlashObject include to the SPC output
    $script .= MAX_javascriptToHTML(MAX_flashGetFlashObjectExternal(), $varprefix . 'fo');

    return $script;
}

?>
