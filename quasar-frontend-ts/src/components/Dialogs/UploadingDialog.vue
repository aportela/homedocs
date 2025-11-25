<template>
  <BaseDialog v-model="visible" @close="onClose" width="1280px" max-width="80vw">
    <template v-slot:header-left>
      {{ t("File upload activity & status") }}
    </template>
    <template v-slot:body>
      <div class="q-p-none q-markup-table-container scroll">
        <q-markup-table v-if="hasTransfers">
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
              <td class="text-right">{{ fullDateTimeHuman(transfer.start, localStorageDateTimeFormat.get()) }}</td>
              <td class="text-right">
                <span v-if="transfer.end && transfer.end > 0">{{ fullDateTimeHuman(transfer.end,
                  localStorageDateTimeFormat.get()) }}</span>
              </td>
              <td class="text-center">
                <q-chip square v-if="transfer.error" class="full-width bg-red-9 text-white">
                  <q-avatar icon="cancel" class="q-ma-xs" />
                  {{ transfer.errorHTTPCode == 413
                    ?
                    t("Transfer rejected max filesize", {
                      maxUploadFileSize:
                        format.humanStorageSize(serverEnvironment.maxUploadFileSize)
                    })
                    :
                    t(transfer.errorMessage || 'Error')
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
      </div>
    </template>
    <template v-slot:actions-prepend>
      <q-toggle v-model="visibilityCheck" @update:modelValue="saveVisibilityCheck" checked-icon="check" color="green"
        :label="t(visibilityCheckLabel)" unchecked-icon="clear" class="q-mr-md" />
    </template>
  </BaseDialog>
</template>

<script setup lang="ts">
import { ref, watch, computed } from "vue";
import { format } from "quasar";
import { useI18n } from "vue-i18n";

import { useFormatDates } from "src/composables/useFormatDates"
import { useLocalStorage } from "src/composables/useLocalStorage"
import { useServerEnvironmentStore } from "src/stores/serverEnvironment";

import { type UploadTransfer as UploadTransferInterface } from "src/types/upload-transfer";
import { default as BaseDialog } from "src/components/Dialogs/BaseDialog.vue"

const { t } = useI18n();

const { fullDateTimeHuman } = useFormatDates();
const { alwaysOpenUploadDialog: localStorageAlwaysOpenUploadDialog, dateTimeFormat: localStorageDateTimeFormat } = useLocalStorage();
const serverEnvironment = useServerEnvironmentStore();

const emit = defineEmits(['update:modelValue', 'close']);

interface UploadingDialogProps {
  modelValue: boolean;
  transfers: UploadTransferInterface[]
};

const props = defineProps<UploadingDialogProps>();

const hasTransfers = computed(() => props.transfers?.length > 0);

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    if (value) {
      // before showing dialog always set q-toggle value
      const toggleValue = localStorageAlwaysOpenUploadDialog.get();
      if (toggleValue !== visibilityCheck.value) {
        visibilityCheck.value = toggleValue; // only if there are changes
      }
    }
    emit('update:modelValue', value);
  }
});

const visibilityCheck = ref<boolean>(localStorageAlwaysOpenUploadDialog.get());

watch(() => visible.value, (val: boolean) => {
  if (val) {
    visibilityCheck.value = localStorageAlwaysOpenUploadDialog.get();
  }
});

const saveVisibilityCheck = (val: boolean) => {
  localStorageAlwaysOpenUploadDialog.set(val);
};

const visibilityCheckLabel = computed(() => visibilityCheck.value ? "Always display this progress window when uploading files" : "Only display this progress window when uploading failed");

const onClose = () => {
  visible.value = false;
  emit('update:modelValue', false);
}

</script>

<style lang="css" scoped>
.q-markup-table-container {
  height: 40vh;
}
</style>