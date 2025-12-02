Cowegis geocode widget
======================

[![Build Status](https://img.shields.io/github/actions/workflow/status/cowegis/cowegis-contao-geocode-widget-bundle/diagnostics.yaml?branch=master&logo=githubactions&logoColor=%23fff&style=for-the-badge)](https://github.com/netzmacht/contao-leaflet-geocode-widget/actions)
[![Version](http://img.shields.io/packagist/v/netzmacht/contao-leaflet-geocode-widget.svg?style=flat-square)](http://packagist.org/packages/cowegis/cowegis-contao-geocode-widget-bundle)
[![License](http://img.shields.io/packagist/l/netzmacht/contao-leaflet-geocode-widget.svg?style=flat-square)](http://packagist.org/packages/cowegis/cowegis-contao-geocode-widget-bundle)
[![Downloads](http://img.shields.io/packagist/dt/netzmacht/contao-leaflet-geocode-widget.svg?style=flat-square)](http://packagist.org/packages/cowegis/cowegis-contao-geocode-widget-bundle)

This extension provides a widget to pick coordinates from a map. It uses the leaflet framework.

Changlog
--------

See [CHANGELOG](CHANGELOG.md).

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
