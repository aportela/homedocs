<template>
  <q-select ref="selectRef" :label="t('Tags')" v-model="currentTags" :dense="dense" :options-dense="dense" outlined
    use-input use-chips multiple hide-dropdown-icon :options="filteredTags" input-debounce="0"
    new-value-mode="add-unique" clearable :disable="disabled || loading || loadingError" :loading="loading"
    :error="loadingError" :errorMessage="t('Error loading available tags')" @filter="onFilterTags" @add="onAddTag">
    <template v-slot:selected>
      <q-chip removable :dense="dense" v-for="tag, index in currentTags" :key="tag" @remove="removeTagAtIndex(index)"
        color="dark" text-color="white" icon="label_important">
        <router-link v-if="props.link" :to="{ name: 'advancedSearchByTag', params: { tag: tag } }"
          style="text-decoration: none; color: white;">
          {{ tag }}
        </router-link>
        <span v-else>{{ tag }}</span>
      </q-chip>
    </template>
  </q-select>
</template>

<script setup>

import { ref, watch, computed } from "vue";
import { api } from "boot/axios";
import { useI18n } from "vue-i18n";

const { t } = useI18n();

const props = defineProps({
  modelValue: Array,
  disabled: Boolean,
  dense: Boolean,
  link: Boolean
});

const emit = defineEmits(['update:modelValue', 'error']);

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

loadAvailableTags();

</script>