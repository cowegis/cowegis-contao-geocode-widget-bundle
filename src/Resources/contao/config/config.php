<?php

declare(strict_types=1);

use Cowegis\Bundle\ContaoGeocodeWidget\Widget\GeocodeWidget;
use Cowegis\Bundle\ContaoGeocodeWidget\Widget\RadiusWidget;

$GLOBALS['BE_FFL']['cowegis_geocode'] = GeocodeWidget::class;
$GLOBALS['BE_FFL']['cowegis_radius']  = RadiusWidget::class;
