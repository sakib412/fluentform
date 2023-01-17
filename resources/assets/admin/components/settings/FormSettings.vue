<template>
    <div class="ff_settings_off" v-loading="!formSettings" :element-loading-text="$t('Loading Settings...')"
         style="min-height: 100px;">
        <template v-if="formSettings">
            <div class="ff_settings_header">
                <el-row>
                    <el-col :md="12">
                        <h2>{{ $t('Form Settings') }}</h2>
                    </el-col>
                    <el-col :md="12">
                        <div class="pull-right">
                            <el-button
                                :loading="loading"
                                size="small"
                                type="primary"
                                icon="el-icon-success"
                                @click="saveSettings">
                                {{loading ? $t('Saving ') : $t('Save ')}} {{ $t('Settings') }}
                            </el-button>
                        </div>
                    </el-col>
                </el-row>
            </div>

            <!--Save settings-->
            <!-- Confirmation Settings -->
            <div class="ff_settings_block">
                <el-row class="setting_header">
                    <el-col :md="12">
                        <h2>{{ $t('Confirmation Settings') }}</h2>
                    </el-col>

                    <!--Save settings-->
                    <el-col :md="12" class="action-buttons clearfix mb15">
                        <video-doc btn_size="medium" class="pull-right ff-left-spaced" route_id="formConfirmation"/>
                    </el-col>
                </el-row>
                <!--confirmation settings form-->
                <el-form label-width="205px" label-position="left">
                    <add-confirmation
                        :pages="pages"
                        :editorShortcodes="editorShortcodes"
                        :confirmation="formSettings.confirmation"
                        :errors="errors">
                    </add-confirmation>
                </el-form>
            </div>

            <!--Double Opt-in settings-->
            <div v-if="double_optin" class="ff_settings_block">
                <el-checkbox true-label="yes" false-label="no" v-model="double_optin.status">
                    {{ $t('Enable ')}}<b>{{ $t('Double Optin ') }}</b> {{ $t('Confirmation before Form Data Processing') }}
                </el-checkbox>

                <el-form class="ff_top_50" v-if="double_optin.status == 'yes'" :data="double_optin" label-width="205px"
                         label-position="left">

                    <el-form-item>
                        <template slot="label">
                            {{ $t('Primary Email Field') }}
                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <div slot="content">
                                    <p>
                                        {{ $t('\Select the primary email field from the form fields.')}} <br/>
                                        {{  $t('In the selected email field, the double optin email will be sent for verification.')}}
                                    </p>
                                </div>

                                <i class="el-icon-info el-text-info"/>
                            </el-tooltip>
                        </template>

                        <el-select v-model="double_optin.email_field" :placeholder="$t('Select an email field')">
                            <el-option
                                v-for="(item, index) in emailFields"
                                :key="index"
                                :label="item.admin_label"
                                :value="item.attributes.name">
                            </el-option>
                        </el-select>

                    </el-form-item>

                    <template v-if="double_optin.email_field">
                        <el-form-item>
                            <template slot="label">
                                {{ $t('Initial Success Message') }}
                                <el-tooltip class="item" placement="bottom-start" effect="light">
                                    <div slot="content">
                                        <h3>{{ $t('Initial Success Message') }}</h3>
                                        <p>
                                            {{ $t('Enter the text you would like the user to ')}}<br>
                                            {{ $t('see just after initial form submission.') }}
                                        </p>
                                    </div>

                                    <i class="el-icon-info el-text-info"/>
                                </el-tooltip>
                            </template>
                            <wp-editor :height="75" :editor-shortcodes="editorShortcodes"
                                       v-model="double_optin.confirmation_message"/>
                            <p>{{ $t('This message will be shown after the intial form submission') }}</p>
                        </el-form-item>

                        <el-form-item :label="$t('Email Type')">
                            <el-radio-group v-model="double_optin.email_body_type">
                                <el-radio label="global">{{ $t('As Per Global Settings') }}</el-radio>
                                <el-radio label="custom">{{ $t('Customized Double Optin Email') }}</el-radio>
                            </el-radio-group>
                        </el-form-item>

                        <template v-if="double_optin.email_body_type == 'custom'">
                            <el-form-item>
                                <template slot="label">
                                    {{ $t('Optin Email Subject') }}
                                    <el-tooltip class="item" placement="bottom-start" effect="light">
                                        <div slot="content">
                                            <p>
                                                {{ $t('Email Subject for double optin email. ') }}<br/>{{ $t('You can use any smart code in the email subject') }}
                                            </p>
                                        </div>

                                        <i class="el-icon-info el-text-info"/>
                                    </el-tooltip>
                                </template>
                                <el-input size="small" :placeholder="$t('Email Subject')"
                                          v-model="double_optin.email_subject"/>
                            </el-form-item>
                            <el-form-item>
                                <template slot="label">
                                    {{ $t('Optin Email Body') }}
                                    <el-tooltip class="item" placement="bottom-start" effect="light">
                                        <div slot="content">
                                            <h3>{{ $t('Optin Email Body') }}</h3>
                                            <p>
                                                {{ $t('Enter the content you would like the user to ')}}<br>
                                                {{ $t('send via email for confirmation.') }}
                                            </p>
                                        </div>

                                        <i class="el-icon-info el-text-info"/>
                                    </el-tooltip>
                                </template>
                                <input-popover :rows="10" v-if="double_optin.asPlainText == 'yes'" fieldType="textarea"
                                               v-model="double_optin.email_body"
                                               :placeholder="$t('Double Opt-in Email Body HTML')"
                                               :data="editorShortcodes"
                                ></input-popover>
                                <wp-editor v-else :height="150" :editor-shortcodes="editorShortcodes"
                                           v-model="double_optin.email_body"/>
                                <el-checkbox style="margin-bottom: 10px;" true-label="yes" false-label="no" v-model="double_optin.asPlainText">
                                    {{ $t('Send Email as RAW HTML Format') }}
                                </el-checkbox>

                                <p>{{ $t('Use #confirmation_url# smartcode for double optin confirmation URL') }}</p>
                            </el-form-item>
                        </template>

                        <div class="form_item">
                            <el-checkbox true-label="yes" false-label="no" v-model="double_optin.skip_if_logged_in">
                                {{ $t('Disable Double Optin for Logged in users') }}
                            </el-checkbox>
                        </div>

                        <div v-if="hasFluentCRM" class="form_item">
                            <el-checkbox true-label="yes" false-label="no" v-model="double_optin.skip_if_fc_subscribed">
                                {{ $t('Disable Double Optin if contact email is subscribed in ')}}<b>FluentCRM</b>
                            </el-checkbox>
                        </div>
                    </template>

                </el-form>
            </div>

            <!-- Appearance Settings -->
            <div class="ff_settings_block">
                <el-row class="setting_header">
                    <el-col :md="12">
                        <h2>{{ $t('Form Layout') }}</h2>
                    </el-col>
                    <el-col :md="12">
                        <video-doc :btn_text="$t('Learn More')" class="pull-right ff-left-spaced" route_id="formErrorMessage"/>
                    </el-col>
                </el-row>
                <!--Appearance settings form-->
                <el-form label-width="205px" label-position="left">
                    <!--Label placement-->
                    <el-form-item>
                        <template slot="label">
                            {{ $t('Label Alignment') }}

                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <div slot="content">
                                    <h3>{{ $t('Form Label Placement') }}</h3>

                                    <p>
                                        {{ $t('Select the default label placement.Labels can be') }} <br>
                                        {{ $t('top aligned above a field, left aligned to the') }} <br>
                                        {{ $t('left of a field, or right aligned to the right of a field.') }}
                                    </p>
                                </div>

                                <i class="el-icon-info el-text-info"></i>
                            </el-tooltip>
                        </template>

                        <el-radio v-for="(labelOption, optionName) in labelPlacementOptions"
                                  v-model="formSettings.layout.labelPlacement" :label="optionName"
                                  :key="optionName" border>
                            {{ labelOption }}
                        </el-radio>
                    </el-form-item>

                    <!--Help Message placement-->
                    <el-form-item>
                        <template slot="label">
                            {{ $t('Help Message Position') }}

                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <div slot="content">
                                    <h3>{{ $t('Help Message Placement') }}</h3>

                                    <p>
                                        {{ $t('Select the default help message placement.') }} <br>
                                        {{ $t('Help messages can be placed beside') }} <br>
                                        {{ $t('label as a tooltip, or below each input.') }}
                                    </p>
                                </div>

                                <i class="el-icon-info el-text-info"></i>
                            </el-tooltip>
                        </template>

                        <el-radio v-for="(option, optionName) in helpMessagePlacementOptions"
                                  v-model="formSettings.layout.helpMessagePlacement" :label="optionName"
                                  :key="optionName" border> {{ option }}
                        </el-radio>
                    </el-form-item>

                    <!--Error Message placement-->
                    <el-form-item>
                        <template slot="label">
                            {{ $t('Error Message Position') }}

                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <div slot="content">
                                    <h3>{{ $t('Error Message placement') }}</h3>

                                    <p>
                                        {{ $t('Select the default error message placement.')}}<br>
                                        {{ $t('Error messages can be placed below each input, ')}}<br>
                                        {{ $t('or stacked after the form submit button.') }}
                                    </p>
                                </div>

                                <i class="el-icon-info el-text-info"></i>
                            </el-tooltip>
                        </template>

                        <el-radio v-for="(option, optionName) in errorMessagesPlacement"
                                  v-model="formSettings.layout.errorMessagePlacement" :label="optionName"
                                  :key="optionName" border>{{ option }}
                        </el-radio>
                    </el-form-item>

                    <!--Required asterisk mark position -->
                    <el-form-item>
                        <template slot="label">
                            {{ $t('Asterisk Position') }}

                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <div slot="content">
                                    <h3>{{ $t('Required Asterisk Position') }}</h3>

                                    <p>
                                        {{ $t('The asterisk marker position for the required elements.') }}
                                    </p>
                                </div>

                                <i class="el-icon-info el-text-info"></i>
                            </el-tooltip>
                        </template>

                        <el-radio v-for="(option, optionName) in asteriskPlacementMock"
                                  v-model="formSettings.layout.asteriskPlacement" :label="optionName"
                                  :key="optionName" border>{{ option }}
                        </el-radio>
                    </el-form-item>
                </el-form>
            </div>

            <!-- Form Restrictions -->
            <div class="ff_settings_block">
                <el-row class="setting_header">
                    <el-col :md="12">
                        <h2>
                            {{ $t('Scheduling & Restrictions') }}
                        </h2>
                    </el-col>
                    <el-col :md="12">
                        <video-doc class="pull-right" :btn_text="$t('Learn More')" route_id="formScheduling"></video-doc>
                    </el-col>
                </el-row>
                <!--Restriction settings form-->
                <div class="ff_settings_section">
                    <div class="ff_settings_body">
                        <form_restriction :data="formSettings.restrictions"></form_restriction>
                    </div>
                </div>
            </div>

            <div class="ff_advanced_validation_wrapper ff_settings_block">
                <!-- Header -->
                <el-row class="setting_header">
                    <el-col :md="24">
                        <h2>{{ $t('Advanced Form Validation') }}</h2>
                        <p>
                            {{
                                $t('You can set rules to the user input and based on the rules you can prevent the form submission.')
                            }}
                            {{ $t('This is very useful feature for preventing spam / bot submissions.') }} <a target="_blank"
                                                                                                rel="noopener" href="https://wpmanageninja.com/docs/fluent-form/advanced-features-functionalities-in-wp-fluent-form/advanced-form-validation-in-wp-fluent-forms-wordpress-plugin/">{{ $t('Learn More here')}}</a>
                        </p>
                    </el-col>
                </el-row>
                <!-- Form Body -->
                <div class="ff_settings_section">
                    <div class="ff_settings_body">
                        <advanced-validation :inputs="inputs"
                                             :settings="advancedValidationSettings"></advanced-validation>
                    </div>
                </div>
            </div>

            <!-- Survey Result -->
            <div class="ff_settings_block">
                <el-row class="setting_header">
                    <el-col :md="24">
                        <h2>{{ $t('Survey Result') }}</h2>
                    </el-col>
                </el-row>

                <div class="ff_settings_section">
                    <div class="ff_settings_body">
                        <survey-result :data="formSettings.appendSurveyResult" :hasPro="hasPro"/>

                        <p v-if="!hasPro"><br/>{{ $t('This feature is only available in pro version of Fluent Forms') }}</p>
                    </div>
                </div>
            </div>

            <div class="ff_settings_block">
                <el-row class="setting_header">
                    <el-col :md="24">
                        <h2>
                            {{ $t('Compliance Settings') }}
                            <el-tooltip class="item" placement="bottom-start" effect="light">
                                <div slot="content">
                                    <h3>{{ $t('Delete entry on form submission') }}</h3>

                                    <p>
                                        {{ $t('If you enable this settings then your entry data will be deleted from database.') }}<br>
                                        {{ $t('It\'s useful for HIPPA/GDPR Compliance for some forms.') }}
                                    </p>
                                </div>

                                <i class="el-icon-info el-text-info"></i>
                            </el-tooltip>
                        </h2>
                    </el-col>
                </el-row>
                <div class="ff_settings_section">
                    <div class="ff_settings_body">
                        <el-checkbox :disabled="!hasPro" true-label="yes" false-label="no"
                                     v-model="formSettings.delete_entry_on_submission">{{
                                $t('Delete entry data after form submission')
                            }}
                        </el-checkbox>

                        <p v-if="!hasPro"><br/>{{ $t('This feature is only available in pro version of Fluent Forms') }}</p>

                        <template v-if="formSettings.delete_entry_on_submission == 'yes'">
                            <p><br/>
                                {{
                                    $t('Your data will be deleted on form submission so no entry data, analytics and visual reporting will be available for this form')
                                }}
                            </p>
                        </template>
                        <div v-if="formSettings.delete_entry_on_submission != 'yes'" style="margin-top: 20px;" class="ff_auto_delete_section">
                            <el-checkbox :disabled="!hasPro" true-label="yes" false-label="no"
                                         v-model="formSettings.delete_after_x_days">
                                {{ $t('Enable auto delete old entries') }}
                            </el-checkbox>
                            <div v-if="formSettings.delete_after_x_days == 'yes'" class="el-form-item">
                                <label class="el-form-item__label">
                                    {{ $t('Specify how many days old entries will be deleted for this form') }}
                                </label>
                                <div class="el-form-item__content">
                                    <el-input-number
                                        :min="1"
                                        :disabled="!hasPro"
                                        size="small"
                                        v-model="formSettings.auto_delete_days"/>
                                </div>
                                <p style="color: red; padding-top: 20px;" v-if="formSettings.auto_delete_days">
                                    {{ $t('Entries older than ') }} <b>{{formSettings.auto_delete_days}} {{ $t(' days ') }}</b> {{ $t('will be deleted automatically') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ff_settings_block">
                <el-row class="setting_header">
                    <el-col :md="24">
                        <h2>
                            {{ $t('Other') }}
                        </h2>
                    </el-col>
                </el-row>

                <div class="ff_settings_section">
                    <div class="ff_settings_body">
                        <div class="el-form-item">
                            <label class="el-form-item__label" style="width: 205px; text-align: left;">
                                {{ $t('Extra CSS Form Class') }}
                            </label>
                            <div class="el-form-item__content" style="margin-left: 205px;">
                                <el-input
                                    :disabled="!hasPro"
                                    :placeholder="$t('extra css class')"
                                    size="small"
                                    v-model="formSettings.form_extra_css_class"/>
                            </div>
                        </div>
                        <p v-if="!hasPro"><br/>{{ $t('This feature is only available in pro version of Fluent Forms') }}</p>
                    </div>
                </div>
            </div>

            <div class="ff_settings_block" v-if="affiliate_wp">
                <el-row class="setting_header">
                    <el-col :md="24">
                        <h2>
                            {{ $t('Affiliate') }}
                        </h2>
                    </el-col>
                </el-row>

                <div class="ff_settings_section">
                    <div class="ff_settings_body">
                        <div class="el-form-item">
                            <label class="el-form-item__label" style="width: 205px; text-align: left;">
                                {{$t('Allow referrals')}}
                            </label>
                            <el-checkbox true-label="yes" false-label="no" v-model="affiliate_wp.status">
                                {{$t('Enable')}}
                            </el-checkbox>
                        </div>
                        <div class="el-form-item">
                            <label class="el-form-item__label" style="width: 205px; text-align: left;">
                                {{$t('Allow referrals')}}
                            </label>
                            <el-select v-model="affiliate_wp.selected_type" :placeholder="$t('Select type')">
                                <el-option
                                    v-for="(item, value) in affiliate_wp.types"
                                    :key="value"
                                    :value="value"
                                    :label="item.label"
                                    >
                                </el-option>
                            </el-select>
                        </div>
                        
                        <p v-if="!hasPro"><br/>{{ $t('This feature is only available in pro version of Fluent Forms') }}</p>
                    </div>
                </div>
            </div>

            <el-row style="margin-top: 50px">
                <el-button
                    :loading="loading"
                    class="pull-right"
                    size="small"
                    type="primary"
                    icon="el-icon-success"
                    @click="saveSettings">
                    {{loading ? $t('Saving ') : $t('Save ')}} {{ $t('Settings') }}
                </el-button>
            </el-row>
        </template>
    </div>
</template>

<script type="text/babel">
    import wpEditor from '../../../common/_wp_editor';
    import form_restriction from './FormSettings/Restrictions';
    import SurveyResult from './FormSettings/SurveyResult';
    import errorView from '../../../common/errorView';
    import AddConfirmation from './Includes/AddConfirmation.vue'
    import AdvancedValidation from "./Includes/AdvancedValidation";
    import VideoDoc from '@/common/VideoInstruction.vue';
    import inputPopover from '../input-popover.vue';

    export default {
        name: 'FormSettings',
        props: {
            'form': Object,
            'form_id': [Number, String],
            'editor-shortcodes': {
                type: Array,
                default: []
            },
            'inputs': Object
        },
        components: {
            wpEditor,
            'form_restriction': form_restriction,
            errorView,
            AddConfirmation,
            SurveyResult,
            AdvancedValidation,
            VideoDoc,
            inputPopover
        },
        data() {
            return {
                savingSettings: false,
                loading: false,
                redirectToOptions: {
                    samePage: 'Same Page',
                    customPage: 'To a Page',
                    customUrl: 'To a Custom URL'
                },
                labelPlacementOptions: {
                    'top': 'Top',
                    'left': 'Left',
                    'right': 'Right'
                },
                helpMessagePlacementOptions: {
                    'with_label': 'Beside Label (Tooltip)',
                    'under_input': 'Below Input Fields',
                    'on_focus': 'Focus on Element',
                    'after_label': 'Before input'
                },
                errorMessagesPlacement: {
                    'inline': 'Below Input Fields',
                    'stackToBottom': 'Stacked after Form'
                },
                asteriskPlacementMock: {
                    '': 'None',
                    'asterisk-left': 'Left to Label',
                    'asterisk-right': 'Right to Label'
                },
                advancedValidationSettings: {
                    status: false,
                    type: 'all',
                    conditions: [
                        {
                            field: null,
                            operator: '=',
                            value: null
                        }
                    ],
                    error_message: ''
                },
                pages: [],
                formSettings: false,
                isPageLoading: true,
                errors: new Errors,
                delete_entry_on_submission: 'no',
                hasPro: !!window.FluentFormApp.hasPro,
                hasFluentCRM: !!window.FluentFormApp.hasFluentCRM,
                double_optin: false,
                affiliate_wp: false,
            }
        },
        computed: {
            emailFields() {
                return _ff.filter(this.inputs, (input) => {
                    return input.attributes.type === 'email';
                });
            }
        },
        methods: {
            setDefaultSettings() {
                this.formSettings = {
                    confirmation: {
                        redirectTo: 'samePage',
                        messageToShow: 'Thank you for your message. We will get in touch with you shortly',
                        customPage: null,
                        samePageFormBehavior: 'hide_form',
                        customUrl: null,
                    },
                    restrictions: {
                        limitNumberOfEntries: {
                            enabled: false,
                            numberOfEntries: null,
                            period: 'total',
                            limitReachedMsg: 'Maximum number of entries exceeded.',
                        },
                        scheduleForm: {
                            enabled: false,
                            start: null,
                            end: null,
                            pendingMsg: 'Form submission is not started yet.',
                            expiredMsg: 'Form submission is now closed.',
                        },
                        requireLogin: {
                            enabled: false,
                            requireLoginMsg: 'You must be logged in to submit the form.',
                        },
                        denyEmptySubmission: {
                            enabled: false,
                            message: 'Sorry, you cannot submit an empty form. Let\'s hear what you wanna say.'
                        }
                    },
                    layout: {
                        labelPlacement: 'top',
                        asteriskPlacement: 'asterisk-right',
                        helpMessagePlacement: 'with_label',
                        errorMessagePlacement: 'inline',
                        cssClassName: ''
                    }
                }
            },
            fetchSettings() {
                FluentFormsGlobal.$get({
                    action: 'fluentform-settings-general-formSettings',
                    form_id: this.form_id
                })
                    .done(response => {
                        if (response.data.generalSettings) {
                            let settings = response.data.generalSettings;
                            if (!settings.confirmation)
                                settings.confirmation = {};
                            if (!settings.restrictions)
                                settings.restrictions = {};
                            if (!settings.layout)
                                settings.layout = {};

                            if (!settings.restrictions.limitNumberOfEntries)
                                settings.restrictions.limitNumberOfEntries = {};
                            if (!settings.restrictions.scheduleForm)
                                settings.restrictions.scheduleForm = {};
                            if (!settings.restrictions.requireLogin)
                                settings.restrictions.requireLogin = {};

                            if (!settings.restrictions.denyEmptySubmission)
                                settings.restrictions.denyEmptySubmission = {};

                            if (!settings.appendSurveyResult) {
                                settings.appendSurveyResult = {
                                    enabled: false,
                                    showLabel: false,
                                    showCount: false
                                }
                            }

                            this.formSettings = settings;
                        } else {
                            this.setDefaultSettings();
                        }
                        this.advancedValidationSettings = response.data.advancedValidationSettings;

                        this.double_optin = response.data.double_optin;
                        this.affiliate_wp = response.data.affiliate_wp;

                    })
                    .fail(e => {
                        this.setDefaultSettings();
                    })
                    .always(() => {
                    });
            },
            fetchPages() {
                FluentFormsGlobal.$get({
                    action: 'fluentform-get-pages',
                    form_id: this.form_id
                })
                    .then(response => {
                        this.pages = response.data.pages;
                    })
                    .fail(e => {
                        this.loading = false;
                    })
            },
            saveSettings() {
                this.loading = true;
                let data = {
                    form_id: this.form_id,
                    formSettings: JSON.stringify(this.formSettings),
                    advancedValidationSettings: JSON.stringify(this.advancedValidationSettings),
                    double_optin: JSON.stringify(this.double_optin),
                    affiliate_wp: JSON.stringify(this.affiliate_wp),
                    action: 'fluentform-save-settings-general-formSettings'
                };

                FluentFormsGlobal.$post(data)
                    .then(response => {
                        this.$success(response.data.message);
                    })
                    .fail(error => {
                        this.errors.record(error.responseJSON.data.errors);
                    })
                    .always(() => {
                        this.loading = false;
                    });
            },
        },
        mounted() {
            this.fetchSettings();
            this.fetchPages();
            jQuery('head title').text('Confirmation Settings - Fluent Forms');
        },
        beforeCreate() {
            ffSettingsEvents.$emit('change-title', 'Form Settings');
        }
    };
</script>
