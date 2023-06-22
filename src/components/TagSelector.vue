<template>
  <q-select :error="loadingError" errorMessage="Error loading available tags" ref="selectRef" label="Tags" dense outlined
    v-model="currentTags" use-input use-chips multiple hide-dropdown-icon :options="availableTags" @filter="filterFn"
    input-debounce="0" new-value-mode="add-unique" clearable :disable="loading || loadingError" @add="onAddTag">
  </q-select>
</template>

<script>

import { api } from 'boot/axios'
import { ref } from "vue";

import { defineComponent } from 'vue'

export default defineComponent({
  name: 'TagSelector',
  props: ['selectedTags'],
  setup(props, context) {


    const loading = ref(false);
    const loadingError = ref(false);
    const currentTags = ref(props.selectedTags || []);

    const selectRef = ref('');

    const availableTags = ref([]);
    const allTags = ref([]);

    function filterFn(val, update) {
      if (val === '') {
        update(() => {
          availableTags.value = allTags.value;
        })
        return
      }

      update(() => {
        const needle = val.toLowerCase()
        availableTags.value = allTags.value.filter(v => v.toLowerCase().indexOf(needle) > -1)
      })
    }

    function loadAvailableTags() {
      loadingError.value = false;
      api.tag.search().then((response) => {
        allTags.value = response.data.tags;
        loadingError.value = false;
      }).catch((error) => {
        loadingError.value = true;
        loading.value = false;
        // TODO emit error.response
      });
    }

    function onAddTag() {
      selectRef.value.hidePopup()
      // TODO clear input
    }

    loadAvailableTags();

    return ({ loading, loadingError, currentTags, availableTags, selectRef, onAddTag, filterFn });
  }

});
</script>
