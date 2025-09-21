// stores/advancedSearchData.js
import { reactive, computed } from "vue";
import { date } from "quasar";

// Estado reactivo
const state = reactive({
  filter: {
    title: null,
    description: null,
    noteBody: null,
    tags: [],
    // created on
    creationDateFilterType: null,
    creationDateRange: null,
    fromCreationDate: null,
    fixedCreationDate: null,
    toCreationDate: null,
    fromCreationTimestamp: null,
    toCreationTimestamp: null,
    // last update on
    lastUpdateFilterType: null,
    lastUpdateDateRange: null,
    fromLastUpdate: null,
    fixedLastUpdate: null,
    toLastUpdate: null,
    fromLastUpdateTimestamp: null,
    toLastUpdateTimestamp: null,
    // updated on
    updatedOnDateFilterType: null,
    updatedOnDateRange: null,
    fromUpdatedOn: null,
    fixedUpdatedOn: null,
    toUpdatedOn: null,
    fromUpdatedOnTimestamp: null,
    toUpdatedOnTimestamp: null,
  },
  sort: {
    field: "lastUpdateTimestamp",
    order: "DESC",
  },
  pager: {
    currentPage: 1,
    resultsPage: 32,
    totalResults: 0,
    totalPages: 0,
  },
});

const isSortAscending = computed(() => state.sort.order === "ASC");
const isSortDescending = computed(() => state.sort.order === "DESC");

const sortField = computed(() => state.sort.field);
const sortOrder = computed(() => state.sort.order);

// creation date
const hasFromCreationDateFilter = computed(
  () =>
    state.filter.creationDateFilterType &&
    [3, 4, 5, 6, 8, 10].includes(state.filter.creationDateFilterType.value),
);
const hasToCreationDateFilter = computed(
  () =>
    state.filter.creationDateFilterType &&
    [3, 4, 5, 6, 9, 10].includes(state.filter.creationDateFilterType.value),
);
const hasFixedCreationDateFilter = computed(
  () =>
    state.filter.creationDateFilterType &&
    [1, 2, 7].includes(state.filter.creationDateFilterType.value),
);
const denyChangeCreationDateFilters = computed(
  () =>
    state.filter.creationDateFilterType &&
    [1, 2, 3, 4, 5, 6].includes(state.filter.creationDateFilterType.value),
);

// last update
const hasFromLastUpdateFilter = computed(
  () =>
    state.filter.lastUpdateFilterType &&
    [3, 4, 5, 6, 8, 10].includes(state.filter.lastUpdateFilterType.value),
);
const hasToLastUpdateFilter = computed(
  () =>
    state.filter.lastUpdateFilterType &&
    [3, 4, 5, 6, 9, 10].includes(state.filter.lastUpdateFilterType.value),
);
const hasFixedLastUpdateFilter = computed(
  () =>
    state.filter.lastUpdateFilterType &&
    [1, 2, 7].includes(state.filter.lastUpdateFilterType.value),
);
const denyChangeLastUpdateFilters = computed(
  () =>
    state.filter.lastUpdateFilterType &&
    [1, 2, 3, 4, 5, 6].includes(state.filter.lastUpdateFilterType.value),
);

// updated on
const hasFromUpdatedOnFilter = computed(
  () =>
    state.filter.updatedOnDateFilterType &&
    [3, 4, 5, 6, 8, 10].includes(state.filter.updatedOnDateFilterType.value),
);
const hasToUpdatedOnFilter = computed(
  () =>
    state.filter.updatedOnDateFilterType &&
    [3, 4, 5, 6, 9, 10].includes(state.filter.updatedOnDateFilterType.value),
);
const hasFixedUpdatedOnFilter = computed(
  () =>
    state.filter.updatedOnDateFilterType &&
    [1, 2, 7].includes(state.filter.updatedOnDateFilterType.value),
);
const denyChangeUpdatedOnFilters = computed(
  () =>
    state.filter.updatedOnDateFilterType &&
    [1, 2, 3, 4, 5, 6].includes(state.filter.updatedOnDateFilterType.value),
);

const isSortedByField = (field) => {
  return state.sort.field === field;
};

const toggleSort = (field) => {
  if (state.sort.field === field) {
    state.sort.order = state.sort.order === "ASC" ? "DESC" : "ASC";
  } else {
    state.sort.field = field;
    state.sort.order = "ASC";
  }
};

const setCurrentPage = (page) => {
  state.pager.currentPage = page;
};

export function useAdvancedSearchFilter() {
  return {
    state,
    sortField,
    sortOrder,
    isSortAscending,
    isSortDescending,
    hasResults,
    isSortedByField,
    toggleSort,
    setCurrentPage,
    recalcCreationDates,
  };
}
