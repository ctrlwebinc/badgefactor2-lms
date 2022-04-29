# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

- Added: badgr provider now has an update assertion call
- Added: badgr update_assertion command
- Modified: badgr add_assertion command now accepts all parameters supported by provider
- Modified: approver list now includes admins

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

