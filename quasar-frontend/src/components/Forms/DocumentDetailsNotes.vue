<template>
  <q-list class="bg-transparent q-pa-sm">
    <q-item class="transparent-background text-color-primary q-pa-none">
      <q-item-section v-show="hasNotes">
        <q-input type="search" icon="search" outlined dense clearable :disable="disable || !hasNotes"
          v-model.trim="searchText" @update:model-value="onSearchTextChanged" :label="t('Filter by text on note body')"
          :placeholder="t('type text search condition')"></q-input>
      </q-item-section>
      <q-item-section side>
        <q-btn size="md" :label="t('Add note')" icon="add" class="bg-blue text-white full-width" :disable="disable"
          @click.stop.prevent="onAddNote"></q-btn>
      </q-item-section>
    </q-item>
    <q-separator class="q-my-md" />
    <div v-if="hasNotes" class="q-list-notes-container scroll">
      <q-item class="q-pa-none bg-transparent" v-for="note, noteIndex in notes" :key="note.id"
        v-show="!hiddenIds.includes(note.id)">
        <q-item-section>
          <InteractiveTextFieldCustomInput v-model.trim="note.body" dense outlined type="textarea" maxlength="4096"
            autogrow name="description" :label="`${note.creationDate} (${note.creationDateTimeAgo})`"
            :start-mode-editable="!!note.startOnEditMode" :disable="disable" clearable :max-lines="6"
            :rules="requiredFieldRules" :error="!note.body" :error-message="fieldIsRequiredLabel"
            :autofocus="note.startOnEditMode" :placeholder="t('type note body')">
            <template v-slot:top-icon-append="{ showTopHoverIcons }">
              <q-icon name="delete" size="sm" class="q-ml-sm q-mr-sm" clickable v-show="showTopHoverIcons"
                @click.stop.prevent="onRemoveNoteAtIndex(noteIndex)">
                <DesktopToolTip>{{ t("Click to remove note") }}</DesktopToolTip>
              </q-icon>
            </template>
            <template v-slot:icon-append-on-edit>
              <q-icon name="delete" size="sm" class="cursor-pointer" clickable
                @click.stop.prevent="onRemoveNoteAtIndex(noteIndex)">
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
import { ref, computed } from "vue";
import { useI18n } from "vue-i18n";

import { useFormUtils } from "src/composables/formUtils"
import { useDocument } from "src/composables/document"

import { default as DesktopToolTip } from "src/components/DesktopToolTip.vue";
import { default as InteractiveTextFieldCustomInput } from "src/components/Forms/Fields/InteractiveTextFieldCustomInput.vue"
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue"

const { t } = useI18n();

const { requiredFieldRules, fieldIsRequiredLabel } = useFormUtils();
const { escapeRegExp, getNewNote } = useDocument();

const emit = defineEmits(['update:modelValue']);

const props = defineProps({
  modelValue: {
    type: Array,
    required: false,
    default: () => [],
    validator(value) {
      return Array.isArray(value);
    }
  },
  disable: {
    type: Boolean,
    required: false,
    default: false,
  }
});

const notes = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
  }
});

const hiddenIds = ref([]);

const hasNotes = computed(() => notes.value?.length > 0);

const searchText = ref(null);

const onSearchTextChanged = (text) => {
  if (text) {
    const regex = new RegExp(escapeRegExp(text), "i");
    hiddenIds.value = notes.value?.filter(note => !note.body?.match(regex)).map(note => note.id);
    // TODO: map new fragment with bold ?
  } else {
    hiddenIds.value = [];
  }
};

const onAddNote = () => {
  notes.value = [getNewNote(), ...notes.value];
};

const onRemoveNoteAtIndex = (index) => {
  notes.value = notes.value.filter((_, i) => i !== index);
};

</script>

<style lang="css" scoped>
.q-list-notes-container {
  min-height: 50vh;
  max-height: 50vh;
}
</style>