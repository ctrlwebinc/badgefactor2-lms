# Installation

## Wordpress Configuration

BadgeFactor2 requires the use of [permalinks](https://wordpress.org/support/article/using-permalinks/).

BadgeFactor2 handles passwords and these passwords are encrypted at rest.

Sensitive encryption information is kept out of the wordpress database and is instead kept in wp-config.php .

You should add the following entries in your wp-config.php file before you start using BadgeFactor2 taking care of using your own values for *key* and *iv*:

```
define( 'BF2_ENCRYPTION_ALGORITHM',  'AES-256-CBC' );
define( 'BF2_SECRET_KEY',            'replace with your own unique secret key' );
define( 'BF2_SECRET_IV',             'replace with your own unique secret iv ' );
```

## Setting up a local badgr-server instance

With these instructions, you'll have a local Badgr host to connect to.

You'll need to be familiar with:

- git
- configuring a linux environment
- installing Python packages

### Install badgr-server

Start with the [Badgr Server Instllation Instructions](https://github.com/concentricsky/badgr-server) themselves . Follow the instructions up until and including [Migrate databases, build front-end components](https://github.com/concentricsky/badgr-server#migrate-databases-build-front-end-components).

Badge Factor 2 acts as the ui for your Badgr host; it isn't necessary to install badgr-ui in this configuration.

Browse to /staff and check your installation by logging in using your super user account.

### Configure the oauth client

Edit apps/mainsite/settings.py and specify the login url and Http origin:

```bash
LOGIN_URL = '/staff/login/'
HTTP_ORIGIN = 'http://127.0.0.1:8000'
```

Edit apps/mainsite/settings_local.py to add your Badge Factor 2 host to the allowed hosts, set the Http origin and provide an unsusbscribe secret key:

```bash
ALLOWED_HOSTS = ['my-badge-factor-2.example.net']
HTTP_ORIGIN = 'http://127.0.0.1:8000'
UNSUBSCRIBE_SECRET_KEY = '7GGGDKOT4H4O7QU4GPGZ7ERY9GPE2FKALAO81WYP'
```

Login using your super user account. Add a first application under DJANGO OAUTH TOOLKIT > Applications and configure as follows:

- **Redirect uris:** Add your Badge Factor 2 url with the path /bf2/auth . E.g. https:://my-badge-factor-2.example.net/bf2/auth
- **Client type:** Public
- **Authorization grant type:** Choose _Authorization code_
- **Name:** Enter your site's friendly name. This name is displayed to users when they are asked to grant access to their account.
- **Allowed scopes:** Enter _rw:issuer rw:backpack rw:profile rw:serverAdmin_

Make a note of client id and client secret and save the application.

Add a second application under DJANGO OAUTH TOOLKIT > Applications and configure as follows:

- **Client type:** Public
- **Authorization grant type:** Choose _Resource owner password-based_
- **Name:** Enter your site's friendly name and add Password to distinguish the name from the first client.
- **Allowed scopes:** Enter _rw:issuer rw:backpack rw:profile_

Under Home › Badgeuser › Terms versions, add a terms version.

Create a new badgr app under Home › Mainsite › Badgr apps. If BadgeFactor2 is running at https:://my-badge-factor-2.example.net, use the following values:

- CORS: ensure this setting matches the domain on which you are running BadgeFactor2, including the port if other than the standard HTTP or HTTPS ports. `https:://my-badge-factor-2.example.net`
- Oauth authorization redirect: `https:://my-badge-factor-2.example.net/bf2/auth`
- User auth code exchange: should be checked
- Oauth application: choose the first one created previously
- Is default: should be checked
- Signup redirect: `https:://my-badge-factor-2.example.net/bf2/signup`
- Email confirmation redirect: `https:://my-badge-factor-2.example.net/bf2/emailConfirm`
- Forgot password redirect: `https:://my-badge-factor-2.example.net/forgotPassword`
- UI login redirect: `https:://my-badge-factor-2.example.net/bf2/loginRedirect`
- UI signup success redirect: `https:://my-badge-factor-2.example.net/bf2/signup`
- UI signup faillure: `https:://my-badge-factor-2.example.net/bf2/signup`
- UI connect success redirect: `https:://my-badge-factor-2.example.net/signup`
- Public pages redirect: `https:://my-badge-factor-2.example.net/public/`

Add the application settings to your Badge Factor 2 installation under Badge Factor 2 > Badgr Server. Once configured, you'll be redirected to your Badgr instance to login and grant permissions to Badge Factor 2.

When you return to Badge Factor 2, you should see the Server Status as _Active_.

### Badge Factor 2 and Badgr on the same server

If your Badge Factor 2 instance and your Badgr instance are running on the same server, you'll need to make some adjustments to your settings.

Make sure that your oauth client redirect uris include a version prefixed with 127.0.0.1 . E.g. https:://my-badge-factor-2.example.net/wp-admin/admin.php _and_ http:://127.0.0.1/wp-admin/admin.php .

In the Badge Factor 2 Badgr Server Settings, make sure to fill out the _Internal URL_ . E.g. [http://127.0.0.1:8000](http://127.0.0.1:8000) .

### Dockerized installations considerations

If you're using Docker either for your Badge Factor 2 instance or for your Badgr instance or both, you'll need to specify the _Internal URL_ accordingly.

The _Internal URL_ should be set to whatever path your Badge Factor 2 instance needs to follow to connect to your Badgr instance. For example, if your Badge factor 2 instance is dockerized and your Badgr server is running directly on your host, you would probably use [http://host.docker.internal:8000](http://host.docker.internal:8000) as your Internal URL. If both your Badge Factor 2 and your Badgr instances are dockerized, you'll probaby use the badgr container name for your _Inernal URL_ as in [http://badgr:8000](http://badgr:8000).

Refer to Docker's networking configuration documentation for help.
[https://docs.docker.com/v17.09/engine/userguide/networking/](https://docs.docker.com/v17.09/engine/userguide/networking/)

### Necessary adjustments to Badgr server

For the moment badgr server needs some adjustments to be fully decoupled from its front-end and to operate with BF2. Here are the required changes based on release/jamiroquai :

```bash
pip install https://github.com/ctrlwebinc/badgr-server-bf2/archive/master.zip
```

apps/badgeuser/api.py

```patch
     v2_serializer_class = BadgeUserSerializerV2
     permission_classes = (permissions.AllowAny, BadgrOAuthTokenHasScope)
     valid_scopes = {
-        "post": ["*"],
+        "post": ["*","rw:profile"],
         "get": ["r:profile", "rw:profile"],
         "put": ["rw:profile"],
     }
```

apps/entity/api.py

```patch

 import badgrlog
 from mainsite.pagination import BadgrCursorPagination
+from bf2.pagination import BadgrLimitOffsetPagination


 class BaseEntityView(APIView):
```

```patch
     min_per_page = 1
     max_per_page = 500
     default_per_page = None  # dont paginate by default
-    per_page_query_parameter_name = 'num'
+    per_page_query_parameter_name = 'limit'
     ordering = "-created_at"

     def get_ordering(self):
```

```patch

         # only paginate on request
         if per_page:
-            self.paginator = BadgrCursorPagination(ordering=self.get_ordering(), page_size=per_page)
+            # self.paginator = BadgrCursorPagination(ordering=self.get_ordering(), page_size=per_page)
+            self.paginator = BadgrLimitOffsetPagination(ordering=self.get_ordering(), page_size=per_page)
             page = self.paginator.paginate_queryset(queryset, request=request)
         else:
             page = list(queryset)
```

apps/issuer/api.py

```patch
     apispec_delete_operation, apispec_list_operation, apispec_post_operation
 from mainsite.permissions import AuthenticatedWithVerifiedIdentifier, IsServerAdmin
 from mainsite.serializers import CursorPaginatedListSerializer
+from bf2.serializers import LimitOffsetPaginatedListSerializer
 from mainsite.models import AccessTokenProxy

 logger = badgrlog.BadgrLogger()
```

```patch
         return Response(serializer.data)


-class PaginatedAssertionsSinceSerializer(CursorPaginatedListSerializer):
+class PaginatedAssertionsSinceSerializer(LimitOffsetPaginatedListSerializer):
     child = BadgeInstanceSerializerV2()

     def __init__(self, *args, **kwargs):
```

```patch
         return Response(serializer.data)


-class PaginatedBadgeClassesSinceSerializer(CursorPaginatedListSerializer):
+class PaginatedBadgeClassesSinceSerializer(LimitOffsetPaginatedListSerializer):
     child = BadgeClassSerializerV2()

     def __init__(self, *args, **kwargs):
```

```patch
         return Response(serializer.data)


-class PaginatedIssuersSinceSerializer(CursorPaginatedListSerializer):
+class PaginatedIssuersSinceSerializer(LimitOffsetPaginatedListSerializer):
     child = IssuerSerializerV2()

     def __init__(self, *args, **kwargs):
```

apps/mainsite/account_adapter.py

```patch
             if source:
                 query_params['source'] = source

-            signup = request.query_params.get('signup', None)
+            #signup = request.query_params.get('signup', None)
+            signup = False
             if signup:
                 query_params['signup'] = 'true'
                 return set_url_query_params(badgr_app.get_path('/auth/welcome'), **query_params)

```

apps/mainsite/settings.py

```patch
      'DEFAULT_VERSION': 'v1',
     'ALLOWED_VERSIONS': ['v1','v2'],
     'EXCEPTION_HANDLER': 'entity.views.exception_handler',
-    'PAGE_SIZE': 100,
+    'DEFAULT_PAGINATION_CLASS': 'rest_framework.pagination.LimitOffsetPagination',
+    'PAGE_SIZE': 10,
 }


```

apps/mainsite/urls.py

```patch
     url(r'^apple-app-site-association', AppleAppSiteAssociation.as_view(), name="apple-app-site-association"),

     # OAuth2 provider URLs
-    url(r'^o/authorize/?$', AuthorizationApiView.as_view(), name='oauth2_api_authorize'),
+    #url(r'^o/authorize/?$', AuthorizationApiView.as_view(), name='oauth2_api_authorize'),
     url(r'^o/token/?$', TokenView.as_view(), name='oauth2_provider_token'),
     url(r'^o/code/?$', AuthCodeExchange.as_view(), name='oauth2_code_exchange'),
     url(r'^o/', include(oauth2_provider_base_urlpatterns, namespace='oauth2_provider')),
```

```patch
     url(r'^v2/externaltools/', include('externaltools.v2_api_urls'), kwargs={'version': 'v2'}),


+    # bf2 API additions
+    url(r'^v2/', include('bf2.urls')),
+
 ]

 # Test URLs to allow you to see these pages while DEBUG is True
```


