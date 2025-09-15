import { defineStore } from "pinia";

import { date } from "quasar";

export const useAdvancedSearchData = defineStore("advancedSearchData", {
  state: () => ({
    filter: {
      title: null,
      description: null,
      noteBody: null,
      creationDateFilterType: null,
      creationDateRange: null,
      fromCreationDate: null,
      fixedCreationDate: null,
      toCreationDate: null,
      fromCreationTimestamp: null,
      toCreationTimestamp: null,
      lastUpdateFilterType: null,
      lastUpdateDateRange: null,
      fromLastUpdate: null,
      fixedLastUpdate: null,
      toLastUpdate: null,
      fromLastUpdateTimestamp: null,
      toLastUpdateTimestamp: null,
      tags: [],
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
    results: [],
  }),
  getters: {
    isSortAscending: (state) => state.sort.order == "ASC",
    isSortDescending: (state) => state.sort.order == "DESC",
    sortField: (state) => state.sort.field,
    sortOrder: (state) => state.sort.order,
    hasResults: (state) => state.results && state.results.length > 0,
    hasFromCreationDateFilter: (state) =>
      state.filter.creationDateFilterType &&
      [3, 4, 5, 6, 8, 10].includes(state.filter.creationDateFilterType.value),
    hasToCreationDateFilter: (state) =>
      state.filter.creationDateFilterType &&
      [3, 4, 5, 6, 9, 10].includes(state.filter.creationDateFilterType.value),
    hasFixedCreationDateFilter: (state) =>
      state.filter.creationDateFilterType &&
      [1, 2, 7].includes(state.filter.creationDateFilterType.value),
    denyChangeCreationDateFilters: (state) =>
      state.filter.creationDateFilterType &&
      [1, 2, 3, 4, 5, 6].includes(state.filter.creationDateFilterType.value),
    hasFromLastUpdateFilter: (state) =>
      state.filter.lastUpdateFilterType &&
      [3, 4, 5, 6, 8, 10].includes(state.filter.lastUpdateFilterType.value),
    hasToLastUpdateFilter: (state) =>
      state.filter.lastUpdateFilterType &&
      [3, 4, 5, 6, 9, 10].includes(state.filter.lastUpdateFilterType.value),
    hasFixedLastUpdateFilter: (state) =>
      state.filter.lastUpdateFilterType &&
      [1, 2, 7].includes(state.filter.lastUpdateFilterType.value),
    denyChangeLastUpdateFilters: (state) =>
      state.filter.lastUpdateFilterType &&
      [1, 2, 3, 4, 5, 6].includes(state.filter.lastUpdateFilterType.value),
  },
  actions: {
    isSortedByField(field) {
      return this.sort.field == field;
    },
    toggleSort(field) {
      if (this.sort.field == field) {
        this.sort.order = this.sort.order == "ASC" ? "DESC" : "ASC";
      } else {
        this.sort.field = field;
        this.sort.order = "ASC";
      }
    },
    setCurrentPage(page) {
      this.pager.currentPage = page;
    },
    recalcCreationDates(creationDateFilterType) {
      this.filter.fromCreationDate = null;
      this.filter.fromCreationTimestamp = null;
      this.filter.fixedCreationDate = null;
      this.filter.toCreationDate = null;
      this.filter.toCreationTimestamp = null;
      switch (creationDateFilterType.value) {
        // NONE
        case 0:
          break;
        // TODAY
        case 1:
          this.filter.fixedCreationDate = date.formatDate(Date.now(), "YYYY/MM/DD");
          break;
        // YESTERDAY
        case 2:
          this.filter.fixedCreationDate = date.formatDate(
            date.addToDate(Date.now(), { days: -1 }),
            "YYYY/MM/DD",
          );
          break;
        // LAST 7 DAYS
        case 3:
          this.filter.fromCreationDate = date.formatDate(
            date.addToDate(Date.now(), { days: -7 }),
            "YYYY/MM/DD",
          );
          this.filter.toCreationDate = date.formatDate(
            date.addToDate(Date.now(), { days: -1 }),
            "YYYY/MM/DD",
          );
          break;
        // LAST 15 DAYS
        case 4:
          this.filter.fromCreationDate = date.formatDate(
            date.addToDate(Date.now(), { days: -15 }),
            "YYYY/MM/DD",
          );
          this.filter.toCreationDate = date.formatDate(
            date.addToDate(Date.now(), { days: -1 }),
            "YYYY/MM/DD",
          );
          break;
        // LAST 31 DAYS
        case 5:
          this.filter.fromCreationDate = date.formatDate(
            date.addToDate(Date.now(), { days: -31 }),
            "YYYY/MM/DD",
          );
          this.filter.toCreationDate = date.formatDate(
            date.addToDate(Date.now(), { days: -1 }),
            "YYYY/MM/DD",
          );
          break;
        // LAST 365 DAYS
        case 6:
          this.filter.fromCreationDate = date.formatDate(
            date.addToDate(Date.now(), { days: -365 }),
            "YYYY/MM/DD",
          );
          this.filter.toCreationDate = date.formatDate(
            date.addToDate(Date.now(), { days: -1 }),
            "YYYY/MM/DD",
          );
          break;
        // FIXED DATE
        case 7:
          break;
        // FROM DATE
        case 8:
          this.filter.fromCreationDate = date.formatDate(Date.now(), "YYYY/MM/DD");
          break;
        // TO DATE
        case 9:
          this.filter.toCreationDate = date.formatDate(Date.now(), "YYYY/MM/DD");
          break;
        // BETWEEN DATES
        case 10:
          this.filter.fromCreationDate = date.formatDate(Date.now(), "YYYY/MM/DD");
          this.filter.toCreationDate = date.formatDate(Date.now(), "YYYY/MM/DD");
          break;
      }
    },
    recalcLastUpdates(lastUpdateFilterType) {
      this.filter.fromLastUpdate = null;
      this.filter.fromLastUpdateTimestamp = null;
      this.filter.fixedLastUpdate = null;
      this.filter.toLastUpdate = null;
      this.filter.toLastUpdateTimestamp = null;
      switch (lastUpdateFilterType.value) {
        // NONE
        case 0:
          break;
        // TODAY
        case 1:
          this.filter.fixedLastUpdate = date.formatDate(Date.now(), "YYYY/MM/DD");
          break;
        // YESTERDAY
        case 2:
          this.filter.fixedLastUpdate = date.formatDate(
            date.addToDate(Date.now(), { days: -1 }),
            "YYYY/MM/DD",
          );
          break;
        // LAST 7 DAYS
        case 3:
          this.filter.fromLastUpdate = date.formatDate(
            date.addToDate(Date.now(), { days: -7 }),
            "YYYY/MM/DD",
          );
          this.filter.toLastUpdate = date.formatDate(
            date.addToDate(Date.now(), { days: -1 }),
            "YYYY/MM/DD",
          );
          break;
        // LAST 15 DAYS
        case 4:
          this.filter.fromLastUpdate = date.formatDate(
            date.addToDate(Date.now(), { days: -15 }),
            "YYYY/MM/DD",
          );
          this.filter.toLastUpdate = date.formatDate(
            date.addToDate(Date.now(), { days: -1 }),
            "YYYY/MM/DD",
          );
          break;
        // LAST 31 DAYS
        case 5:
          this.filter.fromLastUpdate = date.formatDate(
            date.addToDate(Date.now(), { days: -31 }),
            "YYYY/MM/DD",
          );
          this.filter.toLastUpdate = date.formatDate(
            date.addToDate(Date.now(), { days: -1 }),
            "YYYY/MM/DD",
          );
          break;
        // LAST 365 DAYS
        case 6:
          this.filter.fromLastUpdate = date.formatDate(
            date.addToDate(Date.now(), { days: -365 }),
            "YYYY/MM/DD",
          );
          this.filter.toLastUpdate = date.formatDate(
            date.addToDate(Date.now(), { days: -1 }),
            "YYYY/MM/DD",
          );
          break;
        // FIXED DATE
        case 7:
          break;
        // FROM DATE
        case 8:
          this.filter.fromLastUpdate = date.formatDate(Date.now(), "YYYY/MM/DD");
          break;
        // TO DATE
        case 9:
          this.filter.toLastUpdate = date.formatDate(Date.now(), "YYYY/MM/DD");
          break;
        // BETWEEN DATES
        case 10:
          this.filter.fromLastUpdate = date.formatDate(Date.now(), "YYYY/MM/DD");
          this.filter.toLastUpdate = date.formatDate(Date.now(), "YYYY/MM/DD");
          break;
      }
    },
  },
});
