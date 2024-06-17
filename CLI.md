# Command line utilities

Badge Factor 2 includes some command line utilities.

## Available commands

Commands available in the _badgr_ command space:

```bash
add_user firstname lastname email
check_user_verified slug
get_user_badgr_info user_id
list_issuers
add_issuer name email url description
update_issuer issuer_slug name email url [description]
get_issuer_by_slug slug
delete_issuer slug
list_badge_classes
list_badge_classes_by_issuer issuer_slug
add_badge_class name issuer_slug description image_filename
get_badge_class_by_slug slug
update_badge_class badge_class_slug name description [image_filename]
delete_badge_class slug
add_assertion issuer_slug badge_class_slug recipient_identity
list_assertions_by_issuer issuer_slug
list_assertions_by_badge_class badge_class_slug
get_assertion_by_slug slug
revoke_assertion slug reason
check_if_user_has_badgr_verfied_email
```

Add user example:

```bash
wp badgr add_user Suzy Anderson suzy.anderson@example.net
```

Commands interact with the connected Badgr server using the admin credentials configured for Badge Factor 2 and as such should be considered super user commands.
