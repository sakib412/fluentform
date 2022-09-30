<?php

namespace FluentForm\App\Services\Migrator\Classes;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Modules\Form\Form;
use FluentForm\App\Services\Migrator\Classes\BaseMigrator;
use FluentForm\Framework\Helpers\ArrayHelper;

class WpFormsMigrator extends BaseMigrator
{

    protected function getForms()
    {
        $forms = [];
        if (function_exists('wpforms')) {
            $formItems = wpforms()->form->get('');
            foreach ($formItems as $form) {
                $formData = json_decode($form->post_content, true);
                $forms[] = [
                    'ID'       => $form->ID,
                    'name'     => $form->post_title,
                    'fields'   => ArrayHelper::get($formData, 'fields'),
                    'settings' => ArrayHelper::get($formData, 'settings'),
                ];
            }
        }
        return $forms;
    }
    
    public function getFields($form)
    {
        $fluentFields = [];
        $fields = ArrayHelper::get($form, 'fields');
        
        foreach ($fields as $field) {
            $fieldType = ArrayHelper::get($this->fieldTypeMap(), ArrayHelper::get($field, 'type'));
            
            $args = $this->formatFieldData($field, $fieldType);
            if ($fieldData = $this->getFluentClassicField($fieldType, $args)) {
                $fluentFields[$field['id']] = $fieldData;
            } else {
                $this->unSupportFields[] = ArrayHelper::get($field, 'label');
            }
        }
        $submitBtn = $this->getSubmitBttn([
            'uniqElKey' => 'button_' . time(),
            'label'     => ArrayHelper::get($form, 'settings.submit_text'),
            'class'     => ArrayHelper::get($form, 'settings.submit_class'),
        ]);
        if (empty($fluentFields)) {
            return false;
        }
        
        return [
            'fields'       => $fluentFields,
            'submitButton' => $submitBtn
        ];
    }
    
    public function getSubmitBttn($args)
    {
        return [
            'uniqElKey'      => $args['uniqElKey'],
            'element'        => 'button',
            'attributes'     => [
                'type'  => 'submit',
                'class' => $args['class']
            ],
            'settings'       => [
                'container_class'  => '',
                'align'            => 'left',
                'button_style'     => 'default',
                'button_size'      => 'md',
                'color'            => '#ffffff',
                'background_color' => '#409EFF',
                'button_ui'        => [
                    'type'    => ArrayHelper::get($args, 'type', 'default'),
                    'text'    => $args['label'],
                    'img_url' => ArrayHelper::get($args, 'img_url', '')
                ],
                'normal_styles'    => [],
                'hover_styles'     => [],
                'current_state'    => "normal_styles"
            ],
            'editor_options' => [
                'title' => 'Submit Button',
            ],
        
        ];
    }
    
    protected function getFormName($form)
    {
        return $form['name'];
    }
    
    protected function getFormMetas($form)
    {
        $formObject = new Form(wpFluentForm());
        $defaults = $formObject->getFormsDefaultSettings();
        $confirmationsFormatted = $this->getConfirmations($form, $defaults['confirmation']);
        $defaultConfirmation = array_pop($confirmationsFormatted);
        
        $notifications = $this->getNotifications($form);
        
        return [
            'formSettings'               => [
                'confirmation' => $defaultConfirmation,
                'restrictions' => $defaults['restrictions'],
                'layout'       => $defaults['layout'],
            ],
            'advancedValidationSettings' => $this->getAdvancedValidation(),
            'delete_entry_on_submission' => 'no',
            'notifications'              => $notifications,
            'confirmations'              => $confirmationsFormatted,
        ];
    }
    
    protected function getFormId($form)
    {
        return $form['ID'];
    }
    
    public function getFormsFormatted()
    {
        $forms = [];
        $items = $this->getForms();
        foreach ($items as $item) {
            $forms[] = [
                'name'           => $item['name'],
                'id'             => $item['ID'],
                'imported_ff_id' => $this->isAlreadyImported($item),
            ];
        }
        return $forms;
    }
    
    public function exist()
    {
        return !!defined('WPFORMS_VERSION');
    }
    
    protected function formatFieldData($field, $type)
    {
        $args = [
            'uniqElKey'       => $field['id'] . '-' . time(),
            'index'           => $field['id'],
            'required'        => ArrayHelper::isTrue($field, 'required'),
            'label'           => $field['label'],
            'name'            => ArrayHelper::get($field, 'type') . '_' . $field['id'],
            'placeholder'     => ArrayHelper::get($field, 'placeholder', ''),
            'class'           => ArrayHelper::get($field, 'css', ''),
            'value'           => ArrayHelper::get($field, 'default_value', ''),
            'help_message'    => ArrayHelper::get($field, 'description'),
            'container_class' => '',
        ];
        
        switch ($type) {
            case 'input_name':
                $args['input_name_args'] = [];
                $fields = ArrayHelper::get($field, 'format');
                if (!$fields) {
                    break;
                }
                $fields = explode('-', $fields);
                $required = ArrayHelper::isTrue($field, 'required');
                foreach ($fields as $subField) {
                    if ($subField == 'simple') {
                        $label = $args['label'];
                        $subName = 'first_name';
                    } else {
                        $subName = $subField . '_name';
                        $label = ucfirst($subField);
                    }
                    $args['input_name_args'][$subName]['label'] = $label;
                    $args['input_name_args'][$subName]['visible'] = true;
                    $args['input_name_args'][$subName]['required'] = $required;
                    $args['input_name_args'][$subName]['name'] = $subName;
                }
                break;
            case 'select':
            case 'input_radio':
            case 'input_checkbox':
                $args['options'] = $this->getOptions(ArrayHelper::get($field, 'choices', []));
                $args['randomize_options'] = ArrayHelper::isTrue($field, 'random');
                $defaultVal = [];
                foreach ($args['options'] as $option) {
                    if (ArrayHelper::isTrue($option, 'default')) {
                        $defaultVal[] = ArrayHelper::get($option, 'value');
                    }
                }
                if ($type == 'select') {
                    $isMulti = ArrayHelper::exists($field, 'multiple');
                    if ($isMulti) {
                        $args['multiple'] = true;
                        $args['value'] = $defaultVal;
                    } else {
                        $args['value'] = array_shift($defaultVal);
                    }
                } elseif ($type == 'input_checkbox') {
                    $args['value'] = $defaultVal;
                }
                if ($type == 'input_radio') {
                    $args['value'] = array_shift($defaultVal);
                }
                break;
            case 'input_date':
                $args['format'] = Arrayhelper::get($field, 'date_format');
                if ($args['format'] == 'default') {
                    $args['format'] = 'd/m/Y';
                }
                break;
            case 'rangeslider':
                $args['step'] = $field['step'];
                $args['min'] = $field['min'];
                $args['max'] = $field['max'];
               
                break;
            case 'input_hidden':
                $args['value'] = ArrayHelper::get($field, 'default', '');
                break;
            case 'ratings':
                $number = ArrayHelper::get($field, 'number_of_stars', 5);
                $args['options'] = array_combine(range(1, $number), range(1, $number));
                break;
            case 'input_file':
                
                break;
            case 'custom_html':
                $args['html_codes'] = $field['default'];
                break;
            
            case 'gdpr_agreement': // ??
                $args['tnc_html'] = $field['config']['agreement'];
                break;
        }
        return $args;
    }
    
    private function fieldTypeMap()
    {
        return [
            'email'         => 'email',
            'text'          => 'input_text',
            'name'          => 'input_name',
            'hidden'        => 'input_hidden',
            'textarea'      => 'input_textarea',
            'select'        => 'select',
            'radio'         => 'input_radio',
            'checkbox'      => 'input_checkbox',
            'number-slider' => 'rangeslider',
            'number'      => 'input_number',



//            'website'     => 'input_url',
//            'phone'       => 'phone',
//            'list'        => 'repeater_field',
//            'multiselect' => 'multi_select',
//            'date'        => 'input_date',
//            'time'        => 'input_date',
//            'fileupload'  => 'input_file',
//            'consent'     => 'terms_and_condition',
//            'captcha'     => 'reCaptcha',
//            'html'        => 'custom_html',
//            'section'     => 'section_break',
//            'page'        => 'form_step',
//            'address'     => 'address',
        ];
    }
    
    private function getConfirmations($form, $defaultValues)
    {
        $confirmations = ArrayHelper::get($form, 'settings.confirmations');
        $confirmationsFormatted = [];
        if (!empty($confirmations)) {
            foreach ($confirmations as $confirmation) {
                $type = $confirmation['type'];
                if ($type == 'redirect') {
                    $redirectTo = 'customUrl';
                } else {
                    if ($type == 'page') {
                        $redirectTo = 'customPage';
                    } else {
                        $redirectTo = 'samePage';
                    }
                }
                $confirmationsFormatted[] = wp_parse_args(
                    [
                        'name'                 => ArrayHelper::get($confirmation, 'name'),
                        'messageToShow'        => ArrayHelper::get($confirmation, 'message'),
                        'samePageFormBehavior' => 'hide_form',
                        'redirectTo'           => $redirectTo,
                        'customPage'           => intval(ArrayHelper::get($confirmation, 'page')),
                        'customUrl'            => ArrayHelper::get($confirmation, 'redirect'),
                        'active'               => true
                    ], $defaultValues
                );
            }
        }
        return $confirmationsFormatted;
    }
    
    private function getAdvancedValidation(): array
    {
        return [
            'status'          => false,
            'type'            => 'all',
            'conditions'      => [
                [
                    'field'    => '',
                    'operator' => '=',
                    'value'    => ''
                ]
            ],
            'error_message'   => '',
            'validation_type' => 'fail_on_condition_met'
        ];
    }
    
    private function getNotifications($form)
    {
        $confirmationsFormatted = [];
        $enabled = ArrayHelper::isTrue($form, 'settings.notification_enable');
        $confirmations = ArrayHelper::get($form, 'settings.confirmations');
        foreach ($confirmations as $confirmation) {
            $confirmationsFormatted[] = [
                'sendTo'    => [
                    'type'    => 'email',
                    'email'   => ArrayHelper::get($form, 'mailer.recipients'),
                    'field'   => '',
                    'routing' => [],
                ],
                'enabled'   => $enabled,
                'name'      => ArrayHelper::get($confirmation, 'name', 'Admin Notification'),
                'subject'   => ArrayHelper::get($confirmation, 'subject', 'Notification'),
                'to'        => ArrayHelper::get($confirmation, 'email', '{wp.admin_email}'),
                'replyTo'   => ArrayHelper::get($confirmation, 'replyto', '{wp.admin_email}'),
                'message'   => str_replace('{all_fields}', '{all_data}',
                    ArrayHelper::get($confirmation, 'mailer.email_message')),
                'fromName'  => ArrayHelper::get($confirmation, 'sender_name'),
                'fromEmail' => ArrayHelper::get($confirmation, 'sender_address'),
                'bcc'       => '',
            ];
        }
        return $confirmationsFormatted;
    }
    
    public function getOptions($options)
    {
        $formattedOptions = [];
        foreach ($options as $key => $option) {
            $formattedOptions[] = [
                'label'      => ArrayHelper::get($option, 'label', 'Item -' . $key),
                'value'      => !empty(ArrayHelper::get($option, 'value')) ? ArrayHelper::get($option,
                    'value') : ArrayHelper::get($option, 'label', 'Item -' . $key),
                'image'      => ArrayHelper::get($option, 'image'),
                'calc_value' => '',
                'id'         => $key,
                'default'    => ArrayHelper::exists($option, 'default'),
            ];
        }
        return $formattedOptions;
    }
    
}
