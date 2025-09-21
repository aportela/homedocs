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
    dateFilters: {
      creationDate: {
        filterType: null,
        timestamps: {
          from: null,
          to: null,
        },
        formattedDate: {
          fixed: null,
          from: null,
          to: null,
        },
      },
      lastUpdate: {
        filterType: null,
        timestamps: {
          from: null,
          to: null,
        },
        formattedDate: {
          fixed: null,
          from: null,
          to: null,
        },
      },
      updatedOn: {
        filterType: null,
        timestamps: {
          from: null,
          to: null,
        },
        formattedDate: {
          fixed: null,
          from: null,
          to: null,
        },
      },
    },
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

const recalcDates = (dateFilter) => {
  dateFilter.formattedDate.from = null;
  dateFilter.formattedDate.to = null;
  dateFilter.formattedDate.fixed = null;
  dateFilter.timestamps.from = null;
  dateFilter.timestamps.to = null;
  // generate model/formatted (visible) dates
  switch (dateFilter.filterType) {
    // NONE
    case 0:
      break;
    // TODAY
    case 1:
      dateFilter.formattedDate.fixed = date.formatDate(
        Date.now(),
        "YYYY/MM/DD",
      );
      break;
    // YESTERDAY
    case 2:
      dateFilter.formattedDate.fixed = date.formatDate(
        date.addToDate(Date.now(), { days: -1 }),
        "YYYY/MM/DD",
      );
      break;
    // LAST 7 DAYS
    case 3:
      dateFilter.formattedDate.from = date.formatDate(
        date.addToDate(Date.now(), { days: -7 }),
        "YYYY/MM/DD",
      );
      dateFilter.formattedDate.to = date.formatDate(
        date.addToDate(Date.now(), { days: -1 }),
        "YYYY/MM/DD",
      );
      break;
    // LAST 15 DAYS
    case 4:
      dateFilter.formattedDate.from = date.formatDate(
        date.addToDate(Date.now(), { days: -15 }),
        "YYYY/MM/DD",
      );
      dateFilter.formattedDate.to = date.formatDate(
        date.addToDate(Date.now(), { days: -1 }),
        "YYYY/MM/DD",
      );
      break;
    // LAST 31 DAYS
    case 5:
      dateFilter.formattedDate.from = date.formatDate(
        date.addToDate(Date.now(), { days: -31 }),
        "YYYY/MM/DD",
      );
      dateFilter.formattedDate.to = date.formatDate(
        date.addToDate(Date.now(), { days: -1 }),
        "YYYY/MM/DD",
      );
      break;
    // LAST 365 DAYS
    case 6:
      dateFilter.formattedDate.from = date.formatDate(
        date.addToDate(Date.now(), { days: -365 }),
        "YYYY/MM/DD",
      );
      dateFilter.formattedDate.to = date.formatDate(
        date.addToDate(Date.now(), { days: -1 }),
        "YYYY/MM/DD",
      );
      break;
    // FIXED DATE
    case 7:
      break;
    // FROM DATE
    case 8:
      dateFilter.formattedDate.from = date.formatDate(Date.now(), "YYYY/MM/DD");
      break;
    // TO DATE
    case 9:
      dateFilter.formattedDate.to = date.formatDate(Date.now(), "YYYY/MM/DD");
      break;
    // BETWEEN DATES
    case 10:
      dateFilter.formattedDate.from = date.formatDate(Date.now(), "YYYY/MM/DD");
      dateFilter.formattedDate.to = date.formatDate(Date.now(), "YYYY/MM/DD");
      break;
  }
  // generate API timestamps (real filters)
  if (dateFilter.formattedDate.fixed) {
    dateFilter.timestamps.from = date.formatDate(
      date.adjustDate(
        date.extractDate(dateFilter.formattedDate.fixed, "YYYY/MM/DD"),
        { hour: 0, minute: 0, second: 0, millisecond: 0 },
      ),
      "X",
    );
    dateFilter.timestamps.from = date.formatDate(
      date.adjustDate(
        date.extractDate(dateFilter.formattedDate.fixed, "YYYY/MM/DD"),
        { hour: 0, minute: 0, second: 0, millisecond: 0 },
      ),
      "X",
    );
  } else {
    if (dateFilter.formattedDate.from) {
      dateFilter.timestamps.from = date.formatDate(
        date.adjustDate(
          date.extractDate(dateFilter.formattedDate.from, "YYYY/MM/DD"),
          { hour: 0, minute: 0, second: 0, millisecond: 0 },
        ),
        "X",
      );
    }
    if (dateFilter.formattedDate.to) {
      dateFilter.timestamps.from = date.formatDate(
        date.adjustDate(
          date.extractDate(dateFilter.formattedDate.to, "YYYY/MM/DD"),
          { hour: 0, minute: 0, second: 0, millisecond: 0 },
        ),
        "X",
      );
    }
  }
};

const recalcCreationDates = () => {
  recalcDates(state.dateFilters.creationDate);
};

const recalcLastUpdates = () => {
  recalcDates(state.dateFilters.lastUpdate);
};

const recalcUpdatedOnDates = () => {
  recalcDates(state.dateFilters.updatedOn);
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
    recalcCreationDates,
    recalcLastUpdates,
    recalcUpdatedOnDates,
  };
}
