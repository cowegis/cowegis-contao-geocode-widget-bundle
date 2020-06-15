<?php

namespace Cowegis\Bundle\ContaoGeocodeWidget\Widget;

use Contao\Widget;

/**
 * Class GeocodeWidget
 *
 * @property int  size
 * @property bool multiple
 */
class GeocodeWidget extends Widget
{
    /**
     * Submit user input.
     *
     * @var boolean
     */
    protected $blnSubmitInput = true;

    /**
     * Add a for attribute.
     *
     * @var boolean
     */
    protected $blnForAttribute = true;

    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'be_widget';

    /**
     * Template name.
     *
     * @var string
     */
    protected $widgetTemplate = 'be_widget_cowegis_geocode';

    /**
     * Validate the input.
     *
     * @param mixed $value Given value.
     *
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function validator($value)
    {
        $value = parent::validator($value);

        if (!$value) {
            return $value;
        }

        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = $this->validator($val);
            }

            return $value;
        }

        // See: http://stackoverflow.com/a/18690202
        if (!preg_match(
            '#^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)(,[-+]?\d+)?$#',
            $value
        )) {
            $this->addError(
                sprintf(
                    $GLOBALS['TL_LANG']['ERR']['cowegisInvalidCoordinate'],
                    $value
                )
            );
        }

        return $value;
    }

    /**
     * Generate the widget.
     *
     * @return string
     */
    public function generate()
    {
        $wrapperClass = 'wizard';

        if (!$this->multiple || !$this->size) {
            $this->size = 1;
        } else {
            $wrapperClass .= ' wizard_' . $this->size;
        }

        if (!is_array($this->value)) {
            $this->value = [$this->value];
        }

        $buffer = '';

        for ($index = 0; $index < $this->size; $index++) {
            $template = new \BackendTemplate($this->widgetTemplate);
            $template->setData(
                [
                    'wrapperClass' => $wrapperClass,
                    'widget'       => $this,
                    'value'        => \StringUtil::specialchars($this->value[$index]),
                    'class'        => $this->strClass ? ' ' . $this->strClass : '',
                    'id'           => $this->strId . (($this->size > 1) ? '_' . $index : ''),
                    'name'         => $this->strName . (($this->size > 1) ? '[]' : ''),
                    'attributes'   => $this->getAttributes(),
                    'wizard'       => $this->wizard,
                    'label'        => $this->strLabel,
                    'radius'       => $this->buildRadiusOptions()
                ]
            );

            $buffer .= $template->parse();
        }

        return $buffer;
    }

    /**
     * Build the radius options.
     *
     * @return array|null
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function buildRadiusOptions()
    {
        if (!$this->radius || !isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->radius])) {
            return null;
        }

        $options = [
            'element'      => 'ctrl_' . $this->radius,
            'min'          => 0,
            'max'          => 0,
            'defaultValue' => 0
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
