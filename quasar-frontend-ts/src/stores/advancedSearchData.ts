import { defineStore } from "pinia";
import { type OrderType } from "src/types/order-type";
import { type DateFilterClass } from "src/types/date-filters";

interface Filters {
  text: {
    title: string | null;
    description: string | null;
    notes: string | null;
    attachmentNames: string | null;
  },
  tags: string[];
  dates: {
    creationDate: any; // DateFilterClass | null;
    lastUpdate: any; // DateFilterClass | null;
    updatedOn: any; // DateFilterClass | null;
  }
};

interface Sort {
  field: string;
  label: string;
  order: OrderType;
}

interface State {
  filters: Filters | null;
  sort: Sort | null;
};

export const useAdvancedSearchData = defineStore("advancedSearchData", {
  state: (): State => ({
    filters: null,
    sort: null,
  }),
  getters: {
    filters: (state) => state.filters,
    sort: (state) => state.sort,
  },
  actions: {
    reset() {
      this.filters = null;
      this.sort = null;
    },
    setFilters(filters: Filters | null): void {
      this.filters = filters;
    },
    setSort(sort: Sort | null): void {
      this.sort = sort;
    },
    getCurrentAPIFilter(currentPage: number, resultsPage: number, sortBy: string, sortOrder: OrderType): object {
      return (
        {
          title: this.filters?.text?.title || null,
          description: this.filters?.text?.description || null,
          notesBody: this.filters?.text?.notes || null,
          attachmentsFilename: this.filters?.text?.attachmentNames || null,
          tags: this.filters?.tags || [],
          fromCreationTimestampCondition: this.filters?.dates?.creationDate?.timestamps?.from || null,
          toCreationTimestampCondition: this.filters?.dates?.creationDate?.timestamps?.to || null,
          fromLastUpdateTimestampCondition: this.filters?.dates?.lastUpdate?.timestamps?.from || null,
          toLastUpdateTimestampCondition: this.filters?.dates?.lastUpdate?.timestamps?.to || null,
          fromUpdatedOnTimestampCondition: this.filters?.dates?.updatedOn?.timestamps?.from || null,
          toUpdatedOnTimestampCondition: this.filters?.dates?.updatedOn?.timestamps?.to || null,
          currentPage: currentPage,
          resultsPage: resultsPage,
          sortBy: sortBy,
          sortOrder: sortOrder
        }
      );
    }
  },
});
