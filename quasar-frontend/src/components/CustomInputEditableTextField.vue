<template>
  <div v-if="readOnly" class="cursor-pointer q-pa-sm q-mb-md relative-position white-space-pre-line"
    style="border: 1px solid rgba(0, 0, 0, 0.12); border-radius: 4px;" @mouseenter="showTopHoverIcons = true"
    @mouseleave="showTopHoverIcons = false" @click="onToggleReadOnly">
    <div style="font-size: 12px; color: rgba(0, 0, 0, 0.6); margin-left: 0px; margin-bottom: 4px;">
      {{ props.label }}</div>
    <span class="absolute-top-right text-grey q-mt-sm">
      <slot name="top-icon-prepend" :showTopHoverIcons="showTopHoverIcons"></slot>
      <q-icon name="expand" size="sm" v-show="showTopHoverIcons" clickable @click.stop="collapsedView = !collapsedView">
        <q-tooltip>{{ t("Click to expand/collapse") }}</q-tooltip>
      </q-icon>
      <q-icon name="edit" size="sm" class="q-ml-sm" v-show="showTopHoverIcons">
        <q-tooltip>{{ t("Click to toggle edit mode") }}</q-tooltip>
      </q-icon>
      <slot name="top-icon-append" :showTopHoverIcons="showTopHoverIcons"></slot>
    </span>
    <div class="q-mt-sm" :class="{ 'collapsed': collapsedView }" :style="`--max-lines: ${maxLines}`">
      {{ model }}
    </div>
  </div>
  <q-input v-else v-bind="attrs" ref="qInputRef" :label="label" v-model.trim="model">
    <template v-slot:append v-if="model">
      <q-icon name="done" class="cursor-pointer" @click="onToggleReadOnly">
        <q-tooltip>{{ t("Click to toggle edit mode") }}</q-tooltip>
      </q-icon>
    </template>
  </q-input>
</template>

<script setup>

import { ref, useAttrs, watch, nextTick } from "vue";
import { useI18n } from 'vue-i18n'

const props = defineProps({
  startModeEditable: {
    type: Boolean,
    required: false,
    default: true
  },
  modelValue: {
    type: String,
    required: false,
    default: "",
  },
  label: {
    type: String,
    required: false,
  },
  maxLines: {
    type: Number,
    required: false,
    default: 2
  }
});

const attrs = useAttrs();
const { t } = useI18n();

const emit = defineEmits(['update:modelValue']);

const qInputRef = ref(null);

const readOnly = ref(!props.startModeEditable);
const showTopHoverIcons = ref(false); // TODO: ONLY ON DESKTOP (NOT MOBILE, ALWAYS SHOWED)
const collapsedView = ref(true);
const model = ref(props.modelValue)

watch(() => props.modelValue, val => model.value = val);
watch(model, val => emit('update:modelValue', val));

const focus = () => {
  nextTick(() => {
    qInputRef.value?.focus();
  });
}

const onToggleReadOnly = () => {
  readOnly.value = !readOnly.value;
  if (!readOnly.value) {
    focus();
  }
}

defineExpose({
  focus
});

</script>

<style scoped>
.collapsed {
  overflow: hidden;
  -webkit-line-clamp: var(--max-lines);
  display: -webkit-box;
  -webkit-box-orient: vertical;
  text-overflow: ellipsis;
}
</style>