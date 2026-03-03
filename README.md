Cowegis geocode widget
======================

[![Build Status](https://img.shields.io/github/actions/workflow/status/cowegis/cowegis-contao-geocode-widget-bundle/diagnostics.yml?branch=master&logo=githubactions&logoColor=%23fff&style=flat-squar)](https://github.com/cowegis/cowegis-contao-geocode-widget-bundle/actions)
[![Version](http://img.shields.io/packagist/v/netzmacht/contao-leaflet-geocode-widget.svg?style=flat-square)](http://packagist.org/packages/cowegis/cowegis-contao-geocode-widget-bundle)
[![License](http://img.shields.io/packagist/l/netzmacht/contao-leaflet-geocode-widget.svg?style=flat-square)](http://packagist.org/packages/cowegis/cowegis-contao-geocode-widget-bundle)
[![Downloads](http://img.shields.io/packagist/dt/netzmacht/contao-leaflet-geocode-widget.svg?style=flat-square)](http://packagist.org/packages/cowegis/cowegis-contao-geocode-widget-bundle)

This extension provides a widget to pick coordinates from a map. It uses the leaflet framework.

Requirements
------------

 - Contao ^4.13||^5.0
 - PHP ^8.2


Install
-------

### 1. Install using composer

```bash
php composer.phar require cowegis/cowegis-contao-geocode-widget-bundle

```

### 2. Use the widget

#### Coordinates only

The widget is used to add a map icon which opens a map in a pop-up. You can enter an address in the search field and
press Enter to resolve it. Either a list of suggested addresses is displayed or a marker is placed on the map.
The marker can still be positioned using the mouse. Click on the ‘Apply’ button to apply the geocoordinates.

```php
$GLOBALS['TL_DCA']['tl_example']['fields']['coordinates'] = [
    'label'     => ['Coordinates', 'Enter the coordinates - comma separated as \'latitude,longitude\'.'],
    'inputType' => 'cowegis_geocode',
    'eval'      => [
        'tl_class' => 'w50',
    ],
    'sql' => 'varchar(255) NOT NULL default \'\''
];
```
However, if you already have input fields for the address, such as postcode, town and street, you don't want to have
to enter this information again in the search field in the pop-up.

With the appropriate information in the DCA, you can automatically transfer this information to the search field – here
is an example for fields `street`, `zip` and `city` and country fixed as "Deutschland":

```php
$GLOBALS['TL_DCA']['tl_example']['fields']['coordinates'] = [
    'label'     => ['Coordinates', 'Enter the coordinates - comma separated as \'latitude,longitude\'.'],
    'inputType' => 'cowegis_geocode',
    'eval'      => [
        'query_widget_ids' => ['ctrl_street', 'ctrl_zip', 'ctrl_city'],
        'query_pattern'    => '#ctrl_street# #ctrl_zip# #ctrl_city#, Deutschland',
        'url_template'     => 'https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png',
        'attribution'      => 'Tiles style by <a href="https://www.hotosm.org/" target="_blank">Humanitarian OpenStreetMap Team</a> hosted by <a href="https://openstreetmap.fr/" target="_blank">OpenStreetMap France</a>'.
        'tl_class'         => 'w50',
    ],
    'sql' => 'varchar(255) NOT NULL default \'\''
];
```

The information is as follows:

- `query_widget_ids`: List of field IDs from which the information is to be taken
- `query_pattern`: Pattern as it appears in the search input in the pop-up - the field IDs with `#<id>#`
- `url_template`: Template for the map
- `attribution`: Note on the map layer

#### Coordinates and radius

To pick the radius in meters as well, you have to configure the `eval.radius` option for the related radius field.
The radius field should be a simple text input. The `default`, `minval` and `maxval` flags are passed to the geocode
widget so that only radius in that boundary can be chosen.

```php
$GLOBALS['TL_DCA']['tl_page']['fields']['coordinates'] = [
    'label'     => ['Coordinates', 'Enter the coordinates - comma separated as \'latitude,longitude\'.'],
    'inputType' => 'cowegis_geocode',
    'eval'      => [
        'tl_class' => 'w50',
        'radius'   => 'radius'
    ],
    'sql' => 'varchar(255) NOT NULL default \'\''
];

$GLOBALS['TL_DCA']['tl_page']['fields']['radius'] = [
    'label'     => ['Radius', 'Specification of the radius in metres.'],
    'inputType' => 'cowegis_radius', // Optional, you can use a text widget as well
    'eval'      => [
        'default'  => 500,
        'minval'   => 100,
        'maxval'   => 5000,
        'steps'    => 100, // Round value to the closest 100 m.
        'tl_class' => 'w50',
    ],
    'sql' => 'varchar(255) NOT NULL default \'\''
];
```

If you want to add a wizard icon to the radius field as well, you only have to reference the coordinate field.

```php
$GLOBALS['TL_DCA']['tl_page']['fields']['radius'] = [
    'label'     => ['Radius', 'Specification of the radius in metres.'],
    'inputType' => 'cowegis_radius',
    'eval'      => [
        'rgxp'        => 'natural',
        'default'     => 500,
        'minval'      => 100,
        'maxval'      => 5000,
        'tl_class'    => 'w50 wizard',
        'coordinates' => 'coordinates'
    ],
    'sql' => 'varchar(255) NOT NULL default \'\''
];
```
