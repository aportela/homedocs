<template>
  <q-dialog v-model="visible" @hide="emit('close')">
    <q-card style="width: 1280px; max-width: 80vw;">
      <q-card-section class="row items-center q-p-none">
        <div class="q-card-notes-dialog-header" v-if="title">{{ t("Document title") }}: <span>{{ title }}</span>
        </div>
        <div class="q-card-notes-dialog-header" v-else>{{ t("Document notes") }}</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup aria-label="Close modal" />
      </q-card-section>
      <q-card-section class="q-pt-none scroll" style="max-height: 50vh">
        <div v-for="note in notes" :key="note.id" class="q-pa-sm q-mb-md relative-position white-space-pre-line"
          style="border: 1px solid rgba(0, 0, 0, 0.12); border-radius: 4px;">
          <div style="font-size: 12px; color: rgba(0, 0, 0, 0.6); margin-left: 0px; margin-bottom: 4px;">
            {{ note.createdOn }} ({{ timeAgo(note.createdOnTimestamp * 1000) }})</div>
          <q-icon name="expand" size="sm" class="absolute-top-right text-grey cursor-pointer q-mr-sm q-mt-sm"
            @click.stop="note.expanded = !note.expanded">
            <q-tooltip>{{ t("Click to expand/collapse") }}</q-tooltip>
          </q-icon>
          <div class="q-mt-sm" :class="{ 'collapsed': !note.expanded }" :style="`--max-lines: ${maxLines}`">
            {{ note.body }}
          </div>
        </div>
      </q-card-section>
      <q-card-section class="q-pt-none">
        <q-card-actions align="right">
          <q-btn color="primary" v-close-popup :label="t('Close')" icon="close" aria-label="Close modal" />
        </q-card-actions>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref } from "vue";
import { useI18n } from 'vue-i18n'
import { useFormatDates } from "src/composables/formatDate"

const { t } = useI18n();
const { timeAgo } = useFormatDates();

const props = defineProps({
  title: {
    type: String,
    required: false,
    default: ""
  },
  notes: {
    type: Array,
    required: true
  },
  index: {
    type: Number,
    required: false,
    default: 0
  },
  maxLines: {
    type: Number,
    required: false,
    default: 2
  }
});

const emit = defineEmits(['close']);

const visible = ref(true);

</script>

<style scoped>
.q-card-notes-dialog-header {
  font-size: 1.2em;
  font-weight: bold;
}

.q-card-notes-dialog-header span {
  font-weight: normal;
}


.collapsed {
  overflow: hidden;
  -webkit-line-clamp: var(--max-lines);
  display: -webkit-box;
  -webkit-box-orient: vertical;
  text-overflow: ellipsis;
}
</style>