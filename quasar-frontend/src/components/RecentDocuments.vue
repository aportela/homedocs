<template>
  <q-card class="my-card fit" flat bordered>
    <q-card-section>
      <q-expansion-item :header-class="loadingError ? 'bg-red' : ''" expand-separator
        :icon="loadingError ? 'error' : 'work_history'" :label="t('Recent documents')"
        :caption="t(loadingError ? 'Error loading data' : 'Click on title to open document')" :model-value="expanded">
        <p class="text-center" v-if="loading">
          <q-spinner-pie v-if="loading" color="grey-5" size="md" />
        </p>
        <div v-else>
          <q-markup-table v-if="hasRecentDocuments">
            <thead>
              <tr>
                <th class="text-left">{{ t("Title") }}</th>
                <th class="text-left">{{ t("Created on") }}</th>
                <th class="text-right">{{ t("Files") }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="recentDocument in recentDocuments" :key="recentDocument.id">
                <td class="text-left"><router-link :to="{ name: 'document', params: { id: recentDocument.id } }">{{
                  recentDocument.title }}</router-link>
                </td>
                <td class="text-left">{{ recentDocument.createdOn }}</td>
                <td class="text-right">{{ recentDocument.fileCount }}</td>
              </tr>
            </tbody>
          </q-markup-table>
          <q-banner class="bg-grey text-white" v-else><q-icon name="info" size="md" class="q-mr-sm" />
            {{ t("You haven't created any documents yet") }}
          </q-banner>
        </div>
      </q-expansion-item>
    </q-card-section>
  </q-card>
</template>

<script setup>

import { ref, computed } from "vue";
import { useI18n } from 'vue-i18n'
import { date, useQuasar } from 'quasar'
import { api } from 'boot/axios'

const { t } = useI18n();
const $q = useQuasar();
const loadingError = ref(false);
const loading = ref(false);
const expanded = ref(!$q.screen.lt.md);
const recentDocuments = ref([]);

const hasRecentDocuments = computed(
  () => recentDocuments.value && recentDocuments.value.length > 0
);

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
      $q.notify({
        type: "negative",
        message: t("API Error: fatal error"),
        caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
      });
    });
}

refresh();

</script>
