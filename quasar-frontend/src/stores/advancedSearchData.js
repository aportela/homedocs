import { defineStore } from "pinia";

import { date } from "quasar";

export const useAdvancedSearchData = defineStore("advancedSearchData", {
  state: () => ({
    filter: {
      title: null,
      description: null,
      noteBody: null,
      dateFilterType: null,
      dateRange: null,
      fromDate: null,
      fixedDate: null,
      toDate: null,
      fromTimestamp: null,
      toTimestamp: null,
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
    hasFromDateFilter: (state) =>
      state.filter.dateFilterType &&
      [3, 4, 5, 6, 8, 10].includes(state.filter.dateFilterType.value),
    hasToDateFilter: (state) =>
      state.filter.dateFilterType &&
      [3, 4, 5, 6, 9, 10].includes(state.filter.dateFilterType.value),
    hasFixedDateFilter: (state) =>
      state.filter.dateFilterType &&
      [1, 2, 7].includes(state.filter.dateFilterType.value),
    denyChangeDateFilters: (state) =>
      state.filter.dateFilterType &&
      [1, 2, 3, 4, 5, 6].includes(state.filter.dateFilterType.value),
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
    recalcDates(dateFilterType) {
      this.filter.fromDate = null;
      this.filter.fromTimestamp = null;
      this.filter.fixedDate = null;
      this.filter.toDate = null;
      this.filter.toTimestamp = null;
      switch (dateFilterType.value) {
        // NONE
        case 0:
          break;
        // TODAY
        case 1:
          this.filter.fixedDate = date.formatDate(Date.now(), "YYYY/MM/DD");
          break;
        // YESTERDAY
        case 2:
          this.filter.fixedDate = date.formatDate(
            date.addToDate(Date.now(), { days: -1 }),
            "YYYY/MM/DD",
          );
          break;
        // LAST 7 DAYS
        case 3:
          this.filter.fromDate = date.formatDate(
            date.addToDate(Date.now(), { days: -7 }),
            "YYYY/MM/DD",
          );
          this.filter.toDate = date.formatDate(
            date.addToDate(Date.now(), { days: -1 }),
            "YYYY/MM/DD",
          );
          break;
        // LAST 15 DAYS
        case 4:
          this.filter.fromDate = date.formatDate(
            date.addToDate(Date.now(), { days: -15 }),
            "YYYY/MM/DD",
          );
          this.filter.toDate = date.formatDate(
            date.addToDate(Date.now(), { days: -1 }),
            "YYYY/MM/DD",
          );
          break;
        // LAST 31 DAYS
        case 5:
          this.filter.fromDate = date.formatDate(
            date.addToDate(Date.now(), { days: -31 }),
            "YYYY/MM/DD",
          );
          this.filter.toDate = date.formatDate(
            date.addToDate(Date.now(), { days: -1 }),
            "YYYY/MM/DD",
          );
          break;
        // LAST 365 DAYS
        case 6:
          this.filter.fromDate = date.formatDate(
            date.addToDate(Date.now(), { days: -365 }),
            "YYYY/MM/DD",
          );
          this.filter.toDate = date.formatDate(
            date.addToDate(Date.now(), { days: -1 }),
            "YYYY/MM/DD",
          );
          break;
        // FIXED DATE
        case 7:
          break;
        // FROM DATE
        case 8:
          this.filter.fromDate = date.formatDate(Date.now(), "YYYY/MM/DD");
          break;
        // TO DATE
        case 9:
          this.filter.toDate = date.formatDate(Date.now(), "YYYY/MM/DD");
          break;
        // BETWEEN DATES
        case 10:
          this.filter.fromDate = date.formatDate(Date.now(), "YYYY/MM/DD");
          this.filter.toDate = date.formatDate(Date.now(), "YYYY/MM/DD");
          break;
      }
    },
  },
});
