<template>
  <div class="fit">
    <q-expansion-item class="theme-default-q-expansion-item q-my-lg"
      header-class="theme-default-q-expansion-item-header" expand-separator :model-value="expanded">
      <template v-slot:header>
        <q-item-section avatar>
          <q-icon name="work_history"></q-icon>
        </q-item-section>
        <q-item-section class="">
          <q-item-label>{{ t("Most recent activity") }} <q-chip size="sm" color="primary" text-color="white">2
              documents</q-chip></q-item-label>
          <q-item-label caption>{{ t('Click on title to open document') }}</q-item-label>
        </q-item-section>
      </template>
      <q-card class="q-ma-xs" flat>
        <q-card-section class="q-pa-none">
          <p class="text-center" v-if="loading">
            <q-spinner-pie color="grey-5" size="md" />
          </p>
          <div v-else>
            <q-banner v-if="loadingError" class="transparent-background text-red">
              <q-icon name="error" size="sm" class="q-mr-sm" />
              {{ t("Error loading data") }}
            </q-banner>
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
          </div>
        </q-card-section>
      </q-card>
    </q-expansion-item>

    <q-expansion-item header-class="theme-default-q-expansion-item-header" expand-separator icon="work_history"
      :label="t('Recent documents')" :caption="t('Click on title to open document')" :model-value="expanded"
      class="theme-default-q-expansion-item">
      <q-card class="q-ma-xs" flat>
        <q-card-section class="q-pa-none">
          <p class="text-center" v-if="loading">
            <q-spinner-pie color="grey-5" size="md" />
          </p>
          <div v-else>
            <q-banner v-if="loadingError" class="transparent-background text-red">
              <q-icon name="error" size="sm" class="q-mr-sm" />
              {{ t("Error loading data") }}
            </q-banner>
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
      //loading.value = false;
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