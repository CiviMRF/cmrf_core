# CiviCRM Webform integration

This submodule pre-fills fields and sends webform submissions to CiviCRM.

## How it works

* First of all, you need to create a CiviMRF Profile
  under `/admin/config/cmrf_profile` which contains CiviCRM instance URL, site
  key and API key.
* Then create a connector which uses the profile created in above step and
  simply makes the profile available for `cmrf_webform` module

### Prefill default values into form fields

* Then under `/admin/config/cmrf_webform` click on `Webform default form values`
  and create a new handler. Fields are like below:
    1. Just a name
    2. The same connector you've created in previews step
    3. Select which webform
    4. The CiviCRM handler
    5. Machine name of the field you want to prefill
    6. Parameters if needed
    7. If the return of handler contains multiple values, chose which key you
       want

This is all, now your field should be pre-filled.

### Send form submissions to CiviCRM

Under `/admin/config/cmrf_webform` click on `Webform submission handler`

Add a handler:>

1. Give it a name
2. Select CiviCRM connector
3. Select the webform entity
4. Select whether you want to delete submission after successfully sent to
   CiviCRM or not
5. Whether you want to send the submission to CiviCRM immediately or later in
   the background
6. Fill in which entity of CiviCRM you want to use, this is
   usually `FormProcessor`
7. Fill in the handler/form processor