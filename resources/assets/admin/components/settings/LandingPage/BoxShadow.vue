<template>
    <div>
        <el-form-item>

            <template slot="label">
                {{ valueItem.label }}

                <el-tooltip class="item" placement="bottom-start" effect="light">
                    <div slot="content">
                        <h3>{{ valueItem.label }}</h3>
                        <p>
                            {{ $t('Choose Shadow for your form container') }}
                        </p>
                    </div>
                    <i class="el-icon-info el-text-info"></i>
                </el-tooltip>
            </template>
            <el-popover
                    width="300"
                    popper-class="ff-landing-layout-box-shadow-popover"
                    v-model="visible"
            >
                <div class="ff_type_settings">
                    <div class="ff-type-control">
                        <label class="ff-control-title">
                            Color
                        </label>
                        <div class="ff-type-value" style="margin-left:200px;">
                            <el-color-picker size="mini" v-model="valueItem.color"
                                             show-alpha></el-color-picker>
                        </div>
                    </div>

                    <div class="ff-type-control">
                        <label class="ff-control-title">
                            Horizontal
                        </label>
                        <div class="ff-type-value">
                            <input type="number" v-model="valueItem.horizontal"/>
                        </div>
                    </div>
                    <div class="ff-type-control">
                        <label class="ff-control-title">
                            Vertical
                        </label>
                        <div class="ff-type-value">
                            <input type="number" v-model="valueItem.vertical"/>
                        </div>
                    </div>
                    <div class="ff-type-control">
                        <label class="ff-control-title">
                            Blur
                        </label>
                        <div class="ff-type-value">
                            <input type="number" v-model="valueItem.blur"/>
                        </div>
                    </div>
                    <div class="ff-type-control">
                        <label class="ff-control-title">
                            Spread
                        </label>
                        <div class="ff-type-value">
                            <input type="number" v-model="valueItem.spread"/>
                        </div>
                    </div>

                    <div class="ff-type-control">
                        <label class="ff-control-title">
                            Position
                        </label>
                        <div class="ff-type-value">
                            <el-select size="mini" v-model="valueItem.position" placeholder="Select">
                                <el-option
                                        v-for="(item, key) in {'': 'Outline','inset': 'Inset'}"
                                        :key="key"
                                        :label="item"
                                        :value="key">
                                </el-option>
                            </el-select>
                        </div>
                    </div>
                </div>
                <el-button type="mini" slot="reference"><i class="el-icon-edit"></i></el-button>
            </el-popover>
            <el-button @click="reset" type="mini" icon="el-icon-refresh-left"></el-button>
        </el-form-item>

    </div>
</template>
<script type="text/babel">

    export default {
        name: 'ff_boxshadow',
        props: ['valueItem'],
        data() {
            return {
                visible: false,
            }
        },
        methods: {
            reset() {
                this.valueItem.position = "";
                this.valueItem.horizontal = "0";
                this.valueItem.vertical = "0";
                this.valueItem.blur = "0";
                this.valueItem.spread = "0";
                this.valueItem.color = "";
            }
        }
    }
</script>
