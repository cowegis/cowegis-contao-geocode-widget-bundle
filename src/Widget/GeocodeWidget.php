<?php

declare(strict_types=1);

namespace Cowegis\Bundle\ContaoGeocodeWidget\Widget;

use Contao\BackendTemplate;
use Contao\StringUtil;
use Contao\Widget;
use Override;
use Symfony\Contracts\Translation\TranslatorInterface;

use function array_map;
use function assert;
use function intval;
use function is_array;
use function preg_match;

/**
 * @property int             $size
 * @property bool            $multiple
 * @property string          $radius
 * @property string|int|null $mapMaxZoom
 * @property string|int|null $mapMinZoom
 * @property string|int|null $mapDefaultZoom
 * @property array|null      $mapCenter
 * @psalm-suppress PropertyNotSetInConstructor
 * @psalm-suppress ClassMustBeFinal
 */
class GeocodeWidget extends Widget
{
    /**
     * Submit user input.
     *
     * @var bool
     */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
    protected $blnSubmitInput = true;

    /**
     * Add a for attribute.
     *
     * @var bool
     */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
    protected $blnForAttribute = true;

    /**
     * Template.
     *
     * @var string
     */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
    protected $strTemplate = 'be_widget';

    /**
     * Template name.
     */
    protected string $widgetTemplate = 'be_widget_cowegis_geocode';

    /** {@inheritDoc} */
    #[Override]
    protected function validator($varInput)
    {
        $varInput = parent::validator($varInput);

        if (! $varInput) {
            return $varInput;
        }

        if (is_array($varInput)) {
            foreach ($varInput as $key => $val) {
                $varInput[$key] = $this->validator($val);
            }

            return $varInput;
        }

        // See: http://stackoverflow.com/a/18690202
        if (
            preg_match(
                '#^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)(,[-+]?\d+)?$#',
                $varInput,
            ) !== 1
        ) {
            $this->addError($this->translate('ERR.cowegisInvalidCoordinate', [$varInput], 'contao_default'));
        }

        return $varInput;
    }

    #[Override]
    public function generate(): string
    {
        $wrapperClass = 'wizard';

        if (! $this->multiple || ! $this->size) {
            $this->size = 1;
        } else {
            $wrapperClass .= ' wizard_' . $this->size;
        }

        if (! is_array($this->value)) {
            $this->value = [$this->value];
        }

        $buffer = '';

        for ($index = 0; $index < $this->size; $index++) {
            $template = new BackendTemplate($this->widgetTemplate);
            $template->setData(
                [
                    'wrapperClass' => $wrapperClass,
                    'widget'       => $this,
                    'value'        => StringUtil::specialchars($this->value[$index]),
                    'class'        => $this->strClass ? ' ' . $this->strClass : '',
                    'id'           => $this->strId . ($this->size > 1 ? '_' . $index : ''),
                    'name'         => $this->strName . ($this->size > 1 ? '[]' : ''),
                    'attributes'   => $this->getAttributes(),
                    'wizard'       => $this->wizard,
                    'label'        => $this->strLabel,
                    'radius'       => $this->buildRadiusOptions(),
                    'geocode'      => $this->buildGeocodeOptions(),
                    'mapOptions'   => $this->buildMapOptions(),
                    'urlTemplate'  => self::getContainer()->getParameter('cowegis_contao_geocode_widget.url_template'),
                ],
            );

            $buffer .= $template->parse();
        }

        return $buffer;
    }

    /**
     * Build the geocode options.
     *
     * @return array<string,string|int>
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function buildGeocodeOptions(): array
    {
        $options['urlTemplate'] = null;

        if ('' !== ($urlTemplate = self::getContainer()->getParameter('cowegis_contao_geocode_widget.url_template'))) {
            $options['urlTemplate'] = $urlTemplate;
        }

        if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->name]['eval'])) {
            $config = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->name]['eval'];

            if ('' === ($options['urlTemplate'] ?? '')) {
                $options['urlTemplate'] = $config['url_template'] ?? '';
            }

            $options['attribution']    = $config['attribution'] ?? '';
            $options['queryPattern']   = $config['query_pattern'] ?? '';
            $options['queryWidgetIds'] = $config['query_widget_ids'] ?? [];

            return $options;
        }

        return $options;
    }

    /**
     * Build the radius options.
     *
     * @return array<string,string|int>|null
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function buildRadiusOptions(): array|null
    {
        if (! $this->radius || ! isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->radius])) {
            return null;
        }

        $options = [
            'element' => 'ctrl_' . $this->radius,
            'min'     => 1,
            'max'     => 0,
            'default' => 0,
            'steps'   => 0,
        ];

        if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->radius]['eval'])) {
            $config = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->radius]['eval'];

            $options['min']     = max(1, (int) ($config['minval'] ?? $options['min']));
            $options['max']     = (int) ($config['maxval'] ?? $options['max']);
            $options['default'] = (int) ($config['default'] ?? $options['default']);
            $options['steps']   = (int) ($config['steps'] ?? $options['steps']);
        }

        return $options;
    }

    /** @return array<string, mixed> */
    private function buildMapOptions(): array
    {
        $options = [
            'maxZoom' => 18,
            'minZoom' => 2,
            'center' => [0,0],
            'zoom' => 2,
        ];

        if ($this->mapMaxZoom > 0) {
            $options['maxZoom'] = (int) $this->mapMaxZoom;
        }

        if ($this->mapMinZoom > 0) {
            $options['minZoom'] = (int) $this->mapMinZoom;
        }

        if ($this->mapDefaultZoom > 0) {
            $options['zoom'] = (int) $this->mapDefaultZoom;
        }

        if (is_array($this->mapCenter)) {
            $options['center'] = array_map(intval(...), $this->mapCenter);
        }

        return $options;
    }

    /** @param array<array-key, string|int> $parameters */
    private function translate(string $key, array $parameters = [], string|null $domain = null): string
    {
        $translator = self::getContainer()->get('translator');
        assert($translator instanceof TranslatorInterface);

        return $translator->trans($key, $parameters, $domain);
    }
}
