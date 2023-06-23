<template>
  <q-select ref="selectRef" :label="t('Tags')" dense outlined v-model="currentTags" use-input use-chips multiple
    hide-dropdown-icon :options="filteredTags" input-debounce="0" new-value-mode="add-unique" clearable
    :disable="disabled || loading || loadingError" :loading="loading" :error="loadingError"
    :errorMessage="t('Error loading available tags')" @filter="onFilterTags" @add="onAddTag">
    <template v-slot:selected>
      <q-chip removable v-for="tag, index in currentTags" :model="tag" :key="tag" @remove="removeTagAtIndex(index)"
        color="dark" text-color="white" icon="label_important">
        {{ tag }}
      </q-chip>
    </template>
  </q-select>
</template>

<script setup>

import { defineProps, defineEmits, ref, watch } from "vue";
import { api } from 'boot/axios'
import { useI18n } from 'vue-i18n'

const { t } = useI18n();

const props = defineProps({
  disabled: Boolean,
  selectedTags: Array
});

const emit = defineEmits(['change', 'error']);

const selectRef = ref('');

const loading = ref(false);
const loadingError = ref(false);
const availableTags = ref([]);
const filteredTags = ref([]);
const currentTags = ref(props.selectedTags || []);

watch(currentTags, (newValue) => {
  emit('change', newValue);
});


function onFilterTags(val, update) {
  if (val === '') {
    update(() => {
      filteredTags.value = availableTags.value;
    });
  } else {
    update(() => {
      const needle = val.toLowerCase();
      filteredTags.value = availableTags.value.filter(v => v.toLowerCase().indexOf(needle) > -1)
    });
  }
}

function loadAvailableTags() {
  loadingError.value = false;
  loading.value = true;
  api.tag.search().then((response) => {
    availableTags.value = response.data.tags;
    currentTags.value = props.selectedTags || [];
    loading.value = false;
  }).catch((error) => {
    loadingError.value = true;
    loading.value = false;
    emit('error', error.response);
  });
}

function onAddTag() {
  // TODO: hide select popup after adding element not working
  selectRef.value.hidePopup()
}

function removeTagAtIndex(index) {
  currentTags.value.splice(index, 1);
}
loadAvailableTags();

</script>
