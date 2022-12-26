<template>
    <div>
        <el-row class="setting_header">
            <el-col :md="18">
                <h2>{{ settings.menu_title }}</h2>
                <p class="integration_description" v-html="settings.menu_description"></p>
            </el-col>
            <el-col :md="6">
                <video-doc class="pull-right" :route_id="settings_key" btn_text="Video Tutorial" />
            </el-col>
        </el-row>

        <div v-if="settings.hide_on_valid && integration.status" class="integration_success_state">
            <div v-if="settings.logo" class="integration_logo">
                <img style="max-height:50px;" :src="settings.logo"/>
                <br/> <br/>
            </div>

            <p v-html="settings.discard_settings.section_description"></p>
            <el-button @click="disconnect(settings.discard_settings.data)" type="danger" size="small">
                {{ settings.discard_settings.button_text }}
            </el-button>
            <el-button v-if="settings.discard_settings.show_verify" v-loading="saving" @click="save()" type="primary"
                       size="small" icon="el-icon-success">{{ $t('Verify Connection Again') }}
            </el-button>


        </div>

        <div v-else v-loading="loading" class="section-body">
            <template v-if="settings.config_instruction && !integration.status">
                <div class="integration_instraction" v-html="settings.config_instruction"></div>
            </template>
            <el-form label-width="205px" label-position="left">
                <!--Site key-->
                <el-form-item v-for="(field,fieldKey) in settings.fields" :key="fieldKey">
                    <template slot="label">
                        {{ field.label }}
                        <el-tooltip v-if="field.label_tips" class="item" placement="bottom-start" effect="light">
                            <div slot="content">
                                <div v-html="field.label_tips">
                                </div>
                            </div>
                            <i class="el-icon-info el-text-info"></i>
                        </el-tooltip>
                    </template>

                    <template v-if="field.type == 'select'">
                        <el-select v-model="integration[fieldKey]">
                            <el-option
                                v-for="(optionName, optionValue) in field.options"
                                :key="optionValue"
                                :label="optionName"
                                :value="optionValue"></el-option>
                        </el-select>
                    </template>
                    <template v-else-if="field.type == 'link'">
                        <a :target="field.target" :class="field.btn_class" :href="field.link">{{ field.link_text }}</a>
                        <p>{{ field.tips }}</p>
                    </template>
                    <template v-else-if="field.type == 'dynamic_link'">
                        <a :target="field.target" :disabled="!getDynamicAuthLink(field)" :class="field.btn_class" :href="getDynamicAuthLink(field)">{{ field.link_text }}</a>
                        <p>{{ field.tips }}</p>
                    </template>
                    <template v-else-if="field.type == 'checkbox-single'">
                        <el-checkbox v-model="integration[fieldKey]">
                            {{field.checkbox_label}}
                        </el-checkbox>
                    </template>
                    <template v-else>
                        <el-input :placeholder="field.placeholder" :type="field.type"
                                  v-model="integration[fieldKey]"></el-input>
                        <p v-if="field.tips">{{ field.tips }}</p>
                    </template>
                </el-form-item>
                <!--Validate Keys-->

                <el-form-item>
                    <el-button v-loading="saving" type="primary" icon="el-icon-success" size="small" @click="save">
                        {{ settings.save_button_text }}
                    </el-button>
                </el-form-item>
            </el-form>

            <div v-if="integration.status">
                <p><i class="el-icon-success"></i> {{ settings.valid_message }}</p>
            </div>
            <div v-else>
                <p><i class="el-icon-error"></i> {{ settings.invalid_message }}</p>
            </div>
        </div>

        <p v-if="error_message">{{ error_message }}</p>
    </div>
</template>

<script type="text/babel">
import VideoDoc from '@/common/VideoInstruction.vue';

export default {
    name: "generalIntegration",
    props: ['app', 'settings_key'],
    components: {
        VideoDoc
    },
    data() {
        return {
            integration: {},
            loading: false,
            saving: false,
            settings: {},
            error_message: ''
        }
    },
    watch: {
        settings_key() {
            this.integration = {};
            this.settings = {};
            this.getIntegrationSettings();
        }
    },
    methods: {
        getDynamicAuthLink(field){
            if (field.link && this.integration[field.app_key_field_name]){
                let link = field.link;
                return link.replace("app_key", this.integration[field.app_key_field_name]);
            }
            return false;

        },
        save() {
            this.saving = true;
            FluentFormsGlobal.$post({
                action: 'fluentform_post_global_integration_settings',
                settings_key: this.settings_key,
                integration: this.integration
            })
                .then(response => {
                    this.$success(response.data.message);
                    if (response.data.redirect_url) {
                        window.location.href = response.data.redirect_url;
                        return;
                    }

                    this.integration.status = response.data.status;

                    if (this.settings.reload_on_save) {
                        this.getIntegrationSettings();
                    }
                })
                .fail(error => {
                    this.integration.status = false;
                    this.$fail(error.responseJSON.data.message);
                })
                .always(() => {
                    this.saving = false;
                });
        },
        getIntegrationSettings() {
            this.loading = true;
            FluentFormsGlobal.$get({
                action: 'fluentform_get_global_integration_settings',
                settings_key: this.settings_key
            })
                .then(response => {
                    this.integration = response.data.integration;
                    this.settings = response.data.settings;
                })
                .fail(error => {
                    this.error_message = error.responseJSON.data.message;
                })
                .always(() => {
                    this.loading = false;
                });
        },
        disconnect(data) {
            this.integration = data;
            this.save();
        }
    },
    mounted() {
        this.getIntegrationSettings();
    }
}
</script>
