<template>
  <div v-if="readOnly">
    <!-- TODO: q-mb-md not working on errorMessages -->
    <div class="cursor-pointer q-pa-sm q-mb-md relative-position white-space-pre-line read-only-input-container"
      :class="{ 'border-error': error }" @mouseenter="onMouseEnter" @mouseleave="onMouseLeave"
      @click="onToggleReadOnly">
      <div style="font-size: 12px; color: rgba(0, 0, 0, 0.6); margin-left: 0px; margin-bottom: 4px;">
        {{ props.label }}</div>
      <span class="absolute-top-right text-grey q-mt-sm">
        <slot name="top-icon-prepend" :showTopHoverIcons="showTopHoverIcons"></slot>
        <q-icon :name="!collapsedView ? 'unfold_less' : 'expand'" size="sm" v-show="showTopHoverIcons"
          @click.stop="collapsedView = !collapsedView"
          :aria-label="t(collapsedView ? 'Click to expand' : 'Click to collapse')">
          <DesktopToolTip>{{ t(collapsedView ? "Click to expand" : "Click to collapse") }}</DesktopToolTip>
        </q-icon>
        <q-icon name="edit" size="sm" class="q-ml-sm" v-show="showTopHoverIcons"
          :aria-label="t('Click to toggle edit mode')">
          <DesktopToolTip>{{ t("Click to toggle edit mode") }}</DesktopToolTip>
        </q-icon>
        <slot name="top-icon-append" :showTopHoverIcons="showTopHoverIcons"></slot>
      </span>
      <div class="q-mt-sm" :class="{ 'collapsed': collapsedView }" :style="`--max-lines: ${maxLines}`">
        {{ text }}
      </div>
    </div>
    <p class="text-red q-ml-sm error-message" v-if="error">{{ errorMessage }}</p>
  </div>
  <q-input v-else v-bind="attrs" ref="qInputRef" :label="label" v-model.trim="text" :rules="rules" :error="error"
    :error-message="errorMessage">
    <template v-slot:append>
      <q-icon name="done" class="cursor-pointer" @click.stop="onToggleReadOnly" v-if="!error">
        <DesktopToolTip>{{ t("Click to toggle edit mode") }}</DesktopToolTip>
      </q-icon>
      <slot name="icon-append-on-edit"></slot>
    </template>
  </q-input>
</template>

<script setup>

import { ref, useAttrs, computed, nextTick, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { useQuasar } from "quasar";

import { default as DesktopToolTip } from "src/components/DesktopToolTip.vue";

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
  },
  rules: {
    type: Array,
    default: () => [],
    validator(value) {
      return Array.isArray(value);
    }
  },
  autofocus: {
    type: Boolean,
    default: false,
  },
  error:
  {
    type: Boolean,
    required: false,
    default: false
  },
  errorMessage: {
    type: String,
    required: false,
    default: null
  },
});

const attrs = useAttrs();
const { t } = useI18n();

const $q = useQuasar();
const isDesktop = computed(() => $q.platform.is.desktop);

const emit = defineEmits(['update:modelValue']);

const qInputRef = ref(null);

const readOnly = ref(!props.startModeEditable);

const text = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
  }
});

const showTopHoverIcons = ref(!isDesktop.value);

const collapsedView = ref(true);

const focus = () => {
  nextTick(() => {
    qInputRef.value?.focus();
  });
}

const onMouseEnter = () => {
  if (isDesktop.value) {
    showTopHoverIcons.value = true
  }
};

const onMouseLeave = () => {
  if (isDesktop.value) {
    showTopHoverIcons.value = false
  }
};

const onToggleReadOnly = () => {
  readOnly.value = !readOnly.value;
  if (!readOnly.value) {
    focus();
  }
}

const unsetReadOnly = () => {
  readOnly.value = false;
}

defineExpose({
  focus, unsetReadOnly
});

onMounted(() => {
  if (props.autofocus && props.startModeEditable) {
    focus();
  }
});

</script>

<style lang="css" scoped>
.collapsed {
  overflow: hidden;
  -webkit-line-clamp: var(--max-lines);
  display: -webkit-box;
  -webkit-box-orient: vertical;
  text-overflow: ellipsis;
}

.read-only-input-container {
  border: 1px solid rgba(0, 0, 0, 0.12);
  border-radius: 4px;
}

.body--dark {
  .read-only-input-container {
    border: 1px solid rgba(255, 255, 255, 0.28);
  }
}

.border-error {
  border: 2px solid red !important;
}

/* TODO: do not use relative position */
.error-message {
  position: relative;
  top: -10px;
  font-size: 0.8em;
}
</style>
