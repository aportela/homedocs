import { defineStore } from "pinia";

export const useAdvancedSearchData = defineStore("advancedSearchData", {
  state: () => ({
    filters: null,
    sort: null,
  }),
  actions: {
    reset() {
      this.filters = null;
      this.sort = null;
    }
  },
});
