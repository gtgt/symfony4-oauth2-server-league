#!/bin/bash

curl --include --location \
--request GET \
-H "Cookie: XDEBUG_SESSION=PHPSTORM" \
"http://localhost:8088/api/authCode?client_id=client_id&scope=*&grant_type=authorization_code&response_type=code"
exit 0;
AUTH=$(echo "client_id:client_secret"|base64)
#-H "Authorization: OAuth $AUTH" \
curl --include --location \
--user "client_id:client_secret" \
--request POST  \
--data "scope=testscope&grant_type=authorization_code" \
-H "Cookie: XDEBUG_SESSION=PHPSTORM" \
"http://localhost:8088/api/accessToken"

