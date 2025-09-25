<template>
  <CustomExpansionWidget title="Tag cloud" :caption="isExpanded ? 'Click to collapse' : 'Click to expand'" icon="tag"
    iconToolTip="Click to refresh data" :onHeaderIconClick="onRefresh" :loading="state.loading"
    :error="state.loadingError" :expanded="isExpanded" @expand="isExpanded = true" @collapse="isExpanded = false">
    <template v-slot:header-extra>
      <q-chip square size="sm" color="primary" text-color="white" class="shadow-1">{{ t("Total tags", {
        count:
          tags.length
      }) }}</q-chip>
    </template>
    <template v-slot:content>
      <div class="row items-center q-gutter-sm q-pa-xs" v-if="state.loading">
        <q-skeleton square width="12em" height="2em" class="" v-for="j in 32" :key="j"></q-skeleton>
      </div>
      <CustomErrorBanner v-else-if="state.loadingError" :text="state.errorMessage || 'Error loading data'"
        :apiError="state.apiError">
      </CustomErrorBanner>
      <div v-else-if="hasTags">
        <div v-if="hasTags">
          <router-link v-for="tag in tags" :key="tag.tag"
            :to="{ name: 'advancedSearchByTag', params: { tag: tag.tag } }" class="text-decoration-none"
            aria-label="Browse by tag">
            <q-chip square class="theme-default-q-chip q-chip-10em shadow-1">
              <q-avatar class="theme-default-q-avatar">{{ tag.total }}</q-avatar>
              <div class="full-width text-center ellipsis">
                {{ tag.tag }}
              </div>
              <q-tooltip>{{ t("Browse by tag: ", { tag: tag.tag }) }}</q-tooltip>
            </q-chip>
          </router-link>
        </div>
        <q-banner v-else class="transparent-background">
          <q-icon name="warning" size="sm" class="q-mr-sm" />
          {{ t("You haven't created any tags yet") }}
        </q-banner>
      </div>
      <CustomBanner v-else warning text="You haven't created any tags yet"></CustomBanner>
    </template>
  </CustomExpansionWidget>
</template>

<script setup>

import { ref, reactive, computed, onMounted, onBeforeUnmount } from "vue";
import { useI18n } from "vue-i18n";
import { bus } from "src/boot/bus";
import { api } from "src/boot/axios";

import { default as CustomExpansionWidget } from "src/components/Widgets/CustomExpansionWidget.vue";
import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue";

const { t } = useI18n();

const props = defineProps({
  expanded: {
    type: Boolean,
    required: false,
    default: true
  }
});

const isExpanded = ref(props.expanded);

const state = reactive({
  loading: false,
  loadingError: false,
  errorMessage: null,
  apiError: null
});

const tags = reactive([]);
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
        if (!isExpanded.value) {
          isExpanded.value = true;
        }
      })
      .catch((errorResponse) => {
        state.loadingError = true;
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

<style scoped>
.q-chip-10em {
  width: 10em;
}
</style>