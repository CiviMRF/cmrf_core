# Version 2.0.8

* Fixed issue with tokens.

# Version 2.0.7

* Cached the views definition see https://www.drupal.org/project/cmrf_core/issues/3403976 .

# Version 1.4

* added CHANGELOG.md file
* Cron deletes all calls after a number of days (configure this on the profile)
* Cron deletes the calls that are DONE
* Added a delete option to the CRMR Calls report
* Drupal 9 compatibility - replace deprecated \Drupal::url
* documented composer only installation in the README.md
* made local autoload conditional, enabling using the global autoload
* remove civimrf/cmrf_abstract_core repository from composer.json file. It can be found now on packagist.
* Drupal 9 compatibility - replaced deprecated function dateformatter
* Drupal 9 compatibility - remove unnecessary from table name
* Drupal 9 compatibility - add config_export annotations
