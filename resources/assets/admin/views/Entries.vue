<template>
    <div>
        <div class="ff_nav_top">
            <div class="ff_nav_title">
                <h3>{{$t('Entries')}}</h3>
                <div class="ff_nav_sub_actions">
                    <el-select
                            clearable
                            size="mini"
                            v-model="entry_type"
                            :placeholder="$t('All Types')"
                            @change="filterEntryType()"
                    >
                        <el-option
                                v-for="(status, status_key) in entry_statuses"
                                :key="status_key"
                                :value="status_key"
                                :label="status"
                        >
                            {{status}} <span v-show="counts[status_key]">({{counts[status_key]}})</span>
                        </el-option>
                    </el-select>
                    <el-select
                            v-if="has_payment"
                            style="min-width: 300px"
                            clearable
                            size="mini"
                            multiple
                            v-model="selectedPaymentStatuses"
                            :placeholder="$t('All Payments')"
                            @change="filterPaymentStatuses()"
                    >
                        <el-option
                                v-for="(status, status_key) in payment_statuses"
                                :key="status_key"
                                :value="status_key"
                                :label="status"
                        >
                            {{status}}
                        </el-option>
                    </el-select>
                </div>
            </div>
            <div class="ff_nav_action">

                <el-button @click="gotoVisualReport()" type="primary" size="mini">
                    <span
                            style="line-height: 10px;width: auto;height: auto;font-size: 14px;"
                            class="dashicons dashicons-chart-pie"
                    ></span> {{ $t('View Visual Report') }}
                </el-button>

                <el-dropdown
                        size="mini"
                        @command="handleSwitchForm"
                        split-button type="default"
                        class="current_form_name"
                >
                  <span class="el-dropdown-link">
                    {{ current_form_title }}
                  </span>
                    <el-dropdown-menu slot="dropdown" style="max-height:300px; overflow-y:scroll;">
                        <el-dropdown-item
                                v-for="form in forms"
                                :key="'form_switch_'+form.id"
                                :command="form.id"
                                :disabled="form.id == form_id"
                        >{{ form.title }}
                        </el-dropdown-item>
                    </el-dropdown-menu>
                </el-dropdown>


                <el-dropdown

                        size="mini"
                        split-button type="default"
                        class="current_form_name"
                        :hide-on-click="false"
                >

                  <span class="el-dropdown-link">
                        {{$t('Columns')}}
                  </span>
                    <el-dropdown-menu slot="dropdown" style="max-height:300px; overflow-y:scroll;" >

                        <el-dropdown-item
                                v-for="(column, column_name) in columns"
                                :key="column_name"

                        >
                            <el-checkbox @change="handleColumnChange" :key="column" :label="column_name"  v-model="visibleColumns" >
                                {{ column }}
                            </el-checkbox>

                        </el-dropdown-item>

                        <el-dropdown-item
                                key="column_order"
                                command="column_order"
                        >
                            <el-button @click="visibleColReorderModal =true" style="width:100%;"  type="primary" size="mini">
                                 {{ $t('Reorder Column') }}
                            </el-button>

                        </el-dropdown-item>

                    </el-dropdown-menu>
                </el-dropdown>

            </div>
        </div>

        <el-dialog   :visible.sync="visibleColReorderModal" :title=" $t('Change Column Display Order') " >
            <ColumnDragAndDrop
                    :columns="columns"
                    :columns_order ="columnsOrder"
                    :form_id="form_id"
                    :visible_columns="visibleColumns" >

            </ColumnDragAndDrop>
        </el-dialog>

        <el-alert
                v-if="autoDeleteStatus"
                :title="$t('Auto delete entry on form submission is enabled! No new entry data will be saved for this form.')"
                :description="$t('You can disable the auto delete option from Settings & Integrations Tab')"
                type="error">
        </el-alert>

        <hr>
        <div v-loading="loading"
             :element-loading-text="$t('Loading Entries...')"
             style="min-height: 60px;"
             class="entries_table">

            <div class="ff_nav_top">
                <div class="ff_nav_title">
                    <template v-if="entrySelections.length">
                        <label for="bulk-action-selector-top" class="screen-reader-text">
                            {{ $t('Select bulk action') }}
                        </label>
                        <el-select
                                clearable
                                placeholder="Bulk Actions"
                                id="bulk-action-selector-top"
                                name="action"
                                popper-class="el-big-items"
                                v-model="bulkAction"
                                size="small"
                        >
                            <el-option-group
                                    v-for="(group,groupKey) in bulkActions"
                                    :key="groupKey"
                                    :label="groupKey">
                                <el-option
                                        v-for="item in group"
                                        :key="item.action"
                                        :label="item.label"
                                        :value="item.action">
                                </el-option>
                            </el-option-group>
                        </el-select>
                        <el-button
                                type="primary"
                                size="small"
                                @click.prevent="handleBulkAction"
                        >{{ $t('Apply') }}
                        </el-button>
                    </template>
                </div>

                <div class="ff_nav_action pull-right">
                    <div class="ff_search_inline">
                        <label for="search_bar" class="screen-reader-text">
                            {{ $t('Search Entry') }}
                        </label>
                        <el-input
                                v-on:keyup.enter.native="handleSearch"
                                size="mini"
                                :placeholder="$t('Search')"
                                v-model="search_string"
                        >
                            <el-button
                                    slot="append"
                                    icon="el-icon-search"
                                    v-on:click.prevent="handleSearch"
                            />
                        </el-input>
                    </div>

                    <el-dropdown @command="exportEntries">
                        <el-button type="info" size="mini">
                            {{$t('Export')}} <i class="el-icon-arrow-down el-icon--right"></i>
                        </el-button>
                        <el-dropdown-menu slot="dropdown">
                            <el-dropdown-item command="csv">{{ $t('Export as') }} CSV</el-dropdown-item>
                            <el-dropdown-item command="xlsx">{{ $t('Export as') }} Excel (xlsx)</el-dropdown-item>
                            <el-dropdown-item command="ods">{{ $t('Export as') }} ODS</el-dropdown-item>
                            <el-dropdown-item command="json">{{ $t('Export as') }} JSON Data</el-dropdown-item>
                        </el-dropdown-menu>
                    </el-dropdown>
                    <el-button @click="advancedFilter = true" size="mini">{{$t('Advanced Filter')}}</el-button>
                </div>
            </div>

            <div v-if="advancedFilter" class="ff_nav_top ff_advanced_search">
                <div class="widget_title">
                    {{$t('Filter By Date Range')}}
                    <el-date-picker
                            size="mini"
                            v-model="filter_date_range"
                            type="daterange"
                            @change="getData()"
                            :picker-options="pickerOptions"
                            format="dd MMM, yyyy"
                            value-format="yyyy-MM-dd"
                            range-separator="-"
                            :start-placeholder="$t('Start date')"
                            :end-placeholder="$t('End date')">
                    </el-date-picker>
                    <el-button @click="getData" size="mini" type="success">{{ $t('Search') }}</el-button>
                    <el-button @click="resetAdvancedFilter()" size="mini">{{ $t('Hide') }}</el-button>
                </div>
                <div style="margin-top: 20px" class="widget-title">
                    <el-checkbox @change="getData()" true-label="yes" false-label="no" v-model="show_favorites">
                        {{$t('Show Favorites Entries only')}}
                    </el-checkbox>
                </div>
            </div>

            <el-table
                    :data="entries"
                    :stripe="true"
                    :class="{'compact': isCompact}"
                    @sort-change="handleTableSort"
                    @selection-change="handleSelectionChange">

                <el-table-column
                        type="selection"
                        fixed
                        width="40">
                </el-table-column>

                <el-table-column
                        label="#"
                        sortable="custom"
                        prop="id"
                        width="90px"
                >
                    <template slot-scope="scope">
                        <div class="has_hover_item">
                            <router-link :to="{
                                    name: 'form-entry',
                                    params: {
                                        form_id: scope.row.form_id,
                                        entry_id: scope.row.id
                                    },
                                    query: {
                                        sort_by: sort_by,
                                        current_page: paginate.current_page,
                                        pos: scope.$index,
                                        type: entry_type
                                    }
                                }">
                                {{ scope.row.serial_number }}
                            </router-link>
                            <div v-if="scope.row.status != 'trashed'" class="show_on_hover inline_actions">
                                    <span @click="changeFavorite(scope.row.id, scope.$index, 0)"
                                          :title="$t('Remove from Favorites')" v-if="scope.row.is_favourite != '0'"
                                          class="el-icon-star-on action_button"></span>
                                <span @click="changeFavorite(scope.row.id, scope.$index, 1)"
                                      :title="$t('Mark as Favorite')" v-else=""
                                      class="el-icon-star-off action_button"></span>
                                <span @click="changeStatus(scope.row.id, scope.$index, 'unread')"
                                      :title="$t('Mark as Unread')" v-if="scope.row.status == 'read'"
                                      class="el-icon-circle-check action_button"></span>
                                <span @click="changeStatus(scope.row.id, scope.$index, 'read')" :title="$t('Mark as Read')"
                                      v-else="" class="el-icon-circle-check-outline action_button"></span>
                            </div>

                            <div class="inline_actions inline_item" v-else>
                                    <span @click="restoreEntry(scope.row.id, scope.$index)" title="Restore"
                                          class="el-icon-circle-check-outline action_button">{{ $t('Restore') }}</span>
                            </div>
                        </div>
                    </template>
                </el-table-column>

                <el-table-column
                        v-for="(column, column_name) in formattedColumn"
                        v-if="visibleColumns.includes(column_name)"
                        :label="column"
                        :show-overflow-tooltip="isCompact"
                        min-width="200"
                        :key="column_name">
                    <template slot-scope="scope">
                        <span v-html="scope.row.user_inputs[column_name]"></span>
                    </template>
                </el-table-column>

                <el-table-column
                        label="Entry Status"
                        width="120px">
                    <template slot-scope="scope">
                        {{ getStatusName(scope.row.status) }}
                    </template>
                </el-table-column>

                <template v-if="has_payment">
                    <el-table-column
                            :label="$t('Amount')"
                            min-width="120px">
                        <template slot-scope="scope">
                            <span v-html="formatMoney(scope.row.payment_total, scope.row.currency)"></span>
                        </template>
                    </el-table-column>
                    <el-table-column
                            :label="$t('Payment Status')"
                            min-width="120px">
                        <template slot-scope="scope">
                            <span class="ff_pay_status_badge" 
                                  :class="'ff_pay_status_'+scope.row.payment_status"
                                  v-if="scope.row.payment_status"
                            >
                                {{ scope.row.payment_status }}
                            </span>
                        </template>
                    </el-table-column>
                    <el-table-column
                            :label="$t('Payment Method')"
                            min-width="120px">
                        <template slot-scope="scope">
                            <span class="ff_card_badge" v-if="scope.row.payment_method">
                                {{ getPaymentMethodName(scope.row.payment_method) }}
                            </span>
                        </template>
                    </el-table-column>
                </template>

                <el-table-column
                        :label="$t('Submitted at')"
                        width="120px">
                    <template slot-scope="scope">
                        {{ dateFormat(scope.row.created_at) }}
                    </template>
                </el-table-column>

                <el-table-column
                        fixed="right"
                        :label="$t('Actions')"
                        :width="115"
                        align="center"
                >
                    <template slot-scope="scope">
                        <el-button-group>
                            <router-link :to="{
                                    name: 'form-entry',
                                    params: {
                                        form_id: scope.row.form_id,
                                        entry_id: scope.row.id
                                    },
                                    query: {
                                        sort_by: sort_by,
                                        current_page: paginate.current_page,
                                        pos: scope.$index,
                                        type: entry_type
                                    }
                                }">
                                <el-button type="primary" icon="el-icon-view" size="mini"></el-button>
                            </router-link>
                            
                            <remove 
                                v-if="hasPermission('fluentform_manage_entries')"
                                icon="el-icon-delete" 
                                @on-confirm="removeEntry(scope.row.id)"
                            />
                        </el-button-group>
                    </template>
                </el-table-column>
            </el-table>

            <div class="tablenav bottom">
                <div class="alignleft actions bulkactions">
                    <email-resend v-if="entrySelections.length" :btn_text="$t('Bulk Resend Notifications')" :entry_ids="selection_ids" :form_id="form_id"></email-resend>
                    <el-checkbox class="compact_input" v-model="isCompact">{{ $t('Compact View') }}</el-checkbox>
                </div>
                <div class="pull-right">
                    <el-pagination
                            @size-change="handleSizeChange"
                            @current-change="goToPage"
                            :current-page.sync="paginate.current_page"
                            :page-sizes="[5, 10, 20, 50, 100]"
                            :page-size="parseInt(paginate.per_page)"
                            layout="total, sizes, prev, pager, next, jumper"
                            :total="paginate.total">
                    </el-pagination>
                </div>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
    import remove from '../components/confirmRemove'
    import moment from 'moment';
    import each from 'lodash/each';
    import EmailResend from './Helpers/_ResentEmailNotification'
    import ColumnDragAndDrop from "./ColumnDragAndDrop";

    export default {
        name: 'FormEntries',
        props: ['form_id', 'has_pdf'],
        components: {
            remove,
            EmailResend,
            ColumnDragAndDrop
        },
        watch: {
            search_string() {
                if (!this.search_string.length) {
                    this.getData();
                }
            }
        },
        data() {
            return {
                loading: true,
                entry_type: this.$route.query.type || '',
                sort_by: this.$route.query.sort_by || "DESC",
                selectedPaymentStatuses: [],
                entries: [],
                entrySelections: [],
                columns: [],
                bulkAction: '',
                paginate: {
                    total: 0,
                    current_page: parseInt(this.$route.query.page) || 1,
                    last_page: 1,
                    per_page: localStorage.getItem('entriesPerPage') || 20
                },
                search_string: '',
                forms: window.fluent_form_entries_vars.forms,
                current_form_title: window.fluent_form_entries_vars.current_form_title,
                counts: {},
                no_found_text: window.fluent_form_entries_vars.no_found_text,
                entry_statuses: window.fluent_form_entries_vars.entry_statuses,
                payment_statuses: window.fluent_form_entries_vars.payment_statuses,
                has_payment: !!window.fluent_form_entries_vars.has_payment,
                isCompact: true,
                advancedFilter: false,
                filter_date_range: ['', ''],
                autoDeleteStatus: window.fluent_form_entries_vars.enabled_auto_delete,
                pickerOptions: {
                    disabledDate(time) {
                        return time.getTime() >= Date.now();
                    },
                    shortcuts: [
                        {
                            text: 'Today',
                            onClick(picker) {
                                const start = new Date();
                                picker.$emit('pick', [start, start]);
                            }
                        },
                        {
                            text: 'Yesterday',
                            onClick(picker) {
                                const start = new Date();
                                start.setTime(start.getTime() - 3600 * 1000 * 24 * 1);
                                picker.$emit('pick', [start, start]);
                            }
                        },
                        {
                            text: 'Last week',
                            onClick(picker) {
                                const end = new Date();
                                const start = new Date();
                                start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
                                picker.$emit('pick', [start, end]);
                            }
                        }, {
                            text: 'Last month',
                            onClick(picker) {
                                const end = new Date();
                                const start = new Date();
                                start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
                                picker.$emit('pick', [start, end]);
                            }
                        }
                    ]
                },
                show_favorites: 'no',
                available_pdf: null,
                pdf_dropdown: null,
                visibleColReorderModal: false,
                visibleColumns: null,
                columnsOrder: null,
            }
        },
        computed: {
            bulkActions() {
                let bulk_actions = {
                    'statuses': [],
                    'other': [
                        {
                            label: this.$t('Mark as Favorite'),
                            action: 'other.make_favorite'
                        },
                        {
                            label: this.$t('Remove from Favorite'),
                            action: 'other.unmark_favorite'
                        }
                    ]
                };

                if (this.hasPermission('fluentform_manage_entries')) {
                    bulk_actions['other'].push(
                        {
                            label: this.$t('Delete Permanently'),
                            action: 'other.delete_permanently'
                        }
                    );
                }

                each(this.entry_statuses, (status_name, status_key) => {
                    if (this.entry_type != status_key) {
                        bulk_actions.statuses.push({
                            label: 'Mark as ' + status_name,
                            action: status_key
                        });
                    }
                });

                return bulk_actions;
            },
            selection_ids() {
                let selectedEntries = [];

                this.entrySelections.forEach(function (element) {
                    selectedEntries.push(element.id);
                });
                return selectedEntries;
            },
            formattedColumn(){
                // if null, display order is not set now set default column data
                if( this.columnsOrder === null){
                    return this.columns;
                }
                // if column display order is set
                let columns = this.columnsOrder;
                let array = {};
                each(columns, (key, index) => {
                    array[key.value] = key.label;
                });
                return array;
            }
        },
        methods: {
            getStatusName(status) {
                if (this.entry_statuses[status]) {
                    return this.entry_statuses[status];
                }
                return status;
            },
            setDefaultPaginate() {
                this.paginate = {
                    total: 0,
                    current_page: 1,
                    last_page: 1,
                    per_page: localStorage.getItem('entriesPerPage') || 20
                }
            },
            getEntryCounts() {
                let data = {
                    action: 'fluentform-form-entry-counts',
                    form_id: this.form_id
                };
                FluentFormsGlobal.$get(data)
                    .then((response) => {
                        this.counts = response.data.counts;
                    })
                    .fail((error) => {

                    });
            },
            getData() {
                let data = {
                    action: 'fluentform-form-entries',
                    form_id: this.form_id,
                    entry_type: this.entry_type,
                    current_page: this.paginate.current_page,
                    per_page: this.paginate.per_page,
                    search: this.search_string,
                    sort_by: this.sort_by,
                    payment_statuses: this.selectedPaymentStatuses
                };

                if (this.advancedFilter) {
                    data.date_range = this.filter_date_range;
                    if (this.show_favorites == 'yes') {
                        data.entry_type = 'favorite';
                    }
                    data.show_favorites = this.show_favorites;
                }

                this.loading = true;
                FluentFormsGlobal.$get(data)
                    .then((response) => {
                        this.entries = response.data.submissions.data;
                        this.paginate = response.data.submissions.paginate;
                        this.columns = response.data.labels;

                        this.visibleColumns = response.data.visible_columns ;
                        this.columnsOrder = response.data.columns_order ;

                        this.resetUrlParams();
                    })
                    .fail((error) => {

                    })
                    .always(() => {
                        this.getVisibleColumns();
                        this.loading = false;
                    });
            },
            handleTableSort(column) {
                if (column.order) {
                    if (column.prop === 'id') {
                        this.sort_by = (column.order === 'ascending') ? 'ASC' : 'DESC';
                        this.getData();
                    }
                }
            },
            handleSelectionChange(val) {
                this.entrySelections = val;
            },
            removeEntry(entryId) {
                let action = 'fluentform-change-entry-status';
                if (this.entry_type === 'trashed') {
                    action = 'fluentform-delete-entry';
                }
                let data = {
                    action: action,
                    form_id: this.form_id,
                    entry_id: entryId,
                    status: 'trashed'
                };

                FluentFormsGlobal.$post(data)
                    .then(response => {
                        this.$notify({
                            title: 'Success',
                            message: response.data.message,
                            type: 'success',
                            offset: 30
                        });
                        this.getData();
                        this.getEntryCounts();
                    })
                    .fail(error => {
                        console.log(error);
                    });
            },
            handleBulkAction() {
                if (this.bulkAction) {
                    let actionType = this.bulkAction;
                    this.operationOnSelectedEntries(actionType);
                }
            },
            operationOnSelectedEntries(actionType) {
                let selectedEntries = [];

                this.entrySelections.forEach(function (element) {
                    selectedEntries.push(element.id);
                });

                let data = {
                    action: 'fluentform-do_entry_bulk_actions',
                    form_id: this.form_id,
                    entries: selectedEntries,
                    action_type: actionType
                };

                FluentFormsGlobal.$post(data)
                    .then(response => {
                        this.$notify.success({
                            title: 'Success',
                            message: response.data.message,
                            offset: 30
                        });
                        this.getData();
                        this.getEntryCounts();
                    })
                    .fail(error => {
                        this.$notify.error({
                            title: 'Error',
                            message: error.responseJSON.data.message,
                            offset: 30
                        });
                        console.log(error);
                    });

            },
            goToPage(value) {
                this.paginate.current_page = value;
                this.getData();
            },
            handleSizeChange(val) {
                this.paginate.per_page = val;
                localStorage.setItem('entriesPerPage', val);
                this.getData();
            },
            filterEntryType() {
                this.bulkAction = '';
                this.search_string = '';
                this.setDefaultPaginate();
                this.getData();
            },
            filterPaymentStatuses() {
                this.bulkAction = '';
                this.search_string = '';
                this.setDefaultPaginate();
                this.getData();
            },
            handleSearch() {
                this.setDefaultPaginate();
                this.getData();
            },
            resetUrlParams() {
                this.$router.push({
                    name: 'form-entries',
                    params: {
                        form_id: this.form_id
                    },
                    query: {
                        sort_by: this.sort_by,
                        type: this.entry_type,
                        page: this.paginate.current_page
                    }
                });
            },
            changeFavorite(entry_id, index, is_favourite) {
                let data = {
                    action: 'fluentform-change-entry-favorites',
                    form_id: this.form_id,
                    entry_id: entry_id,
                    is_favourite: is_favourite
                };

                FluentFormsGlobal.$post(data)
                    .then(response => {
                        this.entries[index].is_favourite = response.data.is_favourite;
                        this.getEntryCounts();
                    })
                    .fail(error => {
                        console.log(error);
                    });
            },
            changeStatus(entry_id, index, status) {
                let data = {
                    action: 'fluentform-change-entry-status',
                    form_id: this.form_id,
                    entry_id: entry_id,
                    status: status
                };

                FluentFormsGlobal.$post(data)
                    .then(response => {
                        this.entries[index].status = response.data.status;
                        this.getEntryCounts();
                    })
                    .fail(error => {
                        console.log(error);
                    });
            },
            restoreEntry(entry_id, index) {
                let data = {
                    action: 'fluentform-change-entry-status',
                    form_id: this.form_id,
                    entry_id: entry_id,
                    status: 'read'
                };

                FluentFormsGlobal.$post(data)
                    .then(response => {
                        this.entries.splice(index, 1);
                        this.getEntryCounts();
                        this.$notify({
                            type: 'success',
                            title: 'Success',
                            message: this.$t('Entry has been restored'),
                            offset: 30
                        });
                    })
                    .fail(error => {
                        console.log(error);
                    });
            },
            handleSwitchForm(formId) {
                window.location.href = window.fluent_form_entries_vars.entries_url_base + formId;
            },
            exportEntries(format = 'csv') {

                let selectedEntries = [];

                this.entrySelections.forEach(function (element) {
                    selectedEntries.push(element.id);
                });

                let data = {
                    action: 'fluentform-form-entries-export',
                    form_id: this.form_id,
                    format: format,
                    entry_type: this.entry_type,
                    entries: selectedEntries,
                    sort_by: this.sort_by,
                    search: this.search_string,
                    payment_statuses: this.selectedPaymentStatuses,
                    fluent_forms_admin_nonce: window.fluent_forms_global_var.fluent_forms_admin_nonce
                };
                if (this.advancedFilter) {
                    data.date_range = this.filter_date_range;
                    data.is_favourite = this.show_favorites;
                }
                let url = ajaxurl + '?' + jQuery.param(data);
                location.href = url;
            },
            dateFormat(date, format) {
                if (!format) {
                    format = 'MMM DD, YYYY';
                }
                let dateString = (date === undefined) ? null : date;
                let dateObj = moment(dateString);
                return dateObj.isValid() ? dateObj.format(format) : null;
            },
            resetAdvancedFilter() {
                this.advancedFilter = false;
                this.getData();
            },
            gotoVisualReport() {
                this.$router.push({
                    name: 'form-reports'
                });
            },
            getPaymentMethodName(status) {
                if(status == 'test') {
                    return 'Offline';
                }
                return status;
            },
            handleColumnChange(){
                let data = {
                    action : 'fluentform-save-form-entry_column_view_settings',
                    form_id : this.form_id,
                    visible_columns : JSON.stringify(this.visibleColumns)
                };
                FluentFormsGlobal.$post(data)
                    .then(response => {
                    })
                    .fail(error => {
                        console.log(error);
                    });
            },
            getVisibleColumns(){
                if(this.visibleColumns === null){
                    //visibleColumns is not set initially so set all columns to visible
                    this.visibleColumns = Object.keys(this.columns);
                }
            },
        },
        mounted() {
            this.getData();
            this.getEntryCounts();
            this.filter_date_range = [moment().format('YYYY-MM-DD'), moment().format('YYYY-MM-DD')];
            (new ClipboardJS('.copy')).on('success', (e) => {
                this.$copy();
            });
        },
        beforeCreate() {
            ffEntriesEvents.$emit('change-title', 'All Entries');
        }
    };
</script>

