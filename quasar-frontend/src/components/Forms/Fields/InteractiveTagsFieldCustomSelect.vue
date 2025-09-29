<template>
  <div v-if="readOnly" class="cursor-pointer q-pa-sm relative-position white-space-pre-line"
    style="border: 1px solid rgba(0, 0, 0, 0.12); border-radius: 4px;" @mouseenter="showUpdateHoverIcon = true"
    @mouseleave="showUpdateHoverIcon = false" @click="onToggleReadOnly">
    <div style="font-size: 12px; color: rgba(0, 0, 0, 0.6); margin-left: 0px; margin-bottom: 4px;">
      {{ t(label) }}</div>
    <q-icon v-if="!denyChangeEditableMode" name="edit" size="sm"
      class="absolute-top-right text-grey cursor-pointer q-mr-sm q-mt-sm" v-show="showUpdateHoverIcon">
      <q-tooltip>{{ t("Click to toggle edit mode") }}</q-tooltip>
    </q-icon>
    <BrowseByTagButton v-for="tag in currentTags" :key="tag" :tag="tag" icon="tag" />
  </div>
  <q-select v-else ref="selectRef" :label="t(label)" v-model="currentTags" :dense="dense" :options-dense="dense"
    outlined use-input use-chips multiple hide-dropdown-icon :options="filteredTags" input-debounce="0"
    new-value-mode="add-unique" :disable="disabled || state.loading || state.loadingError" :loading="state.loading"
    :error="state.loadingError" :errorMessage="t('Error loading available tags')" @filter="onFilterTags"
    @add="onAddTag">
    <template v-slot:prepend>
      <slot name="prepend">
        <q-icon name="tag" />
      </slot>
    </template>
    <template v-slot:append v-if="!denyChangeEditableMode">
      <q-icon name="done" class="cursor-pointer" @click="onToggleReadOnly">
        <q-tooltip>{{ t("Click to toggle edit mode") }}</q-tooltip>
      </q-icon>
    </template>
    <template v-slot:selected>
      <q-chip square :removable="!readOnly" :dense="dense" v-for="tag, index in currentTags" :key="tag"
        @remove="removeTagAtIndex(index)" color="primary" text-color="white" icon="tag" :label="tag">
      </q-chip>
    </template>
  </q-select>
</template>

<script setup>

import { ref, reactive, watch, computed, onMounted, onBeforeUnmount, nextTick } from "vue";
import { useI18n } from "vue-i18n";

import { bus } from "src/boot/bus";
import { api } from "src/boot/axios";

import { default as BrowseByTagButton } from "src/components/Buttons/BrowseByTagButton.vue";

const { t } = useI18n();

const props = defineProps({
  startModeEditable: Boolean,
  denyChangeEditableMode: Boolean,
  modelValue: Array,
  disabled: Boolean,
  dense: Boolean,
  label: {
    type: String,
    required: false,
    default: "Document tags"
  }
});

const emit = defineEmits(['update:modelValue', 'error']);

const showUpdateHoverIcon = ref(false);

const readOnly = ref(!props.startModeEditable);
const selectRef = ref(null);
const filteredTags = ref([]);
const currentTags = ref([]);
const selectedTagsProp = computed(() => props.modelValue || []);

const state = reactive({
  loading: false,
  loadingError: false,
  errorMessage: null,
  apiError: null
});

const availableTags = reactive([]);

watch(selectedTagsProp, (newValue) => {
  currentTags.value = newValue || [];
});

watch(currentTags, (newValue) => {
  emit('update:modelValue', newValue || []);
});

function onFilterTags(val, update) {
  if (val === '') {
    update(() => {
      filteredTags.value = availableTags.value;
    });
  } else {
    update(() => {
      const needle = val.toLowerCase();
      filteredTags.value = availableTags.filter(v => v.toLowerCase().indexOf(needle) > -1);
    });
  }
}

function onRefresh() {
  if (!state.loading) {
    state.loading = true;
    state.loadingError = false;
    state.errorMessage = null;
    state.apiError = null;
    api.tag.search().then((successResponse) => {
      availableTags.length = 0;
      availableTags.push(...successResponse.data.tags);
      currentTags.value = selectedTagsProp.value;
      state.loading = false;
    }).catch((errorResponse) => {
      state.loadingError = true;
      switch (errorResponse.response.status) {
        case 401:
          state.apiError = errorResponse.customAPIErrorDetails;
          state.errorMessage = "Auth session expired, requesting new...";
          bus.emit("reAuthRequired", { emitter: "InteractiveTagsFieldCustomSelect" });
          break;
        default:
          state.apiError = errorResponse.customAPIErrorDetails;
          state.errorMessage = "API Error: fatal error";
          break;
      }
      state.loading = false;
      emit('error', errorResponse.response);
    });
  }
}

function onAddTag(tag) {
  selectRef.value?.hidePopup()
}

function removeTagAtIndex(index) {
  currentTags.value?.splice(index, 1);
}

async function focus() {
  await nextTick()
  selectRef.value?.focus()
}

defineExpose({
  focus
});

function onToggleReadOnly() {
  readOnly.value = !readOnly.value;
  if (!readOnly.value) {
    focus();
  }
}

onMounted(() => {
  onRefresh();
  bus.on("reAuthSucess", (msg) => {
    if (msg.to?.includes("InteractiveTagsFieldCustomSelect")) {
      onRefresh();
    }
  });
});

onBeforeUnmount(() => {
  bus.off("reAuthSucess");
});

</script>