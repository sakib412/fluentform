<template>
    <div>
        <el-row>
            <el-col :sm="12">
                <div style="display: inline-block; margin-right: 20px; font-size: 23px;"
                     class="wp-heading-inline">
                    {{ $t('Forms') }}
                </div>
                <div style="display: inline-block;  padding-right: 20px;" class="ff_nav_sub_actions">
                <el-select
                    clearable
                    size="mini"
                    v-model="filter_by"
                    :placeholder="$t('All Types')"
                    @change="filterFormType()"
                >
                  <el-option
                      v-for="(status, status_key) in form_statuses"
                      :key="status_key"
                      :value="status_key"
                      :label="status"
                  >
                    {{status}}
                  </el-option>
                </el-select>
              </div>

                <el-dropdown
                    v-if="hasPermission('fluentform_forms_manager')"
                    split-button
                    size="small"
                    type="primary"
                    trigger="click"
                    style="display:inline-block; margin: 12px 0;"
                    @click="showAddFormModal = true"
                    @command="createForm"
                >
                    {{ $t('Add a New Form') }}

                    <!-- The split dropdown for creating predefined forms easily -->
                    <el-dropdown-menu slot="dropdown" style="top:25px !important;">
                        <el-dropdown-item
                            :command="{ key: 'conversational', form: { title:'Conversational Form', type: 'blank_conversational' }}">
                            {{$t('Create Conversational Form')}}
                        </el-dropdown-item>
                        <el-dropdown-item
                            v-if="has_post_feature"
                            :key="key"
                            :command="{key, form}"
                            v-for="(form, key) in predefinedDropDownForms"
                        >
                            {{$t('Create')}} {{ form.title }}
                        </el-dropdown-item>
                    </el-dropdown-menu>
                </el-dropdown>
            </el-col>

            <el-col :sm="12">
                <div class="text-right">
                    <el-row>
                        <el-col :sm="{ span: 14, offset: 10 }">
                            <el-form @submit.native.prevent="searchForms" style="margin-bottom: 12px; ">
                                <el-input
                                    clearable
                                    @clear="refetchItems"
                                    size="small"
                                    v-model="searchFormsKeyWord"
                                    :placeholder="$t('Search Forms')"
                                >
                                    <el-button native-type="submit" slot="append">{{$t('Search')}}</el-button>
                                </el-input>
                            </el-form>
                        </el-col>
                        <el-col :sm="{span:12, offset:12}">
                          <el-button @click="advancedFilter = true"  size="mini">{{$t('Advanced Filter')}}</el-button>
                        </el-col>
                    </el-row>
                </div>
            </el-col>
        </el-row>
        <hr/>
        <div class="ff_forms_table">
            <template v-if="app.formsCount > 0">
                <div
                    class="entries_table">
                    <div class="tablenav top">
                      <div v-if="advancedFilter" class="ff_nav_top ff_advanced_search">
                        <div class="widget_title">
                          {{$t('Filter By Date Range')}}
                          <el-date-picker
                              size="mini"
                              v-model="filter_date_range"
                              type="daterange"
                              @change="fetchItems()"
                              :picker-options="pickerOptions"
                              format="dd MMM, yyyy"
                              value-format="yyyy-MM-dd"
                              range-separator="-"
                              :start-placeholder="$t('Start date')"
                              :end-placeholder="$t('End date')">
                          </el-date-picker>
                          <el-button @click="fetchItems()" size="mini" type="primary" plain style="margin-left: 10px;">
                            {{ $t('Search') }}
                          </el-button>
                          <el-button @click="resetAdvancedFilter()" size="mini">
                            {{ $t('Hide') }}
                          </el-button>
                        </div>
                      </div>

                      <el-table
                            v-loading="loading"
                            :element-loading-text="$t('Loading Forms...')"
                            :data="items"
                            :stripe="true"
                            @sort-change="handleTableSort"
                            @selection-change="handleSelectionChange"
                            :row-class-name="tableRowClass">

                            <el-table-column sortable="custom" :label="$t('ID')" prop="id" width="60"></el-table-column>

                            <el-table-column sortable="custom" :label="$t('Title')" prop="title" min-width="230">
                                <template slot-scope="scope">
                                    <strong>
                                        {{ scope.row.title }}
                                    </strong>
                                    <span v-show="scope.row.has_payment == '1'" class="el-icon el-icon-money"></span>
                                    <div class="row-actions">
                                        <template v-if="hasPermission('fluentform_forms_manager')">
                                            <span class="ff_edit">
                                                <a :href="scope.row.edit_url"> {{ $t('Edit') }}</a> |
                                            </span>
                                            <span class="ff_edit">
                                                <a :href="scope.row.settings_url"> {{ $t('Settings') }}</a> |
                                            </span>
                                        </template>
                                        <span v-if="hasPermission('fluentform_entries_viewer')" class="ff_entries">
                                             <a :href="scope.row.entries_url"> {{ $t('Entries') }}</a> |
                                        </span>
                                        <span v-if="scope.row.conversion_preview" class="ff_entries">
                                             <a target="_blank" :href="scope.row.conversion_preview"> {{ $t('Conversational Preview') }}</a> |
                                        </span>
                                        <span class="ff_entries">
                                             <a target="_blank" :href="scope.row.preview_url"> {{ $t('Preview') }}</a> |
                                        </span>


                                        <template v-if="hasPermission('fluentform_forms_manager')">
                                            <span class="ff_duplicate">
                                                <a href="#" @click.prevent="duplicateForm(scope.row.id)"> {{
                                                        $t('Duplicate')
                                                    }}</a> |
                                            </span>
                                            <span class="ff_entries">
                                              <a href="#" @click.stop.prevent="findFormLocations(scope.row.id)">
                                                  {{ $t('Find') }}
                                                  <i class="el-icon-loading"  v-if="loadingLocations"></i>
                                              </a> |
                                            </span>
                                            <span class="trash">
                                                <remove @on-confirm="removeForm(scope.row.id, scope.$index)">
                                                    <a slot="icon">{{ $t('Delete') }}</a>
                                                </remove>
                                            </span> |
                                            <el-switch 
                                                active-color="#13ce66" 
                                                :active-text="$t(scope.row.status === 'published' ? 'Active' : 'Inactive')"
                                                @change="toggleStatus(scope.row.id, scope.row.title, scope.row.status)" 
                                                size="mini" 
                                                active-value="published" 
                                                inactive-value="unpublished" 
                                                v-model="scope.row.status" 
                                            />

                                        </template>
                                        <div class="form-locations" v-if="Object.keys(formLocations).includes(scope.row.id) && formLocations[scope.row.id].length >= 1">
                                            {{$t('Found in')}}
                                            <ul class="ff_inline_list">
                                                <li v-for="location in formLocations[scope.row.id] " >
                                                    <a :href="location.edit_link">
                                                        <code class="item ">{{location.title}}</code>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div v-if="Object.keys(formLocations).includes(scope.row.id) && formLocations[scope.row.id].length == 0 ">
                                            {{$t('Could not find anywhere')}}
                                        </div>
                                    </div>



                                </template>
                            </el-table-column>

                            <el-table-column :label="$t('Short Code')" width="280">
                                <template slot-scope="scope">
                                    <el-tooltip class="item classic_shortcode shortcode_btn"
                                                effect="dark"
                                                :content="$t('Click to copy shortcode')"
                                                :title="$t('Click to copy shortcode')"
                                                placement="top">
                                        <code class="copy"
                                              :data-clipboard-text='`[fluentform id="${scope.row.id}"]`'>
                                            <i class="el-icon-document"></i> [fluentform id="{{ scope.row.id }}"]
                                        </code>
                                    </el-tooltip>
                                    <el-tooltip class="item conversational_shortcode shortcode_btn"
                                                effect="dark"
                                                :content="$t('Click to copy shortcode')"
                                                :title="$t('Click to copy shortcode')"
                                                v-if="scope.row.conversion_preview"
                                                placement="top">
                                        <code class="copy"
                                              :data-clipboard-text='`[fluentform type="conversational" id="${scope.row.id}"]`'>
                                            <i class="el-icon-document"></i> [fluentform type="conversational" id="{{
                                            scope.row.id }}"]
                                        </code>
                                    </el-tooltip>
                                </template>

                            </el-table-column>

                            <el-table-column width="150" :label="$t('Entries')">
                                <template slot-scope="scope">
                                    <a :href="scope.row.entries_url"><span
                                        v-show="scope.row.unread_count">{{ scope.row.unread_count }} / </span>{{
                                        scope.row.total_Submissions
                                        }}</a>
                                </template>
                            </el-table-column>

                            <el-table-column v-if="!isDisabledAnalytics" width="80" :label="$t('Views')">
                                <template slot-scope="scope">
                                    {{ scope.row.total_views }}
                                </template>
                            </el-table-column>

                            <el-table-column v-if="!isDisabledAnalytics" width="120" :label="$t('Conversion')">
                                <template slot-scope="scope">
                                    {{ scope.row.conversion }}%
                                </template>
                            </el-table-column>
                        </el-table>

                        <div class="tablenav bottom">
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
            <div v-else>
                <div class="fluent_form_intro">
                    <h1 class="text-center">
                        {{ $t('Welcome to WP Fluent Froms') }}
                    </h1>
                    <p class="text-center">
                        {{ $t('Thank you for installing WP Fluent Froms - The Most Advanced Form Builder Plugin for WordPress') }}
                    </p>
                    <div class="text-center">
                        <el-button
                            round
                            type="primary"
                            @click="showAddFormModal = true"
                        >
                          {{$t('Click Here to Create Your First Form')}}
                        </el-button>
                    </div>
                </div>
                <div class="fluent_form_intro_video">
                    <h2>{{$t('Check the Video Intro')}}</h2>
                    <div class="videoWrapper">
                        <iframe width="1237" height="696" src="https://www.youtube.com/embed/AqVr0l1JrGE"
                                frameborder="0"
                                allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>

        <predefinedFormsModal
            v-if="hasPermission('fluentform_forms_manager')"
            ref="predefinedFormsModal"
            v-show="showAddFormModal"
            :categories="categories"
            :predefinedForms="predefinedForms"
            :visibility.sync="showAddFormModal"
        />

        <PostTypeSelectionModal
            v-if="has_post_feature && hasPermission('fluentform_forms_manager')"
            @on-post-type-selction-end="onPostTypeSelctionEnd"
            :postTypeSelectionDialogVisibility="postTypeSelectionDialogVisibility"
        />
    </div>
</template>

<script type="text/babel">
import Clipboard from 'clipboard';
import remove from '../components/confirmRemove'
import AddFormModal from '../components/modals/AddFormModal';
import predefinedFormsModal from '../components/modals/predefinedFormsModal';
import PostTypeSelectionModal from '../components/modals/PostTypeSelectionModal';
import moment from "moment";

export default {
    name: 'AllForms',
    components: {
        predefinedFormsModal,
        AddFormModal,
        remove,
        PostTypeSelectionModal
    },
    data() {
        return {
            has_post_feature: !!window.FluentFormApp.has_post_feature,
            app: window.FluentFormApp,
            paginate: {
                total: 0,
                current_page: 1,
                last_page: 1,
                per_page: localStorage.getItem('formItemsPerPage') || 10
            },
            loading: true,
            items: [],
            predefinedForms: {},
            predefinedDropDownForms: false,
            categories: [],
            search_string: '',
            selectAll: 0,
            showAddFormModal: false,
            checkedItems: [],
            showSelectFormModal: false,
            advancedFilter: false,
            filter_date_range: ['', ''],
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
            filter_by:'all',
            form_statuses: {all: this.$t('All'), published: this.$t('Active'), unpublished: this.$t('Inactive'), is_payment: this.$t('Payment Form'), post: this.$t('Post Form'), conv_form: this.$t('Conversational Form'), step_form: this.$t('Step Form')},
            searchFormsKeyWord: '',
            clearingSearchKeyword: false,
            postTypeSelectionDialogVisibility: false,
            isDisabledAnalytics: !!window.FluentFormApp.isDisableAnalytics,
            sort_column: 'id',
            sort_by: 'DESC',
            formLocations: {},
            loadingLocations: false,
        }
    },
    methods: {
        toggleStatus(id,title,status){
            this.loading = true;
    
            let data = {
                action: 'fluentform-form-update',
                title: title,
                formId: id,
                status: status,
            };
    
            FluentFormsGlobal.$post(data)
                .then((response) => {
                    this.$success(response.message);
                })
                .fail(error => {
                    this.$fail(this.$t('Something went wrong, please try again.'));
                })
                .always(() => {
                    this.loading = false;
                });
        },
        goToPage(val) {
            jQuery('html, body').animate({scrollTop: 0}, 300).promise().then(elements => {
                this.fetchItems(
                    this.paginate.current_page = val
                );
            });
        },
        handleSizeChange(val) {
            localStorage.setItem('formItemsPerPage', val);
            this.paginate.per_page = val;
            this.fetchItems();
        },
        fetchItems() {
            this.loading = true;
            let data = {
                search: this.searchFormsKeyWord,
                action: 'fluentform-forms',
                filter_by: this.filter_by,
                per_page: this.paginate.per_page,
                page: this.paginate.current_page,
                sort_column: this.sort_column,
                sort_by: this.sort_by
            };
            if (this.advancedFilter) {
              data.date_range = this.filter_date_range;
            }
            FluentFormsGlobal.$get(data)
                .done((response) => {
                    this.items = response.data;
                    this.paginate.total = response.total;
                    this.paginate.current_page = response.current_page;
                    this.paginate.last_page = response.last_page;
                })
                .fail(error => {
                    this.$fail(this.$t('Something went wrong, please try again.'));
                })
                .always(() => {
                    this.loading = false;
                });
        },
        refetchItems() {
            this.paginate.current_page = 1;
            this.clearingSearchKeyword = true;
            this.fetchItems();
            this.$nextTick(() => {
                this.clearingSearchKeyword = false;
            });
        },
        getPredefinedForms() {
            this.loading = true;

            FluentFormsGlobal.$get({
                action: 'fluentform-predefined-forms'
            }).done(res => {
                this.predefinedForms = res.forms;
                this.categories = res.categories;
                this.predefinedDropDownForms = res.predefined_dropDown_forms;
            }).fail(error => {
                this.$fail(this.$t('Something went wrong, please try again.'));
            })
                .always(() => {
                    this.loading = false;
                });
        },
        removeForm(id, index) {
            let data = {
                action: 'fluentform-form-delete',
                formId: id
            }
            FluentFormsGlobal.$get(data)
                .done(res => {
                    this.items.splice(index, 1);
                    this.$success(res.message);
                })
                .fail(_ => {
                });
        },
        duplicateForm(id) {
            let data = {
                action: 'fluentform-form-duplicate',
                formId: id
            }
            FluentFormsGlobal.$post(data)
                .then(res => {
                    this.$success(res.message);
                    if (res.redirect) {
                        window.location.href = res.redirect;
                    } else {
                        alert(this.$t('Something is wrong! Please try again'));
                    }
                })
                .fail(error => {
                    alert(this.$t('Something is wrong! Please try again'));
                });
        },
        handleTableSort(column) {
            if (column.order) {
                this.sort_column = column.prop;
                this.sort_by = (column.order === 'ascending') ? 'ASC' : 'DESC';
                this.fetchItems();
            }
        },
        handleSelectionChange(val) {
            this.entrySelections = val;
        },
        searchForms(event) {
            this.paginate.current_page = 1;
            this.fetchItems();
        },
        createForm({key, form}) {
            if (key === 'post') {
                return this.createPostForm(key, form);
            }

            this.$refs.predefinedFormsModal.createForm(
                key, // formType
                form // form
            );
        },
        createPostForm(key, form) {
            this.postTypeSelectionDialogVisibility = true;
        },
        onPostTypeSelctionEnd(post_type) {
            this.postTypeSelectionDialogVisibility = false;

            if (post_type) {
                this.$refs.predefinedFormsModal.doCreateForm({
                    post_type,
                    type: 'post',
                    title: 'Post Form',
                    predefined: 'blank_form',
                    action: 'fluentform-predefined-create'
                });
            }
        },
        resetAdvancedFilter() {
          this.advancedFilter = false;
          this.fetchItems();
        },
        filterFormType() {
          this.search_string = '';
          this.setDefaultPaginate();
          this.fetchItems();
        },
        setDefaultPaginate() {
          this.paginate = {
            total: 0,
            current_page: 1,
            last_page: 1,
            per_page: localStorage.getItem('formItemsPerPage') || 10
          }
        },
        tableRowClass({row}) {
            return row.status == 'unpublished' ? 'inactive_form' : '';
        },
        findFormLocations(formId){

            this.loadingLocations = true;
            let data = {
                action: 'fluentform-form-find-shortcode-locations',
                form_id: formId
            }
            FluentFormsGlobal.$get(data)
                .then(res => {
                    if (res.status === true){
                        this.$set(this.formLocations, formId, res.locations);
                    }else{
                        this.$set(this.formLocations, formId, []);
                    }
                    this.$success(res.message);
                })
                .fail(error => {
                    alert(this.$t('Something is wrong! Please try again'));
                })
                .always(()=>{
                    this.loadingLocations = false;
                })
            ;
        },
    },
    mounted() {
        this.fetchItems();
        this.getPredefinedForms();
        this.filter_date_range = [moment().format('YYYY-MM-DD'), moment().format('YYYY-MM-DD')];

        (new Clipboard('.copy')).on('success', event => {
            this.$copy();
        });
    },
    created() {
        let hash = window.location.hash;

        if (hash.indexOf('add=1') != -1) {
            this.showAddFormModal = true;
        }

        if (hash.indexOf('entries') != -1) {
            this.showSelectFormModal = true;
        }

        jQuery('a[href="admin.php?page=fluent_forms#add=1"]').on('click', event => {
            this.showAddFormModal = true;
            this.showSelectFormModal = false;
        });

        jQuery('a[href="admin.php?page=fluent_forms#entries"]').on('click', event => {
            this.showAddFormModal = false;
            this.showSelectFormModal = true;
        });
    },
    watch: {
        searchFormsKeyWord: function (newVal, oldVal) {
            if ((oldVal && !newVal) && !this.clearingSearchKeyword) {
                this.paginate.current_page = 1;
                this.fetchItems();
            }
        }
    }
};
</script>
<style scoped>
.el-dropdown-menu{
  z-index: 9999 !important;
}
</style>

