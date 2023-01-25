<template>
    <div v-loading="loading" :element-loading-text="$t('Loading Settings...')" class="landing-page-settings">

        <el-row class="setting_header" type="flex" justify="space-between" align="middle">
            <el-col :sm="12">
                <h2>{{ $t('Landing Page') }}</h2>
                <p v-if="settings.status != 'yes'">{{ ('Create completely custom "distraction-free" form landing pages to boost conversions') }}</p>
                <el-checkbox style="margin-bottom: 15px;" v-model="settings.status" true-label="yes" @change="offFullScreen" false-label="no">
                    {{ $t('Enable Form Landing Page Mode') }}
                </el-checkbox>
            </el-col>
            <!--Save settings-->
            <el-col v-if="!error_text" :sm="12" class="action-buttons clearfix ">
                <a v-show="share_url && settings.status == 'yes'" 
                    class="ff_landing_full_screen el-button pull-right el-button--mini"
                    @click="fullScreen"
                >
                    <i class="el-icon-full-screen"></i>
                </a>

                <el-button
                        :loading="saving"
                        class="pull-right"
                        size="small"
                        type="primary"
                        icon="el-icon-success"
                        @click="saveSettings()">
                    {{loading ? $t('Saving ') : $t('Save ')}} {{ $t('Settings') }}
                </el-button>
                <a 
                    v-show="share_url && settings.status == 'yes'" 
                    style="margin-right: 10px" 
                    target="_blank" 
                    rel="noopener" 
                    :href="final_share_url" 
                    class="el-button el-button--primary el-button--small is-plain pull-right"
                >
                    <i class="el-icon-share"></i>
                </a>
            </el-col>
        </el-row>


        <p>{{error_text}}</p>

        <div v-if="settings.status == 'yes'" class="ff_landing">
            <div class="ff_landing_sidebar">
                <div class="ffc_sidebar_header">
                    <ul>
                        <li :class="{ffc_active : active_tab == 'design'}" @click="active_tab = 'design'">{{ $t('Design') }}</li>
                        <li :class="{ffc_active : active_tab == 'share'}" @click="active_tab = 'share'">{{ $t('Share') }}</li>
                    </ul>
                </div>
                <div v-if="settings && active_tab == 'design'" class="ff_landing_settings_wrapper ffc_sidebar_body">
                    <el-form ref="form" :model="settings" label-position="top" label-width="140px">
                        <el-form-item>
                            <template slot="label">
                                {{ $t('Page Design Style') }}
                            </template>
                            <el-radio-group v-model="settings.design_style">
                                <el-radio v-for="(layoutName, layoutCode) in layouts" :key="layoutCode" :label="layoutCode">{{layoutName}}</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <div>
                            <LayoutPref :pref="settings"></LayoutPref>
                        </div>

                        <el-form-item>
                            <template slot="label">
                                {{ $t('BG Color') }}
                                <el-tooltip class="item" placement="bottom-start" effect="light">
                                    <div slot="content">
                                        <h3>{{ $t('Background Color') }}</h3>
                                        <p>
                                            {{ $t('Choose Custom Color for your form page') }}
                                        </p>
                                    </div>
                                    <i class="el-icon-info el-text-info"></i>
                                </el-tooltip>
                            </template>
                            <el-color-picker @active-change="(color) => { settings.custom_color = color; }" v-model="settings.custom_color"></el-color-picker>
                        </el-form-item>


                        <div v-for="(item,i) in settings.form_shadow">

                            <ff_boxshadow :valueItem="item"/>

                        </div>


                        <el-form-item>
                            <template slot="label">
                                {{ $t('BG Image') }}
                                <el-tooltip class="item" placement="bottom-start" effect="light">
                                    <div slot="content">
                                        <h3>{{ $t('Background Image') }}</h3>
                                        <p>
                                            {{ $t('Page Background Image') }}
                                        </p>
                                    </div>
                                    <i class="el-icon-info el-text-info"></i>
                                </el-tooltip>
                            </template>
                            <photo-uploader enable_clear="yes" design_mode="horizontal" v-model="settings.background_image" />
                        </el-form-item>

                        <el-form-item>
                            <template slot="label">
                                {{ $t('Form Logo') }}
                                <el-tooltip class="item" placement="bottom-start" effect="light">
                                    <div slot="content">
                                        <h3>{{ $t('Logo') }}</h3>
                                        <p>
                                            {{ $t('You may upload your logo and it will show on the top of the page') }}
                                        </p>
                                    </div>
                                    <i class="el-icon-info el-text-info"></i>
                                </el-tooltip>
                            </template>
                            <photo-uploader enable_clear="yes" design_mode="horizontal" v-model="settings.logo" />
                        </el-form-item>

                        <el-form-item>
                            <template slot="label">
                                {{ $t('Featured Image') }}
                                <el-tooltip class="item" placement="bottom-start" effect="light">
                                    <div slot="content">
                                        <h3>{{ $t('Featured Image') }}</h3>
                                        <p>
                                            {{ $t('Featured Image will be shown in social media share preview') }}
                                        </p>
                                    </div>
                                    <i class="el-icon-info el-text-info"></i>
                                </el-tooltip>
                            </template>
                            <photo-uploader enable_clear="yes" design_mode="horizontal" v-model="settings.featured_image" />
                        </el-form-item>

                    </el-form>
                    <el-form ref="form" :model="settings" label-position="top">
                        <div class="ff_landing_page_items">

                            <el-form-item>
                                <template slot="label">
                                    {{ $t('Page Heading') }}
                                    <el-tooltip class="item" placement="bottom-start" effect="light">
                                        <div slot="content">
                                            <h3>{{ $t('Form Title') }}</h3>
                                            <p>
                                                {{ $t('This will show at the top of your page') }}
                                            </p>
                                        </div>
                                        <i class="el-icon-info el-text-info"></i>
                                    </el-tooltip>
                                </template>
                                <el-input :placeholder="$t('eg: My Awesome Form')" v-model="settings.title"/>
                            </el-form-item>

                            <el-form-item>
                                <template slot="label">
                                    {{ $t('Description') }}
                                    <el-tooltip class="item" placement="bottom-start" effect="light">
                                        <div slot="content">
                                            <h3>{{ $t('Description') }}</h3>
                                            <p>
                                                {{ $t('This will show at the top of your page after form title') }}
                                            </p>
                                        </div>
                                        <i class="el-icon-info el-text-info"></i>
                                    </el-tooltip>
                                </template>
                                <wp-editor :height="100" v-model="settings.description" />
                            </el-form-item>

                            <el-form-item v-if="share_url">
                                <template slot="label">
                                    {{ $t('Security Code') }}
                                    <el-tooltip class="item" placement="bottom-start" effect="light">
                                        <div slot="content">
                                            <p>
                                                {{ $t('A Salt to secure your share url so nobody can guess by form ID.') }}
                                            </p>
                                        </div>
                                        <i class="el-icon-info el-text-info"></i>
                                    </el-tooltip>
                                </template>
                                <el-input v-model="settings.share_url_salt" />
                            </el-form-item>

                            <el-form-item style="margin-top: 20px">
                                <el-button
                                    :loading="saving"
                                    class="pull-right"
                                    size="small"
                                    type="primary"
                                    icon="el-icon-success"
                                    @click="saveSettings()">
                                    {{loading ? $t('Saving ') : $t('Save ')}} {{ $t('Settings') }}
                                </el-button>
                            </el-form-item>
                        </div>
                    </el-form>
                </div>
                <div style="padding-top: 20px;" class="ff_landing_settings_wrapper ffc_sidebar_body" v-else-if="active_tab == 'share'">
                    <p>{{ $t('Share your form by unique URL or copy and paste the shortcode to embed in your page and post') }}</p>
                </div>
            </div>
            <div class="ff_landing_preview ffc_design_container">
                <template v-if="active_tab == 'design'">
                    <browser :settings="settings" v-if="final_share_url && show_frame" :preview_url="final_share_url" @change-device-type="changeDeviceType"/>
                </template>
                <share v-else-if="final_share_url" :share_url="final_share_url" :form_id="form_id"  />
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
    import WpEditor from '../../../../common/_wp_editor';
    import PhotoUploader from '../../../../common/PhotoUploader';
    import ConversionStylePref from "../../../conversion_templates/ConversionStylePref";
    import Browser from './_Browser';
    import Share from './_Sharing';
    import Ff_boxshadow from './BoxShadow';

    export default {
        name: 'landing_pages',
        components: {
            Ff_boxshadow,
            WpEditor,
            PhotoUploader,
            Browser,
            Share,
            LayoutPref : ConversionStylePref
        },
        data() {
            return {
                loading: false,
                saving: false,
                settings: false,
                form_id: window.FluentFormApp.form_id,
                error_text: '',
                share_url: '',
                color_schemas: {
                    '#4286c4' : 'Blue',
                    '#32373c' : 'Dark Gray',
                    '#67c23a' : 'Green',
                    '#19a59f': 'darkcyan',
                    '#d34342' : 'Red',
                    '#999999' : 'Gray',
                    'custom': 'Custom'
                },
                layouts: {
                    modern: 'Boxed',
                    classic: 'Classic'
                },
                active_tab: 'design',
                show_frame: true,
                setup: false
            }
        },
        computed: {
            final_share_url() {
                if(this.settings.share_url_salt) {
                    return this.share_url + '&form='+this.settings.share_url_salt;
                } else {
                    return this.share_url;
                }
            }
        },
        watch: {
            'settings.share_url_salt': function (value, oldValue){
                this.settings.share_url_salt = this.string_to_slug(value);
            },
            'settings.design_style': function (){
                this.setup && this.saveSettings(true);
            },
            'settings.layout': function (){
                this.saveSettings(true);
            },
            'settings.media': function (){
                this.saveSettings(true);
            },
            'settings.background_image': function (){
                this.setup && this.saveSettings(true);
            }
        },
        methods: {
            saveSettings(silence) {
                this.saving = true;
                this.show_frame = false;

                let data = {
                    action: 'ff_store_landing_page_settings',
                    form_id: this.form_id,
                    settings: this.settings
                };

                FluentFormsGlobal.$post(data)
                    .then(response => {
                        this.share_url = response.data.share_url;
                        if(!silence) {
                            this.$success(response.data.message);
                        }

                        this.setup = true;
                    })
                    .fail(error => {

                    })
                    .always(() => {
                        this.saving = false;
                        this.show_frame = true;
                    });
            },
            fetchSettings() {
                this.loading = true;
                
                let data = {
                    action: 'ff_get_landing_page_settings',
                    form_id: this.form_id
                };

                FluentFormsGlobal.$get(data)
                    .then(response => {
                        this.share_url = response.data.share_url;
                        const settings = response.data.settings;
                        if(settings.color_schema != 'custom') {
                            settings.custom_color = settings.color_schema;
                            settings.color_schema = 'custom';
                        }
                        settings.remember_device_type = settings.remember_device_type || 'desktop';

                        if (!settings.form_shadow || !settings.form_shadow.length) {
                            settings.form_shadow = [
                                {
                                    label : this.$t('Outer Shadow'),
                                    position: "",
                                    horizontal: "0",
                                    vertical: "30",
                                    blur: "40",
                                    spread: "0",
                                    color :"rgb(0 0 0 / 25%)"
                                },
                                {
                                    label : this.$t('Inner Shadow'),
                                    position: "inset",
                                    horizontal: "0",
                                    vertical: "4",
                                    blur: "0",
                                    spread: "0",
                                    color : "#a1c5e5",
                                },
                            ];
                        }
                        this.settings = settings;
                        this.settings.brightness = parseInt(this.settings.brightness);
                        this.settings.media_x_position = parseInt(this.settings.media_x_position);
                        this.settings.media_y_position = parseInt(this.settings.media_y_position);

                    })
                    .fail(error => {
                        if (!error.responseJSON) {
                            this.error_text = 'Looks like you do not have latest version of Fluent Forms Pro Installed. Please install latest version of Fluent Forms Pro to use this feature';
                            return;
                        }
                    })
                    .always(() => {
                        this.loading = false;
                    });
            },
            string_to_slug (text) {
                return text
                    .toString()                     // Cast to string
                    .toLowerCase()                  // Convert the string to lowercase letters
                    .normalize('NFD')       // The normalize() method returns the Unicode Normalization Form of a given string.
                    .replace(/\s+/g, '-')           // Replace spaces with -
                    .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
                    .replace(/\-\-+/g, '-');        // Replace multiple - with single -

            },
            fullScreen() {
                const $body = jQuery('body');
                let wasFullScreen = $body.hasClass('ff_full_screen');
                if (window.localStorage) {
                    if (wasFullScreen) {
                        window.localStorage.setItem('ff_landing_is_full_screen', 'no');
                    } else {
                        window.localStorage.setItem('ff_landing_is_full_screen', 'yes');
                    }
                }
                $body.toggleClass('ff_full_screen');
            },
            offFullScreen(status) {
                if (status !== 'yes') {
                    jQuery('body').removeClass('ff_full_screen');
                    if (window.localStorage) {
                        window.localStorage.setItem('ff_landing_is_full_screen', 'no');
                    }
                }
            },
            changeDeviceType (type) {
                this.settings.remember_device_type = type;
            }
        },
        mounted() {
            this.fetchSettings();
            jQuery('head title').text('Landing Page Settings - Fluent Forms');
            
            if (window.localStorage) {
                if (window.localStorage.getItem('ff_landing_is_full_screen') == 'yes') {
                    jQuery('body').addClass('ff_full_screen').addClass('folded');
                }
            } else {
                jQuery('body').addClass('ff_full_screen').addClass('folded');
            }
        }
    };
</script>
