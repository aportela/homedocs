<template>
  <BaseWidget title="Your system stats" caption="Small resume of your platform usage" icon="analytics">
    <template v-slot:content>
      <div>
        <h6 class="text-center text-h6 q-my-sm"><q-icon :name="heatmapStats.loading ? 'settings' : 'insights'"
            :class="{ 'animation-spin': heatmapStats.loading }"></q-icon> {{ t("Activity Heatmap") }}</h6>
        <div>
          <ActivityHeatMap class="q-mx-auto" :show-navigation-buttons="true" @loading="onActivityHeatMapLoading"
            @loaded="onActivityHeatMapLoaded" @error="onActivityHeatMapError"></ActivityHeatMap>
        </div>
        <q-separator class="q-my-md" />
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4 col-xl-4">
            <SystemStatsCountTotalDocumentsWidget />
          </div>
          <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4 col-xl-4">
            <SystemStatsCountTotalAttachmentsWidget />
          </div>
          <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4 col-xl-4">
            <SystemStatsDiskUsage />
          </div>
        </div>
      </div>
    </template>
  </BaseWidget>

</template>

<script setup lang="ts">

  import { reactive } from "vue";
  import { useI18n } from "vue-i18n";

  import ActivityHeatMap from "src/components/Widgets/ActivityHeatMap.vue";
  import { default as BaseWidget } from "src/components/Widgets/BaseWidget.vue";
  import { default as SystemStatsCountTotalDocumentsWidget } from "./SystemStatsCountTotalDocumentsWidget.vue";
  import { default as SystemStatsCountTotalAttachmentsWidget } from "./SystemStatsCountTotalAttachmentsWidget.vue";
  import { default as SystemStatsDiskUsage } from "./SystemStatsDiskUsage.vue";

  const { t } = useI18n();

  const heatmapStats = reactive({
    loading: false,
    loadingError: false
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
</script>