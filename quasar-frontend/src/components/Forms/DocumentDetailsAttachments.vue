<template>
  <q-list class="bg-transparent scroll q-pa-sm q-list-attachments-container">
    <q-item class="transparent-background text-color-primary q-pa-none">
      <q-item-section>
        <q-input type="search" icon="search" outlined dense clearable :disable="disable || !hasAttachments"
          v-model.trim="filterAttachmentsByFilename" :label="t('Filter by text on filename')"
          :placeholder="t('type text search condition')"></q-input>
      </q-item-section>
      <q-item-section side>
        <q-btn size="md" :label="t('Add attachment')" icon="add" class="bg-blue text-white full-width"
          :disable="disable" @click.stop="onAddAttachment"></q-btn>
      </q-item-section>
    </q-item>
    <q-separator class="q-my-md" />
    <div v-if="hasAttachments">
      <q-item class="q-pa-none bg-transparent" v-for="attachment, attachmentIndex in attachments" :key="attachment.id"
        v-show="attachment.visible">
        <q-item-section class="q-mx-md">
          <q-item-label>
            Filename: {{ attachment.name }}
          </q-item-label>
          <q-item-label caption>
            Length: {{ format.humanStorageSize(attachment.size) }}
          </q-item-label>
          <q-item-label caption>
            Uploaded on: {{ attachment.createdOn }}
          </q-item-label>
        </q-item-section>
        <q-item-section side top>
          <q-chip size="md" square class="theme-default-q-chip shadow-1 full-width"
            :class="{ 'cursor-not-allowed': disable }" :clickable="!disable"
            @click.stop.prevent="onRemoveAttachment(attachmentIndex)">
            <q-avatar class="theme-default-q-avatar text-white bg-blue-6" icon="delete" />
            {{ t("Remove") }}
          </q-chip>
          <q-chip size="md" square class="theme-default-q-chip shadow-1 full-width"
            :class="{ 'cursor-not-allowed': disable || !allowPreview(attachment.name) }"
            :clickable="!disable && allowPreview(attachment.name)"
            @click.stop.prevent="onPreviewAttachment(attachmentIndex)">
            <q-avatar class="theme-default-q-avatar text-white bg-blue-6" icon="preview" />
            {{ t("Preview") }}
          </q-chip>
        </q-item-section>
      </q-item>
    </div>
    <CustomBanner v-else-if="!disable" warning text="No document notes found" class="q-ma-none">
    </CustomBanner>
  </q-list>

</template>

<script setup>
import { ref, computed } from "vue";
import { useI18n } from "vue-i18n";
import { format } from "quasar";

import { useFormUtils } from "src/composables/formUtils"
import { useFileUtils } from "src/composables/fileUtils"

import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue"

const { t } = useI18n();

const { requiredFieldRules, fieldIsRequiredLabel } = useFormUtils();
const { allowPreview } = useFileUtils();

const emit = defineEmits(['update:attachments', 'addAttachment', 'removeAttachmentAtIndex', 'previewAttachmentAtIndex']);

const props = defineProps({
  attachments: {
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

const hasAttachments = computed(() => props.attachments.length > 0);

const filterAttachmentsByFilename = ref(null);

const onAddAttachment = () => {
  emit("addAttachment");
};

const onRemoveAttachment = (index) => {
  emit("removeAttachmentAtIndex", index);
};

const onPreviewAttachment = (index) => {
  emit("previewAttachmentAtIndex", index);
};

</script>

<style scoped>
.q-list-attachments-container {
  min-height: 50vh;
  max-height: 50vh;
}
</style>