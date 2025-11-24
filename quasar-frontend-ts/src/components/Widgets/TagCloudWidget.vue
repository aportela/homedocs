<template>
  <CustomExpansionWidget title="Tag cloud" icon="tag" icon-tool-tip="Click to refresh data"
    :on-header-icon-click="onRefresh" :loading="state.loading" :error="state.loadingError" :expanded="isExpanded"
    @expand="isExpanded = true" @collapse="isExpanded = false">
    <template v-slot:header-extra>
      <q-chip square size="sm" color="grey-7" text-color="white">{{ t("Total tags", {
        count:
          tags.length
      }) }}</q-chip>
    </template>
    <template v-slot:content>
      <div class="row items-center q-gutter-sm q-mt-none q-pa-xs" v-if="state.loading">
        <q-skeleton square width="12em" height="2em" class="" v-for="j in 32" :key="j"></q-skeleton>
      </div>
      <CustomErrorBanner v-else-if="state.loadingError" :text="state.errorMessage || 'Error loading data'"
        :api-error="state.apiError">
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
import { ref, reactive, computed, onMounted, onBeforeUnmount } from "vue";
import { useI18n } from "vue-i18n";
import { useBus } from "src/composables/useBus";
import { useAPI } from "src/composables/useAPI";
import type { APIErrorDetails as APIErrorDetailsInterface } from "src/types/api-error-details";

import { default as CustomExpansionWidget } from "src/components/Widgets/CustomExpansionWidget.vue";
import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue";
import { default as BrowseByTagButton } from "src/components/Buttons/BrowseByTagButton.vue";

const { t } = useI18n();
const { api } = useAPI();
const { bus } = useBus();

interface TagCloudWidgetProps {
  expanded?: boolean
};

const props = withDefaults(defineProps<TagCloudWidgetProps>(), {
  expanded: true
});

const isExpanded = ref(props.expanded);

interface State {
  loading: boolean,
  loadingError: boolean,
  errorMessage: string | null,
  apiError: APIErrorDetailsInterface | null
};

const state: State = reactive({
  loading: false,
  loadingError: false,
  errorMessage: null,
  apiError: null
});

const tags = reactive<Array<string>>([]);
const hasTags = computed(() => tags.length > 0);

const onRefresh = () => {
  if (!state.loading) {
    state.loading = true;
    state.loadingError = false;
    state.errorMessage = null;
    state.apiError = null;
    api.tag.getCloud()
      .then((successResponse) => {
        tags.length = 0;
        tags.push(...successResponse.data.tags);
        state.loading = false;
      })
      .catch((errorResponse) => {
        state.loadingError = true;
        if (errorResponse.isAPIError) {
          switch (errorResponse.response.status) {
            case 401:
              state.apiError = errorResponse.customAPIErrorDetails;
              state.errorMessage = "Auth session expired, requesting new...";
              bus.emit("reAuthRequired", { emitter: "TagCloudWidget" });
              break;
            default:
              state.apiError = errorResponse.customAPIErrorDetails;
              state.errorMessage = "API Error: fatal error";
              break;
          }
        } else {
          state.errorMessage = `Uncaught exception: ${errorResponse}`;
          console.error(errorResponse);
        }
        state.loading = false;
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
