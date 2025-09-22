<template>
  <q-page>
    <CustomExpansionWidget title="Advanced search" icon="filter_alt" :loading="state.loading"
      :error="state.loadingError" :expanded="isFilterWidgetExpanded">
      <template v-slot:header-extra>
        <q-chip square size="sm" color="primary" text-color="white">{{ t("Total search conditions count", {
          count:
            totalSearchConditions
        }) }}</q-chip>
      </template>
      <template v-slot:content>
        <form @submit.prevent.stop="onSubmitForm(true)" @reset.prevent.stop="onResetForm" autocorrect="off"
          autocapitalize="off" autocomplete="off" spellcheck="false" class="q-pa-md">
          <div class="row q-col-gutter-sm">
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
              <q-input class="q-mb-md" dense outlined v-model.trim="filters.text.title" type="text" name="title"
                clearable :label="t('Document title')" :disable="state.loading" :autofocus="true"
                :placeholder="t('Type text condition')">
                <template v-slot:prepend>
                  <q-icon name="search" />
                </template>
              </q-input>
              <q-input class="q-mb-md" dense outlined v-model.trim="filters.text.description" type="text"
                name="description" clearable :label="t('Document description')" :disable="state.loading"
                :placeholder="t('Type text condition')">
                <template v-slot:prepend>
                  <q-icon name="search" />
                </template>
              </q-input>
              <q-input class="q-mb-md" dense outlined v-model.trim="filters.text.notes" type="text" name="notesBody"
                clearable :label="t('Document notes')" :disable="state.loading" :placeholder="t('Type text condition')">
                <template v-slot:prepend>
                  <q-icon name="search" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
              <CustomInputDateFilter :options="dateFilterTypeOptions" :label="t('Document creation date')"
                :disable="state.loading || hasCreationDateRouteParamsFilter" v-model="filters.dates.creationDate"
                :auto-open-pop-ups="!hasCreationDateRouteParamsFilter">
              </CustomInputDateFilter>
              <CustomInputDateFilter :options="dateFilterTypeOptions" :label="t('Document last update')"
                :disable="state.loading || hasLastUpdateRouteParamsFilter" v-model="filters.dates.lastUpdate"
                :auto-open-pop-ups="!hasLastUpdateRouteParamsFilter">
              </CustomInputDateFilter>
              <CustomInputDateFilter :options="dateFilterTypeOptions" :label="t('Document updated on')"
                :disable="state.loading || hasUpdatedOnRouteParamsFilter" v-model="filters.dates.updatedOn"
                :auto-open-pop-ups="!hasUpdatedOnRouteParamsFilter">
              </CustomInputDateFilter>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <TagSelector v-model="filters.tags" label="Document tags" :disabled="state.loading" dense
                :start-mode-editable="true" :deny-change-editable-mode="true" clearable
                :placeholder="t('Type text condition')">
                <template v-slot:prepend>
                  <q-icon name="search" />
                </template>
              </TagSelector>
            </div>
          </div>
          <q-btn-group spread>
            <q-btn color="primary" size="md" :label="$t('Search')" no-caps icon="search" :disable="state.loading"
              :loading="state.loading" type="submit">
              <template v-slot:loading>
                <q-spinner-hourglass class="on-left" />
                {{ t("Searching...") }}
              </template>
            </q-btn>
            <q-btn color="secondary" size="md" :label="$t('Reset filters')" no-caps icon="undo" :disable="state.loading"
              type="reset" v-if="useStoreFilter"></q-btn>
          </q-btn-group>
        </form>
      </template>
    </CustomExpansionWidget>
    <CustomExpansionWidget v-if="state.searchLaunched" title="Results" icon="folder_open" :loading="state.loading"
      :error="state.loadingError" expanded class="q-mt-sm" ref="resultsWidgetRef">
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
                  <q-icon :name="sortOrderIcon" v-if="sort.field == 'title'" size="sm"></q-icon>
                </th>
                <th style="width: 20%;" class="text-left cursor-pointer" @click="onToggleSort('createdOnTimestamp')">
                  {{ t("Creation date") }}
                  <q-icon :name="sortOrderIcon" v-if="sort.field == 'createdOnTimestamp'" size="sm"></q-icon>
                </th>
                <th style="width: 20%;" class="text-left cursor-pointer" @click="onToggleSort('lastUpdateTimestamp')">
                  {{ t("Last update") }}
                  <q-icon :name="sortOrderIcon" v-if="sort.field == 'lastUpdateTimestamp'" size="sm"></q-icon>
                </th>
                <th style="width: 10%;" class="text-right cursor-pointer" @click="onToggleSort('fileCount')">
                  {{ t("Files") }}<q-icon :name="sortOrderIcon" v-if="sort.field == 'fileCount'" size="sm"></q-icon>
                </th>
                <th style="width: 10%;" class="text-right cursor-pointer" @click="onToggleSort('noteCount')">
                  {{ t("Notes") }}<q-icon :name="sortOrderIcon" v-if="sort.field == 'noteCount'" size="sm"></q-icon>
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
                <td class="text-right">
                  <q-chip size="md" square class="theme-default-q-chip q-chip-8em">
                    <q-avatar class="theme-default-q-avatar">{{ document.fileCount }}</q-avatar>
                    <span v-if="document.fileCount > 0" class="cursor-pointer"
                      @click="onShowDocumentFiles(document.id)"> {{ t('Total files', { count: document.fileCount })
                      }}</span>
                    <span v-else> {{ t('Total files', { count: document.fileCount }) }}</span>
                  </q-chip>
                </td>
                <td class="text-right"> {{ document.noteCount }}</td>
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
    <FilePreviewModal v-if="showPreviewFileDialog" :files="documentFiles" @close="showPreviewFileDialog = false">
    </FilePreviewModal>
  </q-page>
</template>

<script setup>

import { ref, reactive, computed, onMounted, nextTick } from "vue";
import { useRoute } from "vue-router";
import { date, format } from "quasar";
import { useI18n } from "vue-i18n";
import { api } from "boot/axios";
import { useAdvancedSearchData } from "stores/advancedSearchData"
import { default as TagSelector } from "components/TagSelector.vue";

import { default as CustomExpansionWidget } from "components/CustomExpansionWidget.vue";
import { default as CustomErrorBanner } from "components/CustomErrorBanner.vue";
import { default as CustomBanner } from "components/CustomBanner.vue";

import { useDateFilter } from "src/composables/dateFilter"
import { default as CustomInputDateFilter } from "components/CustomInputDateFilter.vue";
import { default as FilePreviewModal } from "components/FilePreviewModal.vue";

const { t } = useI18n();

const route = useRoute();

const state = reactive({
  loading: false,
  loadingError: false,
  errorMessage: null,
  apiError: null,
  searchLaunched: false
});

const isFilterWidgetExpanded = ref(route.meta.conditionsFilterExpanded);

const resultsWidgetRef = ref(null);

const { getDateFilterInstance, dateFilterTypeOptions } = useDateFilter();

const useStoreFilter = computed(() => route.name == 'advancedSearch');

const store = useAdvancedSearchData();

const filters = reactive({
  text: {
    title: null,
    description: null,
    notes: null
  },
  tags: [],
  dates: {
    creationDate: getDateFilterInstance(),
    lastUpdate: getDateFilterInstance(),
    updatedOn: getDateFilterInstance()
  }
});

const pager = reactive({
  currentPage: 1,
  resultsPage: 32,
  totalResults: 0,
  totalPages: 0,
});

const sort = reactive({
  field: "lastUpdateTimestamp",
  order: "DESC",
});

const results = reactive([]);
const hasResults = computed(() => results.length > 0);

if (route.params.tag !== undefined) {
  filters.tags.push(route.params.tag);
}

const hasCreationDateRouteParamsFilter = computed(() => route.params.fixedCreationDate !== undefined);
const hasLastUpdateRouteParamsFilter = computed(() => route.params.fixedLastUpdate !== undefined);
const hasUpdatedOnRouteParamsFilter = computed(() => route.params.fixedUpdatedOn !== undefined);

const sortOrderIcon = computed(() => sort.order == "ASC" ? "keyboard_double_arrow_up" : "keyboard_double_arrow_down");

const showPreviewFileDialog = ref(false);
const documentFiles = reactive([]);

const totalSearchConditions = computed(() => {
  let total = 0;
  if (filters.text.title) {
    total++;
  }
  if (filters.text.description) {
    total++;
  }
  if (filters.text.notes) {
    total++;
  }
  if (filters.tags && filters.tags.length > 0) {
    total += filters.tags.length;
  }
  if (filters.dates.creationDate.state.hasValue) {
    total++;
  }
  if (filters.dates.lastUpdate.state.hasValue) {
    total++;
  }
  if (filters.dates.updatedOn.state.hasValue) {
    total++;
  }
  return (total);
});

const onPaginationChanged = (pageIndex) => {
  pager.currentPage = pageIndex;
  onSubmitForm(false);
}

const onToggleSort = (field) => {
  if (sort.field == field) {
    sort.order = sort.order == "ASC" ? "DESC" : "ASC";
  } else {
    sort.field = field;
    sort.order = "ASC";
  }
  onSubmitForm(false);
}

const onSubmitForm = (resetPager) => {
  if (resetPager) {
    pager.currentPage = 1;
  }
  state.loading = true;
  state.loadingError = false;
  state.errorMessage = null;
  state.apiError = null;
  results.length = 0;

  if (useStoreFilter.value) {
    store.filters = filters;
    store.sort = sort;
  }

  api.document.search(pager.currentPage, pager.resultsPage, filters, sort.field, sort.order)
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
        resultsWidgetRef.value?.expand();
        state.loading = false;
      } else {
        state.loading = false;
      }
    })
    .catch((errorResponse) => {
      console.log(errorResponse);
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

const onResetForm = () => {
  filters.text.title = null;
  filters.text.description = null;
  filters.text.notes = null;
  filters.tags.length = 0;
  filters.dates = {
    creationDate: getDateFilterInstance(),
    lastUpdate: getDateFilterInstance(),
    updatedOn: getDateFilterInstance()
  }
  sort.field = "lastUpdateTimestamp";
  sort.order = "DESC";
  state.searchLaunched = false;
  results.length = 0;
};

const onShowDocumentFiles = (documentId) => {
  documentFiles.length = 0;
  state.loading = true;
  api.document
    .get(documentId)
    .then((successResponse) => {
      documentFiles.push(...successResponse.data.data.files.map((file) => {
        file.isNew = false;
        file.uploadedOn = date.formatDate(file.uploadedOnTimestamp * 1000, 'YYYY-MM-DD HH:mm:ss');
        file.humanSize = format.humanStorageSize(file.size);
        file.url = "api2/file/" + file.id;
        return (file)
      }));
      showPreviewFileDialog.value = documentFiles.length > 0;
      state.loading = false;
    })
    .catch((errorResponse) => {
      state.loading = false;
      switch (errorResponse.response.status) {
        case 401:
          // TODO
          break;
        default:
          // TODO
          break;
      }
    });

}

onMounted(() => {
  if (hasCreationDateRouteParamsFilter.value) {
    filters.dates.creationDate.skipClearOnRecalc.fixed = true; // UGLY HACK to skip clearing/reseting values on filterType watchers
    filters.dates.creationDate.filterType = dateFilterTypeOptions.value[7];
    filters.dates.creationDate.formattedDate.fixed = route.params.fixedCreationDate.replaceAll("-", "/");
  } else if (hasLastUpdateRouteParamsFilter.value) {
    filters.dates.lastUpdate.skipClearOnRecalc.fixed = true; // UGLY HACK to skip clearing/reseting values on filterType watchers
    filters.dates.lastUpdate.filterType = dateFilterTypeOptions.value[7];
    filters.dates.lastUpdate.formattedDate.fixed = route.params.fixedLastUpdate.replaceAll("-", "/");
  } else if (hasUpdatedOnRouteParamsFilter.value) {
    filters.dates.updatedOn.skipClearOnRecalc.fixed = true; // UGLY HACK to skip clearing/reseting values on filterType watchers
    filters.dates.updatedOn.filterType = dateFilterTypeOptions.value[7];
    filters.dates.updatedOn.formattedDate.fixed = route.params.fixedUpdatedOn.replaceAll("-", "/");
  } else if (useStoreFilter.value) {
    filters.text.title = store.filters?.text.title || null;
    filters.text.description = store.filters?.text.description || null;
    filters.text.notes = store.filters?.text.notes || null;
    filters.tags = store.filters?.tags || [];
    if (store.filters?.dates) {
      // creation date
      filters.dates.creationDate.skipClearOnRecalc.fixed = true;
      filters.dates.creationDate.formattedDate.fixed = store.filters.dates.creationDate.formattedDate.fixed;
      filters.dates.creationDate.skipClearOnRecalc.from = true;
      filters.dates.creationDate.formattedDate.from = store.filters.dates.creationDate.formattedDate.from;
      filters.dates.creationDate.skipClearOnRecalc.to = true;
      filters.dates.creationDate.formattedDate.to = store.filters.dates.creationDate.formattedDate.to;
      filters.dates.creationDate.filterType = store.filters.dates.creationDate.filterType;
      // last update
      filters.dates.lastUpdate.skipClearOnRecalc.fixed = true;
      filters.dates.lastUpdate.formattedDate.fixed = store.filters.dates.lastUpdate.formattedDate.fixed;
      filters.dates.lastUpdate.skipClearOnRecalc.from = true;
      filters.dates.lastUpdate.formattedDate.from = store.filters.dates.lastUpdate.formattedDate.from;
      filters.dates.lastUpdate.skipClearOnRecalc.to = true;
      filters.dates.lastUpdate.formattedDate.to = store.filters.dates.lastUpdate.formattedDate.to;
      filters.dates.lastUpdate.filterType = store.filters.dates.lastUpdate.filterType;
      // updated on
      filters.dates.updatedOn.skipClearOnRecalc.fixed = true;
      filters.dates.updatedOn.formattedDate.fixed = store.filters.dates.updatedOn.formattedDate.fixed;
      filters.dates.updatedOn.skipClearOnRecalc.from = true;
      filters.dates.updatedOn.formattedDate.from = store.filters.dates.updatedOn.formattedDate.from;
      filters.dates.updatedOn.skipClearOnRecalc.to = true;
      filters.dates.updatedOn.formattedDate.to = store.filters.dates.updatedOn.formattedDate.to;
      filters.dates.updatedOn.filterType = store.filters.dates.updatedOn.filterType;
    }
    if (store.sort?.field) {
      sort.field = store.sort.field;
    }
    if (store.sort?.order) {
      sort.order = store.sort.order;
    }
  }
  if (route.meta.autoLaunchSearch) {
    nextTick(() => {
      onSubmitForm(true);
    });
  }
});

</script>

<style scoped>
.q-chip-8em {
  width: 8em;
}
</style>