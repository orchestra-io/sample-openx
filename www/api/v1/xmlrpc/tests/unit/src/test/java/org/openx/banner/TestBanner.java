/*
+---------------------------------------------------------------------------+
| OpenX v${RELEASE_MAJOR_MINOR}                                                                |
| ======${RELEASE_MAJOR_MINOR_DOUBLE_UNDERLINE}                                                                 |
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
$Id: TestBanner.java 41383 2009-08-12 15:11:21Z matteo.beccati $
*/

package org.openx.banner;

import junit.framework.Test;
import junit.framework.TestSuite;

/**
 * Run all Banner service tests
 *
 * @author     Andriy Petlyovanyy <apetlyovanyy@lohika.com>
 */
public class TestBanner {

	public static Test suite() {
		TestSuite suite = new TestSuite("Tests for org.openx.banner");
		// $JUnit-BEGIN$
		suite.addTestSuite(TestBannerDailyStatistics.class);
		suite.addTestSuite(TestModifyBanner.class);
		suite.addTestSuite(TestDeleteBanner.class);
		suite.addTestSuite(TestBannerPublisherStatistics.class);
		suite.addTestSuite(TestBannerZoneStatistics.class);
		suite.addTestSuite(TestAddBanner.class);
		suite.addTestSuite(TestGetBanner.class);
		suite.addTestSuite(TestGetBannerListByCampaignId.class);
		// $JUnit-END$
		return suite;
	}

}
