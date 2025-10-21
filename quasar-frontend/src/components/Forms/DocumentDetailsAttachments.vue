<template>
  <q-list class="bg-transparent q-pa-sm">
    <q-item class="transparent-background text-color-primary q-pa-none">
      <q-item-section v-show="hasAttachments">
        <q-input type="search" outlined dense clearable :disable="isDisabled || !hasAttachments"
          v-model.trim="searchText" @update:model-value="onSearchTextChanged" :label="t('Filter by text on file name')"
          :placeholder="t('type text search condition')">
          <template v-slot:prepend>
            <q-icon name="filter_alt" />
          </template>
        </q-input>
      </q-item-section>
      <q-item-section side>
        <q-btn size="md" :label="t('Add attachment')" icon="add" class="bg-blue text-white full-width"
          :disable="isDisabled" @click.stop="onAddAttachment"></q-btn>
      </q-item-section>
    </q-item>
    <q-separator class="q-my-md" />
    <CustomErrorBanner v-if="state.loadingError && state.errorMessage" :text="state.errorMessage"
      :api-error="state.apiError" class="q-mt-lg">
    </CustomErrorBanner>
    <div v-if="hasAttachments" class="q-list-attachments-container scroll">
      <div v-for="attachment, attachmentIndex in attachments" :key="attachment.id">
        <q-item class="q-pa-xs bg-transparent" v-show="!hiddenIds.includes(attachment.id)" clickable
          :href="attachment.url" @click.stop.prevent="onDownload(attachment.url, attachment.name)">
          <q-item-section class="q-mx-sm">
            <q-item-label>
              {{ t("Filename: ") }} {{ attachment.name }}
            </q-item-label>
            <q-item-label caption>
              {{ t("Size: ") }}{{ format.humanStorageSize(attachment.size) }}
            </q-item-label>
            <q-item-label caption>
              {{ t("Uploaded on: ") }}{{ attachment.creationDate }} ({{ attachment.creationDateTimeAgo }})
            </q-item-label>
          </q-item-section>
          <q-item-section side middle class="q-mr-sm q-item-section-attachment-actions">
            <q-chip size="md" square class="theme-default-q-chip shadow-1 full-width text-center"
              :class="{ 'cursor-not-allowed': isDisabled }" :clickable="!isDisabled"
              @click.stop.prevent="onRemoveAttachmentAtIndex(attachmentIndex)">
              <q-avatar class="theme-default-q-avatar text-white bg-blue-6" icon="delete" />
              {{ t("Remove") }}
            </q-chip>
            <q-chip size="md" square class="theme-default-q-chip shadow-1 full-width"
              v-if="allowPreview(attachment.name)"
              :class="{ 'cursor-not-allowed': isDisabled || !allowPreview(attachment.name) }"
              :clickable="!isDisabled && allowPreview(attachment.name)"
              @click.stop.prevent="onPreviewAttachment(attachmentIndex)">
              <q-avatar class="theme-default-q-avatar text-white bg-blue-6" icon="preview" />
              {{ t("Preview") }}
            </q-chip>
          </q-item-section>
          <DesktopToolTip>{{ t("Click to download") }}</DesktopToolTip>
        </q-item>
        <q-separator v-show="!hiddenIds.includes(attachment.id)" v-if="attachmentIndex !== attachments?.length - 1"
          class="q-my-sm" />
      </div>
    </div>
    <CustomBanner v-else-if="!isDisabled" warning text="No document attachments found" class="q-ma-none">
    </CustomBanner>
  </q-list>

</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount } from "vue";
import { useI18n } from "vue-i18n";
import { format } from "quasar";

import { useAxios } from "src/composables/useAxios";
import { useAPI } from "src/composables/useAPI";
import { useBus } from "src/composables/useBus";
import { useFileUtils } from "src/composables/useFileUtils"
import { useDocument } from "src/composables/useDocument"

import { default as DesktopToolTip } from "src/components/DesktopToolTip.vue";
import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue"

const { t } = useI18n();

const { bgDownload } = useAxios();
const { api } = useAPI();
const { bus } = useBus();

const { allowPreview } = useFileUtils();
const { escapeRegExp } = useDocument();

const emit = defineEmits(['update:modelValue', 'addAttachment', 'previewAttachmentAtIndex',]);

const props = defineProps({
  modelValue: {
    type: Array,
    required: false,
    default: () => [],
    validator(value) {
      return Array.isArray(value);
    }
  },
  disable: {
    type: Boolean,
    required: false,
    default: false
  }
});

const state = reactive({
  loading: false,
  loadingError: false,
  errorMessage: null,
  apiError: null
});

const isDisabled = computed(() => props.disable || state.loading);

const attachments = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
  }
});

const hiddenIds = ref([]);

const hasAttachments = computed(() => attachments.value?.length > 0);

const searchText = ref(null);

const onSearchTextChanged = (text) => {
  if (text) {
    const regex = new RegExp(escapeRegExp(text), "i");
    hiddenIds.value = attachments.value?.filter(attachment => !attachment.name?.match(regex)).map(attachment => attachment.id);
    // TODO: map new fragment with bold ?
  } else {
    hiddenIds.value = [];
  }
};

const onAddAttachment = () => {
  emit("addAttachment");
};

const onRemoveAttachmentAtIndex = (index) => {
  // orphaned elements are uploaded to server, but not associated (until document saved)
  // so we must remove them
  if (attachments.value[index].orphaned) {
    state.loading = true;
    state.loadingError = false;
    state.errorMessage = null;
    state.apiError = null;
    api.document.
      removeFile(attachments.value[index].id)
      .then((successResponse) => {
        state.loading = false;
        attachments.value = attachments.value.filter((_, i) => i !== index);
      })
      .catch((errorResponse) => {
        state.loadingError = true;
        if (errorResponse.isAPIError) {
          state.apiError = errorResponse.customAPIErrorDetails;
          switch (errorResponse.response.status) {
            case 401:
              state.errorMessage = "Auth session expired, requesting new...";
              bus.emit("reAuthRequired", { emitter: "DocumentDetailsAttachments.onRemoveAttachmentAtIndex" });
              break;
            default:
              state.errorMessage = "API Error: fatal error";
              break;
          }
        } else {
          state.errorMessage = `Uncaught exception: ${errorResponse}`;
          console.error(errorResponse);
        }
        state.loading = false;
      });
  } else {
    attachments.value = attachments.value.filter((_, i) => i !== index);
  }
};

const onPreviewAttachment = (index) => {
  emit("previewAttachmentAtIndex", index);
};

const onDownload = (url, fileName) => {
  bgDownload(url, fileName)
    .then((successResponse) => {
      // TODO
    })
    .catch((errorResponse) => {
      // TODO
    });
}

onMounted(() => {
  bus.on("reAuthSucess", (msg) => {
    if (msg.to?.includes("DocumentDetailsAttachments.onRemoveAttachmentAtIndex")) {
      // TODO: save old selected index to remove & re-submit removeFile api call
      //onRemoveAttachmentAtIndex(savedIndex);
    }
  });
});

onBeforeUnmount(() => {
  bus.off("reAuthSucess");
});

</script>

<style lang="css" scoped>
.q-list-attachments-container {
  min-height: 50vh;
  max-height: 50vh;
}

.q-item-section-attachment-actions {
  width: 12em;
}
</style>