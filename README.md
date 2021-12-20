# ACF Tb Places Field

Allows selection and geocoding of an address using Nominatim search API(see https://nominatim.org/release-docs/latest/), and adresse.data.gouv.fr API (see https://adresse.data.gouv.fr/api-doc/adresse, official french administration address data API) for french postcodes when necessary.

-----------------------

### Description

EXTENDED_DESCRIPTION

### Compatibility

This ACF field type is compatible with:
* ACF 5

### Installation

1. Copy the `acf-tb-places` folder into your `wp-content/plugins` folder
2. Activate the Tb Places plugin via the plugins admin page
3. Create a new field via ACF and select the Tb Places type
4. Please refer to the description for more info regarding the field type settings

### Changelog
Please see `readme.txt` for changelog

Inspired by ACF Algolia Places Field previously found on https://github.com/etaminstudio/acf-field-algolia-places (see the original ACF Algolia Places Field readme file), deprecated since Algolia places will no longer be maintained on mai 2022.

### Special case : Convert from _ACF Algolia Places_ Field to _ACF Tb places_

Go to your ACF **field groups**, select successively each field group using _Algolia places_ **field type** and switch to ***Tb places*** then update the field group.
