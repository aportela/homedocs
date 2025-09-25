<template>
  <q-dialog v-model="dialogModel" @hide="emit('close')">
    <q-card class="q-card-attachments-dialog">
      <q-card-section class="row items-center q-p-none">
        <div class="q-card-attachments-dialog-header max-width-90" v-if="documentTitle">{{ t("Document title")
        }}: <router-link :to="{ name: 'document', params: { id: documentId } }" class="text-decoration-hover">{{
            documentTitle
            }}</router-link>
        </div>
        <div class="q-card-attachments-dialog-header" v-else>{{ t("Document attachments") }}</div>
        <q-space />
        <q-chip size="md" square class="gt-md theme-default-q-chip" v-if="!state.loading && !state.loadingError">
          <q-avatar class="theme-default-q-avatar">{{ attachments.length }}</q-avatar>
          {{ t("Total files", { count: attachments.length }) }}
        </q-chip>
        <q-btn icon="close" flat round dense v-close-popup class="gt-md" aria-label="Close modal"
          :disable="state.loading" />
      </q-card-section>
      <q-separator class="q-mb-md"></q-separator>
      <q-card-section class="q-pt-none scroll attachments-scrolled-container">
        <div v-if="state.loading"></div>
        <div v-else-if="state.loadingError">
          <CustomErrorBanner :text="state.errorMessage || 'Error loading data'" :apiError="state.apiError">
          </CustomErrorBanner>
        </div>
        <div v-else-if="!hasAttachments">
          <CustomBanner warning text="This document has no associated attachments."></CustomBanner>
        </div>
        <q-list v-else>
          <div v-for="attachment, index in attachments" :key="attachment.id">
            <q-item class="transparent-background text-color-primary q-pa-sm">
              <q-item-section top>
                <q-item-label>
                  <span class="text-weight-bold">{{ t("Name") }}:</span> {{ attachment.name }}
                </q-item-label>
                <q-item-label>
                  <span class="text-weight-bold">{{ t("Size") }}:</span> {{ attachment.humanSize }}</q-item-label>
                <q-item-label>
                  <span class="text-weight-bold">{{ t('Uploaded on') }}:</span> {{ attachment.uploadedOn }} ({{
                    timeAgo(attachment.uploadedOnTimestamp)
                  }})</q-item-label>
              </q-item-section>
              <q-item-section top side>
                <q-chip size="md" square class="theme-default-q-chip shadow-1 full-width" :clickable="!state.loading">
                  <q-avatar class="theme-default-q-avatar text-white bg-blue-6"><q-icon
                      name="preview"></q-icon></q-avatar>
                  {{ t("Open/Preview") }}
                </q-chip>
                <q-chip size="md" square class="theme-default-q-chip shadow-1 full-width" :clickable="!state.loading">
                  <q-avatar class="theme-default-q-avatar text-white bg-blue-6"><q-icon
                      name="download"></q-icon></q-avatar>
                  {{ t("Download") }}
                </q-chip>
              </q-item-section>
            </q-item>
            <q-separator v-if="index !== attachments.length - 1" class="q-my-xs" />
          </div>
        </q-list>
      </q-card-section>
      <!--
      <q-card-section class="row items-center q-p-none">
        <div class="q-card-notes-dialog-header" v-if="title">{{ t("Document title") }}: <span>{{ title }}</span>
        </div>
        <div class="q-card-notes-dialog-header" v-else>{{ t("File preview") }}</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>
      -->
      <!--
      <q-card-section class="q-pt-none">
        <p class="text-center"><strong>{{ currentFile.name }} ({{ currentFile.humanSize }})</strong></p>
        <q-pagination class="flex flex-center" v-if="totalFiles > 1" v-model="currentIndex" :max="totalFiles"
          color="dark" :max-pages="5" boundary-numbers direction-links icon-first="skip_previous" icon-last="skip_next"
          icon-prev="fast_rewind" icon-next="fast_forward" gutter="md"
          @update:model-value="previewLoadingError = false" />
      </q-card-section>
      <q-card-section class="q-pt-none">
        <div>
          <q-img v-if="isImage(currentFile.name)" :src="currentFile.url" loading="lazy" spinner-color="white"
            @error="previewLoadingError = true">
          </q-img>
          <div v-else-if="isAudio(currentFile.name)">
            <audio controls class="q-mt-md" style="width: 100%;">
              <source :src="currentFile.url" type="audio/mpeg" />
              {{ t("Your browser does not support the audio element") }}
            </audio>
          </div>
          <div v-else>
            <q-banner inline-actions>
              <q-icon name="error" size="sm" />
              {{ t("Preview not available") }}
            </q-banner>
          </div>
          <div class="text-subtitle1 text-center" v-if="previewLoadingError">
            <q-banner inline-actions>
              <q-icon name="error" size="sm" />
              {{ t("Error loading preview") }}
            </q-banner>
          </div>
        </div>
      <q-card-actions align="right">
        <q-btn color="primary" :href="currentFile.url" :label="t('Download')" icon="download"
          :disable="previewLoadingError" />
        <q-btn color="primary" v-close-popup :label="t('Close')" icon="close" />
      </q-card-actions>
      </q-card-section>
        -->
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
import { ref, reactive, computed, onMounted, onBeforeUnmount } from "vue";
import { useI18n } from "vue-i18n";
import { date, format } from "quasar";
import { bus } from "src/boot/bus";
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
  /*
  title: {
    type: String,
    required: false,
    default: ""
  },
  files: {
    type: Array,
    required: true
  },
  index: {
    type: Number,
    required: false,
    default: 0
  }
    */
});

const emit = defineEmits(['close']);

const state = reactive({
  loading: false,
  loadingError: false,
  errorMessage: null,
  apiError: null
});

const attachments = reactive([]);
const hasAttachments = computed(() => attachments?.length > 0);

/*
const currentIndex = ref(props.index + 1 || 1);
const totalFiles = ref(props.files?.length || 0);
if (currentIndex.value > totalFiles.value) {
  currentIndex.value = 1;
}

const currentFile = computed(() => props.files && totalFiles.value > 0 ? props.files[currentIndex.value - 1] : { id: null, name: null, humanSize: 0 });

*/
const dialogModel = ref(true);

const previewLoadingError = ref(false);

function allowPreview(filename) {
  return (filename.match(/.(jpg|jpeg|png|gif|mp3)$/i));
}

/*
function isImage(filename) {
  return (filename.match(/.(jpg|jpeg|png|gif)$/i));
}

function isAudio(filename) {
  return (filename.match(/.(mp3)$/i));
}
*/
const onRefresh = (documentId) => {
  if (documentId) {
    if (!state.loading) {
      state.loading = true;
      state.loadingError = false;
      state.errorMessage = null;
      state.apiError = null;
      api.document
        .getAttachments(documentId)
        .then((successResponse) => {
          attachments.length = 0;
          attachments.push(...successResponse.data.attachments.map((attachment) => {
            attachment.uploadedOn = date.formatDate(attachment.uploadedOnTimestamp, 'YYYY-MM-DD HH:mm:ss');
            attachment.humanSize = format.humanStorageSize(attachment.size);
            attachment.url = "api2/file/" + attachment.id;
            return (attachment);
          }));
          state.loading = false;
        })
        .catch((errorResponse) => {
          state.loadingError = true;
          switch (errorResponse.response.status) {
            case 401:
              state.apiError = errorResponse.customAPIErrorDetails;
              state.errorMessage = "Auth session expired, requesting new...";
              bus.emit("reAuthRequired", { emitter: "DocumentFilesPreviewDialog" });
              break;
            default:
              state.apiError = errorResponse.customAPIErrorDetails;
              state.errorMessage = "API Error: fatal error";
              break;
          }
          state.loading = false;
        });
    }
  } else {
    // TODO
    state.loadingError = true;
  }
};

onMounted(() => {
  onRefresh(props.documentId || null);
  bus.on("reAuthSucess", (msg) => {
    if (msg.to?.includes("DocumentFilesPreviewDialog")) {
      onRefresh(props.documentId || null);
    }
  });
});

onBeforeUnmount(() => {
  bus.off("reAuthSucess");
});

</script>

<style scoped>
.q-card-attachments-dialog {
  width: 1280px;
  max-width: 80vw;
}

.q-card-attachments-dialog-header {
  font-size: 1.2em;
  font-weight: bold;
}

.q-card-attachments-dialog-header span {
  font-weight: normal;
}

.attachments-scrolled-container {
  max-height: 50vh
}
</style>