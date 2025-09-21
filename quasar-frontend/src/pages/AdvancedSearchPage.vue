<template>
  <q-page>
    <CustomExpansionWidget title="Advanced search"
      :caption="state.loading ? 'Loading...' : 'Use custom filters to refine your search'" icon="filter_alt"
      :loading="state.loading" :error="state.loadingError" :expanded="expandedFilter">
      <template v-slot:header-extra>
        <q-chip square size="sm" color="primary" text-color="white">{{ t("Total search conditions count", {
          count:
            totalSearchConditions
        }) }}</q-chip>
      </template>
      <template v-slot:content>
        <form @submit.prevent.stop=" onSubmitForm(true)" autocorrect="off" autocapitalize="off" autocomplete="off"
          spellcheck="false" class="q-ma-xs q-mt-sm">
          <div class="row q-col-gutter-sm">
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
              <q-input class="q-mb-md" dense outlined v-model="advancedSearchData.filter.title" type="text" name="title"
                clearable :label="t('Document title')" :disable="state.loading" :autofocus="true">
                <template v-slot:prepend>
                  <q-icon name="search" />
                </template>
              </q-input>
              <q-input class="q-mb-md" dense outlined v-model="advancedSearchData.filter.description" type="text"
                name="description" clearable :label="t('Document description')" :disable="state.loading">
                <template v-slot:prepend>
                  <q-icon name="search" />
                </template>
              </q-input>
              <q-input class="q-mb-md" dense outlined v-model="advancedSearchData.filter.notesBody" type="text"
                name="notesBody" clearable :label="t('Document notes')" :disable="state.loading">
                <template v-slot:prepend>
                  <q-icon name="search" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">

              <CustomInputDateFilter :options="dateFilterTypeOptions" :label="t('Document creation date')"
                :disable="state.loading || disableCreationDateFilterByRouteParams" v-model="dateFilters.creationDate">
              </CustomInputDateFilter>
              <CustomInputDateFilter :options="dateFilterTypeOptions" :label="t('Document last update')"
                :disable="state.loading || disableLastUpdateFilterByRouteParams" v-model="dateFilters.lastUpdate">
              </CustomInputDateFilter>
              <CustomInputDateFilter :options="dateFilterTypeOptions" :label="t('Document updated on')"
                :disable="state.loading || disableUpdatedOnFilterByRouteParams" v-model="dateFilters.updatedOn">
              </CustomInputDateFilter>
              <!--
              <div class="row q-col-gutter-xs">
                <div class="col">
                  <q-select class="q-mb-md" dense options-dense outlined clearable
                    v-model="advancedSearchData.filter.creationDateFilterType" :options="dateFilterOptions"
                    :label="t('Document creation date')"
                    :disable="state.loading || disableCreationDateFilterByRouteParams" />
                </div>
                <div class="col" v-if="advancedSearchData.hasFromCreationDateFilter">
                  <q-input dense outlined mask="date" v-model="advancedSearchData.filter.fromCreationDate"
                    :label="t('From date')"
                    :disable="state.loading || advancedSearchData.denyChangeCreationDateFilters">
                    <template v-slot:append>
                      <q-icon name="event" class="cursor-pointer">
                        <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                          <q-date v-model="advancedSearchData.filter.fromCreationDate" today-btn
                            :disable="state.loading || advancedSearchData.denyChangeCreationDateFilters">
                            <div class="row items-center justify-end">
                              <q-btn v-close-popup label="Close" color="primary" flat />
                            </div>
                          </q-date>
                        </q-popup-proxy>
                      </q-icon>
                    </template>
                  </q-input>
                </div>
                <div class="col" v-if="advancedSearchData.hasToCreationDateFilter">
                  <q-input dense outlined mask="date" v-model="advancedSearchData.filter.toCreationDate"
                    :label="t('To date')" :disable="state.loading || advancedSearchData.denyChangeCreationDateFilters">
                    <template v-slot:append>
                      <q-icon name="event" class="cursor-pointer">
                        <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                          <q-date v-model="advancedSearchData.filter.toCreationDate" today-btn
                            :disable="state.loading || advancedSearchData.denyChangeCreationDateFilters">
                            <div class="row items-center justify-end">
                              <q-btn v-close-popup label="Close" color="primary" flat />
                            </div>
                          </q-date>
                        </q-popup-proxy>
                      </q-icon>
                    </template>
                  </q-input>
                </div>
                <div class="col" v-if="advancedSearchData.hasFixedCreationDateFilter">
                  <q-input dense outlined mask="date" v-model="advancedSearchData.filter.fixedCreationDate"
                    :label="t('Fixed date')"
                    :disable="state.loading || advancedSearchData.denyChangeCreationDateFilters || disableCreationDateFilterByRouteParams">
                    <template v-slot:append>
                      <q-icon name="event" class="cursor-pointer">
                        <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                          <q-date v-model="advancedSearchData.filter.fixedCreationDate" today-btn
                            :disable="state.loading || advancedSearchData.denyChangeCreationDateFilters">
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
              <div class="row q-col-gutter-xs">
                <div class="col">
                  <q-select class="q-mb-md" dense options-dense outlined clearable
                    v-model="advancedSearchData.filter.lastUpdateFilterType" :options="dateFilterOptions"
                    :label="t('Document last update')"
                    :disable="state.loading || disableLastUpdateFilterByRouteParams" />
                </div>
                <div class="col" v-if="advancedSearchData.hasFromLastUpdateFilter">
                  <q-input dense outlined mask="date" v-model="advancedSearchData.filter.fromLastUpdate"
                    :label="t('From date')" :disable="state.loading || advancedSearchData.denyChangeLastUpdateFilters">
                    <template v-slot:append>
                      <q-icon name="event" class="cursor-pointer">
                        <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                          <q-date v-model="advancedSearchData.filter.fromLastUpdate" today-btn
                            :disable="state.loading || advancedSearchData.denyChangeLastUpdateFilters">
                            <div class="row items-center justify-end">
                              <q-btn v-close-popup label="Close" color="primary" flat />
                            </div>
                          </q-date>
                        </q-popup-proxy>
                      </q-icon>
                    </template>
                  </q-input>
                </div>
                <div class="col" v-if="advancedSearchData.hasToLastUpdateFilter">
                  <q-input dense outlined mask="date" v-model="advancedSearchData.filter.toLastUpdate"
                    :label="t('To date')" :disable="state.loading || advancedSearchData.denyChangeLastUpdateFilters">
                    <template v-slot:append>
                      <q-icon name="event" class="cursor-pointer">
                        <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                          <q-date v-model="advancedSearchData.filter.toLastUpdate" today-btn
                            :disable="state.loading || advancedSearchData.denyChangeLastUpdateFilters">
                            <div class="row items-center justify-end">
                              <q-btn v-close-popup label="Close" color="primary" flat />
                            </div>
                          </q-date>
                        </q-popup-proxy>
                      </q-icon>
                    </template>
                  </q-input>
                </div>
                <div class="col" v-if="advancedSearchData.hasFixedLastUpdateFilter">
                  <q-input dense outlined mask="date" v-model="advancedSearchData.filter.fixedLastUpdate"
                    :label="t('Fixed date')"
                    :disable="state.loading || advancedSearchData.denyChangeLastUpdateFilters || disableLastUpdateFilterByRouteParams">
                    <template v-slot:append>
                      <q-icon name="event" class="cursor-pointer">
                        <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                          <q-date v-model="advancedSearchData.filter.fixedLastUpdate" today-btn
                            :disable="state.loading || advancedSearchData.denyChangeLastUpdateFilters">
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
              <div class="row q-col-gutter-xs">
                <div class="col">
                  <q-select class="q-mb-md" dense options-dense outlined clearable
                    v-model="advancedSearchData.filter.updatedOnDateFilterType" :options="dateFilterOptions"
                    :label="t('Document updated on')" :disable="state.loading || disableUpdatedOnFilterByRouteParams" />
                </div>
                <div class="col" v-if="advancedSearchData.hasFromUpdatedOnFilter">
                  <q-input dense outlined mask="date" v-model="advancedSearchData.filter.fromUpdatedOn"
                    :label="t('From date')" :disable="state.loading || advancedSearchData.denyChangeUpdatedOnFilters">
                    <template v-slot:append>
                      <q-icon name="event" class="cursor-pointer">
                        <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                          <q-date v-model="advancedSearchData.filter.fromUpdatedOn" today-btn
                            :disable="state.loading || advancedSearchData.denyChangeUpdatedOnFilters">
                            <div class="row items-center justify-end">
                              <q-btn v-close-popup label="Close" color="primary" flat />
                            </div>
                          </q-date>
                        </q-popup-proxy>
                      </q-icon>
                    </template>
                  </q-input>
                </div>
                <div class="col" v-if="advancedSearchData.hasToUpdatedOnFilter">
                  <q-input dense outlined mask="date" v-model="advancedSearchData.filter.toUpdatedOn"
                    :label="t('To date')" :disable="state.loading || advancedSearchData.denyChangeUpdatedOnFilters">
                    <template v-slot:append>
                      <q-icon name="event" class="cursor-pointer">
                        <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                          <q-date v-model="advancedSearchData.filter.toUpdatedOn" today-btn
                            :disable="state.loading || advancedSearchData.denyChangeUpdatedOnFilters">
                            <div class="row items-center justify-end">
                              <q-btn v-close-popup label="Close" color="primary" flat />
                            </div>
                          </q-date>
                        </q-popup-proxy>
                      </q-icon>
                    </template>
                  </q-input>
                </div>
                <div class="col" v-if="advancedSearchData.hasFixedUpdatedOnFilter">
                  <q-input dense outlined mask="date" v-model="advancedSearchData.filter.fixedUpdatedOn"
                    :label="t('Fixed date')"
                    :disable="state.loading || advancedSearchData.denyChangeUpdatedOnFilters || disableUpdatedOnFilterByRouteParams">
                    <template v-slot:append>
                      <q-icon name="event" class="cursor-pointer">
                        <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                          <q-date v-model="advancedSearchData.filter.fixedUpdatedOn" today-btn
                            :disable="state.loading || advancedSearchData.denyChangeUpdatedOnFilters">
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
              -->
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <TagSelector v-model="advancedSearchData.filter.tags"
                :disabled="state.loading || advancedSearchData.denyChangeCreationDateFilters" dense
                :start-mode-editable="true" :deny-change-editable-mode="true" clearable>
              </TagSelector>
            </div>
          </div>
          <q-btn color="primary" size="md" :label="$t('Search')" no-caps class="full-width" icon="search"
            :disable="state.loading" :loading="state.loading" type="submit">
            <template v-slot:loading>
              <q-spinner-hourglass class="on-left" />
              {{ t("Searching...") }}
            </template>
          </q-btn>
        </form>
      </template>
    </CustomExpansionWidget>
    <CustomExpansionWidget v-if="state.searchLaunched" title="Results" :caption="state.loading ? 'Loading...' : 'TODO'"
      icon="folder_open" :loading="state.loading" :error="state.loadingError" :expanded="expandedResults"
      class="q-mt-sm">
      <template v-slot:header-extra>
        <q-chip square size="sm" color="primary" text-color="white">{{ t("Total search results count", {
          count:
            pager.totalResults
        }) }}</q-chip>
      </template>
      <template v-slot:content>
        <CustomErrorBanner v-if="state.loadingError" text="Error loading data" :apiError="state.apiError">
        </CustomErrorBanner>
        <div v-else-if="hasResults">
          <div class="q-pa-lg flex flex-center" v-if="pager.totalPages > 1">
            <q-pagination v-model="pager.currentPage" color="dark" :max="pager.totalPages" :max-pages="5"
              boundary-numbers direction-links boundary-links @update:model-value="onPaginationChanged"
              :disable="state.loading" />
          </div>
          <q-markup-table>
            <thead>
              <tr>
                <th style="width: 40%;" class="text-left cursor-pointer" @click="onToggleSort('title')">{{
                  t("Title") }}
                  <q-icon :name="sortOrderIcon" v-if="advancedSearchData.isSortedByField('title')" size="sm"></q-icon>
                </th>
                <th style="width: 20%;" class="text-left cursor-pointer" @click="onToggleSort('createdOnTimestamp')">
                  {{ t("Creation date") }}
                  <q-icon :name="sortOrderIcon" v-if="advancedSearchData.isSortedByField('createdOnTimestamp')"
                    size="sm"></q-icon>
                </th>
                <th style="width: 20%;" class="text-left cursor-pointer" @click="onToggleSort('lastUpdateTimestamp')">
                  {{ t("Last update") }}
                  <q-icon :name="sortOrderIcon" v-if="advancedSearchData.isSortedByField('lastUpdateTimestamp')"
                    size="sm"></q-icon>
                </th>
                <th style="width: 10%;" class="text-right cursor-pointer" @click="onToggleSort('fileCount')">
                  {{ t("Files") }}<q-icon :name="sortOrderIcon" v-if="advancedSearchData.isSortedByField('fileCount')"
                    size="sm"></q-icon>
                </th>
                <th style="width: 10%;" class="text-right cursor-pointer" @click="onToggleSort('noteCount')">
                  {{ t("Notes") }}<q-icon :name="sortOrderIcon" v-if="advancedSearchData.isSortedByField('noteCount')"
                    size="sm"></q-icon>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="document in results" :key="document.id">
                <td class="text-left"><router-link :to="{ name: 'document', params: { id: document.id } }">{{
                  document.title }}</router-link>
                </td>
                <td class="text-left">{{ document.createdOn }}</td>
                <td class="text-left">{{ document.lastUpdate }}</td>
                <td class="text-right">{{ document.fileCount }}</td>
                <td class="text-right">{{ document.noteCount }}</td>
              </tr>
            </tbody>
          </q-markup-table>
          <div class="q-pa-lg flex flex-center" v-if="pager.totalPages > 1">
            <q-pagination v-model="pager.currentPage" color="dark" :max="pager.totalPages" :max-pages="5"
              boundary-numbers direction-links boundary-links @update:model-value="onPaginationChanged"
              :disable="state.loading" />
          </div>
        </div>
        <CustomBanner v-else-if="!state.loading" warning text="No results found with current filter"></CustomBanner>
      </template>
    </CustomExpansionWidget>
  </q-page>
</template>

<script setup>

import { ref, reactive, computed, watch, onMounted } from "vue";
import { useRoute } from "vue-router";
import { date } from "quasar";
import { useI18n } from "vue-i18n";
import { api } from "boot/axios";
import { useAdvancedSearchData } from "stores/advancedSearchData";
import { default as TagSelector } from "components/TagSelector.vue";

import { default as CustomExpansionWidget } from "components/CustomExpansionWidget.vue";
import { default as CustomErrorBanner } from "components/CustomErrorBanner.vue";
import { default as CustomBanner } from "components/CustomBanner.vue";

import { useDateFilter } from "src/composables/dateFilter"
import { default as CustomInputDateFilter } from "components/CustomInputDateFilter.vue";

const { t } = useI18n();

const route = useRoute();

const state = reactive({
  loading: false,
  loadingError: false,
  errorMessage: null,
  apiError: null,
  searchLaunched: false
});

const expandedFilter = ref(route.meta.conditionsFilterExpanded);
const expandedResults = ref(false);

const { getDateFilterInstance, dateFilterTypeOptions } = useDateFilter();

const dateFilters = {
  creationDate: getDateFilterInstance(),
  lastUpdate: getDateFilterInstance(),
  updatedOn: getDateFilterInstance(),
};

const pager = reactive({
  currentPage: 1,
  resultsPage: 32,
  totalResults: 0,
  totalPages: 0,
});

const results = reactive([]);
const hasResults = computed(() => results.length > 0);


// TODO: replace with composable ? or allow reset/restore pinia store
const advancedSearchData = useAdvancedSearchData();

advancedSearchData.filter.tags = route.params.tag !== undefined ? [route.params.tag] : [];

const disableCreationDateFilterByRouteParams = computed(() => route.params.fixedCreationDate !== undefined);
const disableLastUpdateFilterByRouteParams = computed(() => route.params.fixedLastUpdate !== undefined);
const disableUpdatedOnFilterByRouteParams = computed(() => route.params.fixedUpdatedOn !== undefined);


const sortOrderIcon = computed(() => advancedSearchData.sortOrder == "ASC" ? "keyboard_double_arrow_up" : "keyboard_double_arrow_down");

const totalSearchConditions = computed(() => {
  let total = 0;
  if (advancedSearchData.filter.title) {
    total++;
  }
  if (advancedSearchData.filter.description) {
    total++;
  }
  if (advancedSearchData.filter.notesBody) {
    total++;
  }
  if (advancedSearchData.filter.tags && advancedSearchData.filter.tags.length > 0) {
    total += advancedSearchData.filter.tags.length;
  }
  if (dateFilters.creationDate.hasValue) {
    total++;
  }
  if (dateFilters.lastUpdate.hasValue) {
    total++;
  }
  if (dateFilters.updatedOn.hasValue) {
    total++;
  }
  return (total);
});

function onPaginationChanged(pageIndex) {
  pager.currentPage = pageIndex;
  onSubmitForm(false);
}

function onToggleSort(field) {
  advancedSearchData.toggleSort(field);
  onSubmitForm(false);
}

function onSubmitForm(resetPager) {
  if (resetPager) {
    pager.currentPage = 1;
  }
  state.loading = true;
  results.length = 0;
  if (dateFilters.creationDate.hasValue) {
    advancedSearchData.filter.fromCreationTimestamp = dateFilters.creationDate.timestamps.from;
    advancedSearchData.filter.toCreationTimestamp = dateFilters.creationDate.timestamps.to;
  } else {
    advancedSearchData.filter.fromCreationTimestamp = null;
    advancedSearchData.filter.toCreationTimestamp = null;
  }
  if (dateFilters.lastUpdate.hasValue) {
    advancedSearchData.filter.fromLastUpdateTimestamp = dateFilters.lastUpdate.timestamps.from;
    advancedSearchData.filter.toLastUpdateTimestamp = dateFilters.lastUpdate.timestamps.to;
  } else {
    advancedSearchData.filter.fromLastUpdateTimestamp = null;
    advancedSearchData.filter.toLastUpdateTimestamp = null;
  }
  if (dateFilters.updatedOn.hasValue) {
    advancedSearchData.filter.fromUpdatedOnTimestamp = dateFilters.updatedOn.timestamps.from;
    advancedSearchData.filter.toUpdatedOnTimestamp = dateFilters.updatedOn.timestamps.to;
  } else {
    advancedSearchData.filter.fromUpdatedOnTimestamp = null;
    advancedSearchData.filter.toUpdatedOnTimestamp = null;
  }
  api.document.search(pager.currentPage, pager.resultsPage, advancedSearchData.filter, advancedSearchData.sortField, advancedSearchData.sortOrder)
    .then((successResponse) => {
      if (successResponse.data.results) {
        pager.currentPage = successResponse.data.results.pagination.currentPage;
        pager.resultsPage = successResponse.data.results.pagination.resultsPage;
        pager.totalResults = successResponse.data.results.pagination.totalResults;
        pager.totalPages = successResponse.data.results.pagination.totalPages;
        results.push(...successResponse.data.results.documents.map((document) => {
          // convert PHP timestamps (seconds) to JS (milliseconds)
          document.createdOn = date.formatDate(document.createdOnTimestamp * 1000, 'YYYY-MM-DD HH:mm:ss');
          document.lastUpdate = date.formatDate(document.lastUpdateTimestamp * 1000, 'YYYY-MM-DD HH:mm:ss');
          return (document);
        }));
        state.searchLaunched = true;
        if (hasResults.value) {
          expandedResults.value = true;
          // TODO: not expanding if collapsed manually
        }
        state.loading = false;
      } else {
        state.loading = false;
      }
    })
    .catch((errorResponse) => {
      state.apiError = errorResponse.customAPIErrorDetails;
      switch (errorResponse.response.status) {
        case 400:
          state.loadingError = true;
          state.errorMessage = "API Error: invalid/missing param";
          break;
        case 401:
          // TODO: dialog modal signin ?
          this.$router.push({
            name: "signIn",
          });
          break;
        default:
          state.loadingError = true;
          state.errorMessage = "API Error: fatal error";
          break;
      }
      state.loading = false;
    });
}

onMounted(() => {

  if (disableCreationDateFilterByRouteParams.value) {
    dateFilters.creationDate.skipClearOnRecalc.fixed = true; // UGLY HACK to skip clearing/reseting values on filterType watchers
    dateFilters.creationDate.filterType = dateFilterTypeOptions.value[7];
    dateFilters.creationDate.formattedDate.fixed = route.params.fixedCreationDate.replaceAll("-", "/");
  }
  else if (disableLastUpdateFilterByRouteParams.value) {
    dateFilters.lastUpdate.skipClearOnRecalc.fixed = true; // UGLY HACK to skip clearing/reseting values on filterType watchers
    dateFilters.lastUpdate.filterType = dateFilterTypeOptions.value[7];
    dateFilters.lastUpdate.formattedDate.fixed = route.params.fixedLastUpdate.replaceAll("-", "/");
  }
  else if (disableUpdatedOnFilterByRouteParams.value) {
    dateFilters.updatedOn.skipClearOnRecalc.fixed = true; // UGLY HACK to skip clearing/reseting values on filterType watchers
    dateFilters.updatedOn.filterType = dateFilterTypeOptions.value[7];
    dateFilters.updatedOn.formattedDate.fixed = route.params.fixedUpdatedOn.replaceAll("-", "/");
  }

  if (route.meta.autoLaunchSearch) {
    onSubmitForm(true);
  }
});

</script>