<template>
  <q-dialog v-model="dialogModel" @hide="onHide">
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
            <!-- TODO: clickable to preview if allowed or download -->
            <q-item class="transparent-background text-color-primary q-pa-sm" clickable :href="attachment.url"
              @click.stop.prevent="onDownload(attachment.url, attachment.name)">
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
                <q-btn size="md" color="primary" class="q-mt-sm" v-if="allowPreview(attachment.name)"
                  :disable="state.loading" icon="preview" :label="t('Preview')"
                  @click.stop.prevent="onFilePreview(index)"></q-btn>
              </q-item-section>
              <q-tooltip>{{ t("Click to download") }}</q-tooltip>
            </q-item>
            <q-separator v-if="index !== attachments.length - 1" class="q-my-xs" />
          </div>
        </q-list>
      </q-card-section>
      <q-separator class="q-my-sm"></q-separator>
      <q-card-section class="q-pt-none">
        <q-card-actions align="right">
          <CustomBanner v-if="downloadBanner.visible" :success="downloadBanner.success" :error="downloadBanner.error">
            <template v-slot:text>{{ downloadBanner.text }}</template>
          </CustomBanner>
          <q-space></q-space>
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
import { api, bgDownload } from "src/boot/axios";

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
  }
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

const dialogModel = ref(true);

const onFilePreview = (index) => {
  bus.emit("showDocumentFilePreviewDialog", { document: { id: props.documentId, title: props.documentTitle, attachments: attachments }, currentIndex: index });
};

function allowPreview(filename) {
  return (!!filename.match(/.(jpg|jpeg|png|gif|mp3)$/i));
}

const downloadBanner = reactive({
  visible: false,
  success: false,
  error: false,
  text: null
});

const onDownload = (url, fileName) => {
  downloadBanner.visible = false;
  downloadBanner.success = false;
  downloadBanner.error = false;
  downloadBanner.text = null;
  bgDownload(url, fileName)
    .then((successResponse) => {
      downloadBanner.success = true;
      downloadBanner.text = t("FileDownloadedMessage", { filename: successResponse.fileName, length: format.humanStorageSize(successResponse.length) });
      downloadBanner.visible = true;
    })
    .catch((errorResponse) => {
      downloadBanner.error = true;
      downloadBanner.text = t("FileDownloadeErrorMessage", { filename: fileName });
      downloadBanner.visible = true;
    });
}

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

const onHide = () => {
  emit('close');
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