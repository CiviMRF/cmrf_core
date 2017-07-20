# CMRF (Proof-of-Concept)
CMRF is a CiviCRM remote integration toolkit. This repository is a **Drupal module** wrapping the core.

# Install
Use composer to install the abstract core library:

 1. ``> cd cmrf_core``
 1. ``composer install``

# Setup

Configure your default connection profile at admin/config/civimrf/profiles.

# Usage

Examples:
 * https://github.com/systopia/ica_event_cmrf_connector/blob/master/ica_event_cmrf_connector.module#L21-L40
 
# To be documented:

1. Drupal permissions
1. How to configure a connection profile through the UI and through code 
1. How to use this with the drupal webform integration
1. Provided api's for use in other modules
1. Documentation of the options for a call: e.g.: cache, retry_count and retry_interval
1. Document the use of options for the civi api (sort, limit)
1. Document the different statuses of a call, INIT, RETRY, DONE, FAIL, etc..
1. Document that the drupal module has a trigger for failed calls to send an e-mail.

