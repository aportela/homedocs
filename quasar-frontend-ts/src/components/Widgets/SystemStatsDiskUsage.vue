<template>
  <SystemStatsWidgetBase :loading="state.ajaxRunning" :error="state.ajaxErrors" icon="attachment"
    header-label="Total attachments" :total="total" :error-message="state.ajaxErrorMessage"
    :api-error-details="state.ajaxAPIErrorDetails" />
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, onBeforeUnmount } from "vue";

import { bus } from "src/composables/useBus";
import { api } from "src/composables/api";
import { type AjaxState as AjaxStateInterface, defaultAjaxState } from "src/types/ajax-state";
import { type GetDiskUsageStatsResponse as GetDiskUsageStatsResponseInterface } from "src/types/api-responses";
import { default as SystemStatsWidgetBase } from "src/components/Widgets/SystemStatsWidgetBase.vue";

const state: AjaxStateInterface = reactive({ ...defaultAjaxState });

const total = ref<number>(0);

const onRefresh = () => {
  if (!state.ajaxRunning) {
    Object.assign(state, defaultAjaxState);
    state.ajaxRunning = true;
    api.stats.attachmentCount()
      .then((successResponse: GetDiskUsageStatsResponseInterface) => {
        total.value = successResponse.data.count;
      })
      .catch((errorResponse) => {
        state.ajaxErrors = true;
        if (errorResponse.isAPIError) {
          state.ajaxAPIErrorDetails = errorResponse.customAPIErrorDetails;
          switch (errorResponse.response.status) {
            case 401:
              state.ajaxErrorMessage = "Auth session expired, requesting new...";
              bus.emit("reAuthRequired", { emitter: "SystemStatsCountTotalAttachmentsWidget" });
              break;
            default:
              state.ajaxErrorMessage = "API Error: fatal error";
              break;
          }
        } else {
          state.ajaxErrorMessage = `Uncaught exception: ${errorResponse}`;
          console.error(errorResponse);
        }
      }).finally(() => {
        state.ajaxRunning = false;
      });
  }
}

onMounted(() => {
  onRefresh();
  bus.on("reAuthSucess", (msg) => {
    if (msg.to?.includes("SystemStatsCountTotalAttachmentsWidget")) {
      onRefresh();
    }
  });
});

onBeforeUnmount(() => {
  bus.off("reAuthSucess");
});
</script>