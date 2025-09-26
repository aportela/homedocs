<template>
  <q-page>
    <CustomExpansionWidget title="Advanced search" icon="filter_alt" :loading="state.loading"
      :error="state.loadingError" :expanded="isFilterWidgetExpanded">
      <template v-slot:header-extra>
        <q-chip square size="sm" color="grey-7" text-color="white">{{
          t("Total search conditions count", {
            count:
              totalSearchConditions
          }) }}
        </q-chip>
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
              <DateFieldCustomInput :options="dateFilterTypeOptions" :label="t('Document creation date')"
                :disable="state.loading || hasCreationDateRouteParamsFilter" v-model="filters.dates.creationDate"
                :auto-open-pop-ups="!hasCreationDateRouteParamsFilter">
              </DateFieldCustomInput>
              <DateFieldCustomInput :options="dateFilterTypeOptions" :label="t('Document last update')"
                :disable="state.loading || hasLastUpdateRouteParamsFilter" v-model="filters.dates.lastUpdate"
                :auto-open-pop-ups="!hasLastUpdateRouteParamsFilter">
              </DateFieldCustomInput>
              <DateFieldCustomInput :options="dateFilterTypeOptions" :label="t('Document updated on')"
                :disable="state.loading || hasUpdatedOnRouteParamsFilter" v-model="filters.dates.updatedOn"
                :auto-open-pop-ups="!hasUpdatedOnRouteParamsFilter">
              </DateFieldCustomInput>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <InteractiveTagsFieldCustomSelect v-model="filters.tags" label="Document tags" :disabled="state.loading"
                dense :start-mode-editable="true" :deny-change-editable-mode="true" clearable
                :placeholder="t('Type text condition')">
                <template v-slot:prepend>
                  <q-icon name="search" />
                </template>
              </InteractiveTagsFieldCustomSelect>
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
            <q-btn class="action-secondary" size="md" :label="$t('Reset filters')" no-caps icon="undo"
              :disable="state.loading || totalSearchConditions < 1" type="reset" v-if="useStoreFilter"></q-btn>
          </q-btn-group>
        </form>
        <CustomErrorBanner v-if="showErrorBanner" :text="state.errorMessage || 'Error loading data'"
          :apiError="state.apiError" class="q-ma-md">
        </CustomErrorBanner>
        <CustomBanner v-else-if="showNoResultsBanner" warning text="No results found with current filter"
          class="q-ma-md">
        </CustomBanner>
      </template>
    </CustomExpansionWidget>
    <CustomExpansionWidget v-show="hasResults" title="Results" icon="folder_open" :staticIcon="true"
      :loading="state.loading" :error="state.loadingError" expanded class="q-mt-sm" ref="resultsWidgetRef">
      <template v-slot:header-extra>
        <q-chip square size="sm" color="grey-7" text-color="white">{{ t("Total search results count",
          {
            count:
              pager.totalResults
          }) }}</q-chip>
      </template>
      <template v-slot:content>
        <div class="q-ma-md flex flex-center" v-if="pager.totalPages > 1">
          <q-pagination v-model="pager.currentPage" color="dark" :max="pager.totalPages" :max-pages="5" boundary-numbers
            direction-links boundary-links @update:model-value="onPaginationChanged" :disable="state.loading"
            class="theme-default-q-pagination" />
        </div>
        <q-markup-table class="q-ma-md">
          <thead>
            <tr>
              <th class="lt-xl">
                <SortByFieldCustomButtonDropdown square dense no-caps :options="sortFields" :current="sort"
                  @change="(opt) => onToggleSort(opt.field, opt.order)" flat class="action-primary fit full-height">
                </SortByFieldCustomButtonDropdown>
              </th>
              <th v-for="(column, index) in columns" :key="index" :style2="{ width: column.width }"
                :class="['text-left', column.defaultClass, { 'cursor-not-allowed': state.loading, 'cursor-pointer': !state.loading, 'action-primary': sort.field === column.field }]"
                @click="onToggleSort(column.field)">
                <q-icon :name="sort.field === column.field ? sortOrderIcon : 'sort'" size="sm"></q-icon>
                {{ t(column.title) }}
                <q-tooltip v-if="isDesktop">{{ t('Toggle sort by this column', { field: t(column.title) })
                }}</q-tooltip>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="document in results" :key="document.id">
              <td class="lt-xl">
                <q-item clickable :disable="state.loading" :to="{ name: 'document', params: { id: document.id } }"
                  class="bg-transparent">
                  <q-item-section>
                    <q-item-label>
                      {{ t("Title") }}: {{ document.title }}
                    </q-item-label>
                    <q-item-label caption>
                      <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 self-center">Creado: {{
                          document.createdOn }} - Actualizado: {{
                            document.lastUpdate }}</div>
                        <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 self-center text-right"><q-chip
                            size="md" square class="theme-default-q-chip shadow-1 q-chip-8em"
                            :clickable="document.fileCount > 0 && !state.loading"
                            @click.stop.prevent="onShowDocumentFiles(document.id, document.title)">
                            <q-avatar class="theme-default-q-avatar"
                              :class="{ 'text-white bg-blue': document.fileCount > 0 }">{{
                                document.fileCount }}</q-avatar>
                            {{ t('Total files', { count: document.fileCount }) }}
                          </q-chip>
                          <q-chip size="md" square class="theme-default-q-chip shadow-1 q-chip-8em"
                            :clickable="document.noteCount > 0 && !state.loading"
                            @click.stop.prevent="onShowDocumentNotes(document.id, document.title)">
                            <q-avatar class="theme-default-q-avatar"
                              :class="{ 'text-white bg-blue': document.noteCount > 0 }">{{
                                document.noteCount }}</q-avatar>
                            {{ t('Total notes', { count: document.noteCount }) }}
                          </q-chip>
                        </div>
                      </div>
                    </q-item-label>
                  </q-item-section>
                </q-item>
              </td>
              <td class="td-document-link gt-lg">
                <q-btn align="left" no-caps flat :to="{ name: 'document', params: { id: document.id } }"
                  class="fit text-decoration-hover" :label="document.title"></q-btn>
              </td>
              <td class="gt-lg">{{ document.createdOn }}</td>
              <td class="gt-lg">{{ document.lastUpdate }}</td>
              <td class="gt-lg">
                <ViewDocumentDetailsButton size="md" square class="min-width-8em" :count="document.fileCount"
                  :label="'Total files'" :tool-tip="'View document attachments'" :disable="state.loading"
                  @click.stop.prevent="onShowDocumentFiles(document.id, document.title)" q-chip-class="'q-chip-8em'">
                </ViewDocumentDetailsButton>
              </td>
              <td class="gt-lg">
                <ViewDocumentDetailsButton size="md" square class="min-width-8em" :count="document.noteCount"
                  :label="'Total notes'" :tool-tip="'View document notes'" :disable="state.loading"
                  @click.stop.prevent="onShowDocumentNotes(document.id, document.title)" q-chip-class="'q-chip-8em'">
                </ViewDocumentDetailsButton>
              </td>
            </tr>
          </tbody>
        </q-markup-table>
        <div class="q-ma-md flex flex-center" v-if="pager.totalPages > 1">
          <q-pagination v-model="pager.currentPage" color="dark" :max="pager.totalPages" :max-pages="5" boundary-numbers
            direction-links boundary-links @update:model-value="onPaginationChanged" :disable="state.loading"
            class="theme-default-q-pagination" />
        </div>
      </template>
    </CustomExpansionWidget>
  </q-page>
</template>

<script setup>

import { ref, reactive, computed, onMounted, onBeforeUnmount, nextTick } from "vue";
import { useRoute } from "vue-router";
import { useI18n } from "vue-i18n";
import { date, useQuasar } from "quasar";

import { bus } from "src/boot/bus";
import { api } from "src/boot/axios";
import { useAdvancedSearchData } from "src/stores/advancedSearchData"
import { useDateFilter } from "src/composables/dateFilter"
import { useBusDialog } from "src/composables/busDialog";

import { default as InteractiveTagsFieldCustomSelect } from "src/components/Forms/Fields/InteractiveTagsFieldCustomSelect.vue"
import { default as CustomExpansionWidget } from "src/components/Widgets/CustomExpansionWidget.vue";
import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue";
import { default as DateFieldCustomInput } from "src/components/Forms/Fields/DateFieldCustomInput.vue";
import { default as SortByFieldCustomButtonDropdown } from "src/components/Forms/Fields/SortByFieldCustomButtonDropdown.vue";
import { default as ViewDocumentDetailsButton } from "src/components/Buttons/ViewDocumentDetailsButton.vue";

const { t } = useI18n();

const route = useRoute();

const $q = useQuasar();
const isDesktop = computed(() => $q.platform.is.desktop);

const { onShowDocumentFiles, onShowDocumentNotes } = useBusDialog();

const columns = [
  { field: 'title', title: 'Title', defaultClass: "gt-lg" },
  { field: 'createdOnTimestamp', title: 'Creation date', defaultClass: "gt-lg" },
  { field: 'lastUpdateTimestamp', title: 'Last update', defaultClass: "gt-lg" },
  { field: 'fileCount', title: 'Files', defaultClass: "gt-lg" },
  { field: 'noteCount', title: 'Notes', defaultClass: "gt-lg" }
];

const sortFields = columns.map(column => ({
  field: column.field,
  label: column.title
}));

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

const showErrorBanner = computed(() => !state.loading && state.loadingError);
const showNoResultsBanner = computed(() => !state.loading && state.searchLaunched && !hasResults.value);

if (route.params.tag !== undefined) {
  filters.tags.push(route.params.tag);
}

const hasCreationDateRouteParamsFilter = computed(() => route.params.fixedCreationDate !== undefined);
const hasLastUpdateRouteParamsFilter = computed(() => route.params.fixedLastUpdate !== undefined);
const hasUpdatedOnRouteParamsFilter = computed(() => route.params.fixedUpdatedOn !== undefined);

const sortOrderIcon = computed(() => sort.order == "ASC" ? "keyboard_double_arrow_up" : "keyboard_double_arrow_down");

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

const onToggleSort = (field, order) => {
  if (!state.loading) {
    if (sort.field == field) {
      if (!order) {
        sort.order = sort.order == "ASC" ? "DESC" : "ASC";
      } else {
        sort.order = order;
      }
    } else {
      sort.field = field;
      sort.order = !order ? "ASC" : order;
    }
    onSubmitForm(false);
  }
}

const onSubmitForm = (resetPager) => {
  if (resetPager) {
    pager.currentPage = 1;
  }
  state.loading = true;
  state.loadingError = false;
  state.errorMessage = null;
  state.apiError = null;
  if (useStoreFilter.value) {
    store.filters = filters;
    store.sort = sort;
  }
  api.document.search(pager.currentPage, pager.resultsPage, filters, sort.field, sort.order)
    .then((successResponse) => {
      if (successResponse.data.results) {
        results.length = 0;
        pager.currentPage = successResponse.data.results.pagination.currentPage;
        pager.resultsPage = successResponse.data.results.pagination.resultsPage;
        pager.totalResults = successResponse.data.results.pagination.totalResults;
        pager.totalPages = successResponse.data.results.pagination.totalPages;
        results.push(...successResponse.data.results.documents.map((document) => {
          document.createdOn = date.formatDate(document.createdOnTimestamp, 'YYYY-MM-DD HH:mm:ss');
          document.lastUpdate = date.formatDate(document.lastUpdateTimestamp, 'YYYY-MM-DD HH:mm:ss');
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
      state.apiError = errorResponse.customAPIErrorDetails;
      switch (errorResponse.response.status) {
        case 400:
          state.loadingError = true;
          state.errorMessage = "API Error: invalid/missing param";
          break;
        case 401:
          state.apiError = errorResponse.customAPIErrorDetails;
          state.errorMessage = "Auth session expired, requesting new...";
          bus.emit("reAuthRequired", { emitter: "AdvancedSearchPage.onSubmitForm" });
          break;
        default:
          state.loadingError = true;
          state.errorMessage = "API Error: fatal error";
          break;
      }
      state.loading = false;
      state.searchLaunched = true;
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
  bus.on("reAuthSucess", (msg) => {
    if (msg.to?.includes("AdvancedSearchPage.onSubmitForm")) {
      onSubmitForm(false);
    }
  });
});

onBeforeUnmount(() => {
  bus.off("reAuthSucess");
});

</script>

<style scoped>
th:first-child {
  height: 100%;
  padding: 0px !important;
}

td:first-child {
  padding: 0px !important;
}

.q-chip-8em {
  width: 8em;
}
</style>