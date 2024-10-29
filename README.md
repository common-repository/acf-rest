# Acf rest select plugin

### Description

This plugin adds a new type of select box to ACF. The plugin provides an input for an endpoint from which the necessary
options to populate the select are retrieved. It provides API authentication via username and password for the connection between Wordpress and the magento API. There is validation about whether the entered string is a valid url, as well as 
whether the response is in valid JSON. After successful retrieval of the options a select box gets populated, from which the desired default option for the resulting select can be set.

### Important note
The **response** of the request to the endpoint has to adhere to a *very strict** format in order for the plugin to be able
to get the options from it. The important part id that the returned JSON object needs to contain a property with the
name **entry** that is an array, whose elements each contain properties with the names **value** and **identifier**, where
**value** corresponds to the resulting value of the option and **identifier** to the resulting label of that option.

*Example valid response*
```
{
   "totalResults": 2,
   "itemsPerPage": 1,
   "startIndex": 0,
   "entry": [
       {
           "entity_id": 1,
           "identifier": "",
           "value": ""
       },
       {
           "entity_id": 2,
           "identifier": "Waranty",
           "value": "Waranty"
       }
   ]
}
```

### Compatibility

This ACF field type is compatible with:
* ACF 5

### Installation

1. Copy the `acf-rest` folder into your `wp-content/plugins` folder
2. Activate the plugin via the plugins admin page

### Usage

1. Create a new field via ACF and select the Rest type.
2. Select the endpoint type (Magento or other).
3. Enter a valid url for the endpoint with the desired options.
4. Set if authentication is required for the connection.
5. Enter username and password for the API to authenticate (optional).
6. From the "Select default" select box that gets populated, select the option you would like as the default option.
7. Click on Update.