package org.openx.banner;

import java.net.MalformedURLException;
import java.util.HashMap;
import java.util.Map;

import org.apache.xmlrpc.XmlRpcException;
import org.openx.utils.ErrorMessage;

public class TestTargeting extends BannerTestCase {

	public void testGetAndSetTargetingWithEmptyArrays()
			throws MalformedURLException, XmlRpcException {

		assertNotNull(campaignId);
		Map<String, Object> myBanner = getBannerParams("test1");
		myBanner.put(STATUS, 1);

		Object[] XMLRPCMethodParameters = new Object[] { sessionId, myBanner };
		final Integer bannerId = (Integer) execute(ADD_BANNER_METHOD,
				XMLRPCMethodParameters);
		
		assertNotNull(bannerId);

		try {
			Object[] targeting = new Object[0];
			XMLRPCMethodParameters = new Object[] { sessionId, bannerId,
					targeting };
			
			final Boolean result = (Boolean) execute(SET_BANNER_TARGETING,
					XMLRPCMethodParameters);
			
			assertTrue(result);
			XMLRPCMethodParameters = new Object[] { sessionId, bannerId };
			final Object[] targetingResult = (Object[]) execute(
					GET_BANNER_TARGETING, XMLRPCMethodParameters);
			
			assertEquals(targetingResult.length, 0);
		} finally {
			deleteBanner(bannerId);
		}
	}

	@SuppressWarnings("unchecked")
	public void testGetAndSetTargeting() throws MalformedURLException,
			XmlRpcException {

		assertNotNull(campaignId);
		Map<String, Object> myBanner = getBannerParams("test1");
		myBanner.put(STATUS, 1);

		Object[] XMLRPCMethodParameters = new Object[] { sessionId, myBanner };
		final Integer bannerId = (Integer) execute(ADD_BANNER_METHOD,
				XMLRPCMethodParameters);
		
		assertNotNull(bannerId);

		try {

			Map<String, Object> targetingInfo1 = new HashMap<String, Object>();
			targetingInfo1.put(TARGETING_LOGICAL, "and");
			targetingInfo1.put(TARGETING_TYPE, "deliveryLimitations:Geo:Country");
			targetingInfo1.put(TARGETING_COMPATISON, "=");
			targetingInfo1.put(TARGETING_DATA, "");

			Object[] targeting = new Object[] { targetingInfo1 };

			XMLRPCMethodParameters = new Object[] { sessionId, bannerId,
					targeting };
			final Boolean result = (Boolean) execute(SET_BANNER_TARGETING,
					XMLRPCMethodParameters);
			assertTrue(result);

			XMLRPCMethodParameters = new Object[] { sessionId, bannerId };
			final Object XMLRPCResult[] = (Object[]) execute(
							GET_BANNER_TARGETING, XMLRPCMethodParameters);
			
			assertEquals(XMLRPCResult.length, 1);
			
			Map<String, Object> targetingResult = (Map<String, Object>) XMLRPCResult[0];
			
			checkParameter(targetingResult, targetingInfo1, TARGETING_LOGICAL);
			checkParameter(targetingResult, targetingInfo1, TARGETING_TYPE);
			checkParameter(targetingResult, targetingInfo1, TARGETING_COMPATISON);
			checkParameter(targetingResult, targetingInfo1, TARGETING_DATA);
		} finally {
			deleteBanner(bannerId);
		}
	}

	public void testGetAndSetTargetingWithError() throws MalformedURLException,
			XmlRpcException {

		assertNotNull(campaignId);
		Map<String, Object> myBanner = getBannerParams("test1");
		myBanner.put(STATUS, 1);

		Object[] XMLRPCMethodParameters = new Object[] { sessionId, myBanner };
		final Integer bannerId = (Integer) execute(ADD_BANNER_METHOD,
				XMLRPCMethodParameters);
		assertNotNull(bannerId);

		try {

			Map<String, Object> targetingInfo1 = new HashMap<String, Object>();
			targetingInfo1.put(TARGETING_LOGICAL, "and");
			targetingInfo1.put(TARGETING_TYPE, "deliveryLimitations:Geo:Country");
			targetingInfo1.put(TARGETING_COMPATISON, "=");

			Object[] targeting = new Object[] { targetingInfo1 };

			try {
				XMLRPCMethodParameters = new Object[] { sessionId, bannerId,
						targeting };
				execute(SET_BANNER_TARGETING, XMLRPCMethodParameters);
				fail();
			} catch (XmlRpcException e) {
				assertEquals(ErrorMessage.getMessage(ErrorMessage.FIELD_IN_STRUCTURE_DOES_NOT_EXISTS,
						"data"), e.getMessage());
			}
		} finally {
			deleteBanner(bannerId);
		}
	}

}
