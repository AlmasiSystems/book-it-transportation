=== Plugin Name ===
Contributors: bmarshall511
Donate link: http://www.benmarshall.me/limo-reservation-software/
Tags: reservations, transportation, limo, limousines, taxi, bookings
Requires at least: 3.0.1
Tested up to: 3.5.1
Stable tag: 1.0.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A reservation management system for your business enabling you to easily accept & manage reservations. A great limo reservation software solution.

== Installation ==

1. Upload `book-it-transportation` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. To include a reservation booking form in a blog post or page, use the `[bookit_reservation_form]` shortcode.

== Screenshots ==

See http://www.benmarshall.me/limo-reservation-software/

== Changelog ==

= 1.0.8 =
* Fixed bug where reservations couldn't be deleted (#1043)

= 1.0.7 =
* Fixed bug where confirmation codes get generated for other post types besides reservations.
* Fixed outsource email bug (#1040)

= 1.0.6 =
* Corrected the reservation shortcode form in the readme file
* Added the 'Powered by' link and option

= 1.0.5 =
* Resolved bug #1038

= 1.0.4 =
* Fixed publish bug when saving reservations using the 'Save Reservation' button
* Renamed all instances of bookittrans to bookit. WARNING: This will cause all reservations and related settings to be lost
* Optimized some code and resolved a few bugs
* Split out premium features
* Added a reservation failed redirect option
* Re-built more stable and reliable email system

= 1.0.3 =
* Fixed placeholder bug for titles in posts
* Updated code for to comply with WordPress standards
* Modified how Outsource Companies are selected
* Added a Company Email field to the Outsource Companies taxonomy
* Added the ability to send reservation emails to outsource companies
* Added the ability to change the email subject of reservations sent to outsource companies
* Added the ability to edit reservation confirmation emails
* Added the ability to add shortcodes to the email templates
* Added the ability to edit new reservation emails
* Added the ability to edit outsource reservation emails
* Added the ability to submit a bug or feature request
* Added Date Reserved column to reservation list
* Added a date format shortchode for reservation dates in email templates

= 1.0.2 =
* Added the ability to change the email subject of the new reservation booking emails
* Added the ability to change the email subject of the reservation confirmation emails
* Fixed bug in reservation email template
* Added the ability to update the default reservation status
* Added Book It! Reservation plugin details on settings page

= 1.0.01 =
* Fixed bug on plugin options page
* Added the plugin options page
* Added the ability to update the reservation received URL setting