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
$Id: TestPublisherAdvertiserStatistics.java 41383 2009-08-12 15:11:21Z matteo.beccati $
*/

package org.openx.publisher;

import java.net.MalformedURLException;

import org.apache.xmlrpc.XmlRpcException;
import org.openx.utils.DateUtils;
import org.openx.utils.ErrorMessage;
import org.openx.utils.TextUtils;

/**
 * Verify Publisher Advertiser Statistics method
 *
 * @author     Andriy Petlyovanyy <apetlyovanyy@lohika.com>
 */
public class TestPublisherAdvertiserStatistics extends PublisherTestCase {
	private Integer publisherId = null;

	@Override
	protected void setUp() throws Exception {
		super.setUp();
		publisherId = createPublisher();
	}

	@Override
	protected void tearDown() throws Exception {
		deletePublisher(publisherId);
		super.tearDown();
	}

	/**
	 * Execute test method with error
	 *
	 * @param params -
	 *            parameters for test method
	 * @param errorMsg -
	 *            true error messages
	 * @throws MalformedURLException
	 */
	private void executePublisherAdvertiserStatisticsWithError(Object[] params,
			String errorMsg) throws MalformedURLException {
		try {
			execute(PUBLISHER_ADVERTISER_STATISTICS_METHOD, params);
			fail(ErrorMessage.METHOD_EXECUTED_SUCCESSFULLY_BUT_SHOULD_NOT_HAVE);
		} catch (XmlRpcException e) {
			assertEquals(ErrorMessage.WRONG_ERROR_MESSAGE, errorMsg, e
					.getMessage());
		}
	}

	/**
	 * Test method with all required fields and some optional.
	 *
	 * @throws XmlRpcException
	 * @throws MalformedURLException
	 */
	public void testPublisherAdvertiserStatisticsAllReqAndSomeOptionalFields()
			throws XmlRpcException, MalformedURLException {
		assertNotNull(publisherId);

		Object[] params = new Object[] { sessionId, publisherId,
				DateUtils.MIN_DATE_VALUE };

		Object[] result = (Object[]) execute(
				PUBLISHER_ADVERTISER_STATISTICS_METHOD, params);
		assertNotNull(result);
	}

	/**
	 * Test method without some required fields.
	 *
	 * @throws MalformedURLException
	 *
	 */
	public void testPublisherAdvertiserStatisticsWithoutSomeRequiredFields()
			throws MalformedURLException {
		Object[] params = new Object[] { sessionId };

		executePublisherAdvertiserStatisticsWithError(params, ErrorMessage
				.getMessage(ErrorMessage.INCORRECT_PARAMETERS_PASSED_TO_METHOD,
						"5, 4, 3, or 2", "1"));
	}

	/**
	 * Test method with fields that has value greater than max.
	 *
	 * @throws MalformedURLException
	 * @throws XmlRpcException
	 */
	public void testPublisherAdvertiserStatisticsGreaterThanMaxFieldValueError()
			throws MalformedURLException, XmlRpcException {
		Object[] params = new Object[] { sessionId, publisherId,
				DateUtils.MIN_DATE_VALUE, DateUtils.DATE_GREATER_THAN_MAX };

		executePublisherAdvertiserStatisticsWithError(params,
				ErrorMessage.YEAR_SHOULD_BE_IN_RANGE_1970_2038);
	}

	/**
	 * Test method with fields that has value less than min
	 *
	 * @throws MalformedURLException
	 */
	public void testPublisherAdvertiserStatisticsLessThanMinFieldValueError()
			throws MalformedURLException {
		Object[] params = new Object[] { sessionId, publisherId,
				DateUtils.DATE_LESS_THAN_MIN, DateUtils.MAX_DATE_VALUE };

		executePublisherAdvertiserStatisticsWithError(params,
				ErrorMessage.YEAR_SHOULD_BE_IN_RANGE_1970_2038);
	}

	/**
	 * Test method with fields that has min. allowed values.
	 *
	 * @throws XmlRpcException
	 * @throws MalformedURLException
	 */
	public void testPublisherAdvertiserStatisticsMinValues()
			throws XmlRpcException, MalformedURLException {
		Object[] params = new Object[] { sessionId, publisherId,
				DateUtils.MIN_DATE_VALUE, DateUtils.MIN_DATE_VALUE };
		final Object[] result = (Object[]) execute(
				PUBLISHER_ADVERTISER_STATISTICS_METHOD, params);
		assertNotNull(result);
	}

	/**
	 * Test method with fields that has max. allowed values.
	 *
	 * @throws XmlRpcException
	 * @throws MalformedURLException
	 */
	public void testPublisherAdvertiserStatisticsMaxValues()
			throws XmlRpcException, MalformedURLException {
		Object[] params = new Object[] { sessionId, publisherId,
				DateUtils.MAX_DATE_VALUE, DateUtils.MAX_DATE_VALUE };
		final Object[] result = (Object[]) execute(
				PUBLISHER_ADVERTISER_STATISTICS_METHOD, params);
		assertNotNull(result);
	}

	/**
	 * Test methods for Unknown ID Error, described in API
	 *
	 * @throws MalformedURLException
	 * @throws XmlRpcException
	 */
	public void testPublisherAdvertiserStatisticsUnknownIdError()
			throws MalformedURLException, XmlRpcException {
		int publisheId = createPublisher();
		assertNotNull(publisheId);
		deletePublisher(publisheId);

		Object[] params = new Object[] { sessionId, publisheId };
		executePublisherAdvertiserStatisticsWithError(params, ErrorMessage
				.getMessage(ErrorMessage.UNKNOWN_ID_ERROR, PUBLISHER_ID));
	}

	/**
	 * Test methods for Date Error when end date is before start date
	 *
	 * @throws XmlRpcException
	 * @throws MalformedURLException
	 */
	public void testPublisherAdvertiserStatisticsDateError()
			throws XmlRpcException, MalformedURLException {
		Object[] params = new Object[] { sessionId, publisherId,
				DateUtils.MAX_DATE_VALUE, DateUtils.MIN_DATE_VALUE };

		executePublisherAdvertiserStatisticsWithError(params, ErrorMessage
				.getMessage(ErrorMessage.START_DATE_IS_AFTER_END_DATE));
	}

	/**
	 * Test method with fields that has value of wrong type (error).
	 *
	 * @throws MalformedURLException
	 */
	public void testPublisherAdvertiserStatisticsWrongTypeError()
			throws MalformedURLException {
		Object[] params = new Object[] { sessionId, publisherId,
				TextUtils.NOT_DATE, DateUtils.MAX_DATE_VALUE };
		executePublisherAdvertiserStatisticsWithError(
				params,
				ErrorMessage
						.getMessage(
								ErrorMessage.INCORRECT_PARAMETERS_WANTED_DATE_GOT_STRING,
								"3"));

		params = new Object[] { sessionId, publisherId,
				DateUtils.MIN_DATE_VALUE, TextUtils.NOT_DATE };
		executePublisherAdvertiserStatisticsWithError(
				params,
				ErrorMessage
						.getMessage(
								ErrorMessage.INCORRECT_PARAMETERS_WANTED_DATE_GOT_STRING,
								"4"));
	}
}
