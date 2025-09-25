<template>
  <CustomExpansionWidget title="Most recent activity" icon="work_history" iconToolTip="Click to refresh data"
    :onHeaderIconClick="onRefresh" :loading="state.loading" :error="state.loadingError" :expanded="isExpanded"
    @expand="isExpanded = true" @collapse="isExpanded = false">
    <template v-slot:header-extra>
      <q-chip square size="sm" color="primary" text-color="white" class="shadow-1">{{ t("Total document count", {
        count:
          recentDocuments.length
      }) }}</q-chip>
    </template>
    <template v-slot:content>
      <q-list v-if="state.loading">
        <div v-for="i in 4" :key="i">
          <q-item class="transparent-background text-color-primary q-pa-sm">
            <q-item-section top avatar class="gt-xs q-mt-lg">
              <q-skeleton type="QAvatar" square size="32px"></q-skeleton>
            </q-item-section>
            <q-item-section class="q-mx-md">
              <q-item-label>
                <q-skeleton type="text" />
              </q-item-label>
              <q-item-label caption lines="2">
                <q-skeleton type="text" height="2em" />
              </q-item-label>
              <q-item-label>
                <div class="row items-left q-gutter-sm">
                  <q-skeleton square width="6em" height="2em" class="" v-for="j in 5" :key="j"></q-skeleton>
                </div>
              </q-item-label>
            </q-item-section>
            <q-item-section side top>
              <q-item-label caption>
                <q-skeleton type="text" width="8em" height="2em"></q-skeleton>
                <q-skeleton square width="8em" height="2em" class="q-mt-sm"></q-skeleton>
                <q-skeleton square width="8em" height="2em" class="q-mt-sm"></q-skeleton>
              </q-item-label>
            </q-item-section>
          </q-item>
          <q-separator v-if="i < 3" class="q-my-xs" />
        </div>
      </q-list>
      <CustomErrorBanner v-else-if="state.loadingError" :text="state.errorMessage || 'Error loading data'"
        :apiError="state.apiError">
      </CustomErrorBanner>
      <q-list v-else-if="hasRecentDocuments">
        <div v-for="recentDocument, index in recentDocuments" :key="recentDocument.id">
          <q-item class="transparent-background text-color-primary q-pa-sm" clickable
            :to="{ name: 'document', params: { id: recentDocument.id } }">
            <q-item-section top avatar class="gt-xs">
              <q-avatar square icon="work" size="64px" aria-label="Document avatar" />
            </q-item-section>
            <q-item-section top>
              <q-item-label>
                <span class="text-weight-bold">{{ t("Title") }}:</span> {{ recentDocument.title }}
              </q-item-label>
              <q-item-label caption lines="2" v-if="recentDocument.description">
                <span class="text-weight-bold">{{ t("Description") }}:</span> {{ recentDocument.description
                }}</q-item-label>
              <q-item-label v-if="recentDocument.tags?.length > 0">
                <BrowseByTagButton v-for="tag in recentDocument.tags" :key="tag"
                  :to="{ name: 'advancedSearchByTag', params: { tag: tag } }" :tag="tag" icon="tag">
                </BrowseByTagButton>
              </q-item-label>
            </q-item-section>
            <q-item-section side top>
              <q-item-label caption>{{ timeAgo(recentDocument.timestamp) }}</q-item-label>
              <ViewDocumentDetailsButton size="md" square class="min-width-7em" :count="recentDocument.fileCount"
                :label="'Total files'" :tool-tip="'View document attachments'" :disable="state.loading"
                @click.stop.prevent="onShowDocumentFiles(recentDocument.id, recentDocument.title)">
              </ViewDocumentDetailsButton>
              <ViewDocumentDetailsButton size="md" square class="min-width-7em" :count="recentDocument.noteCount"
                :label="'Total notes'" :tool-tip="'View document notes'" :disable="state.loading"
                @click.stop.prevent="onShowDocumentNotes(recentDocument.id, recentDocument.title)">
              </ViewDocumentDetailsButton>
            </q-item-section>
          </q-item>
          <q-separator v-if="index !== recentDocuments.length - 1" class="q-my-xs" />
        </div>
      </q-list>
      <CustomBanner v-else warning text="You haven't created any documents yet"></CustomBanner>
    </template>
  </CustomExpansionWidget>
  <DocumentFilesPreviewDialog v-if="showPreviewFilesDialog" :title="selectedDocument.title"
    :files="selectedDocument.files" @close="showPreviewFilesDialog = false">
  </DocumentFilesPreviewDialog>
  <DocumentNotesPreviewDialog v-if="showPreviewNotesDialog" :documentId="selectedDocument.id"
    :documentTitle="selectedDocument.title" @close="showPreviewNotesDialog = false">
  </DocumentNotesPreviewDialog>
</template>

<script setup>

import { ref, reactive, computed, onMounted, onBeforeUnmount } from "vue";
import { useI18n } from "vue-i18n";
import { date, format } from "quasar";

import { bus } from "src/boot/bus";
import { api } from "src/boot/axios";
import { useFormatDates } from "src/composables/formatDate"

import { default as CustomExpansionWidget } from "src/components/Widgets/CustomExpansionWidget.vue";
import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue";
import { default as BrowseByTagButton } from "src/components/Buttons/BrowseByTagButton.vue";
import { default as ViewDocumentDetailsButton } from "src/components/Buttons/ViewDocumentDetailsButton.vue";
import { default as DocumentFilesPreviewDialog } from "src/components/Dialogs/DocumentFilesPreviewDialog.vue";
import { default as DocumentNotesPreviewDialog } from "src/components/Dialogs/DocumentNotesPreviewDialog.vue";

const { t } = useI18n();
const { timeAgo } = useFormatDates();

const props = defineProps({
  expanded: {
    type: Boolean,
    required: false,
    default: true
  }
});

const isExpanded = ref(props.expanded);

const state = reactive({
  loading: false,
  loadingError: false,
  errorMessage: null,
  apiError: null
});

const recentDocuments = reactive([]);
const hasRecentDocuments = computed(() => recentDocuments.length > 0);

const selectedDocument = reactive({
  id: null,
  title: null,
  files: [],
  notes: [],
});

const showPreviewFilesDialog = ref(false);
const showPreviewNotesDialog = ref(false);

const onRefresh = () => {
  if (!state.loading) {
    state.loading = true;
    state.loadingError = false;
    state.errorMessage = null;
    state.apiError = null;
    api.document.searchRecent(16)
      .then((successResponse) => {
        recentDocuments.length = 0;
        recentDocuments.push(...successResponse.data.recentDocuments.map((document) => {
          document.timestamp = document.lastUpdateTimestamp;
          return document;
        }));
        state.loading = false;
      })
      .catch((errorResponse) => {
        state.loadingError = true;
        switch (errorResponse.response.status) {
          case 401:
            state.apiError = errorResponse.customAPIErrorDetails;
            state.errorMessage = "Auth session expired, requesting new...";
            bus.emit("reAuthRequired", { emitter: "RecentDocumentsWidget" });
            break;
          default:
            state.apiError = errorResponse.customAPIErrorDetails;
            state.errorMessage = "API Error: fatal error";
            break;
        }
        state.loading = false;
      });
  }
};

const onShowDocumentFiles = (documentId, documentTitle) => {
  // TODO: use new dialog (LIKE DOCUMENT NOTES, NOT DONE)
  if (!state.loading) {
    selectedDocument.title = null;
    selectedDocument.notes.length = 0;
    selectedDocument.files.length = 0;
    state.loading = true;
    api.document
      .get(documentId)
      .then((successResponse) => {
        selectedDocument.id = documentId;
        selectedDocument.title = documentTitle;
        selectedDocument.files.push(...successResponse.data.document.files.map((file) => {
          file.isNew = false;
          file.uploadedOn = date.formatDate(file.uploadedOnTimestamp, 'YYYY-MM-DD HH:mm:ss');
          file.humanSize = format.humanStorageSize(file.size);
          file.url = "api2/file/" + file.id;
          return (file)
        }));
        state.loading = false;
        showPreviewFilesDialog.value = selectedDocument.files.length > 0;
      })
      .catch((errorResponse) => {
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
  }
}

const onShowDocumentNotes = (documentId, documentTitle) => {
  if (!state.loading) {
    selectedDocument.id = documentId;
    selectedDocument.title = documentTitle;
    showPreviewNotesDialog.value = true;
  }
};

onMounted(() => {
  onRefresh();
  bus.on("reAuthSucess", (msg) => {
    if (msg.to?.includes("RecentDocumentsWidget")) {
      onRefresh();
    }
  });
});

onBeforeUnmount(() => {
  bus.off("reAuthSucess");
});

</script>