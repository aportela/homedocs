<template>
  <q-card class="my-card fit" flat bordered>
    <q-card-section>
      <q-expansion-item expand-separator icon="work_history" label="Recent documents"
        caption="Click on title to open document" :model-value="!$q.screen.lt.md">
        <q-markup-table v-if="!loading">
          <thead>
            <tr>
              <th class="text-left">Title</th>
              <th class="text-left">Created on</th>
              <th class="text-right">Files</th>
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
        <p class="text-center" v-else>
          <q-spinner-pie color="grey-5" size="md" />
        </p>
      </q-expansion-item>
    </q-card-section>
  </q-card>
</template>

<script setup>

import { ref } from "vue";
import { date } from 'quasar'
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'
import { useI18n } from 'vue-i18n'

const $q = useQuasar();
const { t } = useI18n();

const loading = ref(false);
const recentDocuments = ref([]);

function onRefreshRecentDocuments() {
  loading.value = true;
  api.document.searchRecent(16)
    .then((success) => {
      recentDocuments.value = success.data.recentDocuments.map((document) => {
        document.createdOn = date.formatDate(document.createdOnTimestamp * 1000, 'YYYY-MM-DD HH:mm:ss');
        return (document);
      });
      loading.value = false;
    })
    .catch((error) => {
      switch (error.response.status) {
        // TODO
      }
      loading.value = false;
      $q.notify({
        type: "negative",
        message: t("API Error: fatal error"),
      });
    });
}

onRefreshRecentDocuments();

</script>
