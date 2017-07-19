# CMRF (Proof-of-Concept)
CMRF is a CiviCRM remote integration toolkit. This repository is a **Drupal module** wrapping the core.

# Install
Use composer to install the abstract core library:

 1. ``> cd cmrf_core``
 1. ``composer install``

# Setup

Configure your default connection profile at admin/config/cmrf_profile.
If you need to, you can create multiple profiles there.

# Usage

Have a look at the cmrf_example module. The API Calls itself are wrapped
in a class CiviClient to do the heavy lifting. A Unit Test demonstrates how
to use the class.
