<template>
    <div class="ffc_meta_settings">
        <h3>{{ $t('Meta Settings & Form Messages') }}</h3>
        <el-form :data="meta_settings" label-position="top">
            <el-form-item :label="$t('Meta Title')">
                <el-input v-model="meta_settings.title" type="text" :max-length="60" :placeholder="$t('Meta Title')" />
            </el-form-item>
            <el-form-item :label="$t('Meta Description')">
                <el-input v-model="meta_settings.description" type="textarea" :max-length="160" :placeholder="$t('Meta Description')" />
            </el-form-item>
            <el-row :gutter="30">
                <el-col :span="12">
                    <el-form-item :label="$t('Featured Image')">
                        <photo-uploader v-model="meta_settings.featured_image" design_mode="horizontal" enable_clear="yes"/>
                        <p class="ffc_help">{{ $t('For your social sharing preview Image') }}</p>
                    </el-form-item>
                </el-col>
                <el-col :span="12">
                    <el-form-item :label="$t('Security Code (Optional)')">
                        <el-input v-model="meta_settings.share_key" type="text" :max-length="20" :placeholder="$t('Security Code')" />
                        <p class="ffc_help">{{ $t('A Salt to secure your share url so nobody can guess by form ID.') }}</p>
                    </el-form-item>
                </el-col>
            </el-row>
            <h3>{{ $t('Form Messages') }}</h3>
            <el-row :gutter="30">
                <el-col :span="12">
                    <el-form-item :label="$t('Continue Button Text')">
                        <el-input v-model="meta_settings.i18n.continue" type="text" :placeholder="$t('EG: Continue')" />
                    </el-form-item>
                    <el-form-item :label="$t('Skip Button Text')">
                        <el-input v-model="meta_settings.i18n.skip_btn" type="text" :placeholder="$t('Skip Button Text')" />
                    </el-form-item>
                    <el-form-item :label="$t('Keyboard instruction to go to next question')">
                        <el-input v-model="meta_settings.i18n.keyboard_instruction" type="text" :placeholder="$t('EG: press Enter ↵')" />
                    </el-form-item>
                    <el-form-item :label="$t('Hint for making a line break in Textarea')">
                        <el-input v-model="meta_settings.i18n.long_text_help" type="text" :placeholder="$t('EG: Shift ⇧ + Enter ↵ to make a line break')" />
                    </el-form-item>
                    <el-form-item :label="$t('Hint for multiple selection')">
                        <el-input v-model="meta_settings.i18n.multi_select_hint" type="text" :placeholder="$t('EG: Choose as many as you like')" />
                    </el-form-item>

                    <el-form-item :label="$t('Key Hint Text')">
                        <el-input 
                            v-model="meta_settings.i18n.key_hint_text" 
                            type="text" 
                            :placeholder="$t('Enter key hint text shown to options')"
                        />
                    </el-form-item>
                    <el-form-item :label="$t('File Upload Text')">
                        <el-input
                            v-model="meta_settings.i18n.choose_file"
                            type="text"
                            :placeholder="$t('Enter File Upload Field text')"
                        />
                    </el-form-item>
                </el-col>
                <el-col :span="12">
                    <el-form-item :label="$t('Confirm Button Text')">
                        <el-input v-model="meta_settings.i18n.confirm_btn" type="text" :placeholder="$t('OK')" />
                    </el-form-item>
                    <el-form-item :label="$t('Hint for Single selection')">
                        <el-input v-model="meta_settings.i18n.single_select_hint" type="text" :placeholder="$t('EG: Select an option')" />
                    </el-form-item>
                    <el-form-item :label="$t('Progress Text')">
                        <el-input v-model="meta_settings.i18n.progress_text" type="text" :placeholder="$t('EG: {percent}% completed')" />
                        <p class="ffc_help">{{ $t('Available Variables: {percent}, {step}, {total}') }}</p>
                    </el-form-item>
                    <el-form-item :label="$t('Invalid Prompt')">
                        <el-input v-model="meta_settings.i18n.invalid_prompt" type="text" :placeholder="$t('EG: Please fill out the field correctly')" />
                    </el-form-item>
                    <el-form-item :label="$t('Default Placeholder')">
                        <el-input v-model="meta_settings.i18n.default_placeholder" type="text" :placeholder="$t('EG: Type your answer here')" />
                    </el-form-item>
                    <el-form-item :label="$t('Key Hint Tooltip')">
                        <el-input 
                            v-model="meta_settings.i18n.key_hint_tooltip" 
                            type="text" 
                            :placeholder="$t('Enter Key hint tooltip')"
                        />
                    </el-form-item>
                    <el-form-item :label="$t('File Upload Size Limit Text')">
                        <el-input
                            v-model="meta_settings.i18n.limit"
                            type="text"
                            :placeholder="$t('Enter File Upload Field Size Limit text')"
                        />
                    </el-form-item>
                </el-col>
            </el-row>
        </el-form>
    </div>
</template>

<script type="text/babel">
import PhotoUploader from '../../../common/PhotoUploader';

export default {
    name: 'FormMetaSettings',
    components: {
        PhotoUploader
    },
    props: ['meta_settings']
}
</script>
