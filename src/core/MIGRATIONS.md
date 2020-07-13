# Migrations
If you're switching to Badge Factor 2 from another badge system, the process of requiring each one of your users to respond to an activation email might be problematic in terms of user experience.

Here are steps that you can take to bypass this process on behalf of your users.

-Temporarily disable email backend on badgr
-Temporarily disable caching on badgr
-Mark users for migration with WP command
-Launch the migration with the WP command
-Let the badgr user creation complete
-Update verified and primary in the badgr DB
-Re-enable email and caching on badgr

### Temporarily disable email backend on badgr
In app/mainsite/settings_local.py
```
EMAIL_BACKEND = 'django.core.mail.backends.dummy.EmailBackend'

```

### Temporarily disable caching on badgr
In app/mainsite/settings_local.py
```
CACHES = {
    'default': {
        'BACKEND': 'django.core.cache.backends.dummy.DummyCache',
        'LOCATION': '',
        'TIMEOUT': 300,
        'KEY_PREFIX': '',
        'VERSION': 1,
    }
}
```

### Mark users for migration

To mark your users for migration, you should run this WP command:
```
wp badgr mark_existing_users_for_migration
```

### Launch the migration

To launch the migration of your users, you can run this WP command:
```
wp --allow-root badgr migrate_users_and_mark_as_verified
```

Users that can't be created in Badgr will have a 'failed_to_create' badgr_user_state once this command has run.

### Update verified and primary in the badgr DB

To mark your new users as verified, you can run a query similar to this on your Badgr database:

```sql
UPDATE `account_emailaddress`
SET `verified` = 1,
`primary` = 1
WHERE `id` > 66;
```

The WHERE constraint is optional (and should be adjusted to your particuar dataset) but is there to prevent you from
from approving users that are in a legitimate process of having their email verified. Just note the last id in your account_emailaddress table before you trigger your migration and use that id as the id condition: you'll then be only approving automatically user added after your migration.

### Re-enable email and caching on badgr

Re-enable caching and email to return to a behavior where the system sends an email confirmation to each new user that registers on the site.

## Useful queries for migration preflight

Here are some queries you might run on your WordPress database to identify potential migration issues.

Find users with a lengthy first_name:
```sql
SELECT m1.user_id, m1.meta_value FROM `wp_usermeta` AS m1
WHERE m1.meta_key = 'first_name' AND length(m1.meta_value) > 30;
```

Find users with an email address that includes a dot just before @:
```sql
SELECT user_email, `user_email` NOT REGEXP '\\.@' as email_valid FROM `wp_users` HAVING email_valid = 0;
```

Find users with an email address ending with a single-letter top-level domain:
```sql
SELECT user_email, `user_email` REGEXP '\\..$' as email_invalid FROM `wp_users` HAVING email_invalid = 1;
```

Find users without an email address:
```sql
SELECT user_email, user_login FROM `wp_users` WHERE LENGTH(`user_email`) < 1;
```
