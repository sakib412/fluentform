<template>
    <div class="ff_conv_section" v-if="supportedFields.includes(item.element)">
        <div class="ff_condition_icon" v-html="maybeConditionIcon(item.settings)"></div>
        <vddl-draggable class="panel__body--item js-editor-item"
                        :class="{ 'selected': editItem.uniqElKey == item.uniqElKey }"
                        :draggable="item"
                        :index="index"
                        effect-allowed="move"
                        type="existingElement"
                        :dragstart="handleDragstart"
                        :dragend="handleDragend"
                        :moved="handleMoved"
                        :wrapper="wrapper">

            <div @click="editSelected(index, item)" class="item-actions-wrapper"
                 :class="item.element == 'container' ? 'hover-action-top-right' : 'hover-action-middle'">
                <div class="item-actions">
                    <i class="icon icon-arrows"></i>
                    <i @click="editSelected(index, item)" class="icon icon-pencil"></i>
                    <i @click="duplicateSelected(index, item)" class="icon icon-clone"></i>
                    <i @click="askRemoveConfirm(index)" class="icon icon-trash-o"></i>
                </div>
            </div>

            <i @click.stop="editorInserterPopup(index, wrapper)" class="popup-search-element">+</i>

            <div class="ff_conv_section_wrapper" :class="'ff_conv_layout_'+item.style_pref.layout" v-if="hasRegistered(item)">
                <div class="ff_conv_input">
                    <component :is="guessElTemplate(item)" :item="item"></component>
                    <p style="font-style: italic;" v-if="item.settings.help_message" class="help-text"
                       v-html="item.settings.help_message"></p>
                </div>
                <style-pref-preview :pref="item.style_pref" />
            </div>
        </vddl-draggable>

        <ff_removeElConfirm
            :visibility.sync="showRemoveElConfirm"
            @on-confirm="onRemoveElConfirm"/>
    </div>
</template>

<script type="text/babel">
import NestedHandler from './NestedHandler.js';
import StylePrefPreview from '../conversion_templates/StylePrefPreview';
NestedHandler.components['style-pref-preview'] = StylePrefPreview;

export default {
    name: 'NestedListConversion',
    props: NestedHandler.props,
    components: NestedHandler.components,
    data() {
        return {
            showRemoveElConfirm: false,
            removeElIndex: null,
            supportedFields: window.FluentFormApp.conversational_form_fields
        }
    },
    methods: NestedHandler.methods
};
</script>
