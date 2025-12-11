<template>
  <q-list class="bg-transparent q-pa-sm">
    <q-item class="transparent-background text-color-primary q-pa-none">
      <q-item-section v-show="hasAttachments">
        <q-input type="search" outlined dense clearable :disable="isDisabled || !hasAttachments"
          v-model.trim="searchText" @update:model-value="onSearchTextChanged"
          :label="t('Filter by text on attachment filename')" :placeholder="t('type text search condition')">
          <template v-slot:prepend>
            <q-icon name="filter_alt" />
          </template>
        </q-input>
      </q-item-section>
      <q-item-section side>
        <q-btn size="md" :label="t('Add attachment')" icon="add" no-caps class="bg-blue text-white full-width"
          :disable="isDisabled" @click.stop="onAddAttachment"></q-btn>
      </q-item-section>
    </q-item>
    <q-separator class="q-my-md" />
    <CustomErrorBanner v-if="state.ajaxErrors && state.ajaxErrorMessage" :text="state.ajaxErrorMessage"
      :api-error="state.ajaxAPIErrorDetails" class="q-mt-lg">
    </CustomErrorBanner>
    <div v-if="hasAttachments" class="q-list-attachments-container scroll">
      <div v-for="attachment, attachmentIndex in attachments" :key="attachment.id">
        <q-item class="q-pa-xs bg-transparent" v-show="!hiddenIds.includes(attachment.id)" clickable
          :href="getAttachmentURL(attachment.id, true)"
          @click.stop.prevent="onDownload(attachment.id, attachment.name)">
          <q-item-section class="q-mx-sm">
            <q-item-label>
              {{ attachment.name }}
            </q-item-label>
            <q-item-label caption>
              {{ t("Size: ") }}{{ format.humanStorageSize(attachment.size) }}
            </q-item-label>
            <q-item-label caption>
              {{ t("Uploaded on: ") }}{{ attachment.createdAt.dateTime }} ({{ attachment.createdAt.timeAgo }})
            </q-item-label>
            <div v-if="screen.lt.xl">
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
            </div>
          </q-item-section>
          <q-item-section side middle class="q-mr-sm q-item-section-attachment-actions" v-if="!screen.lt.xl">
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
            <q-chip size="md" square class="theme-default-q-chip shadow-1 full-width" :clickable="!isDisabled"
              @click.stop.prevent="onShare(attachmentIndex)">
              <q-avatar class="theme-default-q-avatar text-white bg-blue-6" icon="share" />
              {{ t(attachment.shareId ? "Shared" : "Share") }}
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

<script setup lang="ts">
import { ref, reactive, computed, onMounted, onBeforeUnmount } from "vue";
import { useI18n } from "vue-i18n";
import { format, useQuasar } from "quasar";

import { bgDownload } from "src/composables/axios";
import { bus } from "src/composables/bus";
import { allowPreview } from "src/composables/fileUtils"
import { getRegexForStringMatch } from "src/composables/common";
import { type AjaxState as AjaxStateInterface, defaultAjaxState } from "src/types/ajaxState";
import { type Attachment as AttachmentInterface } from "src/types/attachment";
import { getURL as getAttachmentURL } from "src/composables/attachment";

import { default as DesktopToolTip } from "src/components/DesktopToolTip.vue";
import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue";

const { t } = useI18n();

const emit = defineEmits(['update:modelValue', 'addAttachment', 'previewAttachmentAtIndex', 'removeAttachmentAtIndex']);

interface DocumentDetailsAttachmentsProps {
  attachments: AttachmentInterface[];
  disable: boolean;
};

const props = withDefaults(defineProps<DocumentDetailsAttachmentsProps>(), {
  disable: false
});

const { screen } = useQuasar();

const state: AjaxStateInterface = reactive({ ...defaultAjaxState });

const isDisabled = computed(() => props.disable || state.ajaxRunning);

const hiddenIds = reactive<Array<string>>([]);

const hasAttachments = computed(() => props.attachments?.length > 0);

const searchText = ref(null);

const onSearchTextChanged = (text: string | number | null) => {
  hiddenIds.length = 0;
  if (text) {
    const regex = getRegexForStringMatch(String(text));
    hiddenIds.push(...props.attachments.filter(attachment => !attachment.name?.match(regex)).map(attachment => attachment.id));
    // TODO: map new fragment with bold ?
  }
};

const onAddAttachment = () => {
  emit("addAttachment");
};

const onRemoveAttachmentAtIndex = (index: number) => {
  emit("removeAttachmentAtIndex", index);
};

const onPreviewAttachment = (index: number) => {
  emit("previewAttachmentAtIndex", index);
};

const onShare = (index: number) => {
  console.log(index);
  bus.emit('showSharePreviewDialog', { document: { id: null, title: null } });
};

const onDownload = (attachmentId: string, fileName: string) => {
  bgDownload(getAttachmentURL(attachmentId), fileName)
    .then(() => {
      /* TODO */
    })
    .catch(() => {
      /* TODO */
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