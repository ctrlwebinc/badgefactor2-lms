# Badge Factor 2

## What is Badge Factor 2

Badge Factor 2 is a [WordPress](https://wordpress.org/) plugin which issues and manages [Open Badges](https://openbadges.org/) with [Badgr Server](https://github.com/concentricsky/badgr-server).

## Templates

Badge Factor 2 uses a custom templating engine which uses Controllers to define all the fields required to display the template, which are assigned to a global `$bf2_template` variable.

If templates have not been overriden in the theme, Badge Factor 2 falls back to the templates provided in its plugin or add-ons.  The template structure is as follows:

### Theme

- Including header and footer: `templates/{plugin}/{template_file}`
- Content template only: `templates/{plugin}/content/{template_file}`

### Plugins

- Including header and footer: `{plugin}/templates/{template_file}`
- Content template only: `{plugin}/templates/content/{template_file}`

### Available templates

#### Assertions (issued badges)

- `single-assertion.tpl.php`
- `content/single-assertion.tpl.php`

#### Badge Pages

- `archive-badge-page.tpl.php`
- `single-badge-page.tpl.php`
- `content/archive-badge-page.tpl.php`
- `content/single-badge-page.tpl.php`

#### Add-Ons

Some official Badge Factor 2 add-ons also provide templates in the same manner as the core plugin.

##### Certificates

Uses the templating engine, but returns a generated PDF, and therefore does not provide a template.

##### Courses

- `archive-course.tpl.php`
- `single-course.tpl.php`
- `content/archive-course.tpl.php`
- `content/single-course.tpl.php`

## Dependancies

Version 1.1.0 requires BuddyPress.

## A new version in the works

A new version of Badgefactor 2 is in the works to handle pathways.

The master-parcours and develop-parcours branches should be used to keep non-retroactively compatible changes out of master and develop.
