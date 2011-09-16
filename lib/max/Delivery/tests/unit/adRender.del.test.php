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
$Id: adRender.del.test.php 36256 2009-05-12 08:29:01Z andrew.hill $
*/

require_once MAX_PATH . '/lib/max/Delivery/adRender.php';

/**
 * A class for testing the ad.php functions.
 *
 * @package    MaxDelivery
 * @subpackage TestSuite
 * @author
 *
 */
class Test_DeliveryAdRender extends UnitTestCase
{

    /**
     * The constructor method.
     */
    function __construct()
    {
        $this->UnitTestCase();
    }

    /**
     * TODO: need data
     *
     */
	function test_MAX_adRender()
	{
//		$this->sendMessage('test_MAX_adRender');

//		require_once MAX_PATH . '/lib/max/Delivery/common.php';
// note: following code to extract test data from db
//      require_once MAX_PATH . '/lib/max/Delivery/tests/data/test_adRenderImage.php';
//	    $return = _adRenderImage($aBanner, $zoneId, $source, $target, $ct0, $withText, $logClick, $logView, $richMedia, $loc, $referer, $context);
//		$this->assertEqual($return, $result);

        // Silly test, test patch OX-2091
        require MAX_PATH . '/lib/max/Delivery/tests/data/test_adRenderImage.php';

        $this->assertNull($aBanner['bannerContent']);
        $this->assertNull($aBanner['logUrl']);
        $this->assertNull($aBanner['clickUrl']);

        $return = MAX_adRender($aBanner);

        $this->assertTrue($aBanner['bannerContent']);
        $this->assertTrue($aBanner['logUrl']);
        $this->assertTrue($aBanner['clickUrl']);
	}

	/**
	 * render an ad of type image
	 *
	 */
    function test_adRenderImage()
	{
        $this->sendMessage('test_adRenderImage');

        require_once MAX_PATH . '/lib/max/Delivery/common.php';

        // note: following code to extract test data from db
        // require_once MAX_PATH . '/lib/OA/Dal/Delivery/'.$GLOBALS['_MAX']['CONF']['database']['type'].'.php';
        // OA_Dal_Delivery_connect();
        // $aBanner = (array)OA_Dal_Delivery_getAd(7);
        // $prn    = print_r($aBanner, TRUE);

        require MAX_PATH . '/lib/max/Delivery/tests/data/test_adRenderImage.php';

		$return	= _adRenderImage($aBanner, $zoneId, $source, $ct0, $withText, $logClick, $logView, $useAlt, $richMedia, $loc, $referer, $useAppend);
		$this->assertEqual($return, $expect);
	}

	/**
	 * @todo functions below test for individual element structures e.g. logging beacon
     *  this function should test for total structure and presence of individual elements
     *  divA is Flash-specific object container
     *  script is Flash object code
     *  divB is logging beacon
	 *
	 * render an ad of type Flash
	 *
	 */
	function test_adRenderFlash()
	{
		$this->sendMessage('test_adRenderFlash');

		require_once MAX_PATH . '/lib/max/Delivery/common.php';

		// note: following code to extract test data from db
//        require_once MAX_PATH . '/lib/OA/Dal/Delivery/'.$GLOBALS['_MAX']['CONF']['database']['type'].'.php';
//        OA_Dal_Delivery_connect();
//        $aBanner = (array)OA_Dal_Delivery_getAd(2);
//        $prn    = print_r($aBanner, TRUE);

        require MAX_PATH . '/lib/max/Delivery/tests/data/test_adRenderFlash.php';
        $return		= _adRenderFlash($aBanner, $zoneId, $source, $ct0, $withText, $logClick, $logView, $useAlt, $loc, $referer);

		$flags		= null;
		$offset		= null;
        // is prepended stuff returned?
        if (array_key_exists('prepend', $aBanner) && (!empty($aBanner['prepend'])))
        {
    		$i	= preg_match('/'.$aPattern['pre'].'/', $return, $aMatch);
    		$this->assertTrue($i,'prepend');
        }
        // is appended stuff returned?
        if (array_key_exists('append', $aBanner) && (!empty($aBanner['append'])))
        {
    		$i	= preg_match('/'.$aPattern['app'].'/', $return, $aMatch);
    		$this->assertTrue($i,'append');
        }
        // break known structure into array of individual elements
   		$i	= preg_match_all('/'.$aPattern['stru'].'/U', $return, $aMatch);
		$this->assertTrue($i,'structure');


		// Test a converted SWF banner
        $aBanner['parameters'] = serialize(array(
            'swf' => array(
                '1' => array(
                    'link' => 'http://www.example.com',
                    'tar'  => '_blank'
                )
            )
		));

	    $return		= _adRenderFlash($aBanner, $zoneId, $source, $ct0, $withText, $logClick, $logView, $useAlt, $loc, $referer);

		$flags		= null;
		$offset		= null;
        // is prepended stuff returned?
        if (array_key_exists('prepend', $aBanner) && (!empty($aBanner['prepend'])))
        {
    		$i	= preg_match('/'.$aPattern['pre'].'/', $return, $aMatch);
    		$this->assertTrue($i,'prepend');
        }
        // is appended stuff returned?
        if (array_key_exists('append', $aBanner) && (!empty($aBanner['append'])))
        {
    		$i	= preg_match('/'.$aPattern['app'].'/', $return, $aMatch);
    		$this->assertTrue($i,'append');
        }
        // break known structure into array of individual elements
   		$i	= preg_match_all('/'.$aPattern['stru'].'/U', $return, $aMatch);
		$this->assertTrue($i,'structure');

		// Check for converded link (now a FlashVar)
		$this->assertTrue(preg_match('/addVariable\(\'alink1\',.*example.*/', $aMatch['script_content'][0]));

		// And target
		$this->assertTrue(strstr($aMatch['script_content'][0], "addVariable('atar1', '_blank')"));

	}

	/**
	 * @todo further test cases:
	 *     presence of individual elements;
	 *     processed HTML e.g. macro replacements {clickurl};
	 *     logging beacon
	 *
	 * render an ad of type HTML
	 *
	 */
	function test_adRenderHtml()
	{
		$this->sendMessage('test_adRenderHtml');
        MAX_Dal_Delivery_Include();
        OA_Dal_Delivery_connect();
        $aBanner    = (array)OA_Dal_Delivery_getAd(4);
        $prn        = print_r($aBanner, TRUE);

        $GLOBALS['_MAX']['CONF']['logging']['adImpressions'] = '';
		$GLOBALS['_MAX']['CONF']['delivery']['execPhp']		= TRUE;

        require_once MAX_PATH . '/lib/max/Delivery/tests/data/test_adRenderHtml.php';
		$ret 	= _adRenderHtml($aBanner, $zoneId, $source, $ct0, $withText, $logClick, $logView, $useAlt, $loc, $referer);
        $this->assertEqual($ret, $expect);

	}

	/**
	 * @todo test for append & prepend
	 *
	 * render an ad of type Text
	 *
	 */
	function test_adRenderText()
	{
		$this->sendMessage('test_adRenderText');
        // Test that it should generate ad without beacon
		$GLOBALS['_MAX']['CONF']['logging']['adImpressions'] = '';
        require_once MAX_PATH . '/lib/max/Delivery/tests/data/test_adRenderText.php';
		$return = _adRenderText($aBanner, $zoneId, $source, $ct0, $withText, $logClick, $logView, $useAlt, $richMedia, $loc, $referer);
        $this->assertEqual($return, $expectNoBeacon);

		// Test that it should generate ad beacon
		$GLOBALS['_MAX']['CONF']['logging']['adImpressions'] = true;
        require_once MAX_PATH . '/lib/max/Delivery/tests/data/test_adRenderText.php';
		$return = _adRenderText($aBanner, $zoneId, $source, $ct0, $withText, $logClick, $logView, $useAlt, $richMedia, $loc, $referer);
        $this->assertEqual($return, $expect);
	}

    /**
     * Test1: external image with no params and not using alt image
     * Test2: local image with no params and not using alt image
     * Test3: local image with no params and using alt image
     * Test4: local image with params and not using alt image
     *
     * build a file URL
     *
     */
	function test_adRenderBuildFileUrl()
	{
		$this->sendMessage('test_adRenderBuildFileUrl');
        // Test1
		$aBanner	= array('filename'=>'myfile.jpg',
							'alt_filename'=>'myaltfile.jpg',
							'imageurl'=>'http://www.somewhere.com/myimageurl.jpg',
							'type'=>'url',
							'contenttype'=>''
							);
		$useAlt = false;
		$params	= '';
		$ret 	= _adRenderBuildFileUrl($aBanner, $useAlt, $params);
		$this->assertEqual($ret,'http://www.somewhere.com/myimageurl.jpg');
        // Test2
		$aBanner['type'] = 'web';
		$useAlt = false;
		$params	= '';
		$GLOBALS['_MAX']['CONF']['webpath']['images'] = 'www.max.net/www/images';
		$ret 	= _adRenderBuildFileUrl($aBanner, $useAlt, $params);
		$this->assertEqual($ret,'http://www.max.net/www/images/myfile.jpg');
		// Test3
		$useAlt = true;
		$params	= '';
		$ret 	= _adRenderBuildFileUrl($aBanner, $useAlt, $params);
		$this->assertEqual($ret,'http://www.max.net/www/images/myaltfile.jpg');
		// Test4
		$useAlt = false;
		$params	= 'a=1&b=2';
		$ret 	= _adRenderBuildFileUrl($aBanner, $useAlt, $params);
		$this->assertEqual($ret,'http://www.max.net/www/images/myfile.jpg?a=1&b=2');

	}

	/**
	 * build an image URL prefix
	 *
	 */
	function test_adRenderBuildImageUrlPrefix()
	{
		$this->sendMessage('test_adGetImageUrlPrefix');

		$GLOBALS['_MAX']['CONF']['webpath']['images'] = 'www.max.net/www/images';
		$ret = _adRenderBuildImageUrlPrefix();
		$this->assertEqual($ret,'http://www.max.net/www/images');
	}

	/**
	 * build a log URL
	 *
	 */
	function test_adRenderBuildLogURL()
	{
		$this->sendMessage('test_adRenderBuildLogURL');

		require_once MAX_PATH . '/lib/max/Delivery/common.php';

		$aBanner	= array('bannerid'=>'9999',
							'url'=>'http://www.somewhere.com',
							'contenttype'=>''
							);
		$zoneId		= 1 ;
		$source 	= 'test';
		$loc 		= 'http://www.example.com/page.php?name=value';
		$referer 	= 'http://www.example.com/referer.php?name=value';
		$amp 		= '&amp;';
		$return = _adRenderBuildLogURL($aBanner, $zoneId, $source, $loc, $referer, $amp);
        $expect = "http://".$GLOBALS['_MAX']['CONF']['webpath']['delivery']."/lg.php?bannerid=&amp;campaignid=&amp;zoneid=1&amp;source=test&amp;loc=http%3A%2F%2Fwww.example.com%2Fpage.php%3Fname%3Dvalue&amp;referer=http%3A%2F%2Fwww.example.com%2Freferer.php%3Fname%3Dvalue&amp;cb={random}";
        $this->assertEqual($return, $expect);
	}

	/**
	 * @todo more test cases
	 *     referer
	 *     loc
	 *     zoneid
	 *     capping info
	 *
	 * render an image beacon
	 *
	 */
	function test_adRenderImageBeacon()
	{
		$this->sendMessage('test_adRenderImageBeacon');

		require_once MAX_PATH . '/lib/max/Delivery/common.php';
        require_once MAX_PATH . '/lib/max/Delivery/tests/data/test_adRenderImageBeacon.php';

		$return = _adRenderImageBeacon($aBanner, $zoneId, $source, $loc, $referer);

        // break known structure into array of individual elements
   		$i	= preg_match_all('/'.$aPattern['struct'].'/U', $return, $aMatch);
		$this->assertTrue($i,'structure');
	}

	/**
	 * build params
	 *
	 */
	function test_adRenderBuildParams()
	{
		$this->sendMessage('test_adRenderBuildParams');
		$aBanner	= array('bannerid'=>'9999',
							'url'=>'http://www.somewhere.com',
							'contenttype'=>''
							);
		$zoneId		= 0;
		$source		= '';
		$ct0		= '';
		$logClick	= true;
		$overrideDest = false;
		$conf = $GLOBALS['_MAX']['CONF'];

		$ret = _adRenderBuildParams($aBanner, $zoneId, $source, $ct0, $logClick, $overrideDest);
        $this->assertEqual($ret, "2__{$conf['var']['adId']}=9999__{$conf['var']['zoneId']}=0__{$conf['var']['cacheBuster']}={random}__{$conf['var']['dest']}=http%3A%2F%2Fwww.somewhere.com");
        preg_match('/(http.*?)$/', $ret, $m);
        $this->assertEqual(urldecode($m[1]), $aBanner['url']);


		$this->sendMessage('test_adRenderBuildParams');
		$aBanner	= array('bannerid'=>'9999',
							'url'=>'http://www.example.com/?foo+bar',
							'contenttype'=>''
							);
		$zoneId		= 0;
		$source		= '';
		$ct0		= '';
		$logClick	= true;
		$overrideDest = false;
		$conf = $GLOBALS['_MAX']['CONF'];

		$ret = _adRenderBuildParams($aBanner, $zoneId, $source, $ct0, $logClick, $overrideDest);
        $this->assertEqual($ret, "2__{$conf['var']['adId']}=9999__{$conf['var']['zoneId']}=0__{$conf['var']['cacheBuster']}={random}__{$conf['var']['dest']}=http%3A%2F%2Fwww.example.com%2F%3Ffoo%2Bbar");
        preg_match('/(http.*?)$/', $ret, $m);
        $this->assertEqual(urldecode($m[1]), $aBanner['url']);


		$this->sendMessage('test_adRenderBuildParams');
		$aBanner	= array('bannerid'=>'9999',
							'url'=>'http://www.example.com/?foo+bar',
							'contenttype'=>''
							);
		$zoneId		= 0;
		$source		= '';
		$ct0		= 'http://www.openx.org/ck.php?foo=bar&dest=';
		$logClick	= true;
		$overrideDest = false;
		$conf = $GLOBALS['_MAX']['CONF'];

		$ret = _adRenderBuildParams($aBanner, $zoneId, $source, $ct0, $logClick, $overrideDest);
        $this->assertEqual($ret, "2__{$conf['var']['adId']}=9999__{$conf['var']['zoneId']}=0__{$conf['var']['cacheBuster']}={random}__{$conf['var']['dest']}=http%3A%2F%2Fwww.openx.org%2Fck.php%3Ffoo%3Dbar%26dest%3Dhttp%253A%252F%252Fwww.example.com%252F%253Ffoo%252Bbar");
        preg_match('/(http.*?openx.org.*?)$/', $ret, $m);
        preg_match('/=(http.*?)$/', urldecode($m[1]), $m);
        $this->assertEqual(urldecode($m[1]), $aBanner['url']);


        // Same as above, wrong ct0
		$this->sendMessage('test_adRenderBuildParams');
		$aBanner	= array('bannerid'=>'9999',
							'url'=>'http://www.example.com/?foo+bar',
							'contenttype'=>''
							);
		$zoneId		= 0;
		$source		= '';
		$ct0		= 'foobar';
		$logClick	= true;
		$overrideDest = false;
		$conf = $GLOBALS['_MAX']['CONF'];

		$ret = _adRenderBuildParams($aBanner, $zoneId, $source, $ct0, $logClick, $overrideDest);
        $this->assertEqual($ret, "2__{$conf['var']['adId']}=9999__{$conf['var']['zoneId']}=0__{$conf['var']['cacheBuster']}={random}__{$conf['var']['dest']}=http%3A%2F%2Fwww.example.com%2F%3Ffoo%2Bbar");
        preg_match('/(http.*?)$/', $ret, $m);
        $this->assertEqual(urldecode($m[1]), $aBanner['url']);
	}

	/**
	 * build click URL
	 *
	 */
	function test_adRenderBuildClickUrl()
	{
		$this->sendMessage('test_adRenderBuildClickUrl');

		// following line suggests that this func not used
		require_once MAX_PATH . '/lib/max/Delivery/common.php';

		$aBanner	= array('bannerid'=>'9999',
							'url'=>'http://www.somewhere.com',
							'contenttype'=>''
							);
		$zoneId		= 0;
		$source		= '';
		$ct0		= '';
		$logClick	= true;
		$overrideDest = false;
		$conf = $GLOBALS['_MAX']['CONF'];

		$ret = _adRenderBuildClickUrl($aBanner, $zoneId, $source, $ct0, $logClick, $overrideDest);
        $this->assertEqual($ret, "http://{$GLOBALS['_MAX']['CONF']['webpath']['delivery']}/ck.php?{$conf['var']['params']}=2__{$conf['var']['adId']}=9999__{$conf['var']['zoneId']}=0__{$conf['var']['cacheBuster']}={random}__{$conf['var']['dest']}=http%3A%2F%2Fwww.somewhere.com");

        // XSS via ct0
		$aBanner	= array('bannerid'=>'9999',
							'url'=>'http://www.somewhere.com',
							'contenttype'=>''
							);
		$zoneId		= 0;
		$source		= '';
		$ct0		= 'http\'>XSS';
		$logClick	= true;
		$overrideDest = false;
		$conf = $GLOBALS['_MAX']['CONF'];

		$ret = _adRenderBuildClickUrl($aBanner, $zoneId, $source, $ct0, $logClick, $overrideDest);
        $this->assertEqual($ret, "http://{$GLOBALS['_MAX']['CONF']['webpath']['delivery']}/ck.php?{$conf['var']['params']}=2__{$conf['var']['adId']}=9999__{$conf['var']['zoneId']}=0__{$conf['var']['cacheBuster']}={random}__{$conf['var']['dest']}=http%27%3EXSShttp%253A%252F%252Fwww.somewhere.com");
	}
}

?>
