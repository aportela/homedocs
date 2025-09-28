<template>
  <q-markup-table class="bg-transparent q-pa-sm q-list-history-container scroll" v-if="hasHistory">
    <thead>
      <tr>
        <th class="text-left">{{ t("Date") }}</th>
        <th class="text-left">{{ t("Operation") }}</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="operation in operations" :key="operation.operationTimestamp">
        <td>{{ operation.createdOn }} ({{ operation.createdOnTimeAgo }})</td>
        <td><q-icon size="md" :name="operation.icon" class="q-mr-sm"></q-icon>{{
          t(operation.label) }}
        </td>
      </tr>
    </tbody>
  </q-markup-table>
  <CustomBanner v-else-if="!disable" warning text="No document history found" class="q-ma-none">
  </CustomBanner>
</template>

<script setup>

import { computed } from "vue";
import { useI18n } from "vue-i18n";

import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue"

const { t } = useI18n();

const props = defineProps({
  operations: {
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

const hasHistory = computed(() => props.operations.length > 0);

</script>

<style scoped>
.q-list-history-container {
  min-height: 50vh;
  max-height: 50vh;
}
</style>