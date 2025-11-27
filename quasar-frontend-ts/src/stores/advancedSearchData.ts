import { defineStore } from "pinia";
import { type SearchFilter as SearchFilterInterface, SearchFilterClass, SearchOnTextEntitiesFilterClass, SearchDatesFilterClass } from "src/types/search-filter";
import { type Sort as SortInterface, SortClass } from "src/types/sort";
import { type Pager as PagerInterface, PagerClass } from "src/types/pager";

const defaultPager = new PagerClass(1, 32, 0, 0);
const defaultSearchOnTextEntitiesFilter = new SearchOnTextEntitiesFilterClass(null, null, null, null);
const defaultSearchDatesFilter = new SearchDatesFilterClass(null, null, null);
const defaultFilter = new SearchFilterClass(defaultSearchOnTextEntitiesFilter, [], defaultSearchDatesFilter);
const defaultSort = new SortClass("lastUpdateTimestamp", "Last update", "DESC")

interface State {
  pager: PagerInterface;
  filter: SearchFilterInterface;
  sort: SortInterface;
};

export const useAdvancedSearchData = defineStore("advancedSearchData", {
  state: (): State => ({
    pager: defaultPager,
    filter: defaultFilter,
    sort: defaultSort,
  }),
  getters: {
    currentPager: (state) => state.pager,
    currentFilter: (state) => state.filter,
    currentSort: (state) => state.sort,
  },
  actions: {
    reset() {
      this.pager = defaultPager;
      this.filter = defaultFilter;
      this.sort = defaultSort;
    },
    setPager(pager: PagerInterface) {
      this.pager = pager;
    },
    setFilter(filter: SearchFilterInterface) {
      this.filter = filter;
    },
    setSort(sort: SortInterface): void {
      this.sort = sort;
    },
  },
});
