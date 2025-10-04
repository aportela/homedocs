<template>
  <q-dialog v-model="visible" @hide="onClose">
    <q-card class="q-card-uploading-dialog">
      <q-card-section class="row q-p-none">
        <div class="q-card-uploading-dialog-header col">{{ t("File upload activity & status") }}</div>
        <q-space />
        <div>
          <q-chip size="md" square class="gt-sm theme-default-q-chip">
            <q-avatar class="theme-default-q-avatar">{{ transfers.length }}</q-avatar>
            {{ t("Total transfers", { count: transfers.length }) }}
          </q-chip>
          <q-btn icon="close" flat round dense v-close-popup aria-label="Close modal" />
        </div>
      </q-card-section>
      <q-separator class="q-mb-md"></q-separator>
      <q-card-section class="q-p-none q-markup-table-container scroll">
        <slot name="body">
          <q-markup-table v-if="transfers?.length > 0">
            <thead>
              <tr>
                <th class="text-left">{{ t("Name") }}</th>
                <th class="text-right">{{ t("Size") }}</th>
                <th class="text-right">{{ t("Started") }}</th>
                <th class="text-right">{{ t("End") }}</th>
                <th class="text-center">{{ t("Status") }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="transfer in transfers" :key="transfer.id" class="text-white"
                :class="{ 'bg-green-5': transfer.done, 'bg-red-4': transfer.error, 'bg-light-blue': transfer.uploading }">
                <td class="text-left">{{ transfer.filename }}</td>
                <td class="text-right">{{ format.humanStorageSize(transfer.filesize) }}</td>
                <td class="text-right">{{ fullDateTimeHuman(transfer.start) }}</td>
                <td class="text-right">{{ fullDateTimeHuman(transfer.end) }}</td>
                <td class="text-center">
                  <q-chip square v-if="transfer.error" class="full-width bg-red-9 text-white">
                    <q-avatar icon="cancel" class="q-ma-xs" />
                    {{ transfer.errorHTTPCode == 413
                      ?
                      t("Transfer rejected max filesize", {
                        maxUploadFileSize:
                          format.humanStorageSize(initialState.maxUploadFileSize)
                      })
                      :
                      t(transfer.errorMessage)
                    }}
                  </q-chip>
                  <q-chip square v-else-if="transfer.done" class="full-width bg-green-9 text-white">
                    <q-avatar icon="check" class="q-ma-xs" />
                    {{ t("Transfer complete") }}
                  </q-chip>
                  <q-chip square v-else-if="transfer.uploading" class="full-width bg-light-blue-9 text-white">
                    <q-spinner size="sm" class="q-ma-xs" />
                    {{ t("Transfering...") }}
                  </q-chip>
                </td>
              </tr>
            </tbody>
          </q-markup-table>
        </slot>
      </q-card-section>
      <q-separator class="q-my-sm"></q-separator>
      <q-card-actions align="right">
        <q-toggle v-model="visibilityCheck" @update:modelValue="saveVisibilityCheck" checked-icon="check" color="green"
          :label="t(visibilityCheckLabel)" unchecked-icon="clear" class="q-mr-md" />
        <q-btn color="primary" icon="close" :label="t('Close')" v-close-popup />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { format } from "quasar";
import { useI18n } from "vue-i18n";

import { useFormatDates } from "src/composables/useFormatDates"
import { useLocalStorage } from "src/composables/useLocalStorage"
import { useInitialStateStore } from "src/stores/initialState";

const { t } = useI18n();

const { fullDateTimeHuman } = useFormatDates();
const { alwaysOpenUploadDialog } = useLocalStorage();
const initialState = useInitialStateStore();

const emit = defineEmits(['update:modelValue', 'close']);

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  },
  transfers: {
    type: Array,
    required: false,
    default: () => [],
    validator(value) {
      return Array.isArray(value);
    }
  }
});

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    if (value) {
      // before showing dialog always set q-toggle value
      const toggleValue = alwaysOpenUploadDialog.get();
      if (toggleValue !== visibilityCheck.value) {
        visibilityCheck.value = toggleValue; // only if there are changes
      }
    }
    emit('update:modelValue', value);
  }
});

const visibilityCheck = ref(alwaysOpenUploadDialog.get());

const saveVisibilityCheck = (val) => {
  alwaysOpenUploadDialog.set(val);
};

const visibilityCheckLabel = computed(() => visibilityCheck.value ? "Always display this progress window when uploading files" : "Only display this progress window when uploading failed");

const onClose = () => {
  visible.value = false;
  emit('update:modelValue', false);
}

</script>

<style lang="css" scoped>
.q-card-uploading-dialog {
  min-width: 72vw;
  max-width: 95vw;
  min-height: 60vh;
  max-height: 60vh;
}

.q-card-uploading-dialog-header {
  font-size: 1.2em;
  font-weight: bold;
}

.q-markup-table-container {
  min-height: 40vh;
  max-height: 40vh;
}
</style>
