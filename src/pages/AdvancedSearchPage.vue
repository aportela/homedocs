<template>
  <q-page class="bg-grey-2">
    <div class="q-pa-md">
      <h3>Advanced search</h3>
      <div class="q-gutter-y-md">
        <div class="text-h6">Search conditions</div>
        <q-card>
          <form @submit.prevent.stop="onSubmitForm" autocorrect="off" autocapitalize="off" autocomplete="off"
            spellcheck="false">
            <q-card-section>
              <q-input class="q-mb-md" dense outlined v-model="filter.title" type="text" name="title" clearable
                :label="t('Document title')" :disable="searching" :autofocus="true">
                <template v-slot:prepend>
                  <q-icon name="search" />
                </template>
              </q-input>
              <q-input class="q-mb-md" dense outlined v-model="filter.description" type="text" name="description"
                clearable :label="t('Document description')" :disable="searching">
                <template v-slot:prepend>
                  <q-icon name="search" />
                </template>
              </q-input>
              <q-select class="q-mb-md" dense outlined v-model="filter.dateFilterType" :options="dateFilterOptions"
                label="Document date" />
              <!--
                <q-input dense outlined v-model="filter.dateRange" mask="date">
                  <template v-slot:append>
                    <q-icon name="event" class="cursor-pointer">
                      <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                        <q-date v-model="filter.dateRange" range>
                          <div class="row items-center justify-end">
                            <q-btn v-close-popup label="Close" color="primary" flat />
                          </div>
                        </q-date>
                      </q-popup-proxy>
                    </q-icon>
                  </template>
                </q-input>
                -->
              <TagSelector v-model="filter.tags" :disabled="searching">
              </TagSelector>
              <q-btn color="dark" size="md" :label="$t('Search')" no-caps class="full-width" icon="search"
                :disable="searching" :loading="searching" type="submit">
                <template v-slot:loading>
                  <q-spinner-hourglass class="on-left" />
                  {{ t('Searching...') }}
                </template>
              </q-btn>
            </q-card-section>
          </form>
        </q-card>
        <div v-if="hasResults">
          <div class="text-h6">Search results</div>
          <div class="q-pa-lg flex flex-center">
            <q-pagination v-model="results.pagination.currentPage" color="dark" :max="results.pagination.totalPages"
              :max-pages="5" boundary-numbers direction-links boundary-links @update="onPaginationChanged"
              :disable="searching" />
          </div>
          <q-markup-table>
            <thead>
              <tr>
                <th class="text-left">Title</th>
                <th class="text-left">Created on</th>
                <th class="text-right">Files</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="document in results.documents" :key="document.id">
                <td class="text-left"><router-link :to="{ name: 'document', params: { id: document.id } }">{{
                  document.title }}</router-link>
                </td>
                <td class="text-left">{{ document.createdOn }}</td>
                <td class="text-right">{{ document.fileCount }}</td>
              </tr>
            </tbody>
          </q-markup-table>
          <div class="q-pa-lg flex flex-center">
            <q-pagination v-model="results.pagination.currentPage" color="dark" :max="results.pagination.totalPages"
              :max-pages="5" boundary-numbers direction-links boundary-links @update="onPaginationChanged"
              :disable="searching" />
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script setup>

import { ref, computed, registerRuntimeCompiler } from "vue";

import { date } from 'quasar'
import { api } from 'boot/axios'
import { useI18n } from 'vue-i18n'

import { default as TagSelector } from "components/TagSelector.vue";

const { t } = useI18n();

const searching = ref(false);

const tab = ref('conditions');

const dateFilterOptions = ref([
  { label: 'Any date', value: 0 },
  { label: 'today', value: 1 },
  { label: 'yesterday', value: 2 }
]);

const filter = ref({
  title: null,
  description: null,
  dateFilterType: null,
  dateRange: null,
  fromTimestamp: null,
  toTimestamp: null,
  tags: []
});

const results = ref({
  pagination: {},
  documents: []
});


const hasResults = computed(() => results.value.documents && results.value.documents.length > 0);

function onSubmitForm() {
  searching.value = true;
  api.document.search(1, 32, filter.value, "", "DESC")
    .then((success) => {
      results.value = success.data.results;
      results.value.documents.map((document) => {
        document.createdOn = date.formatDate(document.createdOnTimestamp, 'YYYY-MM-DD HH:mm:ss');
        return (document);
      });
      searching.value = false;
      if (hasResults.value) {
        tab.value = "results";
      }
    })
    .catch((error) => {
      switch (error.response.status) {
        case 400:
          $q.notify({
            color: "negative",
            icon: "error",
            message: t("API Error: invalid/missing param"),
          });
          break;
        case 401:
          this.$router.push({
            name: "signIn",
          });
          break;
        default:
          $q.notify({
            color: "negative",
            icon: "warning",
            message: t("API Error: fatal error"),
          });
          break;
      }
      searching.value = false;
    });
}

function onPaginationChanged(e) {
  console.log(e);
}
</script>
