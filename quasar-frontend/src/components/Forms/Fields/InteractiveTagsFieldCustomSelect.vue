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
    <q-chip :dense="dense" v-for="tag, index in currentTags" :key="tag" @remove="removeTagAtIndex(index)"
      color="primary" text-color="white" icon="tag">
      <router-link :to="{ name: 'advancedSearchByTag', params: { tag: tag } }"
        style="text-decoration: none; color: white;">
        {{ tag }}
        <q-tooltip>{{ t("Browse by tag: ", { tag: tag }) }}</q-tooltip>
      </router-link>
    </q-chip>
  </div>
  <q-select v-else ref="selectRef" :label="t(label)" v-model="currentTags" :dense="dense" :options-dense="dense"
    outlined use-input use-chips multiple hide-dropdown-icon :options="filteredTags" input-debounce="0"
    new-value-mode="add-unique" :disable="disabled || loading || loadingError" :loading="loading" :error="loadingError"
    :errorMessage="t('Error loading available tags')" @filter="onFilterTags" @add="onAddTag">
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
      <q-chip :removable="!readOnly" :dense="dense" v-for="tag, index in currentTags" :key="tag"
        @remove="removeTagAtIndex(index)" color="primary" text-color="white" icon="tag" :label="tag">
      </q-chip>
    </template>
  </q-select>
</template>

<script setup>

import { ref, watch, computed, onMounted, nextTick } from "vue";
import { api } from "boot/axios";
import { useI18n } from "vue-i18n";

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
const selectRef = ref('');
const loading = ref(false);
const loadingError = ref(false);
const filteredTags = ref([]);
const currentTags = ref([]);
const selectedTagsProp = computed(() => props.modelValue || []);

let availableTags = [];

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

function loadAvailableTags() {
  loadingError.value = false;
  loading.value = true;
  api.tag.search().then((response) => {
    availableTags = response.data.tags;
    currentTags.value = selectedTagsProp.value;
    loading.value = false;
  }).catch((error) => {
    loadingError.value = true;
    loading.value = false;
    emit('error', error.response);
  });
}

function onAddTag(tag) {
  selectRef.value.hidePopup()
}

function removeTagAtIndex(index) {
  currentTags.value.splice(index, 1);
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
  loadAvailableTags();
});

</script>