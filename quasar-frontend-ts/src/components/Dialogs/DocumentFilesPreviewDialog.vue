<template>
  <BaseDialog v-model="visible" @close="onClose" width="1280px" max-width="80vw">
    <template v-slot:header-left>
      <div v-if="documentTitle">{{ t("Document title")
      }}: <router-link :to="{ name: 'document', params: { id: documentId } }" class="text-decoration-hover">{{
          documentTitle
        }}</router-link>
      </div>
      <div v-else>{{ t("Document attachments") }}</div>
    </template>
    <template v-slot:header-right>
      <q-chip size="md" square class="gt-sm theme-default-q-chip shadow-1"
        v-if="!state.ajaxRunning && !state.ajaxErrors">
        <q-avatar class="theme-default-q-avatar">{{ attachments.length }}</q-avatar>
        {{ t("Total attachments count", { count: attachments.length }) }}
      </q-chip>
    </template>
    <template v-slot:body>
      <div class="q-pt-none scroll attachments-scrolled-container">
        <div v-if="state.ajaxRunning"></div>
        <div v-else-if="state.ajaxErrors">
          <CustomErrorBanner :text="state.ajaxErrorMessage || 'Error loading data'"
            :api-error="state.ajaxAPIErrorDetails">
          </CustomErrorBanner>
        </div>
        <div v-else-if="!hasAttachments">
          <CustomBanner warning text="This document has no associated attachments."></CustomBanner>
        </div>
        <q-list v-else>
          <div v-for="attachment, index in attachments" :key="attachment.id" class="border-bottom-except-last-item">
            <q-item class="transparent-background text-color-primary q-pa-sm cursor-default" clickable>
              <q-item-section>
                <div class="row full-width">
                  <div class="col-xl-10 col-lg-10 col-md-9 col-sm-12 col-xs-12">
                    <q-item-label>
                      <span class="text-weight-bold">{{ t("Name") }}:</span> {{ attachment.name }}
                    </q-item-label>
                    <q-item-label>
                      <span class="text-weight-bold">{{ t("Size") }}:</span> {{ attachment.humanSize }}</q-item-label>
                    <q-item-label>
                      <span class="text-weight-bold">{{ t('Uploaded on') }}:</span> {{ attachment.createdOn }} ({{
                        attachment.createdOnTimeAgo }})</q-item-label>
                  </div>
                  <div class="col-xl-2 col-lg-2 col-md-3 col-sm-12 col-xs-12">
                    <q-btn align="left" size="md" color="primary" class="q-mt-sm full-width"
                      :disable="state.ajaxRunning" icon="save" :label="t('Download')" no-caps
                      @click.stop.prevent="onDownload(attachment.id, attachment.name)"
                      :href="getAttachmentURL(attachment.id, true)" />
                    <q-btn align="left" size="md" color="primary" class="q-mt-sm full-width"
                      v-if="allowPreview(attachment.name)" :disable="state.ajaxRunning" icon="preview"
                      :label="t('Preview')" no-caps @click.stop.prevent="onFilePreview(index)" />
                  </div>
                </div>
              </q-item-section>
            </q-item>
          </div>
        </q-list>
      </div>
    </template>
    <template v-slot:actions-prepend>
      <CustomBanner v-if="downloadBanner.visible" :success="downloadBanner.success" :error="downloadBanner.error">
        <template v-slot:text>{{ downloadBanner.text }}</template>
      </CustomBanner>
      <q-space />
    </template>
  </BaseDialog>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, onBeforeUnmount } from "vue";
import { useI18n } from "vue-i18n";
import { date, format } from "quasar";
import { useBus } from "src/composables/useBus";
import { useFormatDates } from "src/composables/useFormatDates"
import { allowPreview } from "src/composables/useFileUtils"
import { bgDownload } from "src/composables/useAxios";
import { api } from "src/composables/useAPI";
import { type AjaxState as AjaxStateInterface, defaultAjaxState } from "src/types/ajax-state";
import { type Attachment as AttachmentInterface } from "src/types/attachment";
import { type CustomBanner as CustomBannerInterface, defaultCustomBanner } from "src/types/custom-banner";
import { getURL as getAttachmentURL } from "src/composables/useAttachments";

import { default as BaseDialog } from "src/components/Dialogs/BaseDialog.vue";
import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue";

const { t } = useI18n();
const { timeAgo } = useFormatDates();
const { bus } = useBus();

interface DocumentFilesPreviewDialogProps {
  documentId: string;
  documentTitle?: string;
};

const props = defineProps<DocumentFilesPreviewDialogProps>();

const emit = defineEmits(['close']);

const visible = ref(true);

const state: AjaxStateInterface = reactive({ ...defaultAjaxState });

const attachments = reactive<Array<AttachmentInterface>>([]);
const hasAttachments = computed(() => attachments?.length > 0);

const onFilePreview = (index: number) => {
  bus.emit("showDocumentFilePreviewDialog", { document: { id: props.documentId, title: props.documentTitle, attachments: attachments }, currentIndex: index });
};

const downloadBanner: CustomBannerInterface = reactive({ ...defaultCustomBanner });

const onDownload = (attachmentId: string, fileName: string) => {
  Object.assign(downloadBanner, defaultCustomBanner);
  bgDownload(getAttachmentURL(attachmentId), fileName)
    .then((successResponse) => {
      downloadBanner.success = true;
      downloadBanner.text = t("FileDownloadedMessage", { filename: successResponse.fileName, length: format.humanStorageSize(successResponse.length) });
    })
    .catch(() => {
      downloadBanner.error = true;
      downloadBanner.text = t("FileDownloadeErrorMessage", { filename: fileName });
    }).finally(() => {
      downloadBanner.visible = true;
    });
}

const onRefresh = (documentId: string) => {
  if (!state.ajaxRunning) {
    Object.assign(state, defaultAjaxState);
    state.ajaxRunning = true;
    api.document
      .getAttachments(documentId)
      .then((successResponse) => {
        attachments.length = 0;
        attachments.push(...successResponse.data.attachments.map((attachment: AttachmentInterface) => {
          // TODO: use local storage datetime format ?
          attachment.createdOn = date.formatDate(attachment.createdOnTimestamp, 'YYYY-MM-DD HH:mm:ss');
          attachment.createdOnTimeAgo = timeAgo(attachment.createdOnTimestamp);
          attachment.humanSize = format.humanStorageSize(attachment.size);
          attachment.orphaned = false;
          return (attachment);
        }));
      })
      .catch((errorResponse) => {
        state.ajaxErrors = true;
        if (errorResponse.isAPIError) {
          state.ajaxAPIErrorDetails = errorResponse.customAPIErrorDetails;
          switch (errorResponse.response.status) {
            case 401:
              state.ajaxErrorMessage = "Auth session expired, requesting new...";
              bus.emit("reAuthRequired", { emitter: "DocumentFilesPreviewDialog" });
              break;
            default:
              state.ajaxErrorMessage = "API Error: fatal error";
              break;
          }
        } else {
          state.ajaxErrorMessage = `Uncaught exception: ${errorResponse}`;
          console.error(errorResponse);
        }
      }).finally(() => {
        state.ajaxRunning = false;
      });
  }
};

const onClose = () => {
  emit('close');
};

onMounted(() => {
  onRefresh(props.documentId);
  bus.on("reAuthSucess", (msg) => {
    if (msg.to?.includes("DocumentFilesPreviewDialog")) {
      onRefresh(props.documentId);
    }
  });
});

onBeforeUnmount(() => {
  bus.off("reAuthSucess");
});
</script>

<style lang="css" scoped>
.attachments-scrolled-container {
  max-height: 50vh;
}
</style>