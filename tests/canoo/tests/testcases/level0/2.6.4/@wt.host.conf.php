;<?php exit; ?>
;*** DO NOT REMOVE THE LINE ABOVE ***
[openads]
installed=1
requireSSL=
sslPort=443
[ui]
enabled=1
applicationName=
headerFilePath=
footerFilePath=
logoFilePath=
headerForegroundColor=
headerBackgroundColor=
headerActiveTabColor=
headerTextColor=
gzipCompression=1
combineAssets=1
dashboardEnabled=1
[database]
type=@db.type
host=@db.host
socket=
port=@db.port
username=@db.login
password=@db.password
name=@db.name
persistent=
mysql4_compatibility=1
protocol=tcp
[databaseCharset]
checkComplete=1
clientCharset=latin1
[databaseMysql]
statisticsSortBufferSize=
[databasePgsql]
schema=
[webpath]
admin=@wt.host:@wt.port/@wt.basepath/admin
delivery=@wt.host:@wt.port/@wt.basepath/delivery
deliverySSL=@wt.host:@wt.port/@wt.basepath/delivery
images=@wt.host:@wt.port/@wt.basepath/images
imagesSSL=@wt.host:@wt.port/@wt.basepath/images
[file]
click="ck.php"
conversionvars="tv.php"
content="ac.php"
conversion="ti.php"
conversionjs="tjs.php"
flash="fl.js"
google="ag.php"
frame="afr.php"
image="ai.php"
js="ajs.php"
layer="al.php"
log="lg.php"
popup="apu.php"
view="avw.php"
xmlrpc="axmlrpc.php"
local="alocal.php"
frontcontroller="fc.php"
singlepagecall="spc.php"
spcjs="spcjs.php"
[store]
mode=0
webDir=@webDir
ftpHost=
ftpPath=
ftpUsername=
ftpPassword=
ftpPassive=
[origin]
type=
host=
port=80
script="/www/delivery/dxmlrpc.php"
timeout=10
protocol=@wt.protocol
[allowedBanners]
sql=1
web=1
url=1
html=1
text=1
[allowedTags]
adjs=1
adlayer=1
adviewnocookies=1
local=1
popup=
adframe=1
adview=
xmlrpc=
[delivery]
cacheExpire=1200
cachePath=
acls=1
obfuscate=
execPhp=
ctDelimiter=__
chDelimiter=","
keywords=
cgiForceStatusHeader=
clicktracking=
[defaultBanner]
imageUrl=
[p3p]
policies=1
compactPolicy="CUR ADM OUR NOR STA NID"
policyLocation=
[graphs]
ttfDirectory=
ttfName=
[logging]
adRequests=
adImpressions=1
adClicks=1
trackerImpressions=1
reverseLookup=
proxyLookup=1
sniff=
useragent=
pageInfo=
referer=
defaultImpressionConnectionWindow=
defaultClickConnectionWindow=
ignoreHosts=
ignoreUserAgents=
enforceUserAgents=
[maintenance]
autoMaintenance=1
timeLimitScripts=300
operationInterval=60
blockAdImpressions=0
blockAdClicks=0
compactStats=1
compactStatsGrace=604800
channelForecasting=
pruneCompletedCampaignsSummaryData=
[priority]
instantUpdate=1
defaultClickRatio="0.005"
defaultConversionRatio="0.0001"
randmax=2147483647
[table]
prefix=ox_
type=MYISAM
account_preference_assoc=account_preference_assoc
account_user_assoc=account_user_assoc
account_user_permission_assoc=account_user_permission_assoc
accounts=accounts
acls=acls
acls_channel=acls_channel
ad_category_assoc=ad_category_assoc
ad_zone_assoc=ad_zone_assoc
affiliates=affiliates
affiliates_extra=affiliates_extra
agency=agency
application_variable=application_variable
audit=audit
banners=banners
campaigns=campaigns
campaigns_trackers=campaigns_trackers
category=category
channel=channel
clients=clients
data_intermediate_ad=data_intermediate_ad
data_intermediate_ad_connection=data_intermediate_ad_connection
data_intermediate_ad_variable_value=data_intermediate_ad_variable_value
data_raw_ad_click=data_raw_ad_click
data_raw_ad_impression=data_raw_ad_impression
data_raw_ad_request=data_raw_ad_request
data_raw_tracker_impression=data_raw_tracker_impression
data_raw_tracker_variable_value=data_raw_tracker_variable_value
data_summary_ad_hourly=data_summary_ad_hourly
data_summary_ad_zone_assoc=data_summary_ad_zone_assoc
data_summary_channel_daily=data_summary_channel_daily
data_summary_zone_impression_history=data_summary_zone_impression_history
images=images
lb_local=lb_local
log_maintenance_forecasting=log_maintenance_forecasting
log_maintenance_priority=log_maintenance_priority
log_maintenance_statistics=log_maintenance_statistics
password_recovery=password_recovery
placement_zone_assoc=placement_zone_assoc
plugins_channel_delivery_assoc=plugins_channel_delivery_assoc
plugins_channel_delivery_domains=plugins_channel_delivery_domains
plugins_channel_delivery_rules=plugins_channel_delivery_rules
preferences=preferences
session=session
targetstats=targetstats
trackers=trackers
tracker_append=tracker_append
userlog=userlog
users=users
variables=variables
variable_publisher=variable_publisher
zones=zones
[email]
logOutgoing=1
headers=
qmailPatch=
fromName=
fromAddress=
fromCompany=
[log]
enabled=1
methodNames=
lineNumbers=
type=file
name="debug.log"
priority=6
ident=OX
paramsUsername=
paramsPassword=
fileMode=0644
[deliveryLog]
enabled=
name="delivery.log"
fileMode=0644
[cookie]
permCookieSeconds=31536000
[debug]
logfile=
production=1
sendErrorEmails=
emailSubject="Error from OpenX"
email="email@example.com"
emailAdminThreshold=3
errorOverride=1
showBacktrace=
[var]
prefix=OA_
cookieTest=ct
cacheBuster=cb
channel=source
dest=oadest
logClick=log
n=n
params=oaparams
viewerId=OAID
viewerGeo=OAGEO
campaignId=campaignid
adId=bannerid
creativeId=cid
zoneId=zoneid
blockAd=OABLOCK
capAd=OACAP
sessionCapAd=OASCAP
blockCampaign=OACBLOCK
capCampaign=OACCAP
sessionCapCampaign=OASCCAP
blockZone=OAZBLOCK
capZone=OAZCAP
sessionCapZone=OASZCAP
vars=OAVARS
trackonly=trackonly
openads=openads
[lb]
enabled=
hasSuper=
type=mysql
host=localhost
port=3306
username=
password=
name=
persistent=
compactStats=1
compactStatsGrace=604800
[sync]
checkForUpdates=1
shareStack=1
shareData=1
[oacSync]
protocol=https
host="sync.openx.org"
path="/xmlrpc.php"
httpPort=80
httpsPort=443
[oacXmlRpc]
protocol=https
host="oxc.openx.org"
port=443
path="/oxc/xmlrpc"
captcha="/oxc/captcha"
signUpUrl="/oxc/advertiser/signup"
publihserUrl="/oxc/advertiser/defzone"
[oacDashboard]
protocol=https
host="oxc.openx.org"
port=443
path="/oxc/dashboard/home"
ssoCheck="/oxc/ssoCheck"
[oacSSO]
protocol=https
host="login.openx.org"
port=443
path="/sso/login"
clientPath="/sso"
signup="/account/signup"
forgot="/account/forgotPassword"
[authentication]
type=internal
deleteUnverifiedUsersAfter=2419200
synchronizeEmail=
[channelDerivation]
cacheExpire=86400
cachePath="/var/plugins/cache/channelDerivation/"
xmlrpcScript="/www/delivery/delivery-xmlrpc.php"
[geotargeting]
type=
saveStats=
geoipCountryLocation=
geoipRegionLocation=
geoipCityLocation=
geoipAreaLocation=
geoipDmaLocation=
geoipOrgLocation=
geoipIspLocation=
geoipNetspeedLocation=
showUnavailable=
[audit]
enabled=1
[marketplace]
enabled=1
cacheTime="-1"
idHost="id.openx.net"
bidHost="bid.openx.net"
defaultEnabled=1
defaultCPM="0.5000"
