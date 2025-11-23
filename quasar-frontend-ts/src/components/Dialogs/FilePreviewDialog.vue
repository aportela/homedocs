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
    <template v-slot:body>
      <div>
        <p class="text-center text-bold q-my-md">{{ currentAttachment.name }} ({{ currentAttachment.humanSize }})</p>
        <q-pagination class="flex flex-center q-my-md" v-if="attachmentsCount > 1" v-model="currentAttachmentIndex"
          :max="attachmentsCount" color="dark" :max-pages="5" boundary-numbers direction-links
          icon-first="skip_previous" icon-last="skip_next" icon-prev="fast_rewind" icon-next="fast_forward" gutter="md"
          @update:model-value="onPaginationChange" />
      </div>
      <div class="q-pt-none">
        <q-img v-if="isImage(currentAttachment.name)" v-show="!previewLoadingError"
          :src="currentAttachment.url + '/inline'" loading="lazy" spinner-color="white" @error="onImageLoadError"
          alt="image preview" fit="scale-down" class="q-img-max-height q-my-md" img-class="q-mx-auto" />
        <div v-else-if="isAudio(currentAttachment.name)">
          <p class="text-center q-my-md">
            <q-icon name="audio_file" size="128px"></q-icon>
          </p>
          <audio controls class="q-mt-md full-width">
            <source :src="currentAttachment.url + '/inline'" type="audio/mpeg" />
            {{ t("Your browser does not support the audio element") }}
          </audio>
        </div>
        <div v-else-if="isPDF(currentAttachment.name)" class="q-pdf-min-height">
          <q-pdfviewer :src="currentAttachment.url + ('/inline')" type="html5"
            inner-content-class="q-pdfviewer-min-height" />
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
    <template v-slot:actions-prepend>
      <CustomBanner v-if="downloadBanner.visible" :success="downloadBanner.success" :error="downloadBanner.error">
        <template v-slot:text>{{ downloadBanner.text }}</template>
      </CustomBanner>
      <CustomBanner warning v-else-if="!allowPreview(currentAttachment.name)">
        <template v-slot:text>
          {{ t("File preview not available", { filename: currentAttachment.name }) }}
        </template>
      </CustomBanner>
      <q-space />
      <q-btn color="primary" no-caps :href="currentAttachment.url" :label="t('Download')" icon="download"
        @click.stop.prevent.stop="onDownload(currentAttachment.url + '/inline', currentAttachment.name)"
        aria-label="Download file" />
    </template>
  </BaseDialog>
</template>

<script setup>
import { ref, reactive, computed } from "vue";
import { useI18n } from "vue-i18n";
import { useAxios } from "src/composables/useAxios";
import { useFileUtils } from "src/composables/useFileUtils";

import { default as BaseDialog } from "src/components/Dialogs/BaseDialog.vue";
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue";

const { t } = useI18n();

const emit = defineEmits(['close']);

const { bgDownload } = useAxios();
const { allowPreview, isImage, isAudio, isPDF } = useFileUtils();
const previewLoadingError = ref(false);

const props = defineProps({
  // TODO: do not pass complete document
  document: {
    type: Object,
    required: true
  },
  currentIndex: {
    type: Number,
    required: false,
    default: 0,
    validator(value) {
      return (value >= 0);
    }
  }
});

const visible = ref(true);

const hasAttachments = computed(() => props.document?.attachments?.length > 0);
const attachmentsCount = computed(() => hasAttachments.value ? props.document?.attachments?.length : 0);

const currentAttachmentIndex = ref(props.currentIndex + 1 || 1);
const currentAttachment = computed(() => props.document.attachments?.length > 0 ? props.document.attachments[currentAttachmentIndex.value - 1] : {})

const onClose = () => {
  emit('close');
};

const downloadBanner = reactive({
  visible: false,
  success: false,
  error: false,
  text: null
});

const onPaginationChange = () => {
  downloadBanner.visible = false;
  downloadBanner.success = false;
  downloadBanner.error = false;
  downloadBanner.text = null;
  previewLoadingError.value = false
};

const onImageLoadError = () => {
  previewLoadingError.value = true;
  downloadBanner.success = false;
  downloadBanner.error = true;
  downloadBanner.text = t("Error loading preview");
  downloadBanner.visible = true;
};

const onDownload = (url, fileName) => {
  downloadBanner.visible = false;
  downloadBanner.success = false;
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

<style>
.q-img-max-height {
  max-height: 40vh;
}

.q-pdf-min-height {
  min-height: 70vh;
}

.q-pdfviewer-min-height {
  height: 70vh;
}
</style>
