# CMRF (Proof-of-Concept)
CMRF is a CiviCRM remote integration toolkit. This repository is a **Drupal module** wrapping the core.

# Install
Use composer to install the abstract core library:

 1. ``> cd cmrf_core``
 1. ``composer install``

# Setup

Since there is no UI yet, you'll have to add the CiviCRM REST API credentials with a SQL command in the Drupal DB. They are stored as the default 'connection profile'.

``
INSERT INTO variable (name,value) VALUES ('cmrf_core_connection_profiles','a:1:{s:7:"default";a:3:{s:3:"url";s:62:"https://civi.my.site/sites/all/modules/civicrm/extern/rest.php";s:7:"api_key";s:12:"XXXXXXXXXXXX";s:8:"site_key";s:32:"XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX";}}');
``

This is a serialised PHP object, so you'll have to adjust the length prefixes if your string lenght differs.

# Usage

Examples:
 * https://github.com/systopia/ica_event_cmrf_connector/blob/master/ica_event_cmrf_connector.module#L21-L40
