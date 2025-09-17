<template>
  <div v-if="readOnly" class="cursor-pointer q-pa-sm q-mb-md relative-position white-space-pre-line"
    style="border: 1px solid rgba(0, 0, 0, 0.12); border-radius: 4px;" @mouseenter="showUpdateHoverIcon = true"
    @mouseleave="showUpdateHoverIcon = false" @click="readOnly = !readOnly">
    <div style="font-size: 12px; color: rgba(0, 0, 0, 0.6); margin-left: 0px; margin-bottom: 4px;">
      {{ props.label }}</div>
    <q-icon name="expand" size="sm" class="absolute-top-right text-grey cursor-pointer q-mr-sm q-mt-sm"
      v-show="showUpdateHoverIcon" @click.stop="collapsedView = !collapsedView">
      <q-tooltip>{{ t("Click to expand/collapse") }}</q-tooltip>
    </q-icon>
    <q-icon name="edit" size="sm" class="absolute-top-right text-grey cursor-pointer q-mr-xl q-mt-sm"
      v-show="showUpdateHoverIcon">
      <q-tooltip>{{ t("Click to toggle edit mode") }}</q-tooltip>
    </q-icon>
    <div class="q-mt-sm" :class="{ 'collapsed': collapsedView }" :style="`--max-lines: ${maxLines}`">
      {{ model }}
    </div>
  </div>
  <q-input v-else v-bind="attrs" ref="myref" :label="label" v-model.trim="model">
    <template v-slot:append v-if="model">
      <q-icon name="done" class="cursor-pointer" @click="readOnly = !readOnly">
        <q-tooltip>{{ t("Click to toggle edit mode") }}</q-tooltip>
      </q-icon>
    </template>
  </q-input>
</template>

<script setup>

import { ref, useAttrs, watch, nextTick } from "vue";
import { useI18n } from 'vue-i18n'

const props = defineProps({
  startModeEditable: Boolean,
  modelValue: String,
  label: String,
  maxLines: {
    type: Number,
    default: 2
  }
});

const attrs = useAttrs();

const emit = defineEmits(['update:modelValue'])

const { t } = useI18n();

const myref = ref(null);

const readOnly = ref(!props.startModeEditable);
const showUpdateHoverIcon = ref(false);
const collapsedView = ref(true);
const model = ref(props.modelValue)

async function focus() {
  await nextTick()
  myref.value?.focus()
}

defineExpose({
  focus
});

watch(() => props.modelValue, val => model.value = val)
watch(model, val => emit('update:modelValue', val))

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