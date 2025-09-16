<template>
  <div class="fit">
    <q-expansion-item :header-class="{ 'bg-red': loadingError, 'q-expansion-item-header': true }" expand-separator
      :icon="loadingError ? 'error' : 'work_history'" :label="t('Recent documents')"
      :caption="t(loadingError ? 'Error loading data' : 'Click on title to open document')" :model-value="expanded"
      class="rounded-borders q-expansion-item-themed" bordered>
      <q-card class="q-ma-xs transparent-background" flat>
        <q-card-section class="q-pa-none">
          <p class="text-center" v-if="loading">
            <q-spinner-pie color="grey-5" size="md" />
          </p>
          <div v-else>
            <q-list v-if="hasRecentDocuments">
              <div v-for="recentDocument, index in recentDocuments" :key="recentDocument.id">
                <q-item class="transparent-background text-color-primary">
                  <q-item-section top avatar class="gt-xs">
                    <q-avatar square icon="work" size="64px" />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label>
                      <router-link :to="{ name: 'document', params: { id: recentDocument.id } }"
                        class="text-decoration-none text-color-primary text-weight-bold">{{ recentDocument.title }}
                      </router-link>
                    </q-item-label>
                    <q-item-label caption lines="2">{{ recentDocument.description }}</q-item-label>
                    <q-item-label>
                      <router-link v-for="tag in recentDocument.tags" :key="tag"
                        :to="{ name: 'advancedSearchByTag', params: { tag: tag } }">
                        <q-chip square size="md" clickable icon="tag" class="q-chip-themed">
                          {{ tag }}
                          <q-tooltip>{{ t("Browse by tag: ", { tag: tag }) }}</q-tooltip>
                        </q-chip>
                      </router-link>
                    </q-item-label>
                  </q-item-section>
                  <q-item-section side top>
                    <q-item-label caption>{{ timeAgo(recentDocument.timestamp * 1000) }}</q-item-label>
                    <q-chip size="md" square class="full-width theme-default-q-chip">
                      <q-avatar class="theme-default-q-avatar">{{ recentDocument.fileCount }}</q-avatar>
                      {{ t("Files") }}
                    </q-chip>
                    <q-chip size="md" square class="full-width theme-default-q-chip">
                      <q-avatar class="theme-default-q-avatar">{{ recentDocument.noteCount }}</q-avatar>
                      {{ t("Notes") }}
                    </q-chip>
                  </q-item-section>
                </q-item>
                <q-separator v-if="index !== recentDocuments.length - 1" class="q-my-md" />
              </div>
            </q-list>
            <q-banner v-else-if="!loadingError"><q-icon name="info" size="md" class="q-mr-sm" />
              {{ t("You haven't created any documents yet") }}
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
import { date, useQuasar } from "quasar";
import { api } from "boot/axios";

const { t } = useI18n();
const $q = useQuasar();
const loadingError = ref(false);
const loading = ref(false);

const expanded = ref(!$q.screen.lt.md);
const recentDocuments = ref([]);
const hasRecentDocuments = computed(() => recentDocuments.value.length > 0);

function timeAgo(timestamp) {
  const now = Date.now();
  const diff = now - new Date(timestamp).getTime();

  const seconds = Math.floor(diff / 1000);
  const minutes = Math.floor(seconds / 60);
  const hours = Math.floor(minutes / 60);
  const days = Math.floor(hours / 24);
  const months = Math.floor(days / 30);  // WARNING: NOT EXACT (30 days / month)
  const years = Math.floor(days / 365);  // WARNING: NOT EXACT (365 days / year)

  if (years > 0) {
    return (t("timeAgo.year", { count: years }));
  } else if (months > 0) {
    return (t("timeAgo.month", { count: months }));
  } else if (days > 0) {
    return (t("timeAgo.day", { count: days }));
  } else if (hours > 0) {
    return (t("timeAgo.hour", { count: hours }));
  } else if (minutes > 0) {
    return (t("timeAgo.minute", { count: minutes }));
  } else if (seconds > 0) {
    return (t("timeAgo.second", { count: seconds }));
  } else {
    return (t("timeAgo.now"));
  }
}

function refresh() {
  recentDocuments.value = [];
  loading.value = true;
  loadingError.value = false;
  api.document.searchRecent(16)
    .then((success) => {
      recentDocuments.value = success.data.recentDocuments.map((document) => {
        document.timestamp = document.lastUpdateTimestamp;
        return (document);
      });
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