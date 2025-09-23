<template>
  <q-dialog v-model="dialogModel" @hide="emit('close')">
    <q-card class="q-card-notes-dialog">
      <q-card-section class="row items-center q-p-none">
        <div class="q-card-notes-dialog-header max-width-90" v-if="documentTitle">{{ t(" Document title")
          }}: <router-link :to="{ name: 'document', params: { id: documentId } }" class="text-decoration-hover">{{
            documentTitle
          }}</router-link>
        </div>
        <div class="q-card-notes-dialog-header" v-else>{{ t("Document notes") }}</div>
        <q-space />
        <q-chip size="md" square class="theme-default-q-chip" v-if="!state.loading && !state.loadingError">
          <q-avatar class="theme-default-q-avatar">{{ notes.length }}</q-avatar>
          {{ t("Total notes", { count: notes.length }) }}
        </q-chip>
        <q-btn icon="close" flat round dense v-close-popup aria-label="Close modal" :disable="state.loading" />
      </q-card-section>
      <q-separator class="q-mb-md"></q-separator>
      <q-card-section class="q-pt-none scroll notes-scrolled-container">
        <div v-if="state.loading"></div>
        <div v-else-if="state.loadingError">
          <CustomErrorBanner text="Error loading data" :apiError="state.apiError">
          </CustomErrorBanner>
        </div>
        <div v-else-if="!hasNotes">
          <CustomBanner warning text="This document has no associated notes."></CustomBanner>
        </div>
        <div v-else>
          <div v-for="note, index in notes" :key="note.id"
            class="note-container q-pa-sm relative-position white-space-pre-line"
            :class="{ 'q-mb-md': index < notes.length - 1 }">
            <div class="note-date-label">
              {{ note.createdOn }} ({{ timeAgo(note.createdOnTimestamp * 1000) }})</div>
            <q-icon :name="note.expanded ? 'unfold_less' : 'expand'" size="sm"
              class="absolute-top-right text-grey cursor-pointer q-mr-sm q-mt-sm" color="blue"
              @click.stop="note.expanded = !note.expanded">
              <q-tooltip>{{ t(note.expanded ? "Click to collapse" : "Click to expand") }}</q-tooltip>
            </q-icon>
            <div class="q-mt-sm" :class="{ 'collapsed': !note.expanded }" :style="`--max-lines: ${maxLines}`">
              {{ note.body }}
            </div>
          </div>
        </div>
      </q-card-section>
      <q-separator class="q-my-sm"></q-separator>
      <q-card-section class="q-pt-none">
        <q-card-actions align="right">
          <q-btn color="primary" :disable="state.loading" v-close-popup :label="t('Close')" icon="close"
            aria-label="Close modal" />
        </q-card-actions>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { date } from "quasar";
import { useFormatDates } from "src/composables/formatDate"
import { api } from "src/boot/axios";

import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue";

const { t } = useI18n();
const { timeAgo } = useFormatDates();

const props = defineProps({
  documentId: {
    type: String,
    required: true,
  },
  documentTitle: {
    type: String,
    required: false,
    default: ""
  },
  maxLines: {
    type: Number,
    required: false,
    default: 2
  }
});

const emit = defineEmits(['close']);

const state = reactive({
  loading: false,
  loadingError: false,
  apiError: null
});

const notes = reactive([]);

const hasNotes = computed(() => notes?.length > 0);

const dialogModel = ref(true);

const onRefresh = (documentId) => {
  if (documentId) {
    state.loading = true;
    state.loadingError = false;
    state.apiError = null;
    api.document
      .getNotes(documentId)
      .then((successResponse) => {
        notes.length = 0;
        notes.push(...successResponse.data.notes.map((note) => {
          note.createdOn = date.formatDate(note.createdOnTimestamp * 1000, 'YYYY-MM-DD HH:mm:ss');
          note.expanded = false;
          return (note);
        }));
        state.loading = false;
      })
      .catch((errorResponse) => {
        state.loadingError = true;
        state.apiError = errorResponse.customAPIErrorDetails;
        state.loading = false;
        switch (errorResponse.response.status) {
          case 401:
            // TODO
            break;
          default:
            // TODO
            break;
        }
      });
  } else {
    // TODO
    state.loadingError = true;
  }
};

onMounted(() => {
  onRefresh(props.documentId || null);
});

</script>

<style scoped>
.q-card-notes-dialog {
  width: 1280px;
  max-width: 80vw;
}

.q-card-notes-dialog-header {
  font-size: 1.2em;
  font-weight: bold;
}

.q-card-notes-dialog-header a {
  font-weight: normal;
}

.max-width-90 {
  max-width: 90%;
}

.notes-scrolled-container {
  max-height: 50vh
}

.note-container {
  border: 1px solid rgba(0, 0, 0, 0.12);
  border-radius: 4px;
}

.note-date-label {
  font-size: 12px;
  color: rgba(0, 0, 0, 0.6);
  margin-left: 0px;
  margin-bottom: 4px;
}

.collapsed {
  overflow: hidden;
  -webkit-line-clamp: var(--max-lines);
  display: -webkit-box;
  -webkit-box-orient: vertical;
  text-overflow: ellipsis;
}
</style>