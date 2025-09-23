<template>
  <CustomExpansionWidget title="Tag cloud" :caption="state.loading ? 'Loading...' : 'Click on tag to browse by tag'"
    icon="tag" iconToolTip="Click to refresh data" :onHeaderIconClick="onRefresh" :loading="state.loading"
    :error="state.loadingError" :expanded="expanded">
    <template v-slot:header-extra>
      <q-chip square size="sm" color="primary" text-color="white">{{ t("Total tags", {
        count:
          tags.length
      }) }}</q-chip>
    </template>
    <template v-slot:content>
      <div class="row items-center q-gutter-sm q-pa-xs" v-if="state.loading">
        <q-skeleton square width="12em" height="2em" class="" v-for="j in 32" :key="j"></q-skeleton>
      </div>
      <CustomErrorBanner v-else-if="state.loadingError" text="Error loading data" :apiError="state.apiError">
      </CustomErrorBanner>
      <div v-else-if="hasTags">
        <div v-if="hasTags">
          <q-chip square class="theme-default-q-chip" v-for="tag in tags" :key="tag.tag">
            <q-avatar class="theme-default-q-avatar">{{ tag.total }}</q-avatar>
            <router-link :to="{ name: 'advancedSearchByTag', params: { tag: tag.tag } }"
              class="text-center text-decoration-none q-chip-10em" aria-label="Browse by tag">
              <div class="ellipsis">
                {{ tag.tag }}
                <q-tooltip>{{ t("Browse by tag: ", { tag: tag.tag }) }}</q-tooltip>
              </div>
            </router-link>
          </q-chip>
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

import { reactive, computed, onMounted } from "vue";
import { useI18n } from "vue-i18n";
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

const state = reactive({
  loading: false,
  loadingError: false,
  apiError: null
});

const tags = reactive([]);
const hasTags = computed(() => tags.length > 0);

function onRefresh() {
  state.loading = true;
  tags.length = 0;
  state.loadingError = false;
  state.apiError = null;
  api.tag.getCloud()
    .then((successResponse) => {
      tags.push(...successResponse.data.tags);
      state.loading = false;
    })
    .catch((errorResponse) => {
      state.loadingError = true;
      state.apiError = errorResponse.customAPIErrorDetails;
      state.loading = false;
    });
}

onMounted(() => {
  onRefresh();
});

</script>

<style scoped>
.q-chip-10em {
  width: 10em;
}
</style>