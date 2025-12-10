<template>
  <CustomExpansionWidget title="Tag cloud" icon="tag" icon-tool-tip="Click to refresh data"
    :on-header-icon-click="onRefresh" :loading="state.ajaxRunning" :error="state.ajaxErrors" :expanded="isExpanded"
    @expand="isExpanded = true" @collapse="isExpanded = false">
    <template v-slot:header-extra>
      <q-chip square size="sm" class="theme-q-chip-highlight" v-show="tags.length > 0">{{ t("Total tags", {
        count:
          tags.length
      }) }}</q-chip>
    </template>
    <template v-slot:content>
      <div class="row items-center q-gutter-sm q-mt-none q-pa-xs" v-if="state.ajaxRunning">
        <q-skeleton square width="12em" height="2em" class="" v-for="j in 32" :key="j"></q-skeleton>
      </div>
      <CustomErrorBanner v-else-if="state.ajaxErrors" :text="state.ajaxErrorMessage || 'Error loading data'"
        :api-error="state.ajaxAPIErrorDetails">
      </CustomErrorBanner>
      <div v-else-if="hasTags">
        <BrowseByTagButton v-for="tag in tags" :key="tag.tag" :tag="tag.tag" :caption="String(tag.total)"
          :q-chip-class="'q-chip-10em'"></BrowseByTagButton>
      </div>
      <CustomBanner v-else warning text="You haven't created any tags yet"></CustomBanner>
    </template>
  </CustomExpansionWidget>
</template>

<script setup lang="ts">
import { ref, shallowRef, reactive, computed, onMounted, onBeforeUnmount } from "vue";
import { useI18n } from "vue-i18n";
import { bus } from "src/composables/bus";
import { api } from "src/composables/api";
import { type AjaxState as AjaxStateInterface, defaultAjaxState } from "src/types/ajaxState";
import { type TagCloudResponse, type TagCloudResponseItem } from "src/types/apiResponses";

import { default as CustomExpansionWidget } from "src/components/Widgets/CustomExpansionWidget.vue";
import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue";
import { default as BrowseByTagButton } from "src/components/Buttons/BrowseByTagButton.vue";

const { t } = useI18n();

interface TagCloudWidgetProps {
  expanded?: boolean
};

const props = withDefaults(defineProps<TagCloudWidgetProps>(), {
  expanded: true
});

const isExpanded = ref(props.expanded);

const state: AjaxStateInterface = reactive({ ...defaultAjaxState });

const tags = shallowRef<Array<TagCloudResponseItem>>([]);

const hasTags = computed(() => tags.value.length > 0);

const onRefresh = () => {
  if (!state.ajaxRunning) {
    Object.assign(state, defaultAjaxState);
    state.ajaxRunning = true;
    api.tag.getCloud()
      .then((successResponse: TagCloudResponse) => {
        tags.value = successResponse.data.tags;
      })
      .catch((errorResponse) => {
        state.ajaxErrors = true;
        if (errorResponse.isAPIError) {
          state.ajaxAPIErrorDetails = errorResponse.customAPIErrorDetails;
          switch (errorResponse.response.status) {
            case 401:
              state.ajaxErrors = false;
              bus.emit("reAuthRequired", { emitter: "TagCloudWidget" });
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
};

onMounted(() => {
  onRefresh();
  bus.on("reAuthSucess", (msg) => {
    if (msg.to?.includes("TagCloudWidget")) {
      onRefresh();
    }
  });
});

onBeforeUnmount(() => {
  bus.off("reAuthSucess");
});
</script>

<style lang="css">
.q-chip-10em {
  width: 10em;
}
</style>
