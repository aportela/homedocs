<template>
  <q-page class="bg-grey-2">
    <div class="q-pa-md">
      <h3 class="q-mt-sm">{{ t("Advanced search") }}</h3>
      <div class="q-gutter-y-md">
        <q-card>
          <q-card-section>
            <q-expansion-item expand-separator icon="filter_alt" :label="t('Conditions')" :model-value="expandedFilter">
              <form @submit.prevent.stop="onSubmitForm(true)" autocorrect="off" autocapitalize="off" autocomplete="off"
                spellcheck="false" class="q-mt-md">
                <q-input class="q-mb-md" dense outlined v-model="advancedSearchData.filter.title" type="text" name="title"
                  clearable :label="t('Document title')" :disable="searching" :autofocus="true">
                  <template v-slot:prepend>
                    <q-icon name="search" />
                  </template>
                </q-input>
                <q-input class="q-mb-md" dense outlined v-model="advancedSearchData.filter.description" type="text"
                  name="description" clearable :label="t('Document description')" :disable="searching">
                  <template v-slot:prepend>
                    <q-icon name="search" />
                  </template>
                </q-input>
                <div class="row">
                  <div class="col">
                    <q-select class="q-mb-md" dense outlined v-model="advancedSearchData.filter.dateFilterType"
                      :options="dateFilterOptions" :label="t('Document date')" :disable="searching" />
                  </div>
                  <div class="col" v-if="advancedSearchData.hasFromDateFilter">
                    <q-input dense outlined mask="date" v-model="advancedSearchData.filter.fromDate"
                      :label="t('From date')" :disable="searching || advancedSearchData.denyChangeDateFilters">
                      <template v-slot:append>
                        <q-icon name="event" class="cursor-pointer">
                          <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                            <q-date v-model="advancedSearchData.filter.fromDate" today-btn
                              :disable="searching || advancedSearchData.denyChangeDateFilters">
                              <div class="row items-center justify-end">
                                <q-btn v-close-popup label="Close" color="primary" flat />
                              </div>
                            </q-date>
                          </q-popup-proxy>
                        </q-icon>
                      </template>
                    </q-input>
                  </div>
                  <div class="col" v-if="advancedSearchData.hasToDateFilter">
                    <q-input dense outlined mask="date" v-model="advancedSearchData.filter.toDate" :label="t('To date')"
                      :disable="searching || advancedSearchData.denyChangeDateFilters">
                      <template v-slot:append>
                        <q-icon name="event" class="cursor-pointer">
                          <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                            <q-date v-model="advancedSearchData.filter.toDate" today-btn
                              :disable="searching || advancedSearchData.denyChangeDateFilters">
                              <div class="row items-center justify-end">
                                <q-btn v-close-popup label="Close" color="primary" flat />
                              </div>
                            </q-date>
                          </q-popup-proxy>
                        </q-icon>
                      </template>
                    </q-input>
                  </div>
                  <div class="col" v-if="advancedSearchData.hasFixedDateFilter">
                    <q-input dense outlined mask="date" v-model="advancedSearchData.filter.fromDate"
                      :label="t('Fixed date')" :disable="searching || advancedSearchData.denyChangeDateFilters">
                      <template v-slot:append>
                        <q-icon name="event" class="cursor-pointer">
                          <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                            <q-date v-model="advancedSearchData.filter.fromDate" today-btn
                              :disable="searching || advancedSearchData.denyChangeDateFilters">
                              <div class="row items-center justify-end">
                                <q-btn v-close-popup label="Close" color="primary" flat />
                              </div>
                            </q-date>
                          </q-popup-proxy>
                        </q-icon>
                      </template>
                    </q-input>
                  </div>
                </div>
                <TagSelector v-model="advancedSearchData.filter.tags"
                  :disabled="searching || advancedSearchData.denyChangeDateFilters" dense>
                </TagSelector>
                <q-btn color="dark" size="md" :label="$t('Search')" no-caps class="full-width" icon="search"
                  :disable="searching" :loading="searching" type="submit">
                  <template v-slot:loading>
                    <q-spinner-hourglass class="on-left" />
                    {{ t('Searching...') }}
                  </template>
                </q-btn>
              </form>
            </q-expansion-item>
          </q-card-section>
        </q-card>
        <q-card v-if="searchLaunched">
          <q-card-section>
            <q-expansion-item expand-separator icon="folder_open"
              :label="t('Results') + ' (' + advancedSearchData.pager.totalResults + ')'" :model-value="expandedResults"
              v-if="advancedSearchData.hasResults">
              <div class="q-pa-lg flex flex-center" v-if="advancedSearchData.pager.totalPages > 1">
                <q-pagination v-model="advancedSearchData.pager.currentPage" color="dark"
                  :max="advancedSearchData.pager.totalPages" :max-pages="5" boundary-numbers direction-links
                  boundary-links @update:model-value="onPaginationChanged" :disable="searching" />
              </div>
              <q-markup-table>
                <thead>
                  <tr>
                    <th style="width: 60%;" class="text-left cursor-pointer" @click="onToggleSort('title')">{{
                      t("Title") }}
                      <q-icon :name="sortOrderIcon" v-if="advancedSearchData.isSortedByField('title')" size="sm"></q-icon>
                    </th>
                    <th style="width: 20%;" class="text-left cursor-pointer" @click="onToggleSort('createdOnTimestamp')">
                      {{ t("Date") }}
                      <q-icon :name="sortOrderIcon" v-if="advancedSearchData.isSortedByField('createdOnTimestamp')"
                        size=" sm"></q-icon>
                    </th>
                    <th style="width: 10%;" class="text-right cursor-pointer" @click="onToggleSort('fileCount')">
                      {{ t("Files") }}<q-icon :name="sortOrderIcon" v-if="advancedSearchData.isSortedByField('fileCount')"
                        size="sm"></q-icon>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for=" document in advancedSearchData.results " :key="document.id">
                    <td class="text-left"><router-link :to="{ name: 'document', params: { id: document.id } }">{{
                      document.title }}</router-link>
                    </td>
                    <td class="text-left">{{ document.createdOn }}</td>
                    <td class="text-right">{{ document.fileCount }}</td>
                  </tr>
                </tbody>
              </q-markup-table>
              <div class="q-pa-lg flex flex-center" v-if="advancedSearchData.pager.totalPages > 1">
                <q-pagination v-model="advancedSearchData.pager.currentPage" color="dark"
                  :max="advancedSearchData.pager.totalPages" :max-pages="5" boundary-numbers direction-links
                  boundary-links @update:model-value="onPaginationChanged" :disable="searching" />
              </div>
            </q-expansion-item>
            <q-banner dense v-else>
              <template v-slot:avatar>
                <q-icon name="error" />
              </template>
              {{ t("No results found with current filter") }}</q-banner>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script setup>

import { ref, computed, watch, registerRuntimeCompiler } from "vue";

import { useRoute } from "vue-router";
import { api } from 'boot/axios'
import { date, useQuasar } from 'quasar'
import { useI18n } from 'vue-i18n'
import { useAdvancedSearchData } from "stores/advancedSearchData";
import { default as TagSelector } from "components/TagSelector.vue";

const { t } = useI18n();
const $q = useQuasar();
const route = useRoute();
const advancedSearchData = useAdvancedSearchData();

const searching = ref(false);
const searchLaunched = ref(false);
const expandedFilter = ref(true);
const expandedResults = ref(false);
const dateFilterOptions = ref([
  { label: 'Any date', value: 0 },
  { label: 'today', value: 1 },
  { label: 'yesterday', value: 2 },
  { label: 'last 7 days', value: 3 },
  { label: 'last 15 days', value: 4 },
  { label: 'last 31 days', value: 5 },
  { label: 'this 365 days', value: 6 },
  { label: 'fixed date', value: 7 },
  { label: 'from date', value: 8 },
  { label: 'to date', value: 9 },
  { label: 'between dates', value: 10 }
]);

advancedSearchData.filter.dateFilterType = dateFilterOptions.value[0];
advancedSearchData.filter.tags = route.params.tag !== undefined ? [route.params.tag] : [];

const sortOrderIcon = computed(() => advancedSearchData.isSortAscending ? "keyboard_double_arrow_up" : "keyboard_double_arrow_down");

watch(
  () => advancedSearchData.filter.dateFilterType,
  (dateFilterType) => {
    advancedSearchData.recalcDates(dateFilterType)
  }
)

function onSubmitForm(resetPager) {
  if (resetPager) {
    advancedSearchData.pager.currentPage = 1;
  }
  searching.value = true;
  if (date.isValid(advancedSearchData.filter.fromDate)) {
    advancedSearchData.filter.fromTimestamp = date.formatDate(date.adjustDate(date.extractDate(advancedSearchData.filter.fromDate, 'YYYY/MM/DD'), { hour: 0, minute: 0, second: 0, millisecond: 0 }), 'X');
  } else {
    advancedSearchData.filter.fromTimestamp = null;
  }
  if (date.isValid(advancedSearchData.filter.toDate)) {
    advancedSearchData.filter.toTimestamp = date.formatDate(date.adjustDate(date.extractDate(advancedSearchData.filter.toDate, 'YYYY/MM/DD'), { hour: 23, minute: 59, second: 59, millisecond: 999 }), 'X');
  } else {
    advancedSearchData.filter.toTimestamp = null;
  }
  api.document.search(advancedSearchData.pager.currentPage, advancedSearchData.pager.resultsPage, advancedSearchData.filter, advancedSearchData.sortField, advancedSearchData.sortOrder)
    .then((success) => {
      advancedSearchData.pager = success.data.results.pagination;
      advancedSearchData.results = success.data.results.documents.map((document) => {
        document.createdOn = date.formatDate(document.createdOnTimestamp * 1000, 'YYYY-MM-DD HH:mm:ss');
        return (document);
      });
      searching.value = false;
      searchLaunched.value = true;
      if (advancedSearchData.hasResults) {
        expandedResults.value = true;
      }
    })
    .catch((error) => {
      switch (error.response.status) {
        case 400:
          $q.notify({
            type: "negative",
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
            type: "negative",
            message: t("API Error: fatal error"),
            caption: t("API Error: fatal error details", { status: error.response.status, statusText: error.response.statusText })
          });
          break;
      }
      searching.value = false;
    });
}

function onPaginationChanged(pageIndex) {
  advancedSearchData.setCurrentPage(pageIndex);
  onSubmitForm(false);
}


function onToggleSort(field) {
  advancedSearchData.toggleSort(field);
  onSubmitForm(false);
}

if (advancedSearchData.filter.tags.length > 0) {
  onSubmitForm(true);
}

</script>
