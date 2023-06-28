<template>
  <q-page class="bg-grey-2">
    <div class="q-pa-md">
      <h3>Advanced search</h3>
      <div class="q-gutter-y-md">
        <q-expansion-item expand-separator icon="filter_alt" label="Search conditions" :model-value="expandedFilter">
          <q-card>
            <form @submit.prevent.stop="onSubmitForm(true)" autocorrect="off" autocapitalize="off" autocomplete="off"
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
                <div class="row">
                  <div class="col">
                    <q-select class="q-mb-md" dense outlined v-model="filter.dateFilterType" :options="dateFilterOptions"
                      label="Document date" :disable="searching" @change="console.log($e)" />
                  </div>
                  <div class="col">
                    <q-input dense outlined mask="date" v-model="filter.fromDate" label="From date"
                      :disable="searching || filter.dateFilterType.value < 7">
                      <template v-slot:append>
                        <q-icon name="event" class="cursor-pointer">
                          <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                            <q-date v-model="filter.fromDate" today-btn
                              :disable="searching || filter.dateFilterType.value < 7">
                              <div class="row items-center justify-end">
                                <q-btn v-close-popup label="Close" color="primary" flat />
                              </div>
                            </q-date>
                          </q-popup-proxy>
                        </q-icon>
                      </template>
                    </q-input>
                  </div>
                  <div class="col">
                    <q-input dense outlined mask="date" v-model="filter.toDate" label="To date"
                      :disable="searching || filter.dateFilterType.value < 7">
                      <template v-slot:append>
                        <q-icon name="event" class="cursor-pointer">
                          <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                            <q-date v-model="filter.toDate" today-btn
                              :disable="searching || filter.dateFilterType.value < 7">
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
                <TagSelector v-model="filter.tags" :disabled="searching" dense>
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
        </q-expansion-item>
        <q-expansion-item expand-separator icon="folder_open" :label="'Search results' + ' (' + pager.totalResults + ')'"
          :model-value="expandedResults">
          <div class="q-pa-lg flex flex-center" v-if="pager.totalPages > 1">
            <q-pagination v-model="pager.currentPage" color="dark" :max="pager.totalPages" :max-pages="5"
              boundary-numbers direction-links boundary-links @update:model-value="onPaginationChanged"
              :disable="searching" />
          </div>
          <q-markup-table>
            <thead>
              <tr>
                <th style="width: 60%;" class="text-left cursor-pointer" @click="onToggleSort('title')">Title<q-icon
                    :name="sortOrderIcon" v-if="sort.field == 'title'" size="sm"></q-icon></th>
                <th style="width: 20%;" class="text-left cursor-pointer" @click="onToggleSort('createdOnTimestamp')">
                  Created
                  on<q-icon :name="sortOrderIcon" v-if="sort.field == 'createdOnTimestamp'" size="sm"></q-icon></th>
                <th style="width: 10%;" class="text-right cursor-pointer" @click="onToggleSort('fileCount')">
                  Files<q-icon :name="sortOrderIcon" v-if="sort.field == 'fileCount'" size="sm"></q-icon></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for=" document  in  results.documents " :key="document.id">
                <td class="text-left"><router-link :to="{ name: 'document', params: { id: document.id } }">{{
                  document.title }}</router-link>
                </td>
                <td class="text-left">{{ document.createdOn }}</td>
                <td class="text-right">{{ document.fileCount }}</td>
              </tr>
            </tbody>
          </q-markup-table>
          <div class="q-pa-lg flex flex-center" v-if="pager.totalPages > 1">
            <q-pagination v-model="pager.currentPage" color="dark" :max="pager.totalPages" :max-pages="5"
              boundary-numbers direction-links boundary-links @update:model-value="onPaginationChanged"
              :disable="searching" />
          </div>
        </q-expansion-item>
      </div>
    </div>
  </q-page>
</template>

<script setup>

import { ref, computed, watch, registerRuntimeCompiler } from "vue";

import { date } from 'quasar'
import { api } from 'boot/axios'
import { useI18n } from 'vue-i18n'

import { useRoute } from "vue-router";

import { default as TagSelector } from "components/TagSelector.vue";

import { useQuasar } from 'quasar'

const { t } = useI18n();

const $q = useQuasar();

const route = useRoute();

const sort = ref({
  field: 'createdOnTimestamp',
  order: 'DESC'
});
const searching = ref(false);

const expandedFilter = ref(true);
const expandedResults = ref(true);

const tab = ref('conditions');

const dateFilter = ref(0);

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

const filter = ref({
  title: null,
  description: null,
  dateFilterType: dateFilterOptions.value[0],
  dateRange: null,
  fromDate: null,
  toDate: null,
  fromTimestamp: null,
  toTimestamp: null,
  tags: route.params.tag !== undefined ? [route.params.tag] : []
});

const pager = ref({
  currentPage: 1,
  resultsPage: 32,
  totalResults: 0,
  totalPages: 0
});

const results = ref({
  pagination: {},
  documents: []
});

const hasResults = computed(() => results.value.documents && results.value.documents.length > 0);

const sortOrderIcon = computed(() => sort.value.order == "ASC" ? "keyboard_double_arrow_up" : "keyboard_double_arrow_down");

watch(
  () => filter.value.dateFilterType,
  (dateFilterType) => {
    switch (dateFilterType.value) {
      case 0:
        filter.value.fromDate = null;
        filter.value.fromTimestamp = null;
        filter.value.toDate = null;
        filter.value.toTimestamp = null;
        break;
      // TODAY
      case 1:
        filter.value.fromDate = date.formatDate(Date.now(), 'YYYY/MM/DD');
        filter.value.toDate = date.formatDate(Date.now(), 'YYYY/MM/DD');
        break;
      // YESTERDAY
      case 2:
        filter.value.fromDate = date.formatDate(date.addToDate(Date.now(), { days: -1 }), 'YYYY/MM/DD');
        filter.value.toDate = date.formatDate(date.addToDate(Date.now(), { days: -1 }), 'YYYY/MM/DD');
        break;
      // LAST 7 DAYS
      case 3:
        filter.value.fromDate = date.formatDate(date.addToDate(Date.now(), { days: -7 }), 'YYYY/MM/DD');
        filter.value.fromDate = date.formatDate(date.addToDate(Date.now(), { days: -1 }), 'YYYY/MM/DD');
        break;
      // LAST 15 DAYS
      case 4:
        filter.value.fromDate = date.formatDate(date.addToDate(Date.now(), { days: -15 }), 'YYYY/MM/DD');
        filter.value.fromDate = date.formatDate(date.addToDate(Date.now(), { days: -1 }), 'YYYY/MM/DD');
        break;
      // LAST 31 DAYS
      case 5:
        filter.value.fromDate = date.formatDate(date.addToDate(Date.now(), { days: -31 }), 'YYYY/MM/DD');
        filter.value.fromDate = date.formatDate(date.addToDate(Date.now(), { days: -1 }), 'YYYY/MM/DD');
        break;
      // LAST 365 DAYS
      case 6:
        filter.value.fromDate = date.formatDate(date.addToDate(Date.now(), { days: -365 }), 'YYYY/MM/DD');
        filter.value.fromDate = date.formatDate(date.addToDate(Date.now(), { days: -1 }), 'YYYY/MM/DD');
        break;
      // FIXED DATE
      case 7:
        filter.value.fromDate = date.formatDate(Date.now(), 'YYYY/MM/DD');
        filter.value.toDate = date.formatDate(Date.now(), 'YYYY/MM/DD');
        break;
      // FROM DATE
      case 8:
        filter.value.fromDate = date.formatDate(Date.now(), 'YYYY/MM/DD');
        filter.value.toDate = date.formatDate(Date.now(), 'YYYY/MM/DD');
        break;
      // TO DATE
      case 9:
        filter.value.fromDate = date.formatDate(Date.now(), 'YYYY/MM/DD');
        filter.value.toDate = date.formatDate(Date.now(), 'YYYY/MM/DD');
        break;
      // BETWEEN DATES
      case 10:
        filter.value.fromDate = date.formatDate(Date.now(), 'YYYY/MM/DD');
        filter.value.toDate = date.formatDate(Date.now(), 'YYYY/MM/DD');
        break;
    }
  }
)

function onSubmitForm(resetPager) {
  if (resetPager) {
    pager.value.currentPage = 1;
  }
  searching.value = true;
  if (date.isValid(filter.value.fromDate)) {
    filter.value.fromTimestamp = date.formatDate(date.adjustDate(date.extractDate(filter.value.fromDate, 'YYYY/MM/DD'), { hour: 0, minute: 0, second: 0, millisecond: 0 }), 'X');
  } else {
    filter.value.fromTimestamp = null;
  }
  if (date.isValid(filter.value.toDate)) {
    filter.value.toTimestamp = date.formatDate(date.adjustDate(date.extractDate(filter.value.toDate, 'YYYY/MM/DD'), { hour: 23, minute: 59, second: 59, millisecond: 999 }), 'X');
  } else {
    filter.value.toTimestamp = null;
  }
  api.document.search(pager.value.currentPage, pager.value.resultsPage, filter.value, sort.value.field, sort.value.order)
    .then((success) => {
      pager.value = success.data.results.pagination;
      results.value = success.data.results;
      results.value.documents.map((document) => {
        document.createdOn = date.formatDate(document.createdOnTimestamp * 1000, 'YYYY-MM-DD HH:mm:ss');
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

function onPaginationChanged(pageIndex) {
  pager.value.currentPage = pageIndex;
  onSubmitForm(false);
}


function onToggleSort(field) {
  if (sort.value.field == field) {
    sort.value.order = sort.value.order == "ASC" ? "DESC" : "ASC";

  } else {
    sort.value.field = field;
    sort.value.order = "ASC";
  }
  onSubmitForm(false);
}

if (filter.value.tags.length > 0) {
  onSubmitForm(true);
}

</script>
