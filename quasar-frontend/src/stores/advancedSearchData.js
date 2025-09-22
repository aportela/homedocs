import { defineStore } from "pinia";

import { date } from "quasar";

export const useAdvancedSearchData = defineStore("advancedSearchData", {
  state: () => ({
    filter: {
      title: null,
      description: null,
      noteBody: null,
      tags: [],
      timestamps: {
        creationDate: {
          from: null,
          to: null,
        },
        lastUpdate: {
          from: null,
          to: null,
        },
        updatedOn: {
          from: null,
          to: null,
        }
      },
    },
    sort: {
      field: "lastUpdateTimestamp",
      order: "DESC",
    },
  }),
  getters: {
    isSortAscending: (state) => state.sort.order == "ASC",
    isSortDescending: (state) => state.sort.order == "DESC",
    sortField: (state) => state.sort.field,
    sortOrder: (state) => state.sort.order,
    hasResults: (state) => state.results && state.results.length > 0,
    // creation date
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
    // last update
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
    // updated on
    hasFromUpdatedOnFilter: (state) =>
      state.filter.updatedOnDateFilterType &&
      [3, 4, 5, 6, 8, 10].includes(state.filter.updatedOnDateFilterType.value),
    hasToUpdatedOnFilter: (state) =>
      state.filter.updatedOnDateFilterType &&
      [3, 4, 5, 6, 9, 10].includes(state.filter.updatedOnDateFilterType.value),
    hasFixedUpdatedOnFilter: (state) =>
      state.filter.updatedOnDateFilterType &&
      [1, 2, 7].includes(state.filter.updatedOnDateFilterType.value),
    denyChangeUpdatedOnFilters: (state) =>
      state.filter.updatedOnDateFilterType &&
      [1, 2, 3, 4, 5, 6].includes(state.filter.updatedOnDateFilterType.value),
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
          this.filter.fixedCreationDate = date.formatDate(
            Date.now(),
            "YYYY/MM/DD",
          );
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
          this.filter.fromCreationDate = date.formatDate(
            Date.now(),
            "YYYY/MM/DD",
          );
          break;
        // TO DATE
        case 9:
          this.filter.toCreationDate = date.formatDate(
            Date.now(),
            "YYYY/MM/DD",
          );
          break;
        // BETWEEN DATES
        case 10:
          this.filter.fromCreationDate = date.formatDate(
            Date.now(),
            "YYYY/MM/DD",
          );
          this.filter.toCreationDate = date.formatDate(
            Date.now(),
            "YYYY/MM/DD",
          );
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
          this.filter.fixedLastUpdate = date.formatDate(
            Date.now(),
            "YYYY/MM/DD",
          );
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
          this.filter.fromLastUpdate = date.formatDate(
            Date.now(),
            "YYYY/MM/DD",
          );
          break;
        // TO DATE
        case 9:
          this.filter.toLastUpdate = date.formatDate(Date.now(), "YYYY/MM/DD");
          break;
        // BETWEEN DATES
        case 10:
          this.filter.fromLastUpdate = date.formatDate(
            Date.now(),
            "YYYY/MM/DD",
          );
          this.filter.toLastUpdate = date.formatDate(Date.now(), "YYYY/MM/DD");
          break;
      }
    },
    recalcUpdatedOnDates(updatedOnFilterType) {
      this.filter.fromUpdatedOn = null;
      this.filter.fromUpdatedOnTimestamp = null;
      this.filter.fixedUpdatedOn = null;
      this.filter.toUpdatedOn = null;
      this.filter.toUpdatedOnTimestamp = null;
      switch (updatedOnFilterType.value) {
        // NONE
        case 0:
          break;
        // TODAY
        case 1:
          this.filter.fixedUpdatedOn = date.formatDate(
            Date.now(),
            "YYYY/MM/DD",
          );
          break;
        // YESTERDAY
        case 2:
          this.filter.fixedUpdatedOn = date.formatDate(
            date.addToDate(Date.now(), { days: -1 }),
            "YYYY/MM/DD",
          );
          break;
        // LAST 7 DAYS
        case 3:
          this.filter.fromUpdatedOn = date.formatDate(
            date.addToDate(Date.now(), { days: -7 }),
            "YYYY/MM/DD",
          );
          this.filter.toUpdatedOn = date.formatDate(
            date.addToDate(Date.now(), { days: -1 }),
            "YYYY/MM/DD",
          );
          break;
        // LAST 15 DAYS
        case 4:
          this.filter.fromUpdatedOn = date.formatDate(
            date.addToDate(Date.now(), { days: -15 }),
            "YYYY/MM/DD",
          );
          this.filter.toUpdatedOn = date.formatDate(
            date.addToDate(Date.now(), { days: -1 }),
            "YYYY/MM/DD",
          );
          break;
        // LAST 31 DAYS
        case 5:
          this.filter.fromUpdatedOn = date.formatDate(
            date.addToDate(Date.now(), { days: -31 }),
            "YYYY/MM/DD",
          );
          this.filter.toUpdatedOn = date.formatDate(
            date.addToDate(Date.now(), { days: -1 }),
            "YYYY/MM/DD",
          );
          break;
        // LAST 365 DAYS
        case 6:
          this.filter.fromUpdatedOn = date.formatDate(
            date.addToDate(Date.now(), { days: -365 }),
            "YYYY/MM/DD",
          );
          this.filter.toUpdatedOn = date.formatDate(
            date.addToDate(Date.now(), { days: -1 }),
            "YYYY/MM/DD",
          );
          break;
        // FIXED DATE
        case 7:
          break;
        // FROM DATE
        case 8:
          this.filter.fromUpdatedOn = date.formatDate(Date.now(), "YYYY/MM/DD");
          break;
        // TO DATE
        case 9:
          this.filter.toUpdatedOn = date.formatDate(Date.now(), "YYYY/MM/DD");
          break;
        // BETWEEN DATES
        case 10:
          this.filter.fromUpdatedOn = date.formatDate(Date.now(), "YYYY/MM/DD");
          this.filter.toUpdatedOn = date.formatDate(Date.now(), "YYYY/MM/DD");
          break;
      }
    },
    reset() {
      this.filter.title = null;
      this.filter.description = null;
      this.filter.noteBody = null;
      this.filter.tags = [];
      this.filter.timestamps.creationDate.from = null;
      this.filter.timestamps.creationDate.to = null;
      this.filter.timestamps.lastUpdate.from = null;
      this.filter.timestamps.lastUpdate.to = null;
      this.filter.timestamps.updatedOn.from = null;
      this.filter.timestamps.updatedOn.to = null;
      this.sort.field = "lastUpdateTimestamp";
      this.sort.order = "DESC";
    }
  },
});
