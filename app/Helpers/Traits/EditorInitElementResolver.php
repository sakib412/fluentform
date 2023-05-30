<?php

namespace FluentForm\App\Helpers\Traits;

use FluentForm\Framework\Helpers\ArrayHelper as Arr;

trait EditorInitElementResolver
{
    public static function resolveValidationRulesGlobalOption(&$field)
    {
        if (isset($field['fields']) && is_array($field['fields'])) {
            foreach ($field['fields'] as &$subField) {
                static::resolveValidationRulesGlobalOption($subField);
            }
        } else {
            if ($rules = Arr::get($field, 'settings.validation_rules', [])) {
                foreach ($rules as $key => $rule) {
                    if(!isset($rule['global'])) {
                        $field['settings']['validation_rules'][$key]['global'] = false;
                    }
                }
            }
        }
    }

    /**
     *
     *  Method for automatic assign field attribute options if not exist
     *
     * @param array $element - Addressed field to resolve
     * @param array $options - Field attribute options to resolve.
     *
     * [
     *     "option1"  => "value1",
     *     "options2_with_empty_value",
     *     "options3" => "value3"
     * ]
     *
     * @return void
     *
     */
    public static function resolveAttributeOption(&$element, $options)
    {
        static::resolveOptions($element, $options,'attributes');
    }

    /**
     *
     *  Method for automatic assign field settings options if not exist
     *
     * @param array $element - Addressed field to resolve
     * @param array $options - Field settings options to resolve.
     *
     * [
     *     "option1"  => "value1",
     *     "options2_with_empty_value",
     *     "options3" => "value3"
     * ]
     *
     * @return void
     *
     */
    public static function resolveSettingsOptions(&$element, $options)
    {
        static::resolveOptions($element, $options);
    }

    private static function resolveOptions(&$element, $options, $key = 'settings')
    {
        if (is_array($options)) {
            foreach ($options as $option => $value) {
                if (!is_string($option) && $value) {
                    $option = $value;
                    $value = '';
                }
                if (!isset($element[$key][$option])) {
                    $element[$key][$option] = $value;
                }
            }
        }
    }

    public static function resolveSettingsAdvancedOptions(&$element, $elementName) {
        if (!Arr::get($element, 'settings.advanced_options')) {
            $formattedOptions = [];
            $oldOptions = Arr::get($element, 'options', []);
            foreach ($oldOptions as $value => $label) {
                $formattedOptions[] = [
                    'label'      => $label,
                    'value'      => $value,
                    'calc_value' => '',
                    'image'      => '',
                ];
            }
            $element['settings']['advanced_options'] = $formattedOptions;
            $element['settings']['enable_image_input'] = false;
            $element['settings']['calc_value_status'] = false;
            unset($element['options']);

            if ('input_radio' == $elementName || 'input_checkbox' == $elementName) {
                $element['editor_options']['template'] = 'inputCheckable';
            }
        }
    }
}