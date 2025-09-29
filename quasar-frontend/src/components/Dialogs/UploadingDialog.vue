<template>
  <q-dialog v-model="visible" @hide="onClose">
    <q-card style="width: 60%; max-width: 80vw;">
      <q-card-section class="row q-p-none">
        <div class="q-card-attachments-dialog-header col">File uploader
        </div>
        <q-space />
        <div>
          <q-chip size="md" square class="gt-sm theme-default-q-chip">
            <q-avatar class="theme-default-q-avatar">{{ files.length }}</q-avatar>
            {{ t("Total files", { count: files.length }) }}
          </q-chip>
          <q-btn icon="close" flat round dense v-close-popup aria-label="Close modal" />
        </div>
      </q-card-section>
      <q-separator class="q-mb-md"></q-separator>
      <q-card-section class="q-p-none">
        <slot name="body">
          <div v-if="files">
            <p v-for="file, fileIndex in files" :key="fileIndex">
              <q-icon name="cancel" v-if="file.error" />
              <q-icon name="check" v-else-if="file.done" />
              <q-spinner v-else />
              Filename: {{ file.name }} ({{
                format.humanStorageSize(file.size) }})
              - {{ file.start || 0 }} -- {{ file.end || 0 }}
              <span v-if="file.start && file.end">Total transfer time: {{ diff(file.start, file.end) }}</span>
            </p>
          </div>

          <q-linear-progress indeterminate class="q-mt-sm" v-if="transfering" />
        </slot>
      </q-card-section>
      <q-separator class="q-my-sm"></q-separator>
      <q-card-actions align="right">
        <q-btn color="primary" icon="left" :label="t('Close')" v-close-popup />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref } from "vue";
import { format } from "quasar";
import { useI18n } from "vue-i18n";

import { useFormatDates } from "src/composables/formatDate"

const { t } = useI18n();

const { diff } = useFormatDates();

const emit = defineEmits(['close']);

const props = defineProps({
  files: {
    type: Array,
    required: false,
    default: () => []
  },
  transfering: {
    type: Boolean,
    required: false,
    default: true,
  }
});

const visible = ref(true);

const onClose = () => {
  visible.value = false;
  emit('close');
}

</script>