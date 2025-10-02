<template>
  <q-list class="bg-transparent q-pa-sm">
    <q-item class="transparent-background text-color-primary q-pa-none">
      <q-item-section v-show="hasAttachments">
        <q-input type="search" icon="search" outlined dense clearable :disable="disable || !hasAttachments"
          v-model.trim="searchText" @update:model-value="onSearchTextChanged" :label="t('Filter by text on file name')"
          :placeholder="t('type text search condition')"></q-input>
      </q-item-section>
      <q-item-section side>
        <q-btn size="md" :label="t('Add attachment')" icon="add" class="bg-blue text-white full-width"
          :disable="disable" @click.stop="onAddAttachment"></q-btn>
      </q-item-section>
    </q-item>
    <q-separator class="q-my-md" />
    <div v-if="hasAttachments" class="q-list-attachments-container scroll">
      <div v-for="attachment, attachmentIndex in internalModel" :key="attachment.id">
        <q-item class="q-pa-xs bg-transparent" v-show="!hiddenIds.includes(attachment.id)" clickable
          :href="attachment.url" @click.stop.prevent="onDownload(attachment.url, attachment.name)">
          <q-item-section class="q-mx-sm">
            <q-item-label>
              Filename: {{ attachment.name }}
            </q-item-label>
            <q-item-label caption>
              Length: {{ format.humanStorageSize(attachment.size) }}
            </q-item-label>
            <q-item-label caption>
              Uploaded on: {{ attachment.creationDate }} ({{ attachment.creationDateTimeAgo }})
            </q-item-label>
          </q-item-section>
          <q-item-section side middle class="q-mr-sm q-item-section-attachment-actions">
            <q-chip size="md" square class="theme-default-q-chip shadow-1 full-width text-center"
              :class="{ 'cursor-not-allowed': disable }" :clickable="!disable"
              @click.stop.prevent="onRemoveAttachmentAtIndex(attachmentIndex)">
              <q-avatar class="theme-default-q-avatar text-white bg-blue-6" icon="delete" />
              {{ t("Remove") }}
            </q-chip>
            <q-chip size="md" square class="theme-default-q-chip shadow-1 full-width"
              v-if="allowPreview(attachment.name)"
              :class="{ 'cursor-not-allowed': disable || !allowPreview(attachment.name) }"
              :clickable="!disable && allowPreview(attachment.name)"
              @click.stop.prevent="onPreviewAttachment(attachmentIndex)">
              <q-avatar class="theme-default-q-avatar text-white bg-blue-6" icon="preview" />
              {{ t("Preview") }}
            </q-chip>
          </q-item-section>
          <q-tooltip>{{ t("Click to download") }}</q-tooltip>
        </q-item>
        <q-separator v-show="!hiddenIds.includes(attachment.id)" v-if="attachmentIndex !== internalModel?.length - 1"
          class="q-my-sm" />
      </div>
    </div>
    <CustomBanner v-else-if="!disable" warning text="No document attachments found" class="q-ma-none">
    </CustomBanner>
  </q-list>

</template>

<script setup>
import { ref, computed } from "vue";
import { useI18n } from "vue-i18n";
import { format } from "quasar";

import { api, bgDownload } from "src/boot/axios";
import { useFileUtils } from "src/composables/fileUtils"
import { useDocument } from "src/composables/document"

import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue"

const { t } = useI18n();

const { allowPreview } = useFileUtils();
const { escapeRegExp } = useDocument();

const emit = defineEmits(['update:modelValue', 'addAttachment', 'previewAttachmentAtIndex',]);

const props = defineProps({
  modelValue: {
    type: Array,
    required: false,
    default: () => []
  },
  disable: {
    type: Boolean,
    required: false,
    default: false
  }
});

const internalModel = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit('update:modelValue', value);
  }
});

const hiddenIds = ref([]);

const hasAttachments = computed(() => internalModel.value?.length > 0);

const searchText = ref(null);

const onSearchTextChanged = (text) => {
  if (text) {
    const regex = new RegExp(escapeRegExp(text), "i");
    hiddenIds.value = internalModel.value?.filter(attachment => !attachment.name?.match(regex)).map(attachment => attachment.id);
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
  if (internalModel.value[index].orphaned) {
    // TODO
    //loading.value = true;
    api.document.
      removeFile(internalModel.value[index].id)
      .then((successResponse) => {
        internalModel.value = internalModel.value.filter((_, i) => i !== index);
        // TODO
        //loading.value = false;
        //state.loading = false;
      })
      .catch((errorResponse) => {
        // TODO
        //loading.value = false;
        //state.loading = false;
      });
  } else {
    internalModel.value = internalModel.value.filter((_, i) => i !== index);
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
</script>

<style scoped>
.q-list-attachments-container {
  min-height: 50vh;
  max-height: 50vh;
}

.q-item-section-attachment-actions {
  width: 12em;
}
</style>
