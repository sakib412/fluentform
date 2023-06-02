<template>
    <card class="ff_inventory_settings">
        <card-head>
            <h5 class="title">{{ $t('Inventory Manager') }}</h5>
        </card-head>
        <card-body>
            <el-row :gutter="6">
                <el-col :md="18">
                    <h6 class="ff_block_title mb-1">{{ $t('Global Inventory') }}</h6>
                    <p class="ff_block_text">{{ $t('Global Inventories can be used accross different forms') }}</p>
                </el-col>
                <el-col :md="6" class="text-right">
                    <el-button
                            type="primary"
                            icon="ff-icon ff-icon-plus"
                            @click="showForm()"
                            size="medium"
                    >
                        {{ $t('Add Inventory') }}
                    </el-button>
                </el-col>
            </el-row>

            <div class="ff_inventory_list mt-4">
                <div class="ff_table_wrap">
                    <el-skeleton :loading="loading" animated :rows="6">
                        <el-table class="ff_table_s2" :data="pagedTableData">

                            <el-table-column :label="$t('Name')" prop="name" width="240" />
                            <el-table-column :label="$t('Slug')" prop="slug" width="240" />

                            <el-table-column :label="$t('Quantty')" prop="quantity" width="120" />

                            <el-table-column :label="$t('Remaining Quantity')">
                                <template slot-scope="scope">

                                </template>
                            </el-table-column>

                            <el-table-column :label="$t('Action')" width="90">
                                <template slot-scope="scope">
                                    <el-button
                                            class="el-button--icon"
                                            size="mini"
                                            type="primary"
                                            icon="ff-icon ff-icon-edit"
                                            @click="edit(scope.row)"
                                    />
                                    <confirm @on-confirm="remove(scope.row)">
                                        <el-button
                                                class="el-button--icon"
                                                size="mini"
                                                type="danger"
                                                icon="ff-icon ff-icon-trash"
                                        />
                                    </confirm>
                                </template>
                            </el-table-column>
                        </el-table>
                    </el-skeleton>
                </div>

                <div class="ff_pagination_wrap text-right mt-4">
                    <el-pagination
                            class="ff_pagination"
                            background
                            @size-change="handleSizeChange"
                            @current-change="goToPage"
                            :current-page.sync="pagination.current_page"
                            :page-sizes="[2, 10, 20, 50, 100]"
                            :page-size="pagination.per_page"
                            layout="total, sizes, prev, pager, next"
                            :total="pagination.total">
                    </el-pagination>
                </div>
            </div>

            <el-dialog
                    :visible.sync="modal"
                    :append-to-body="true"
                    width="36%"
                    class="ff_inventory_form"
            >
                <div slot="title">
                    <h5>{{getModalTitle()}}</h5>
                </div>

                <el-form :data="inventory" label-position="top" class="mt-4">
                    <el-form-item>
                        <template slot="label">
                            <h6>{{$t('Inventory Name')}}</h6>
                        </template>
                        <el-input
                                type="email"
                                :placeholder="$t('Item Name')"
                                v-model="inventory.name"
                        />
                        <error-view field="name" :errors="errors"/>
                    </el-form-item>

                    <el-form-item>
                        <template slot="label">
                            <h6>{{$t('Quantity Per Combination')}}</h6>
                        </template>
                        <el-input
                                type="number"
                                :placeholder="$t('Amount')"
                                v-model="inventory.quantity"
                        />


                        <error-view field="quantity" :errors="errors"/>
                    </el-form-item>
                </el-form>

                <div slot="footer" class="dialog-footer">
                    <btn-group class="ff_btn_group_half">
                        <btn-group-item>
                            <el-button @click="modal = false" type="info" class="el-button--soft">
                                {{$t('Cancel')}}
                            </el-button>
                        </btn-group-item>
                        <btn-group-item>
                            <el-button type="primary" @click="store">
                                {{ $t('Save') }}
                            </el-button>
                        </btn-group-item>
                    </btn-group>
                </div>
            </el-dialog>
        </card-body>
    </card>
</template>

<script>
    import Card from '@/admin/components/Card/Card.vue';
    import CardHead from '@/admin/components/Card/CardHead.vue';
    import CardBody from '@/admin/components/Card/CardBody.vue';
    import ErrorView from "@/common/errorView.vue";
    import Confirm from "@/admin/components/confirmRemove.vue";
    import BtnGroup from '@/admin/components/BtnGroup/BtnGroup.vue';
    import BtnGroupItem from '@/admin/components/BtnGroup/BtnGroupItem.vue';

    export default {
        name: "InventoryManager",
        data() {
            return {
                loading: false,
                modal: false,
                inventory_list: [],
                inventory: {},
                pagination: {
                    total: 0,
                    current_page: 1,
                    per_page: 10
                },
                errors: new Errors()
            };
        },
        components: {
            Card,
            CardHead,
            CardBody,
            ErrorView,
            Confirm,
            BtnGroup,
            BtnGroupItem
        },
        methods: {
            fetchInventoryList() {
                this.loading = true;
                const url = FluentFormsGlobal.$rest.route('getInventoryList');
                let data = {
                    per_page: this.pagination.per_page,
                    page: this.pagination.current_page,
                }

                FluentFormsGlobal.$rest.get(url, data)
                    .then(response => {
                        this.inventory_list = response.inventory_list;
                        this.pagination.total = response.total;
                    })
                    .catch(e => {

                    })
                    .finally(() => {
                        this.loading = false;
                    });
            },

            showForm() {
                this.inventory = {
                    name: "",
                    slug: "",
                    quantity: ""
                };
                this.modal = true;
                this.errors.clear();
            },

            getModalTitle() {
                return this.inventory.id ? "Edit Inventory" : "Add Inventory";
            },

            store() {
                this.loading = true;

                const url = FluentFormsGlobal.$rest.route('storeInventory');
                let data = {
                    inventory: this.inventory
                }

                FluentFormsGlobal.$rest.post(url, data)
                    .then(response => {
                        console.log(response)

                        this.modal = false;
                        this.$success(response.message);
                        // this.fetchInventoryList();
                    })
                    .catch(e => {
                        console.log(e)
                        // this.errors.record(e.errors);
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            },

            edit(inventory) {
                this.modal = true;
                this.inventory = Object.assign({}, inventory);
                this.fetchInventoryList();
                this.errors.clear();
            },

            remove(inventory) {
                const url = FluentFormsGlobal.$rest.route('deleteInventory');
                let data = {
                    id: inventory.id
                }

                FluentFormsGlobal.$rest.delete(url, data)
                    .then(response => {
                        this.modal = false;
                        this.$success(response.message);
                        this.fetchInventoryList();
                    })
                    .catch(e => {
                        this.errors.record(e.errors);
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            },

            goToPage(value) {
                this.pagination.current_page = value;
                this.fetchInventoryList();
            },

            handleSizeChange(value) {
                this.pagination.per_page = value;
                this.fetchInventoryList();
            }
        },
        computed: {
            pagedTableData() {
                console.log( this.inventory_list.slice(this.pagination.per_page * this.pagination.current_page - this.pagination.per_page, this.pagination.per_page * this.page))
                return this.inventory_list.slice(0,2)
            }
        },
        mounted() {
            this.fetchInventoryList();
        }
    };


</script>
