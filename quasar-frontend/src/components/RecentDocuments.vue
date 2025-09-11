<template>
  <div class="fit">
    <q-card class="q-ma-xs" flat bordered>
      <q-card-section v-if="!loading">
        <q-expansion-item :header-class="loadingError ? 'bg-red' : ''" expand-separator
          :icon="loadingError ? 'error' : 'work_history'" :label="t('Recent documents')"
          :caption="t(loadingError ? 'Error loading data' : 'Click on title to open document')" :model-value="expanded">
          <p class="text-center" v-if="loading">
            <q-spinner-pie color="grey-5" size="md" />
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
                      <q-chip square size="md" clickable icon="tag">
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
                  <div>
                    <q-icon name="forum" size="xs" />{{ t("Notes") }}: {{ recentDocument.noteCount }}
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

function refresh() {
  recentDocuments.value = [];
  loading.value = true;
  loadingError.value = false;
  api.document.searchRecent(16)
    .then((success) => {
      recentDocuments.value = success.data.recentDocuments.map((document) => {
        document.createdOn = date.formatDate(document.createdOnTimestamp * 1000, 'YYYY-MM-DD HH:mm:ss');
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