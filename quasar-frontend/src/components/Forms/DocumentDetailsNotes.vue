<template>
  <q-list class="bg-transparent q-pa-sm">
    <q-item class="transparent-background text-color-primary q-pa-none">
      <q-item-section v-show="hasNotes">
        <q-input type="search" icon="search" outlined dense clearable :disable="disable || !hasNotes"
          v-model.trim="searchText" :label="t('Filter by text on note body')"
          :placeholder="t('type text search condition')"></q-input>
      </q-item-section>
      <q-item-section side>
        <q-btn size="md" :label="t('Add note')" icon="add" class="bg-blue text-white full-width" :disable="disable"
          @click.stop.prevent="onAddNote"></q-btn>
      </q-item-section>
    </q-item>
    <q-separator class="q-my-md" />
    <div v-if="hasNotes" class="q-list-notes-container scroll">
      <q-item class="q-pa-none bg-transparent" v-for="note, noteIndex in notes" :key="note.id" v-show="note.visible">
        <q-item-section>
          <InteractiveTextFieldCustomInput v-model.trim="note.body" dense outlined type="textarea" maxlength="4096"
            autogrow name="description" :label="`${note.creationDate} (${note.creationDateTimeAgo})`"
            :start-mode-editable="!!note.startOnEditMode" :disable="disable" clearable :max-lines="6"
            :rules="requiredFieldRules" :error="!note.body" :error-message="fieldIsRequiredLabel"
            :autofocus="note.startOnEditMode" :placeholder="t('type note body')">
            <template v-slot:top-icon-append="{ showTopHoverIcons }">
              <q-icon name="delete" size="sm" class="q-ml-sm q-mr-sm" clickable v-show="showTopHoverIcons"
                @click.stop.prevent="onRemoveNote(noteIndex)">
                <q-tooltip>{{ t("Click to remove note") }}</q-tooltip>
              </q-icon>
            </template>
            <template v-slot:icon-append-on-edit>
              <q-icon name="delete" size="sm" class="cursor-pointer" clickable
                @click.stop.prevent="onRemoveNote(noteIndex)">
              </q-icon>
            </template>
          </InteractiveTextFieldCustomInput>
        </q-item-section>
      </q-item>
    </div>
    <CustomBanner v-else-if="!disable" warning text="No document notes found" class="q-ma-none">
    </CustomBanner>
  </q-list>

</template>

<script setup>
import { ref, computed, watch } from "vue";
import { useI18n } from "vue-i18n";

import { useFormUtils } from "src/composables/formUtils"

import { default as InteractiveTextFieldCustomInput } from "src/components/Forms/Fields/InteractiveTextFieldCustomInput.vue"
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue"

const { t } = useI18n();

const { requiredFieldRules, fieldIsRequiredLabel } = useFormUtils();

const emit = defineEmits(['update:notes', 'addNote', 'removeNoteAtIndex', 'filter']);

const props = defineProps({
  notes: {
    type: Array,
    required: false,
    default: () => []
  },
  disable: {
    type: Boolean,
    required: false,
    default: false
  }
});

const hasNotes = computed(() => props.notes.length > 0);

const searchText = ref(null);

watch(() => searchText.value, val => {
  emit("filter", val);
});

const onAddNote = () => {
  emit("addNote");
};

const onRemoveNote = (index) => {
  emit("removeNoteAtIndex", index);
};

</script>

<style scoped>
.q-list-notes-container {
  min-height: 50vh;
  max-height: 50vh;
}
</style>