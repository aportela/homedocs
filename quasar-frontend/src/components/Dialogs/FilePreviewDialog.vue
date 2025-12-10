<template>
  <BaseDialog v-model="visible" @close="onClose" width="1280px" max-width="80vw">
    <template v-slot:header-left>
      <div v-if="document.title">{{ t("Document title") }}:
        <router-link :to="{ name: 'document', params: { id: document.id } }" class="text-decoration-hover">{{
          document.title }}</router-link>
      </div>
      <div v-else>{{ t("Document attachments preview", {
        count:
          attachmentsCount
      }) }}</div>
    </template>
    <template v-slot:header-right>
      <q-chip size="md" square class="gt-sm theme-default-q-chip shadow-1">
        <q-avatar class="theme-default-q-avatar">{{ attachmentsCount }}</q-avatar>
        {{ t("Total attachments count", { count: attachmentsCount }) }}
      </q-chip>
    </template>
    <template v-slot:body v-if="currentAttachment">
      <div>
        <p class="text-center text-bold q-my-md">{{ currentAttachment.name }} ({{ currentAttachment.humanSize }})</p>
        <q-pagination class="flex flex-center q-my-md" v-if="attachmentsCount > 1" v-model="currentAttachmentIndex"
          :max="attachmentsCount" color="dark" :max-pages="5" boundary-numbers direction-links
          icon-first="skip_previous" icon-last="skip_next" icon-prev="fast_rewind" icon-next="fast_forward" gutter="md"
          @update:model-value="onPaginationChange" />
      </div>
      <div class="q-pt-none">
        <q-img v-if="isImage(currentAttachment.name)" v-show="!previewLoadingError"
          :src="getAttachmentInlineURL(currentAttachment.id, true)" loading="lazy" spinner-color="white"
          @error="onImageLoadError" alt="image preview" fit="scale-down" class="q-img-max-height q-my-md"
          img-class="q-mx-auto" />
        <div v-else-if="isAudio(currentAttachment.name)">
          <p class="text-center q-my-md">
            <q-icon name="audio_file" size="128px"></q-icon>
          </p>
          <audio controls class="q-mt-md full-width">
            <source :src="getAttachmentInlineURL(currentAttachment.id, true)" type="audio/mpeg" />
            {{ t("Your browser does not support the audio element") }}
          </audio>
        </div>
        <div v-else-if="isPDF(currentAttachment.name)" class="pdf-container q-mx-auto q-mb-md">
          <PDFWrapper :path="getAttachmentInlineURL(currentAttachment.id, true)"
            v-if="browserSupportStore.allowPDFPreviews" inner-content-class="pdf-wrapper-inner-class">
          </PDFWrapper>
          <CustomErrorBanner v-else :text="t('Missing browser PDF preview support')" />
        </div>
        <div v-else>
          <p class="text-center q-my-md">
            <q-icon name="hide_source" size="128px"></q-icon>
          </p>
        </div>
        <div class="text-subtitle1 text-center" v-if="previewLoadingError">
          <p class="text-center q-my-md">
            <q-icon name="bug_report" size="128px"></q-icon>
          </p>
        </div>
      </div>
    </template>
    <template v-slot:actions-prepend v-if="currentAttachment">
      <CustomBanner v-if="downloadBanner.visible" :success="downloadBanner.success" :error="downloadBanner.error">
        <template v-slot:text>{{ downloadBanner.text }}</template>
      </CustomBanner>
      <CustomBanner warning v-else-if="!allowPreview(currentAttachment.name)">
        <template v-slot:text>
          {{ t("Attachment preview not available", { filename: currentAttachment.name }) }}
        </template>
      </CustomBanner>
      <q-space />
      <q-btn color="primary" no-caps :href="getAttachmentURL(currentAttachment.id, true)" :label="t('Download')"
        icon="download" @click.stop.prevent.stop="onDownload(currentAttachment.id, currentAttachment.name)"
        aria-label="Download file" />
    </template>
  </BaseDialog>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from "vue";
import { useI18n } from "vue-i18n";
import { bgDownload } from "src/composables/axios";
import { allowPreview, isImage, isAudio, isPDF } from "src/composables/fileUtils";
import { getURL as getAttachmentURL, getInlineURL as getAttachmentInlineURL } from "src/composables/attachment";
import { useBrowserSupportStore } from "src/stores/browserSupport";
import { type Document } from "src/types/document";
import { type CustomBanner as CustomBannerInterface, defaultCustomBanner } from "src/types/customBanner";

import { default as BaseDialog } from "src/components/Dialogs/BaseDialog.vue";
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue";
import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";
import { type Attachment as AttachmentInterface } from "src/types/attachment";
import { default as PDFWrapper } from "src/components/PDFWrapper.vue";
import { DateTimeClass } from "src/types/dateTime";

const { t } = useI18n();

const emit = defineEmits(['close']);

const previewLoadingError = ref(false);

interface FilePreviewDialogProps {
  document: Document;
  currentIndex?: number;
};

const props = withDefaults(defineProps<FilePreviewDialogProps>(), {
  currentIndex: 0
});

const visible = ref<boolean>(true);

const hasAttachments = computed(() => props.document?.attachments?.length > 0);
const attachmentsCount = computed(() => hasAttachments.value ? props.document?.attachments?.length : 0);

const currentAttachmentIndex = ref<number>(props.currentIndex + 1 || 1);

const currentAttachment = computed(() => props.document.attachments.length > 0 && currentAttachmentIndex.value <= props.document.attachments.length ? props.document.attachments[currentAttachmentIndex.value - 1] : <AttachmentInterface>{
  id: '',
  name: '',
  size: 0,
  hash: '',
  humanSize: '',
  createdAt: new DateTimeClass(t, null),
  orphaned: true,
});

const onClose = () => {
  emit('close');
};

const downloadBanner: CustomBannerInterface = reactive({ ...defaultCustomBanner });

const browserSupportStore = useBrowserSupportStore();

const onPaginationChange = () => {
  Object.assign(downloadBanner, defaultCustomBanner);
  previewLoadingError.value = false
};

const onImageLoadError = () => {
  previewLoadingError.value = true;
  downloadBanner.success = false;
  downloadBanner.error = true;
  downloadBanner.text = t("Error loading preview");
  downloadBanner.visible = true;
};

const onDownload = (attachmentId: string, fileName: string) => {
  Object.assign(downloadBanner, defaultCustomBanner);
  bgDownload(getAttachmentURL(attachmentId), fileName)
    .then((successResponse) => {
      downloadBanner.success = true;
      downloadBanner.text = t("FileDownloadedMessage", { filename: successResponse.fileName, length: successResponse.length });
    })
    .catch(() => {
      downloadBanner.error = true;
      downloadBanner.text = t("FileDownloadErrorMessage", { filename: fileName });
    }).finally(() => {
      downloadBanner.visible = true;
    });
}
</script>

<style>
.q-img-max-height {
  max-height: 40vh;
}

.pdf-container {
  width: 98%;
  min-height: 70vh;
}

.pdf-wrapper-inner-class {
  height: 70vh;
}
</style>
