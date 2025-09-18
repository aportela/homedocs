<template>
  <CustomExpansionWidget title="Tag cloud" :caption="loading ? 'Loading...' : 'Click on tag to browse by tag'"
    icon="tag" iconToolTip="Click to refresh data" :onIconClick="refresh" :loading="loading" :error="loadingError"
    :expanded="expanded">
    <template v-slot:header-extra>
      <q-chip square size="sm" color="primary" text-color="white">{{ t("Total tags", {
        count:
          tags.length
      }) }}</q-chip>
    </template>
    <template v-slot:content>
      <div v-if="loading">
        <div class="row items-center q-gutter-sm q-pa-xs">
          <q-skeleton square width="12em" height="2em" class="" v-for="j in 32" :key="j"></q-skeleton>
        </div>
      </div>
      <div v-else-if="!loadingError">
        <div v-if="hasTags">
          <q-chip square class="theme-default-q-chip" v-for="tag in tags" :key="tag.tag">
            <q-avatar class="theme-default-q-avatar">{{ tag.total }}</q-avatar>
            <router-link :to="{ name: 'advancedSearchByTag', params: { tag: tag.tag } }"
              style="text-decoration: none; width: 10em; text-align: center">
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
      <div v-else>
        <CustomBanner class="q-ma-lg" text="Error loading data" error></CustomBanner>
      </div>
    </template>
  </CustomExpansionWidget>
</template>

<script setup>

import { ref, computed, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { useQuasar } from "quasar"
import { api } from "boot/axios";

import { default as CustomExpansionWidget } from "components/CustomExpansionWidget.vue";
import { default as CustomBanner } from "components/CustomBanner.vue";

const { t } = useI18n();
const $q = useQuasar();
const loadingError = ref(false);
const loading = ref(false);

const props = defineProps({
  expanded: Boolean
});

const tags = ref([]);
const hasTags = computed(() => tags.value.length > 0);

function refresh() {
  tags.value = [];
  loading.value = true;
  loadingError.value = false;
  api.tag.getCloud()
    .then((success) => {
      tags.value = success.data.tags;
      loading.value = false;
    })
    .catch((error) => {
      loading.value = false;
      loadingError.value = true;
      const status = error.response?.status || 'N/A';
      const statusText = error.response?.statusText || 'Unknown error';
      $q.notify({
        type: "negative",
        message: t("API Error: fatal error"),
        caption: t("API Error: fatal error details", { status, statusText })
      });
    });
}

onMounted(() => {
  refresh();
});

</script>