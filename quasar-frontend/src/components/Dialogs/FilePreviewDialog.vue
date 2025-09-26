<template>
  <q-dialog v-model="dialogModel" @hide="onHide">
    <q-card class="q-card-file-preview-dialog">
      <q-card-section class="row items-center q-p-none">
        <div class="q-card-file-preview-dialog-header max-width-90" v-if="document.title">{{ t("Document title") }}:
          <router-link :to="{ name: 'document', params: { id: document.id } }" class="text-decoration-hover">{{
            document.title }}</router-link>
        </div>
        <!-- TODO use plurals in translation attachments?.length > 0 ?-->
        <div class="q-card-attachments-dialog-header" v-else>{{ t("Document attachments preview", {
          count:
            attachmentsCount
        }) }}</div>
        <q-space />
        <q-chip size="md" square class="gt-md theme-default-q-chip" v-if="hasAttachments">
          <q-avatar class="theme-default-q-avatar">{{ attachmentsCount }}</q-avatar>
          {{ t("Total files", { count: attachmentsCount }) }}
        </q-chip>
        <q-btn icon="close" flat round dense v-close-popup class="gt-md" aria-label="Close modal" />
      </q-card-section>
      <q-separator class="q-mb-md"></q-separator>
      <q-card-section class="q-pt-none scroll file-preview-scrolled-container">
        <p class="text-center text-bold">{{ currentAttachment.name }} ({{ currentAttachment.humanSize }})</p>
        <q-pagination class="flex flex-center q-my-md" v-if="attachmentsCount > 1" v-model="currentAttachmentIndex"
          :max="attachmentsCount" color="dark" :max-pages="5" boundary-numbers direction-links
          icon-first="skip_previous" icon-last="skip_next" icon-prev="fast_rewind" icon-next="fast_forward" gutter="md"
          @update:model-value="onPaginationChange" />
        <!-- TODO: resize image to FIT on dialog height -->
        <q-img v-if="isImage(currentAttachment.name)" v-show="!previewLoadingError" :src="currentAttachment.url"
          loading="lazy" spinner-color="white" @error="onImageLoadError" fit>
        </q-img>
        <div v-else-if="isAudio(currentAttachment.name)">
          <p class="text-center q-my-md">
            <q-icon name="audio_file" size="192px"></q-icon>
          </p>
          <audio controls class="q-mt-md" style="width: 100%;">
            <source :src="currentAttachment.url" type="audio/mpeg" />
            {{ t("Your browser does not support the audio element") }}
          </audio>
        </div>
        <div v-else>
          <p class="text-center q-my-md">
            <q-icon name="hide_source" size="192px"></q-icon>
          </p>
          <CustomBanner warning text="Preview not available"></CustomBanner>
        </div>
        <div class="text-subtitle1 text-center" v-if="previewLoadingError">
          <p class="text-center q-my-md">
            <q-icon name="bug_report" size="192px"></q-icon>
          </p>
        </div>
      </q-card-section>
      <q-separator class="q-my-sm"></q-separator>
      <q-card-section class="q-pt-none">
        <q-card-actions align="right">
          <CustomBanner v-if="downloadBanner.visible" :success="downloadBanner.success"
            :warning="downloadBanner.warning" :error="downloadBanner.error">
            <template v-slot:text>{{ downloadBanner.text }}</template>
          </CustomBanner>
          <q-space></q-space>
          <q-btn color="primary" :href="currentAttachment.url" :label="t('Download')" icon="download"
            @click.stop.prevent="onDownload(currentAttachment.url, currentAttachment.name)"
            aria-label="Download file" />
          <q-btn color="primary" v-close-popup :label="t('Close')" icon="close" aria-label="Close modal" />
        </q-card-actions>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, reactive, computed } from "vue";
import { useI18n } from "vue-i18n";
import { bgDownload } from "src/boot/axios";
import { useFileUtils } from "src/composables/fileUtils";

import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue";

const { t } = useI18n();

const emit = defineEmits(['close']);

const dialogModel = ref(true);

const { isImage, isAudio } = useFileUtils();
const previewLoadingError = ref(false);

const props = defineProps({
  document: {
    type: Object,
    required: true
  },
  currentIndex: {
    type: Number,
    required: false,
    default: 0
  }
});

const hasAttachments = computed(() => props.document?.attachments?.length > 0);
const attachmentsCount = computed(() => hasAttachments.value ? props.document?.attachments?.length : 0);

const currentAttachmentIndex = ref(props.currentIndex + 1 || 1);
const currentAttachment = computed(() => props.document.attachments?.length > 0 ? props.document.attachments[currentAttachmentIndex.value - 1] : {})

const onHide = () => {
  emit('close');
};

const downloadBanner = reactive({
  visible: false,
  success: false,
  error: false,
  warning: false,
  text: null
});

const onPaginationChange = () => {
  downloadBanner.visible = false;
  downloadBanner.success = false;
  downloadBanner.warning = false;
  downloadBanner.error = false;
  downloadBanner.text = null;
  previewLoadingError.value = false
};

const onImageLoadError = () => {
  previewLoadingError.value = true;
  downloadBanner.success = false;
  downloadBanner.warning = false;
  downloadBanner.error = true;
  downloadBanner.text = t("Error loading preview");
  downloadBanner.visible = true;
};

const onDownload = (url, fileName) => {
  downloadBanner.visible = false;
  downloadBanner.success = false;
  downloadBanner.warning = false;
  downloadBanner.error = false;
  downloadBanner.text = null;
  bgDownload(url, fileName)
    .then((successResponse) => {
      downloadBanner.success = true;
      downloadBanner.text = t("FileDownloadedMessage", { filename: successResponse.fileName, length: successResponse.length });
      downloadBanner.visible = true;
    })
    .catch((errorResponse) => {
      downloadBanner.error = true;
      downloadBanner.text = t("FileDownloadeErrorMessage", { filename: fileName });
      downloadBanner.visible = true;
    });
}

</script>

<style scoped>
.q-card-file-preview-dialog {
  width: 1024px;
  max-width: 80vw;
}

.q-card-file-preview-dialog-header {
  font-size: 1.2em;
  font-weight: bold;
}

.q-card-file-preview-dialog-header a {
  font-weight: normal;
}

.max-width-90 {
  max-width: 90%;
}

.file-preview-scrolled-container {
  height: 50vh
}
</style>