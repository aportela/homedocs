<template>
  <div>
    <h6 class="text-center text-h6 q-my-sm"><q-icon name="insights"></q-icon> {{ t("Activity Heatmap") }}</h6>
    <div>
      <ActivityHeatMap class="q-mx-auto" :showNavigationButtons="true"></ActivityHeatMap>
    </div>
    <q-separator class="q-my-md" />
    <div class="row">
      <div v-for="(stat, index) in stats" :key="index" class="col-4">
        <q-card class="q-ma-sm">
          <q-card-section class="text-center text-h6 theme-default-q-card-section-header">
            <q-icon :name="stat.error ? 'error' : stat.icon" class="gt-xs" :class="{ 'text-red': stat.error }" />
            {{ t(stat.headerLabel) }}
          </q-card-section>
          <q-card-section class="text-center text-h4">{{ stat.total }}</q-card-section>
        </q-card>
      </div>
    </div>
  </div>
</template>

<script setup>

import { reactive, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { format } from "quasar"

import { api } from "boot/axios";

import ActivityHeatMap from 'components/ActivityHeatMap.vue';

const { t } = useI18n();

const stats = reactive({
  documents: {
    total: 0,
    loading: false,
    error: false,
    headerLabel: "Total documents",
    icon: "shelves"
  },
  attachments: {
    total: 0,
    loading: false,
    error: false,
    headerLabel: "Total attachments",
    icon: "attachment"
  },
  diskUsage: {
    total: 0,
    loading: false,
    error: false,
    headerLabel: "Disk usage",
    icon: "data_usage"
  }
});

const onRefreshTotalDocuments = () => {
  stats.documents.loading = true;
  stats.documents.total = 0;
  stats.documents.error = false;
  api.stats.documentCount()
    .then((successResponse) => {
      stats.documents.total = successResponse.data.count;
      stats.documents.loading = false;
    })
    .catch((errorResponse) => {
      stats.documents.error = true;
      stats.documents.loading = false;
    });
}

const onRefreshTotalAttachments = () => {
  stats.attachments.loading = true;
  stats.attachments.total = 0;
  stats.attachments.error = false;
  api.stats.attachmentCount()
    .then((successResponse) => {
      stats.attachments.total = successResponse.data.count;
      stats.attachments.loading = false;
    })
    .catch((errorResponse) => {
      stats.attachments.error = true;
      stats.attachments.loading = false;
    });
}

const onRefreshTotalAttachmentsDiskUsage = () => {
  stats.diskUsage.loading = true;
  stats.diskUsage.total = 0;
  stats.diskUsage.error = false;
  api.stats.attachmentDiskSize()
    .then((successResponse) => {
      stats.diskUsage.total = format.humanStorageSize(successResponse.data.size);
      stats.diskUsage.false;
    })
    .catch((errorResponse) => {
      stats.diskUsage.error = true;
      stats.diskUsage.loading = false;
    });
}

onMounted(() => {
  onRefreshTotalDocuments();
  onRefreshTotalAttachments();
  onRefreshTotalAttachmentsDiskUsage();
});

</script>