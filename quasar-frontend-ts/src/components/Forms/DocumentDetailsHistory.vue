<template>
  <q-markup-table class="bg-transparent q-pa-sm q-list-history-container scroll" v-if="hasHistoryOperations">
    <thead>
      <tr>
        <th class="text-left">{{ t("Date") }}</th>
        <th class="text-left">{{ t("Operation") }}</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="historyOperation in historyOperations" :key="historyOperation.createdOnTimestamp">
        <td>{{ historyOperation.createdOn }} ({{ historyOperation.createdOnTimeAgo }})</td>
        <td><q-icon size="md" :name="historyOperation.icon" class="q-mr-sm"></q-icon>{{
          t(historyOperation.label) }}
        </td>
      </tr>
    </tbody>
  </q-markup-table>
  <CustomBanner v-else-if="!disable" warning text="No document history found" class="q-ma-none">
  </CustomBanner>
</template>

<script setup lang="ts">

import { computed } from "vue";
import { useI18n } from "vue-i18n";
import { type HistoryOperation as HistoryOperationInterface } from "src/types/history-operation";
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue"

const { t } = useI18n();

interface DocumentDetailsHistoryProps {
  modelValue: HistoryOperationInterface[];
  disable: boolean;
};

const props = withDefaults(defineProps<DocumentDetailsHistoryProps>(), {
  disable: false
});

const historyOperations = computed({
  get() {
    return props.modelValue;
  },
  set() {
    /* */
  }
});

const hasHistoryOperations = computed(() => historyOperations.value?.length > 0);

</script>

<style lang="css" scoped>
.q-list-history-container {
  min-height: 50vh;
  max-height: 50vh;
}
</style>