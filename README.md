# CiviMRF (Proof-of-Concept)
CiviMRF is a CiviCRM remote integration toolkit. This repository is a **Drupal module** wrapping the core.

# Install

You have two methods to install this module. Both need composer.

## Download installation method

1. Download and untar this module in the `<drupdalroot>/web/modules/contrib` directory.
1. ``> cd cmrf_core``
1. ``composer install``

## Composer only method
1. Add this repository to the composer configuration with
`composer config repositories.cmrf_core vcs https://github.com/CiviMRF/cmrf_core.git`
1. Now use composer for the install `composer require drupal/cmrf_core`.

# Setup

Configure your default connection profile at admin/config/cmrf_profile.
If you need to, you can create multiple profiles there.

# Usage

Have a look at the cmrf_example module. The API Calls itself are wrapped
in a class CiviClient to do the heavy lifting. A Unit Test demonstrates how
to use the class.
