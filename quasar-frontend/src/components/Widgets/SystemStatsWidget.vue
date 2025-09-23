<template>
  <BaseWidget title="Your system stats" caption="Small resume of your platform usage" icon="analytics">
    <template v-slot:content>
      <div>
        <h6 class="text-center text-h6 q-my-sm"><q-icon :name="heatmapStats.loading ? 'settings' : 'insights'"
            :class="{ 'animation-spin': heatmapStats.loading }"></q-icon> {{ t("Activity Heatmap") }}</h6>
        <div>
          <ActivityHeatMap class="q-mx-auto" :showNavigationButtons="true" @loading="onActivityHeatMapLoading"
            @loaded="onActivityHeatMapLoaded" @error="onActivityHeatMapError"></ActivityHeatMap>
        </div>
        <q-separator class="q-my-md" />
        <div class="row">
          <div v-for="(stat, index) in stats" :key="index" class="col-4">
            <q-card class="q-ma-sm">
              <q-card-section class="text-center text-h6 theme-default-q-card-section-header">
                <q-icon :name="stat.loading ? 'settings' : stat.error ? 'error' : stat.icon" class="gt-xs"
                  :class="{ 'text-red': stat.error, 'animation-spin': stat.loading }" />
                {{ t(stat.headerLabel) }}
              </q-card-section>
              <q-card-section class="text-center text-h4" v-if="!stat.loadingError">{{ stat.total }}</q-card-section>
              <q-card-section v-else>
                <CustomErrorBanner text="Error loading data" :apiError="stat.apiError"></CustomErrorBanner>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </div>
    </template>
  </BaseWidget>

</template>

<script setup>

import { reactive, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { format } from "quasar";

import { api } from "src/boot/axios";

import ActivityHeatMap from "src/components/ActivityHeatMap.vue";
import { default as BaseWidget } from "src/components/Widgets/BaseWidget.vue";
import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";

const { t } = useI18n();

const heatmapStats = reactive({
  loading: false,
  loadingError: false
});

const stats = reactive({
  documents: {
    total: 0,
    loading: false,
    loadingError: false,
    apiError: null,
    headerLabel: "Total documents",
    icon: "shelves"
  },
  attachments: {
    total: 0,
    loading: false,
    loadingError: false,
    apiError: null,
    headerLabel: "Total attachments",
    icon: "attachment"
  },
  diskUsage: {
    total: 0,
    loading: false,
    loadingError: false,
    apiError: null,
    headerLabel: "Disk usage",
    icon: "data_usage"
  }
});

const onActivityHeatMapLoading = () => {
  heatmapStats.loadingError = false;
  heatmapStats.loading = true;
};
const onActivityHeatMapLoaded = () => {
  heatmapStats.loading = false;
};
const onActivityHeatMapError = () => {
  heatmapStats.loadingError = true;
  heatmapStats.loading = false;
};

const onRefreshTotalDocuments = () => {
  stats.documents.loading = true;
  stats.documents.total = 0;
  stats.documents.loadingError = false;
  stats.documents.apiError = null;
  api.stats.documentCount()
    .then((successResponse) => {
      stats.documents.total = successResponse.data.count;
      stats.documents.loading = false;
    })
    .catch((errorResponse) => {
      stats.documents.loadingError = true;
      stats.documents.apiError = errorResponse.customAPIErrorDetails;
      stats.documents.loading = false;
    });
}

const onRefreshTotalAttachments = () => {
  stats.attachments.loading = true;
  stats.attachments.total = 0;
  stats.attachments.loadingError = false;
  stats.attachments.apiError = null;
  api.stats.attachmentCount()
    .then((successResponse) => {
      stats.attachments.total = successResponse.data.count;
      stats.attachments.loading = false;
    })
    .catch((errorResponse) => {
      stats.attachments.loadingError = true;
      stats.attachments.apiError = errorResponse.customAPIErrorDetails;
      stats.attachments.loading = false;
    });
}

const onRefreshTotalAttachmentsDiskUsage = () => {
  stats.diskUsage.loading = true;
  stats.diskUsage.total = 0;
  stats.diskUsage.loadingError = false;
  stats.diskUsage.apiError = null;
  api.stats.attachmentDiskSize()
    .then((successResponse) => {
      stats.diskUsage.total = format.humanStorageSize(successResponse.data.size);
      stats.diskUsage.loading = false;
    })
    .catch((errorResponse) => {
      stats.diskUsage.loadingError = true;
      stats.diskUsage.apiError = errorResponse.customAPIErrorDetails;
      stats.diskUsage.loading = false;
    });
}

onMounted(() => {
  onRefreshTotalDocuments();
  onRefreshTotalAttachments();
  onRefreshTotalAttachmentsDiskUsage();
});

</script>