<template>
  <div class="fit">
    <q-expansion-item class="theme-default-q-expansion-item" header-class="theme-default-q-expansion-item-header"
      expand-separator :model-value="expanded">
      <template v-slot:header>
        <q-item-section avatar>
          <q-icon v-if="loading" name="settings" class="animation-spin"></q-icon>
          <q-icon v-else-if="loadingError" name="error" color="red"></q-icon>
          <q-icon v-else name="tag" class="cursor-pointer" @click.stop="refresh">
            <q-tooltip>{{ t("Click to refresh data") }}</q-tooltip>
          </q-icon>
        </q-item-section>
        <q-item-section class="">
          <q-item-label>{{ t("Tag cloud") }}
            <q-chip square size="sm" color="primary" text-color="white">{{ t("Total tags", {
              count:
                tags.length
            }) }}</q-chip>
          </q-item-label>
          <q-item-label caption>{{ t(loading ? 'Loading...' : 'Click on tag to browse by tag') }}</q-item-label>
        </q-item-section>
      </template>
      <q-card class="q-ma-xs q-mt-sm" flat>
        <q-card-section class="q-pa-none">
          <div v-if="loading">
            <div class="row items-center q-gutter-sm q-pa-xs">
              <q-skeleton square width="12em" height="2em" class="" v-for="j in 32" :key="j"></q-skeleton>
            </div>
          </div>
          <div v-else>
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
        </q-card-section>
      </q-card>
    </q-expansion-item>
  </div>
</template>

<script setup>

import { ref, computed, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { useQuasar } from "quasar"
import { api } from "boot/axios";

const { t } = useI18n();
const $q = useQuasar();
const loadingError = ref(false);
const loading = ref(false);

const expanded = ref(!$q.screen.lt.md);
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