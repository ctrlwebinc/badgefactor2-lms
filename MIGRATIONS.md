# Migrations

Here are some instructions to migrate users, issuers, badge classes and assertions into Badgr and to migrate
badges into bf2 badge pages and courses.

## Users

If you're switching to Badge Factor 2 from another badge system, the process of requiring each one of your users to respond to a Badgr activation email might be problematic in terms of user experience.

Here are steps that you can take to bypass this process on behalf of your users.

-Temporarily disable email backend on badgr
-Temporarily disable caching on badgr
-Mark users for migration with WP command
-Launch the migration with the WP command
-Let the badgr user creation complete
-Update verified and primary in the badgr DB
-Re-enable email and caching on badgr

The process of migrating users for Badgr is independent from migrating issuers, badge classes and assertions and can be done before or after those migrations.

### Temporarily disable email backend on badgr

In app/mainsite/settings_local.py

```bash
EMAIL_BACKEND = 'django.core.mail.backends.dummy.EmailBackend'

```

### Temporarily disable caching on badgr

In app/mainsite/settings_local.py

```bash
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

```bash
wp badgr mark_existing_users_for_migration
```

### Launch the migration

To launch the migration of your users, you can run this WP command:

```bash
wp badgr migrate_users_and_mark_as_verified
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

### Useful queries for user migration preflight

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

## Issuers

Issuers migrations is a one-step process where posts of type 'organisation' are created as issuers in Badgr.

To migrate issuers, run the command:

```bash
wp badgr migrate_issuers
```

By default, the command will migrate all posts of type 'organisation'. To limit to only published posts, you can add the --restrict-to-published flag:

```bash
wp badgr migrate_issuers --restrict-to-published
```

The command attempts to migrate any post of post type 'organisation' that doesn't have a 'badgr_issuer_slug' meta. A successfully migrated issuer will have a coresponding 'badgr_issuer_slug'.

Issuer migrations must be run before badge class ( and assertion ) migrations.

## Badge classes

Badge class migration in a one-step process where posts of post type 'badges' are created in Badgr as badge classes.

Issuers must be migrated before attempting to migrate badge classes and badge classes must be migrated before attemtpting to migrate assertions.

To migrate badge classes, run the command:

```bash
wp badgr migrate_badge_classes
```

Posts of type 'badges' without a 'badgr_badge_class_slug' meta will be migrated. Should any migration fail, those posts will be marked with a 'badgr_badge_class_failed' meta.

## Assertions

Assertion migration is a one-step process where posts of type 'submission' are created as assertions in Badgr with a coresponding approved badge request posts.

Badges classes ( and issuers ) must already be migrated before attempting to migrate assertions.

To migrate assertions, run the command:

```bash
wp badgr migrate_badge_assertions
```

Approved posts of type 'submission' without a 'badgr_assertion_slug' will be migrated. Failed assertion migrations are marked with a 'badgr_assertion_failed' meta.

## Badge pages

Badge Factor 2 uses a different set of post types to manage badges. Posts of post type 'badges' become badge pages.

To migrate badges into badge pages, make sure that the Badges classes migrations have alredy run successfully and then use the command:

```bash
wp bf2 create_badge_pages_from_badges
```

The command creates a new badge page for each badge that has a Badgr badge class slug and for which no badge page with the same slug already exists.

## Courses

Badge Factor 2 has a courses add-on. Part of the content of badges and related posts in Badge Factor (1) map to courses in Badge Factor 2.

To created courses from badges, make sure that the Badges classes migrations have alredy run successfully and then use the command:

```bash
wp bf2 create_courses_from_badges
```

## Linking badge pages and courses

Badge pages and courses are linked in BadgeFactor2

To link badge pages and course, mamke sure that badge pages and courses migrations have already run successfully and then use the command:

```bash
wp bf2 link_badge_pages_and_courses
```

## Removing links from content

BadgeFactor2 includes links to request badges as part of its templates. Since this function wasn't in badgefactor,
users added links manually. These links must be removed.

Because automated treatment of content can be tricky, namely because targetted content is often entered with variations,
this process happens in 2 parts: 
- first we highlight the text to be suppressed (yellow background)
- and then the content is suppressed.

```bash
wp bf2 mark_links_to_remove_from_courses
```

```bash
wp bf2 removed_marked_links_from_courses
```