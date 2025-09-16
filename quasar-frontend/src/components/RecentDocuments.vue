<template>
  <div class="fit">
    <q-expansion-item class="theme-default-q-expansion-item" header-class="theme-default-q-expansion-item-header"
      expand-separator :model-value="expanded">
      <template v-slot:header>
        <q-item-section avatar>
          <q-icon v-if="loading" name="settings" class="animation-spin"></q-icon>
          <q-icon v-else-if="loadingError" name="error" color="red"></q-icon>
          <q-icon v-else name="work_history" class="cursor-pointer" @click.stop="refresh">
            <q-tooltip>{{ t("Click to refresh data") }}</q-tooltip>
          </q-icon>
        </q-item-section>
        <q-item-section class="">
          <q-item-label>{{ t("Most recent activity") }}
            <q-chip square size="sm" color="primary" text-color="white">{{ t("Total documents", {
              count:
                recentDocuments.length
            }) }}</q-chip>
          </q-item-label>
          <q-item-label caption>{{ t(loading ? 'Loading...' : 'Click on title to open document') }}</q-item-label>
        </q-item-section>
      </template>
      <q-card class="q-ma-xs q-mt-sm" flat>
        <q-card-section class="q-pa-none">
          <div v-if="loading">
            <q-list>
              <div v-for="i in 4" :key="i">
                <q-item class="transparent-background text-color-primary">
                  <q-item-section top avatar class="gt-xs q-mt-lg">
                    <q-skeleton type="QAvatar" square size="32px"></q-skeleton>
                  </q-item-section>
                  <q-item-section class="q-mx-md">
                    <q-item-label>
                      <q-skeleton type="text" />
                    </q-item-label>
                    <q-item-label caption lines="2">
                      <q-skeleton type="text" height="2em" />
                    </q-item-label>
                    <q-item-label>
                      <div class="row items-left q-gutter-sm">
                        <q-skeleton square width="6em" height="2em" class="" v-for="j in 5" :key="j"></q-skeleton>
                      </div>
                    </q-item-label>
                  </q-item-section>
                  <q-item-section side top>
                    <q-item-label caption>
                      <q-skeleton type="text" width="8em" height="2em"></q-skeleton>
                      <q-skeleton square width="8em" height="2em" class="q-mt-sm"></q-skeleton>
                      <q-skeleton square width="8em" height="2em" class="q-mt-sm"></q-skeleton>
                    </q-item-label>
                  </q-item-section>
                </q-item>
                <q-separator inset v-if="i < 3" class="q-my-md" />
              </div>
            </q-list>
          </div>
          <div v-else>
            <q-list v-if="hasRecentDocuments">
              <div v-for="recentDocument, index in recentDocuments" :key="recentDocument.id">
                <q-item class="transparent-background text-color-primary">
                  <q-item-section top avatar class="gt-xs">
                    <q-avatar square icon="work" size="64px" />
                  </q-item-section>
                  <q-item-section top>
                    <q-item-label>
                      <router-link :to="{ name: 'document', params: { id: recentDocument.id } }"
                        class="text-decoration-hover text-color-primary text-weight-bold">{{ recentDocument.title }}
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
                    <q-item-label caption>{{ timeAgo(recentDocument.timestamp) }}</q-item-label>
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
                <q-separator inset v-if="index !== recentDocuments.length - 1" class="q-my-md" />
              </div>
            </q-list>
            <q-banner v-else class="transparent-background">
              <q-icon name="warning" size="sm" class="q-mr-sm" />
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
import { useQuasar } from "quasar";
import { api } from "boot/axios";
import { useFormatDates } from "src/composables/formatDate"

const { t } = useI18n();
const { timeAgo } = useFormatDates();
const $q = useQuasar();
const loadingError = ref(false);
const loading = ref(false);

const expanded = ref(!$q.screen.lt.md);
const recentDocuments = ref([]);
const hasRecentDocuments = computed(() => recentDocuments.value.length > 0);

function refresh() {
  recentDocuments.value = [];
  loading.value = true;
  loadingError.value = false;
  api.document.searchRecent(16)
    .then((success) => {
      recentDocuments.value = success.data.recentDocuments.map((document) => {
        document.timestamp = document.lastUpdateTimestamp * 1000;
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