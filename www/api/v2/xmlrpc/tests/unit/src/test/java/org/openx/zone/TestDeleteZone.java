/*
+---------------------------------------------------------------------------+
| `v2.5                                                              |
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
$Id: TestDeleteZone.java 41383 2009-08-12 15:11:21Z matteo.beccati $
*/

package org.openx.zone;

import java.net.MalformedURLException;

import org.apache.xmlrpc.XmlRpcException;
import org.openx.utils.ErrorMessage;
import org.openx.utils.TextUtils;

/**
 * Verify Delete Zone method
 *
 * @author     Andriy Petlyovanyy <apetlyovanyy@lohika.com>
 */
public class TestDeleteZone extends ZoneTestCase {
	/**
	 * Execute test method with error
	 *
	 * @param params -
	 *            parameters for test method
	 * @param errorMsg -
	 *            true error messages
	 * @throws MalformedURLException
	 */
	private void executeDeleteZoneWithError(Object[] params, String errorMsg)
			throws MalformedURLException {
		try {
			execute(DELETE_ZONE_METHOD, params);
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
	public void testDeleteZoneAllReqAndSomeOptionalFields()
			throws XmlRpcException, MalformedURLException {
		int zoneId = createZone();
		assertNotNull("Can't add zone.", zoneId);
		final Boolean result = (Boolean) client.execute(DELETE_ZONE_METHOD,
				new Object[] { sessionId, zoneId });
		assertTrue("Can't delete zone.", result);
	}

	/**
	 * Test method without some required fields.
	 *
	 * @throws MalformedURLException
	 */
	public void testDeleteZoneWithoutSomeRequiredFields() throws MalformedURLException {

		executeDeleteZoneWithError(new Object[] { sessionId }, ErrorMessage
				.getMessage(ErrorMessage.INCORRECT_PARAMETERS_PASSED_TO_METHOD,
						"2", "1"));
	}

	/**
	 * Test methods for Unknown ID Error, described in API
	 *
	 * @throws MalformedURLException
	 * @throws XmlRpcException
	 */
	public void testDeleteZoneUnknownIdError() throws MalformedURLException,
			XmlRpcException {
		int zoneId = createZone();
		assertNotNull(zoneId);
		deleteZone(zoneId);

		Object[] params = new Object[] { sessionId, zoneId };
		executeDeleteZoneWithError(params, ErrorMessage.getMessage(
				ErrorMessage.UNKNOWN_ID_ERROR, ZONE_ID));
	}

	/**
	 * Test method with fields that has value of wrong type (error).
	 *
	 * @throws MalformedURLException
	 */
	public void testDeleteZoneWrongTypeError() throws MalformedURLException {
		Object[] params = new Object[] { sessionId, TextUtils.NOT_INTEGER };

		executeDeleteZoneWithError(params, ErrorMessage.getMessage(
				ErrorMessage.INCORRECT_PARAMETERS_WANTED_INT_GOT_STRING, "2"));
	}
}
