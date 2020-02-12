# Installation

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

Edit apps/mainsite/settings.py and specify the login url:
```bash
LOGIN_URL = '/staff/login/'
```

Edit apps/mainsite/settings_local.py to add your Badge Factor 2 host to the allowed hosts:
```bash
ALLOWED_HOSTS = ['my-badge-factor-2.example.net']
```

Login using your super user account. Add an application under DJANGO OAUTH TOOLKIT > Applications and configure as follows:
- **Redirect uris:** Add your Badge Factor 2 url with the path /wp-admin/admin.php . E.g. https:://my-badge-factor-2.example.net/wp-admin/admin.php
- **Client type:** Public
- **Authorization grant type:** Choose *Authorization code*
- **Name:** Enter your site's friendly name. This name is displayed to users when they are asked to grant access to their account.
- **Allowed scopes:** Enter *rw:issuer rw:backpack rw:profile*

Make a note of client id and client secret and save the application.

Add the application settings to your Badge Factor 2 installation under Badge Factor 2 > Badgr Server. Once configured, you'll be redirected to your Badgr instance to login and grant permissions to Badge Factor 2.

When you return to Badge Factor 2, you should see the Server Status as *Active*.

### Badge Factor 2 and Badgr on the same server

If your Badge Factor 2 instance and your Badgr instance are running on the same server, you'll need to make some adjustments to your settings.

Make sure that your oauth client redirect uris include a version prefixed with 127.0.0.1 . E.g. https:://my-badge-factor-2.example.net/wp-admin/admin.php *and* http:://127.0.0.1/wp-admin/admin.php .

In the Badge Factor 2 Badgr Server Settings, make sure to fill out the *Internal URL* . E.g. http://127.0.0.1:8000 .

### Dockerized installations considerations

If you're using Docker either for your Badge Factor 2 instance or for your Badgr instance or both, you'll need to specify the *Internal URL* accordingly.

The *Internal URL* should be set to whatever path your Badge Factor 2 instance needs to follow to connect to your Badgr instance. For example, if your Badge factor 2 instance is dockerized and your Badgr server is running directly on your host, you would probably use *http://host.docker.internal:8000* as your Internal URL. If both your Badge Factor 2 and your Badgr instances are dockerized, you'll probaby use the badgr container name for your *Inernal URL* as in http://badgr:8000 .

Refer to Docker's networking configuration documentation for help.
https://docs.docker.com/v17.09/engine/userguide/networking/
