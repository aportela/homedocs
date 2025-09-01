<template>
  <div class="fit">
    <q-card class="my-card q-ma-xs" flat bordered>
      <q-card-section v-if="!loading">
        <q-expansion-item :header-class="loadingError ? 'bg-red' : ''" expand-separator
          :icon="loadingError ? 'error' : 'work_history'" :label="t('Recent documents')"
          :caption="t(loadingError ? 'Error loading data' : 'Click on title to open document')" :model-value="expanded">
          <p class="text-center" v-if="loading">
            <q-spinner-pie v-if="loading" color="grey-5" size="md" />
          </p>
          <div v-else>
            <q-list v-if="hasRecentDocuments">
              <q-item v-for="recentDocument in recentDocuments" :key="recentDocument.id">
                <q-item-section top avatar>
                  <q-avatar square icon="work" size="64px" />
                </q-item-section>
                <q-item-section>
                  <q-item-label><router-link :to="{ name: 'document', params: { id: recentDocument.id } }"
                      class="text-decoration-none text-dark">{{ recentDocument.title }}</router-link>
                  </q-item-label>
                  <q-item-label caption lines="2">{{ recentDocument.description }}</q-item-label>
                  <q-item-label>
                    <router-link v-for="tag in recentDocument.tags" :key="tag"
                      :to="{ name: 'advancedSearchByTag', params: { tag: tag } }">
                      <q-chip size="sm" clickable @click="onClick" color="dark" text-color="white" icon="tag">
                        {{ tag }}
                      </q-chip>
                    </router-link>
                  </q-item-label>
                </q-item-section>
                <q-item-section side top>
                  <q-item-label caption>{{ recentDocument.createdOn }}
                  </q-item-label>
                  <div>
                    <q-icon name="save" size="xs" />{{ t("Files") }}: {{ recentDocument.fileCount }}
                  </div>
                </q-item-section>
              </q-item>
            </q-list>
            <q-banner v-else-if="!loadingError"><q-icon name="info" size="md" class="q-mr-sm" />
              {{ t("You haven't created any documents yet") }}
            </q-banner>
          </div>
        </q-expansion-item>
      </q-card-section>
    </q-card>
  </div>
</template>

<script setup>

import { ref } from "vue";
import { useI18n } from "vue-i18n";
import { date, useQuasar } from "quasar";
import { api } from "boot/axios";

const { t } = useI18n();
const $q = useQuasar();
const loadingError = ref(false);
const loading = ref(false);

let expanded = !$q.screen.lt.md;
let recentDocuments = [];
let hasRecentDocuments = false;

function refresh() {
  recentDocuments = [];
  hasRecentDocuments = false;
  loading.value = true;
  loadingError.value = false;
  api.document.searchRecent(16)
    .then((success) => {
      recentDocuments = success.data.recentDocuments.map((document) => {
        document.createdOn = date.formatDate(document.createdOnTimestamp * 1000, 'YYYY-MM-DD HH:mm:ss');
        return (document);
      });
      hasRecentDocuments = recentDocuments.length > 0;
      loading.value = false;
    })
    .catch((error) => {
      loading.value = false;
      loadingError.value = true;
      $q.notify({
        type: "negative",
        message: t("API Error: fatal error"),
        caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
      });
    });
}

refresh();

</script>
