<template>
    <div class="ff_migrator">
        <el-row class="admin_menu_header">
            <el-col :md="24">
                <h3>{{ $t('Fluent Forms Migrator') }}</h3>
                <p>
                    {{ $t('Migrate other plugins forms into Fluent Forms with ease. Please note that previously imported forms and entries will be reset and updated again.') }}
                </p>
            </el-col>
        </el-row>
        <div class="ff_migrator_body">
            
            <div class="ff_migrator_navigation" v-loading="loading">
                <el-tabs v-model="currentFormType" @tab-click="getForms">
                    <el-tab-pane :label="migrators.name" v-for="(migrators,index) in migratorData" :key="index"
                                 :name="migrators.key">
                        <el-row class="setting_header">
                            <el-col :md="18">
                                <h2>
                                    {{ $t('Import ') }} {{ migrators.name }}
                                </h2>
                            </el-col>
                            <el-col :md="6" class="action-buttons clearfix mb15">
                                <el-button v-if="forms.length" size="mini" class="pull-right" type="success" @click="importForms()"
                                > {{ $t('Import All Forms') }}
                                </el-button>
                            </el-col>
                        </el-row>
                    </el-tab-pane>
                    
                    <el-table
                        v-if="forms.length"
                        :data="listForms"
                        class="ff_migrator_table"
                        @selection-change="handleSelectionChange"
                        stripe
                        style="width: 100%">
                        <el-table-column
                            type="selection"
                            width="50">
                        </el-table-column>
                        <el-table-column
                            prop="name"
                            min-width="140"
                            :label="$t('Form Name')">
                        </el-table-column>
                        
                        <el-table-column
                            :label="$t('Imported')"
                            width="120"
                            align="center">
                            <template slot-scope="props">
                                <span v-if="props.row.imported_ff_id">
                                  <i class="el-icon-success el-text-success"></i>
                                </span>
                                <span v-else>
                                     <i class="el-icon-success"></i>
                                </span>
                            </template>
                        </el-table-column>
                        
                        <el-table-column
                            v-if="entryImportSupported"
                            align="right"
                            width="120"
                            label=""
                        >
                            <template slot-scope="props">
                                <el-button v-if="entryImportSupported && props.row.imported_ff_id" size="mini"
                                           class="pull-right" type="success"
                                           @click="importEntries( props.row.imported_ff_id, props.row.id )">
                                    {{ $t('Import Entries') }}
                                </el-button>
                            </template>
                        </el-table-column>
                        
                        <el-table-column width="160" :label="$t('Action')" align="right">
                            <template slot-scope="props">
                                <el-button size="mini" class="pull-right" type="success"
                                           @click="importForms([props.row.id])">
                                    {{ $t('Import Form') }}
                                </el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                    <div v-else>
                        {{$t('No Forms Found')}}
                    </div>
                    <div v-if="multipleSelection.length">
                        <br>
                        <el-button size="mini" class="pull-right" type="success" icon="el-icon-success"
                                   @click="importForms(multipleSelection)">
                            {{ $t('Import Selected Forms') }}
                        </el-button>
                    </div>
                    <br/>
                    
                    <el-pagination
                        background
                        :hide-on-single-page="true"
                        small
                        :page-size="per_page"
                        :current-page.sync="page_number"
                        layout="prev, pager, next"
                        :total="total">
                    </el-pagination>
                    <div v-if="migratedForms.length">
                        <h4>{{ $t('Imported Forms') }}</h4>
                        <el-table
                            max-height="400"
                            :data="migratedForms"
                            class="ff_migrator_response_table"
                            stripe
                            style="width: 100%">
                            
                            <el-table-column
                                prop="title"
                                :label="$t('Imported Form')">
                            </el-table-column>
                            
                            <el-table-column width="120" prop="edit_url" label="" align="right">
                                <template slot-scope="props">
                                    <a :href="props.row.edit_url"> {{ $t('Edit Form') }}</a>
                                </template>
                            </el-table-column>
                        </el-table>
                    
                    </div>
                    <div v-if="unSupportedFields.length" class="ff-error">
                        {{$t('The following fields are not supported, please create them manually.')}}
                        <b>{{unSupportedFields.join(', ')}} </b>
                    </div>
                    <div v-if="entryPageUrl">
                        <h4>{{ $t('Imported Form Entries') }}</h4>
                        <a :href="entryPageUrl"> {{ $t('View Entries') }}</a>
                    </div>
                
                </el-tabs>
            </div>
        
        </div>
    
    </div>

</template>

<script>
export default {
    name: "Migrator",
    data() {
        return {
            currentFormType: false,
            migratedForms: [],
            migratorData: [],
            forms: [],
            loading: false,
            page_number: 1,
            per_page: 20,
            total: 0,
            multipleSelection: [],
            entryPageUrl : false,
            unSupportedFields: [],
        }
    },
    methods: {
        getMigratorData() {
            this.loading = true;
            FluentFormsGlobal.$get({
                action: 'fluentform-migrator-get-migrator-data',
            })
                .then(response => {
                    if (response.status != true) {
                        this.$notify.error({
                            title: 'Error',
                            message: response.message,
                            offset: 30
                        });
                    }
                    this.migratorData = JSON.parse(JSON.stringify(response.migrator_data))
                })
                .fail(error => {
                    this.$showAjaxError(error)
                })
                .always(() => {
                    this.loading = false;
                    this.setActiveTab();
                    this.getForms();
                });
        },
        getForms() {
            this.loading = true;
            this.multipleSelection = [];
            FluentFormsGlobal.$post({
                action: 'fluentform-migrator-get-forms-by-key',
                form_type: this.currentFormType,
                page_number: this.page_number,
                per_page: this.per_page,
            })
                .then(response => {
                    this.forms = response.forms
                })
                .fail(error => {
                    this.$showAjaxError(error)
                })
                .always(() => {
                    this.loading = false;
                });
            
        },
        importForms(formIds = []) {
            this.loading = true;
            
            FluentFormsGlobal.$post({
                action: 'fluentform-migrator-import-forms',
                form_ids: formIds,
                form_type: this.currentFormType,
            })
                .then(response => {
                    
                    if (response.status == true) {
                        this.migratedForms = response.inserted_forms;
                        this.unSupportedFields = response.unsupported_fields
                        this.$notify.success({
                            title: 'Success',
                            message: response.message,
                            offset: 30
                        });
                        return;
                        
                    }
                    this.$notify.error({
                        title: 'Error',
                        message: response.message,
                        offset: 30
                    });
                    
                })
                .fail(error => {
                    this.$showAjaxError(error)
                })
                .always(() => {
                    this.loading = false;
                    this.getMigratorData();
                });
        },
        importEntries(importffId, otherFormId) {
            this.loading = true;
            FluentFormsGlobal.$post({
                action: 'fluentform-migrator-import-entries',
                imported_fluent_form_id: importffId,
                source_form_id: otherFormId,
                form_type: this.currentFormType,
            })
                .then(response => {
                    
                    if (response.status == true) {
                        
                        this.entryPageUrl = response.entries_page_url;
                        this.$notify.success({
                            title: 'Success',
                            message: response.message,
                            offset: 30
                        });
                        return;
                        
                    }
                    this.$notify.error({
                        title: 'Error',
                        message: response.message,
                        offset: 30
                    });
                    
                })
                .fail(error => {
                    this.$showAjaxError(error)
                })
                .always(() => {
                    this.loading = false;
                });
        },
        paginate(forms = []) {
            let page = this.page_number;
            let perPage = this.per_page;
            this.total = forms.length;
            let from = (page * perPage) - perPage;
            let to = (page * perPage);
            return forms.slice(from, to);
        },
        handleSelectionChange(val) {
            
            let selectedIds = [];
            for (let key in val) {
                if (val[key].id) {
                    selectedIds.push(val[key].id);
                }
            }
            this.multipleSelection = selectedIds;
        },
        setActiveTab() {
            if (this.currentFormType == '0' && this.migratorData[0]) {
                this.currentFormType = this.migratorData[0].key;
            }
        },
        $showAjaxError(error) {
            let message = 'Something is wrong when doing ajax request! Please try again';
            if (error.responseJSON && error.responseJSON.data.message) {
                message = error.responseJSON.data.message;
            } else if (error.responseJSON && error.responseJSON.message) {
                message = error.responseJSON.message;
            } else if (error.responseText) {
                message = error.responseText;
            }
            this.$notify.error({
                title: 'Error',
                message: message,
                offset: 30
            });
        }
    },
    computed: {
        listForms() {
            return this.paginate(this.forms);
        },
        entryImportSupported() {
            let supported = ['caldera', 'ninja_forms', 'gravityform', 'wpforms'];
            return supported.indexOf(this.currentFormType) !== -1
        },
    },
    mounted() {
        this.getMigratorData();
    }
}
</script>
