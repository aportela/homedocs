<template>
  <BaseDialog v-model="visible" @close="onClose" width="1280px" max-width="80vw">
    <template v-slot:header-left>
      <div v-if="documentTitle">{{ t("Document title")
      }}: <router-link :to="{ name: 'document', params: { id: documentId } }" class="text-decoration-hover">{{
          documentTitle
        }}</router-link>
      </div>
      <div v-else>{{ t("Document notes") }}</div>
    </template>
    <template v-slot:header-right>
      <q-chip size="md" square class="gt-sm theme-default-q-chip shadow-1" v-if="!state.loading && !state.loadingError">
        <q-avatar class="theme-default-q-avatar">{{ notes.length }}</q-avatar>
        {{ t("Total notes", { count: notes.length }) }}
      </q-chip>
    </template>
    <template v-slot:body>
      <div class="q-pt-none scroll notes-scrolled-container">
        <div v-if="state.loading"></div>
        <div v-else-if="state.loadingError">
          <CustomErrorBanner :text="state.errorMessage || 'Error loading data'" :api-error="state.apiError">
          </CustomErrorBanner>
        </div>
        <div v-else-if="!hasNotes">
          <CustomBanner warning text="This document has no associated notes."></CustomBanner>
        </div>
        <div v-else>
          <div v-for="note in notes" :key="note.id"
            class="q-pa-sm relative-position white-space-pre-line border-bottom-except-last-item">
            <div class="note-date-label">
              {{ note.createdOn }} ({{ timeAgo(note.createdOnTimestamp) }})</div>
            <q-icon :name="note.expanded ? 'unfold_less' : 'expand'" size="sm"
              class="absolute-top-right text-grey cursor-pointer q-mr-sm q-mt-sm" color="blue"
              @click.stop="note.expanded = !note.expanded">
              <DesktopToolTip>{{ t(note.expanded ? "Click to collapse" : "Click to expand") }}</DesktopToolTip>
            </q-icon>
            <div class="q-mt-sm" :class="{ 'collapsed': !note.expanded }" :style="`--max-lines: ${maxLines}`">
              {{ note.body }}
            </div>
          </div>
        </div>
      </div>
    </template>
  </BaseDialog>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, onBeforeUnmount } from "vue";
import { useI18n } from "vue-i18n";
import { date } from "quasar";
import { useBus } from "src/composables/useBus";
import { useFormatDates } from "src/composables/useFormatDates"
import { useAPI } from "src/composables/useAPI";
import type { APIErrorDetails as APIErrorDetailsInterface } from "src/types/api-error-details";

import { default as BaseDialog } from "src/components/Dialogs/BaseDialog.vue";
import { default as DesktopToolTip } from "src/components/DesktopToolTip.vue";
import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue";

const { t } = useI18n();
const { timeAgo } = useFormatDates();
const { api } = useAPI();
const { bus } = useBus();

interface DocumentFilesPreviewDialogProps {
  documentId: string;
  documentTitle?: string;
  maxLines?: number;
};

const props = withDefaults(defineProps<DocumentFilesPreviewDialogProps>(), {
  maxLines: 2
});

const emit = defineEmits(['close']);

const visible = ref(true);

interface State {
  loading: boolean,
  loadingError: boolean,
  errorMessage: string | null,
  apiError: APIErrorDetailsInterface | null
};

const state: State = reactive({
  loading: false,
  loadingError: false,
  errorMessage: null,
  apiError: null
});

interface Note {
  id: string;
  body: string;
  createdOnTimestamp: number;
  createdOn: string;
  expanded: boolean;
};

const notes = reactive<Array<Note>>([]);

const hasNotes = computed(() => notes?.length > 0);

const onRefresh = (documentId: string) => {
  if (!state.loading) {
    state.loading = true;
    state.loadingError = false;
    state.errorMessage = null;
    state.apiError = null;
    api.document
      .getNotes(documentId)
      .then((successResponse) => {
        notes.length = 0;
        notes.push(...successResponse.data.notes.map((note: Note) => {
          note.createdOn = date.formatDate(note.createdOnTimestamp, 'YYYY-MM-DD HH:mm:ss');
          note.expanded = false;
          return (note);
        }));
        state.loading = false;
      })
      .catch((errorResponse) => {
        state.loadingError = true;
        if (errorResponse.isAPIError) {
          switch (errorResponse.response.status) {
            case 401:
              state.apiError = errorResponse.customAPIErrorDetails;
              state.errorMessage = "Auth session expired, requesting new...";
              bus.emit("reAuthRequired", { emitter: "DocumentNotesPreviewDialog" });
              break;
            default:
              state.apiError = errorResponse.customAPIErrorDetails;
              state.errorMessage = "API Error: fatal error";
              break;
          }
        } else {
          state.errorMessage = `Uncaught exception: ${errorResponse}`;
          console.error(errorResponse);
        }
        state.loading = false;
      });
  }
};

const onClose = () => {
  emit('close');
};

onMounted(() => {
  onRefresh(props.documentId);
  bus.on("reAuthSucess", (msg) => {
    if (msg.to?.includes("DocumentNotesPreviewDialog")) {
      onRefresh(props.documentId);
    }
  });
});

onBeforeUnmount(() => {
  bus.off("reAuthSucess");
});

</script>

<style lang="css" scoped>
.notes-scrolled-container {
  max-height: 50vh
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