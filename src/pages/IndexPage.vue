<template>
  <q-page class="bg-grey-2">
    <div class="row">
      <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 flex">
        <q-card class="my-card fit" flat bordered>
          <q-card-section>
            <q-expansion-item expand-separator icon="work_history" label="Recent documents"
              caption="Click on title to open document" :model-value="!$q.screen.lt.md">
              <q-markup-table>
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
            </q-expansion-item>
          </q-card-section>
        </q-card>
      </div>
      <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 flex">
        <q-card class="my-card fit" flat bordered>
          <q-card-section>
            <q-expansion-item expand-separator icon="bookmark" label="Tag cloud" caption="Click on tag to browse by tag"
              :model-value="!$q.screen.lt.md">
              <q-chip square outline text-color="dark" v-for="tag in tags" :key="tag"
                :title="t('Click here to browse documents containing this tag')">
                <q-avatar color="grey-9" text-color="white">{{ tag.total }}</q-avatar>
                <router-link :to="{ name: 'advancedSearchByTag', params: { tag: tag.tag } }" style="text-decoration: none"
                  class="text-dark">
                  {{ tag.tag }}</router-link>
              </q-chip>
            </q-expansion-item>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
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
const tags = ref([]);

function onRefreshRecentDocuments() {
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
      }
      loading.value = false;
    });
}

function onRefreshTagCloud() {
  api.tag.getCloud()
    .then((success) => {
      tags.value = success.data.tags;
      loading.value = false;
    })
    .catch((error) => {
      switch (error.response.status) {
      }
      loading.value = false;
    });
}

onRefreshRecentDocuments();
onRefreshTagCloud();
</script>
