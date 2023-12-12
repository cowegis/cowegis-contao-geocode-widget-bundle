<?php

declare(strict_types=1);

namespace Cowegis\Bundle\ContaoGeocodeWidget\Widget;

use Contao\BackendTemplate;
use Contao\StringUtil;
use Contao\Widget;

use function is_array;
use function preg_match;
use function sprintf;

/**
 * @property int    $size
 * @property bool   $multiple
 * @property string $radius
 * @psalm-suppress PropertyNotSetInConstructor
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

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
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
            ! preg_match(
                '#^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)(,[-+]?\d+)?$#',
                $varInput,
            )
        ) {
            $this->addError(
                sprintf(
                    $GLOBALS['TL_LANG']['ERR']['cowegisInvalidCoordinate'],
                    $varInput,
                ),
            );
        }

        return $varInput;
    }

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
                    'urlTemplate'  => self::getContainer()->getParameter('cowegis_contao_geocode_widget.url_template'),
                ],
            );

            $buffer .= $template->parse();
        }

        return $buffer;
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
            'element'      => 'ctrl_' . $this->radius,
            'min'          => 0,
            'max'          => 0,
            'defaultValue' => 0,
        ];

        if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->radius]['eval'])) {
            $config = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->radius]['eval'];

            $options['min']          = isset($config['minval']) ? (int) $config['minval'] : 0;
            $options['max']          = isset($config['maxval']) ? (int) $config['maxval'] : 0;
            $options['defaultValue'] = isset($config['default']) ? (int) $config['default'] : 0;
            $options['steps']        = isset($config['steps']) ? (int) $config['steps'] : 0;
        }

        return $options;
    }
}
