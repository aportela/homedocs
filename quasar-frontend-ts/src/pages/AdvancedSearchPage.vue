<template>
  <q-page>
    <CustomExpansionWidget title="Advanced search" icon="filter_alt" :loading="state.ajaxRunning"
      :error="state.ajaxErrors" :expanded="isFilterWidgetExpanded">
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
                clearable :label="t('Document title')" :disable="state.ajaxRunning" :autofocus="true"
                :placeholder="t('Type text condition')">
                <template v-slot:prepend>
                  <q-icon name="article" />
                </template>
              </q-input>
              <q-input class="q-mb-md" dense outlined v-model.trim="filters.text.description" type="text"
                name="description" clearable :label="t('Document description')" :disable="state.ajaxRunning"
                :placeholder="t('Type text condition')">
                <template v-slot:prepend>
                  <q-icon name="article" />
                </template>
              </q-input>
              <q-input class="q-mb-md" dense outlined v-model.trim="filters.text.notesBody" type="text" name="notesBody"
                clearable :label="t('Document notes')" :disable="state.ajaxRunning"
                :placeholder="t('Type text condition')">
                <template v-slot:prepend>
                  <q-icon name="speaker_notes" />
                </template>
              </q-input>
              <q-input class="q-mb-md" dense outlined v-model.trim="filters.text.attachmentsFilename" type="text"
                name="notesBody" clearable :label="t('Document attachment filenames')" :disable="state.ajaxRunning"
                :placeholder="t('Type text condition')">
                <template v-slot:prepend>
                  <q-icon name="attach_file" />
                </template>
              </q-input>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 col-xl-6">
              <InteractiveTagsFieldCustomSelect v-model="filters.tags" label="Document tags"
                :disabled="state.ajaxRunning" dense :start-mode-editable="true" :deny-change-editable-mode="true"
                clearable :placeholder="t('Type text condition')">
              </InteractiveTagsFieldCustomSelect>
              <DateFieldCustomInput :options="dateFilterTypeOptions" :label="t('Document creation date')"
                :disable="state.ajaxRunning || hasCreationDateRouteParamsFilter" v-model="filters.dates.createdAt"
                :auto-open-pop-ups="!hasCreationDateRouteParamsFilter">
              </DateFieldCustomInput>
              <DateFieldCustomInput :options="dateFilterTypeOptions" :label="t('Document last update')"
                :disable="state.ajaxRunning || hasLastUpdateRouteParamsFilter" v-model="filters.dates.lastUpdateAt"
                :auto-open-pop-ups="!hasLastUpdateRouteParamsFilter">
              </DateFieldCustomInput>
              <DateFieldCustomInput :options="dateFilterTypeOptions" :label="t('Document updated on')"
                :disable="state.ajaxRunning || hasUpdatedOnRouteParamsFilter" v-model="filters.dates.updatedAt"
                :auto-open-pop-ups="!hasUpdatedOnRouteParamsFilter">
              </DateFieldCustomInput>
            </div>
          </div>
          <q-btn-group spread>
            <q-btn color="primary" size="md" :label="$t('Search')" no-caps icon="search" :disable="state.ajaxRunning"
              :loading="state.ajaxRunning" type="submit">
              <template v-slot:loading>
                <q-spinner-hourglass class="on-left" />
                {{ t("Searching...") }}
              </template>
            </q-btn>
            <q-btn class="action-secondary" size="md" :label="$t('Reset filters')" no-caps icon="undo"
              :disable="state.ajaxRunning || totalSearchConditions < 1" type="reset" v-if="useStoreFilter"></q-btn>
          </q-btn-group>
        </form>
        <CustomErrorBanner v-if="showErrorBanner" :text="state.ajaxErrorMessage || 'Error loading data'"
          :api-error="state.ajaxAPIErrorDetails" class="q-ma-md">
        </CustomErrorBanner>
        <CustomBanner v-else-if="showNoResultsBanner" warning text="No results found with current filter"
          class="q-ma-md">
        </CustomBanner>
      </template>
    </CustomExpansionWidget>
    <CustomExpansionWidget v-show="hasResults" title="Results" icon="folder_open" :staticIcon="true"
      :loading="state.ajaxRunning" :error="state.ajaxErrors" expanded class="q-mt-sm" ref="resultsWidgetRef">
      <template v-slot:header-extra>
        <q-chip square size="sm" color="grey-7" text-color="white">{{ t("Total search results count",
          {
            count:
              pager.totalResults
          }) }}</q-chip>
      </template>
      <template v-slot:content>
        <div class="q-ma-md flex flex-center" v-if="pager.totalPages > 1">
          <q-pagination v-model="pager.currentPageIndex" color="dark" :max="pager.totalPages" :max-pages="5"
            boundary-numbers direction-links boundary-links @update:model-value="onPaginationChanged"
            :disable="state.ajaxRunning" class="theme-default-q-pagination" />
        </div>
        <q-markup-table class="q-ma-md">
          <thead>
            <tr>
              <th class="lt-xl">
                <SortByFieldCustomButtonDropdown square dense no-caps :options="sortFields" :current="sort"
                  @change="(opt) => onToggleSort(opt.field, opt.order)" flat class="action-primary fit full-height" />
              </th>
              <th v-for="(column, index) in columns" :key="index" :style2="{ width: column.width }"
                :class="['text-left', column.defaultClass, { 'cursor-not-allowed': state.ajaxRunning, 'cursor-pointer': !state.ajaxRunning, 'action-primary': sort.field === column.field }]"
                @click="onToggleSort(column.field)">
                <q-icon :name="sort.field === column.field ? sortOrderIcon : 'sort'" size="sm"></q-icon>
                {{ t(column.title) }}
                <DesktopToolTip>{{ t('Toggle sort by this column', { field: t(column.title) })
                }}</DesktopToolTip>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="document in results" :key="document.id">
              <td class="lt-xl">
                <q-list dense>
                  <q-item clickable :disable="state.ajaxRunning" :to="{ name: 'document', params: { id: document.id } }"
                    class="bg-transparent">
                    <q-item-section>
                      <q-item-label>
                        {{ t("Title") }}: {{ document.title }}
                      </q-item-label>
                      <q-item-label caption>{{ t("Creation date") }}: {{ document.createdAt.dateTime }} ({{
                        document.createdAt.timeAgo }})</q-item-label>
                      <q-item-label caption v-if="document.updatedAt?.dateTime">{{ t("Last update") }}: {{
                        document.updatedAt.dateTime
                      }} ({{ document.updatedAt.timeAgo }})</q-item-label>
                    </q-item-section>
                    <q-item-section side top>
                      <ViewDocumentDetailsButton size="md" square class="min-width-9em"
                        :count="document.attachmentCount" :label="'Total attachments count'"
                        :tool-tip="'View document attachments'" :disable="state.ajaxRunning"
                        @click.stop.prevent="onShowDocumentFiles(document.id, document.title)">
                      </ViewDocumentDetailsButton>
                      <ViewDocumentDetailsButton size="md" square class="min-width-9em" :count="document.noteCount"
                        :label="'Total notes'" :tool-tip="'View document notes'" :disable="state.ajaxRunning"
                        @click.stop.prevent="onShowDocumentNotes(document.id, document.title)">
                      </ViewDocumentDetailsButton>
                    </q-item-section>
                  </q-item>
                </q-list>
              </td>
              <td class="td-document-link gt-lg">
                <q-btn align="left" no-caps flat :to="{ name: 'document', params: { id: document.id } }"
                  class="fit text-decoration-hover" :label="document.title"></q-btn>
              </td>
              <td class="gt-lg">{{ document.createdAt.dateTime }}</td>
              <td class="gt-lg">{{ document.updatedAt?.dateTime }}</td>
              <td class="gt-lg">
                <ViewDocumentDetailsButton size="md" square class="min-width-8em" :count="document.attachmentCount"
                  :label="'Total attachments count'" :tool-tip="'View document attachments'"
                  :disable="state.ajaxRunning" @click.stop.prevent="onShowDocumentFiles(document.id, document.title)"
                  q-chip-class="'q-chip-8em'">
                </ViewDocumentDetailsButton>
              </td>
              <td class="gt-lg">
                <ViewDocumentDetailsButton size="md" square class="min-width-8em" :count="document.noteCount"
                  :label="'Total notes'" :tool-tip="'View document notes'" :disable="state.ajaxRunning"
                  @click.stop.prevent="onShowDocumentNotes(document.id, document.title)" q-chip-class="'q-chip-8em'">
                </ViewDocumentDetailsButton>
              </td>
            </tr>
          </tbody>
        </q-markup-table>
        <div class="q-ma-md flex flex-center" v-if="pager.totalPages > 1">
          <q-pagination v-model="pager.currentPageIndex" color="dark" :max="pager.totalPages" :max-pages="5"
            boundary-numbers direction-links boundary-links @update:model-value="onPaginationChanged"
            :disable="state.ajaxRunning" class="theme-default-q-pagination" />
        </div>
      </template>
    </CustomExpansionWidget>
  </q-page>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, onBeforeUnmount, nextTick } from "vue";
import { useRoute } from "vue-router";
import { useI18n } from "vue-i18n";

import { useAdvancedSearchData } from "src/stores/advancedSearchData"
import { bus, onShowDocumentFiles, onShowDocumentNotes } from "src/composables/useBus";
import { api } from "src/composables/api";
import { useDateFilter } from "src/composables/useDateFilter"

import { type AjaxState as AjaxStateInterface, defaultAjaxState } from "src/types/ajax-state";
import { DateTimeClass } from "src/types/date-time";
import { type SearchDocumentResponse as SearchDocumentResponseInterface, type SearchDocumentResponseItem as SearchDocumentResponseItemInterface } from "src/types/api-responses";
import { SearchDocumentItemClass } from "src/types/search-document-item";
import { PagerClass } from "src/types/pager";
import { type OrderType } from "src/types/order-type";
import { SearchOnTextEntitiesFilterClass, SearchDatesFilterClass, SearchFilterClass } from "src/types/search-filter";
import { type Sort as SortInterface, SortClass } from "src/types/sort";


import { default as DesktopToolTip } from "src/components/DesktopToolTip.vue";
import { default as InteractiveTagsFieldCustomSelect } from "src/components/Forms/Fields/InteractiveTagsFieldCustomSelect.vue"
import { default as CustomExpansionWidget } from "src/components/Widgets/CustomExpansionWidget.vue";
import { default as CustomErrorBanner } from "src/components/Banners/CustomErrorBanner.vue";
import { default as CustomBanner } from "src/components/Banners/CustomBanner.vue";
import { default as DateFieldCustomInput } from "src/components/Forms/Fields/DateFieldCustomInput.vue";
import { default as SortByFieldCustomButtonDropdown } from "src/components/Forms/Fields/SortByFieldCustomButtonDropdown.vue";
import { default as ViewDocumentDetailsButton } from "src/components/Buttons/ViewDocumentDetailsButton.vue";

const { t } = useI18n();

const route = useRoute();

const columns = [
  { field: 'title', title: 'Title', defaultClass: "gt-lg" },
  { field: 'createdOnTimestamp', title: 'Creation date', defaultClass: "gt-lg" },
  { field: 'lastUpdateTimestamp', title: 'Last update', defaultClass: "gt-lg" },
  { field: 'attachmentCount', title: 'Attachments', defaultClass: "gt-lg" },
  { field: 'noteCount', title: 'Notes', defaultClass: "gt-lg" }
];

// options for selecting order via dropdown button on small screens
// (on normal screens, order is toggled clicking in column header)
const sortFields: SortInterface[] = columns.map(column => ({
  field: column.field,
  label: column.title,
  order: "ASC",
}));

const searchLaunched = ref<boolean>(false);

const state: AjaxStateInterface = reactive({ ...defaultAjaxState });

const isFilterWidgetExpanded = ref<boolean>(route.meta.conditionsFilterExpanded === true);

const resultsWidgetRef = ref(null);

const { getDateFilterInstance, dateFilterTypeOptions } = useDateFilter();


const useStoreFilter = computed(() => route.name == 'advancedSearch');

const store = useAdvancedSearchData();

const filters = reactive<SearchFilterClass>(
  new SearchFilterClass(
    new SearchOnTextEntitiesFilterClass(null, null, null, null),
    [],
    new SearchDatesFilterClass(null, null, null)
  )
);

const pager = reactive<PagerClass>(new PagerClass(1, 32, 0, 0));

const sort = reactive<SortClass>(new SortClass("lastUpdateTimestamp", "Last update", "DESC"));

const results = reactive<Array<SearchDocumentItemClass>>([]);

const hasResults = computed(() => results.length > 0);

const showErrorBanner = computed(() => !state.ajaxRunning && state.ajaxErrors);
const showNoResultsBanner = computed(() => !state.ajaxRunning && searchLaunched.value && !hasResults.value);

if (route.params.tag !== undefined) {
  //store.setTagsFilter([route.params.tag]);
  filters.tags.push(route.params.tag);
}

const hasCreationDateRouteParamsFilter = computed(() => route.params.fixedCreationDate !== undefined);
const hasLastUpdateRouteParamsFilter = computed(() => route.params.fixedLastUpdate !== undefined);
const hasUpdatedOnRouteParamsFilter = computed(() => route.params.fixedUpdatedOn !== undefined);

const sortOrderIcon = computed(() => sort.order == "ASC" ? "keyboard_double_arrow_up" : "keyboard_double_arrow_down");

const totalSearchConditions = computed(() => {
  let total = 0;
  if (store.filter.text.title) {
    total++;
  }
  if (store.filter.text.description) {
    total++;
  }
  if (store.filter.text.notesBody) {
    total++;
  }
  if (store.filter.text.attachmentsFilename) {
    total++;
  }
  if (store.filter.tags.length > 0) {
    total += store.filter.tags.length;
  }
  if (store.filter.dates.createdAt.state.hasValue) {
    total++;
  }
  if (store.filter.dates.lastUpdateAt.state.hasValue) {
    total++;
  }
  if (store.filter.dates.updatedAt.state.hasValue) {
    total++;
  }
  return (total);
});

const onPaginationChanged = (pageIndex: number) => {
  pager.currentPageIndex = pageIndex;
  onSubmitForm(false);
}

const onToggleSort = (field: string, order?: OrderType) => {
  if (!state.ajaxRunning) {
    sort.toggle(field, order);
    sort.refreshLabel(sortFields);
    //sort.label = sortFields.find((item) => item.field == field)?.label || "";
    onSubmitForm(false);
  }
}

const onSubmitForm = (resetPager: boolean) => {
  if (resetPager) {
    pager.currentPageIndex = 1;
  }
  Object.assign(state, defaultAjaxState);
  state.ajaxRunning = true;
  if (useStoreFilter.value) {
    store.setFilter(filters);
    store.setSort(sort);
  }
  api.document.search(pager, filters, sort, false)
    .then((successResponse: SearchDocumentResponseInterface) => {
      if (successResponse.data.results) {
        results.length = 0;
        pager.currentPageIndex = successResponse.data.results.pagination.currentPage;
        pager.resultsPage = successResponse.data.results.pagination.resultsPage;
        pager.totalResults = successResponse.data.results.pagination.totalResults;
        pager.totalPages = successResponse.data.results.pagination.totalPages;
        results.push(...successResponse.data.results.documents.map((document: SearchDocumentResponseItemInterface) =>
          new SearchDocumentItemClass(
            t,
            document.id,
            new DateTimeClass(t, document.createdAtTimestamp),
            new DateTimeClass(t, document.updatedAtTimestamp),
            document.title,
            document.description,
            document.tags,
            document.attachmentCount,
            document.noteCount,
            document.matchedFragments,
            "",
          )
        ));
        searchLaunched.value = true;
        resultsWidgetRef.value?.expand();
      }
    })
    .catch((errorResponse) => {
      state.ajaxErrors = true;
      if (errorResponse.isAPIError) {
        state.ajaxAPIErrorDetails = errorResponse.customAPIErrorDetails;
        switch (errorResponse.response.status) {
          case 400:
            state.ajaxErrorMessage = "API Error: invalid/missing param";
            break;
          case 401:
            state.ajaxErrorMessage = "Auth session expired, requesting new...";
            bus.emit("reAuthRequired", { emitter: "AdvancedSearchPage.onSubmitForm" });
            break;
          default:
            state.ajaxErrorMessage = "API Error: fatal error";
            break;
        }
      } else {
        state.ajaxErrorMessage = `Uncaught exception: ${errorResponse}`;
        console.error(errorResponse);
      }
      searchLaunched.value = true;
    }).finally(() => {
      state.ajaxRunning = false;
    });
}

const onResetForm = () => {
  filters.text.title = null;
  filters.text.description = null;
  filters.text.notesBody = null;
  filters.text.attachmentsFilename = null;
  filters.tags.length = 0;
  filters.dates = {
    createdAt: getDateFilterInstance(),
    lastUpdateAt: getDateFilterInstance(),
    updatedAt: getDateFilterInstance()
  }
  sort.field = "lastUpdateTimestamp";
  sort.order = "DESC";
  searchLaunched.value = false;
  results.length = 0;
};

onMounted(() => {
  if (hasCreationDateRouteParamsFilter.value) {
    filters.dates.createdAt.skipClearOnRecalc.fixed = true; // UGLY HACK to skip clearing/reseting values on filterType watchers
    filters.dates.createdAt.filterType = dateFilterTypeOptions.value[7];
    filters.dates.createdAt.formattedDate.fixed = route.params.fixedCreationDate ? route.params.fixedCreationDate.replaceAll("-", "/") : null;
  } else if (hasLastUpdateRouteParamsFilter.value) {
    filters.dates.lastUpdateAt.skipClearOnRecalc.fixed = true; // UGLY HACK to skip clearing/reseting values on filterType watchers
    filters.dates.lastUpdateAt.filterType = dateFilterTypeOptions.value[7];
    filters.dates.lastUpdateAt.formattedDate.fixed = route.params.fixedLastUpdate ? route.params.fixedLastUpdate.replaceAll("-", "/") : null;
  } else if (hasUpdatedOnRouteParamsFilter.value) {
    filters.dates.updatedAt.skipClearOnRecalc.fixed = true; // UGLY HACK to skip clearing/reseting values on filterType watchers
    filters.dates.updatedAt.filterType = dateFilterTypeOptions.value[7];
    filters.dates.updatedAt.formattedDate.fixed = route.params.fixedUpdatedOn ? route.params.fixedUpdatedOn.replaceAll("-", "/") : null;
  } else if (useStoreFilter.value) {
    filters.text.title = store.currentFilter.text.title;
    filters.text.description = store.currentFilter.text.description;
    filters.text.notesBody = store.currentFilter.text.notesBody;
    filters.text.attachmentsFilename = store.currentFilter.text.attachmentsFilename;
    filters.tags = store.currentFilter.tags;
    // creation date
    filters.dates.createdAt.skipClearOnRecalc.fixed = true;
    filters.dates.createdAt.formattedDate.fixed = store.currentFilter.dates.createdAt.formattedDate.fixed;
    filters.dates.createdAt.skipClearOnRecalc.from = true;
    filters.dates.createdAt.formattedDate.from = store.currentFilter.dates.createdAt.formattedDate.from;
    filters.dates.createdAt.skipClearOnRecalc.to = true;
    filters.dates.createdAt.formattedDate.to = store.currentFilter.dates.createdAt.formattedDate.to;
    filters.dates.createdAt.filterType = store.currentFilter.dates.createdAt.filterType;
    // last update
    filters.dates.lastUpdateAt.skipClearOnRecalc.fixed = true;
    filters.dates.lastUpdateAt.formattedDate.fixed = store.currentFilter.dates.lastUpdateAt.formattedDate.fixed;
    filters.dates.lastUpdateAt.skipClearOnRecalc.from = true;
    filters.dates.lastUpdateAt.formattedDate.from = store.currentFilter.dates.lastUpdateAt.formattedDate.from;
    filters.dates.lastUpdateAt.skipClearOnRecalc.to = true;
    filters.dates.lastUpdateAt.formattedDate.to = store.currentFilter.dates.lastUpdateAt.formattedDate.to;
    filters.dates.lastUpdateAt.filterType = store.currentFilter.dates.lastUpdateAt.filterType;
    // updated on
    filters.dates.updatedAt.skipClearOnRecalc.fixed = true;
    filters.dates.updatedAt.formattedDate.fixed = store.currentFilter.dates.updatedAt.formattedDate.fixed;
    filters.dates.updatedAt.skipClearOnRecalc.from = true;
    filters.dates.updatedAt.formattedDate.from = store.currentFilter.dates.updatedAt.formattedDate.from;
    filters.dates.updatedAt.skipClearOnRecalc.to = true;
    filters.dates.updatedAt.formattedDate.to = store.currentFilter.dates.updatedAt.formattedDate.to;
    filters.dates.updatedAt.filterType = store.currentFilter.dates.updatedAt.filterType;
    sort.field = store.currentSort.field;
    sort.order = store.currentSort.order;
  }
  if (route.meta.autoLaunchSearch === true) {
    nextTick()
      .then(() => {
        onSubmitForm(true);
      }).catch((e) => {
        console.error(e);
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

<style lang="css" scoped>
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