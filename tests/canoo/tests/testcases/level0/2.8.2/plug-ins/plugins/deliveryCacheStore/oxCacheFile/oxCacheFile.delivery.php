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
$Id: oxCacheFile.delivery.php 33995 2009-03-18 23:04:15Z chris.nutting $
*/

/**
 * A File based cache store plugin for delivery cache - delivery functions
 *
 * @package    OpenXPlugin
 * @subpackage DeliveryCacheStore
 * @author     Lukasz Wikierski <lukasz.wikierski@openx.org>
 */

/**
 * Make sure that the custom path is used if set
 */
if (!empty($GLOBALS['_MAX']['CONF']['oxCacheFile']['cachePath'])) {
    $GLOBALS['OA_Delivery_Cache']['path'] = trim($GLOBALS['_MAX']['CONF']['oxCacheFile']['cachePath']).'/';
} else {
    $GLOBALS['OA_Delivery_Cache']['path'] = MAX_PATH.'/var/cache/';
}

/**
 * Function to fetch a cache entry
 *
 * @param string $filename The name of file where cache entry is stored
 * @return mixed False on error, or array the cache content
 */
function Plugin_deliveryCacheStore_oxCacheFile_oxCacheFile_Delivery_cacheRetrieve($filename) 
{
    $cache_complete = false;
    $cache_contents = '';

    // We are assuming that most of the time cache will exists
    $ok = @include($GLOBALS['OA_Delivery_Cache']['path'].$filename);

    if ($ok && $cache_complete == true) {
        return $cache_contents;
    }

    return false;
}

/**
 * A function to store content a cache entry.
 *
 * @param string $filename The filename where cache entry is stored
 * @param array $cache_contents  The cache content
 * @return bool True if the entry was succesfully stored
 */
function Plugin_deliveryCacheStore_oxCacheFile_oxCacheFile_Delivery_cacheStore($filename, $cache_contents)
{    
    if (!is_writable($GLOBALS['OA_Delivery_Cache']['path'])) {
        return false;
    }

    $filename = $GLOBALS['OA_Delivery_Cache']['path'].$filename;

    $cache_literal  = "<"."?php\n\n";
    $cache_literal .= "$"."cache_contents   = ".var_export($cache_contents, true).";\n\n";
    $cache_literal .= "$"."cache_complete   = true;\n\n";
    $cache_literal .= "?".">";

    // Write cache to a temp file, then rename it, overwritng the old cache
    // On *nix systems this should guarantee atomicity
    $tmp_filename = tempnam($GLOBALS['OA_Delivery_Cache']['path'], $GLOBALS['OA_Delivery_Cache']['prefix'].'tmp_');
    if ($fp = @fopen($tmp_filename, 'wb')) {
        @fwrite ($fp, $cache_literal, strlen($cache_literal));
        @fclose ($fp);

        if (!@rename($tmp_filename, $filename)) {
            // On some systems rename() doesn't overwrite destination
            @unlink($filename);
            if (!@rename($tmp_filename, $filename)) {
                // Make sure that no temporary file is left over
                // if the destination is not writable
                @unlink($tmp_filename);
            }
        }
        if (PHP_SAPI == 'cli') {
            // If delivery cache is used during maintenance with php-cli,
            // most likely the user running it is not the webserver user.
            // Chmod 777 to prevent issues when the webserver tries to
            // access the file
            @chmod($filename, 0777);
        }
        return true;
    }
    return false;
}

?>
