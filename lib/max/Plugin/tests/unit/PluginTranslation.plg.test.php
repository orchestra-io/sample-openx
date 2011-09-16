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
$Id: PluginTranslation.plg.test.php 44377 2009-10-06 11:08:37Z david.keen $
*/

/**
 * @package    Max
 * @subpackage TestSuite
 * @author     Radek Maciaszek <radek@m3.net>
 */

    require_once(MAX_PATH . '/lib/max/Plugin/Translation.php');

    define('MAX_PLUGINTRANSLATION_TEST_DIR', dirname(__FILE__).'/../testdir');

    class TestOfPluginTranslation extends UnitTestCase {

        function TestOfPluginTranslation() {
            $this->UnitTestCase('PluginTranslation test');
        }

        function testIncludePluginLanguageFile() {
            $extension = 'nonExistingModule';
            $group = 'nonExistingPackage';
            $language = 'nonExistingLanguage';

            $ret = MAX_Plugin_Translation::includePluginLanguageFile($extension, null, $language);
            $this->assertIdentical($ret, false);
            $this->assertIdentical($GLOBALS['_MAX']['PLUGIN_TRANSLATION'][$extension], array());

            $ret = MAX_Plugin_Translation::includePluginLanguageFile($extension, $group, $language);
            $this->assertIdentical($ret, false);
            $this->assertIdentical($GLOBALS['_MAX']['PLUGIN_TRANSLATION'][$extension][$group], array());

            $translate = 'Some translation string';
            $ret = MAX_Plugin_Translation::translate($translate, $extension, $group);
            // translation wasn't included so should return the same value
            $this->assertIdentical($ret, $translate);

            $path = MAX_PLUGINTRANSLATION_TEST_DIR . '/_lang/';
            include $path . 'en.php';
            $enWords = $words;

            include $path . 'pl.php';
            $plWords = $words;

            $ret = MAX_Plugin_Translation::includePluginLanguageFile($extension,null,'en',$path);
            $this->assertIdentical($ret, true);
            $this->assertIdentical($GLOBALS['_MAX']['PLUGIN_TRANSLATION'][$extension], $enWords);

            $ret = MAX_Plugin_Translation::translate('translate me', $extension, $group);
            $this->assertIdentical($ret, 'translated text');

            // Clear the translation memory
            unset($GLOBALS['_MAX']['PLUGIN_TRANSLATION']);

            $ret = MAX_Plugin_Translation::includePluginLanguageFile($extension,null,'pl',$path);
            $this->assertIdentical($ret, true);
            $this->assertIdentical($GLOBALS['_MAX']['PLUGIN_TRANSLATION'][$extension], array_merge($enWords, $plWords));

            // Check that a translation which doesn't exist in the selected language falls through to the english
            $ret = MAX_Plugin_Translation::translate('translate me (fallback to english)', $extension, $group);
            $this->assertIdentical($ret, 'this is from the english pack');

            // Check that a translation key which doesn't exist in selected or english languages returns the key unchanged
            $ret = MAX_Plugin_Translation::translate('this string does not exist in the language packs', $extension, $group);
            $this->assertIdentical($ret, 'this string does not exist in the language packs');

            // Check that the non-existent key with the same name as group returns the key unchanged.
            $ret = MAX_Plugin_Translation::translate($group, $extension, $group);
            $this->assertIdentical($ret, $group);

        }

        /**
         * something is wrong with mock objects...
         */
        function _REPAIR_ME_testConfig() {
            Mock::generate('MAX_Plugin');

            $module = 'moduleName';
            $package = 'packageName';
            $processSections = false;

            $mockPlugin = &new MockMAX_Plugin($this);
            $mockPlugin->setReturnValue('getConfigByFileName', true);

            $mockPlugin->expectOnce('getConfigByFileName');

            $ret = $mockPlugin->getConfig($module, $package, null, $commonPackageConfig = true, $processSections);
            $this->assertIdentical($ret, true);
            $mockPlugin->tally();
        }

        /**
         * TODO: upgrade simpletest
         */
        function _REPAIR_ME_testGetPlugins() {
            Mock::generate('MAX_Plugin');
            $mockPlugins = new MockMAX_Plugin($this);
            $mockPlugins->setReturnValue('getPluginsFromFolder', true);

            $recursive = true;
            $mockPlugins->expectOnce('getPluginsFromFolder', array(MAX_PATH.'/plugins/moduleName/packageName', $recursive));
            $ret = $mockPlugins->getPlugins('moduleName', 'packageName', $recursive);
            $mockPlugins->tally();
        }

    }

?>