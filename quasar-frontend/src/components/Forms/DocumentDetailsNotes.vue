<template>
  <q-list class="bg-transparent scroll q-pa-sm" style="min-height: 50vh; max-height: 50vh;">
    <q-item class="transparent-background text-color-primary q-pa-sm">
      <q-item-section>
        <q-input type="search" icon="search" outlined dense clearable :disable="disable || !hasNotes"
          v-model.trim="filterNotesByText" :label="t('Filter by text on note body')"
          :placeholder="t('text search condition')"></q-input>
      </q-item-section>
      <q-item-section side>
        <q-btn size="md" :label="t('Add note')" icon="add" class="bg-blue text-white full-width" :disable="disable"
          @click.stop.prevent="onAddNote"></q-btn>
      </q-item-section>
    </q-item>
    <div v-if="hasNotes">
      <q-item class="q-pa-none bg-transparent" v-for="note, noteIndex in localNotes" :key="note.id"
        v-show="note.visible">
        <q-item-section>
          <InteractiveTextFieldCustomInput v-model.trim="note.body" dense outlined type="textarea" maxlength="4096"
            autogrow name="description" :label="`${note.creationDate} (${timeAgo(note.createdOnTimestamp)})`"
            :start-mode-editable="!!note.startOnEditMode" :disable="disable" clearable :max-lines="6"
            :rules="requiredFieldRules" :error="!note.body" :error-message="fieldIsRequiredLabel"
            :autofocus="note.startOnEditMode">
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
            <!-- TODO: NOT FOCUSING ON TEXTAREA CHANGE TO EDIT MODE -->
          </InteractiveTextFieldCustomInput>
        </q-item-section>
      </q-item>
    </div>
    <CustomBanner v-else-if="!disable" warning text="No document notes found" class="q-ma-sm">
    </CustomBanner>
  </q-list>

</template>

<script setup>
import { ref, reactive, computed, watch, nextTick } from "vue";
import { useI18n } from "vue-i18n";
import { uid } from "quasar";

import { useFormUtils } from "src/composables/formUtils"
import { useFormatDates } from "src/composables/formatDate"

import { default as InteractiveTextFieldCustomInput } from "src/components/Forms/Fields/InteractiveTextFieldCustomInput.vue"
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue"

const { t } = useI18n();

const { requiredFieldRules, fieldIsRequiredLabel } = useFormUtils();
const { timeAgo, fullDateTimeHuman, currentTimestamp, currentFullDateTimeHuman } = useFormatDates();

const emit = defineEmits(['update:notes']);

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

const localNotes = reactive([...props.notes]);
const hasNotes = computed(() => localNotes.length > 0);

watch(() => props.notes, (newNotes) => {
  localNotes.length = 0;
  localNotes.push(...newNotes);
});

const filterNotesByText = ref(null);

const onAddNote = () => {
  localNotes.push(
    reactive({ // TODO: GET NEW NOTE FROM COMPOSABLE
      id: uid(),
      body: null,
      // TODO: timeAgo
      createdOnTimestamp: currentTimestamp(),
      creationDate: currentFullDateTimeHuman(),
      startOnEditMode: true,
      visible: true
    })
  );
  emit("update:notes", localNotes);
};

const onUpdateNote = () => {
  emit("update:notes", localNotes);
};

const onRemoveNote = (index) => {
  localNotes?.splice(index, 1);
  emit("update:notes", localNotes);
};

</script>