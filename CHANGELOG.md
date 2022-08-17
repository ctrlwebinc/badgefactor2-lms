# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

- Corrected typo orthographe "Partager sur les réseaux sociaux"

## 1.6.4

- Added: WP-CLI command to update badge request forms to require user login.

## 1.6.3

- Added: get_user method to BadgrProvider class.
- Fixed: coding style.

## 1.6.2

- Modified: updated plugin version to 1.6.2 for cache busting

## 1.6.1

- Modified: moved assertion privacy popup handling to badgefactor2 privacy.js
- Modified: moved assertion privacy popup styling to badgefactory2 public.css
- Added: new entries in the language file

## 1.6.0

- Modified: Updated privacy.js to add/remove privacy flag class when toggling visibility
- Added: additional classes to sharing_classes in $social_share_data array when there is an assertion privacy
- Added: shareable URL to $social_share_data array
- Fixed: checking if $badgepage is false in Assertion_Controller class when looping on assertions
- Added: social share backoffice configuration


## 1.5.2

- Fixed: Checking whether admin is logged in before approving/rejecting/requesting-a-modification on a badge request.

## 1.5.1

- Updated: CMB2 updated to 2.10.1 to fix php 8.0 deprecation

Note: Social Shares in this version is only partially complete:
configuration in back office presently has no effect.

## 1.5.0-pre-release

- Added: Intervention Image to phar
- Modified: renamed phar to reflect contents
- Added: badgr provider now has an update assertion call
- Added: badgr update_assertion command
- Modified: badgr add_assertion command now accepts all parameters supported by provider
- Modified: approver list now includes admins
- Fixed: php8 compatibility on function parameters order (BadgeFactor2\Helpers\Migration::get_form_id_by_badge_post_id(), BadgeFactor2\Shortcodes\Badges::list())
- Changed: Set Sans-serif as email default font
- Added: filter on revoked assertion on single badge page
- Added: new entry in language file
- Added: flag of a performed action to the Badgr_Entity interface
- Fixed: backoffice badge class update now works

## 1.4.0

- Added: loginRedirect from Badgr now redirects to home page
- Corrected: evidence url now included when adding an assertion through badge requests


## 1.3.3

- Fix: assertion array when the user has no assertions
- Added: assertion privacy control added

## 1.3.2

- Fix: add description to issuer url field

## 1.3.1

- Fix: tooltip in template

## 1.3.0

- Added: now possible to manually trigger admin to badgr auth

## 1.2.4

- Corrected: issuer url field in admin now of type url

## 1.2.3

- Corrected: Remove sortable trait's side-effects in other plugins namely Registration

## 1.2.2

- Corrected: issuer displayed in badge page now the correct one, and field is disabled.
- Corrected: badge-page and badge-request capabilities fix

## 1.2.1

- Corrected: badge requests for manually emitted badges now includes assertion meta

## [1.2.0]

- Added: email from fields for approval feedback
- Changed: badgr password saved ahead of user creation
- Added: optionnaly include an image when creating or updating issuers via provider or cli


## 1.1.2

- Fixed: free courses
- Fixed: duplicated date metas on approvals
- Fixed: empty description when creating issuers generates front-end message

## [1.1.0-rc]

### Added

- LICENSE, README and CHANGELOG files
- Internal dependencies: CMB2, thephpleague/oauth2-client, guzzlehttp/guzzle;
- Other dependancies: BuddyPress plugin
- Badgr Client & Server settings page
- Custom Post Types: Issuer, Badge, Assertion
- WordPress CLI command
- Compatible WP 5.7.x
- Uses a modified version of Badgr as an Open Badges compliant badge store
- Admins can create, modify and delete issuers, badge classes
- Admins can issue and revoke badges 
- Users can store badges in their individual backpack
- Limitation: Move badge class from one issuer to another not supported
- New WP users get automatically created as a Badgr user with access to a backpack
- Allows suppression of WP signup email
- Badgr admin account linked to BF2 via code auth
- Provides upstream workflow for badge issuance via Badge Requests
- Limitation: no image transferred to Badgr for issuers
- Supports svg, png, gif and jpeg image transfers to Badgr

