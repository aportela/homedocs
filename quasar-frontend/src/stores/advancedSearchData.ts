import { defineStore, acceptHMRUpdate } from 'pinia';
import {
  type SearchFilterClass as SearchFilterClassInterface,
  SearchFilterClass,
  SearchOnTextEntitiesFilterClass,
  SearchDatesFilterClass,
} from 'src/types/searchFilter';
import { type SortClass as SortClassInterface, SortClass } from 'src/types/sort';
import { type PagerClass as PagerClassInterface, PagerClass } from 'src/types/pager';

const getDefaultPager = (): PagerClass => {
  return new PagerClass(1, 32, 0, 0);
};

const getDefaultFilter = (): SearchFilterClass => {
  const defaultSearchOnTextEntitiesFilter: SearchOnTextEntitiesFilterClass =
    new SearchOnTextEntitiesFilterClass(null, null, null, null);
  const defaultSearchDatesFilter: SearchDatesFilterClass = new SearchDatesFilterClass(
    null,
    null,
    null,
  );
  return new SearchFilterClass(defaultSearchOnTextEntitiesFilter, [], defaultSearchDatesFilter);
};

const getDefaultSort = (): SortClass => {
  return new SortClass('lastUpdateTimestamp', 'Last update', 'DESC');
};

interface State {
  pager: PagerClassInterface;
  filter: SearchFilterClassInterface;
  sort: SortClassInterface;
}

export const useAdvancedSearchData = defineStore('advancedSearchData', {
  state: (): State => ({
    pager: getDefaultPager(),
    filter: getDefaultFilter(),
    sort: getDefaultSort(),
  }),
  getters: {
    currentPager: (state: State) => state.pager,
    currentFilter: (state: State) => state.filter,
    currentSort: (state: State) => state.sort,
  },
  actions: {
    reset() {
      this.pager = getDefaultPager();
      this.filter = getDefaultFilter();
      this.sort = getDefaultSort();
    },
    setPager(pager: PagerClassInterface) {
      this.pager = pager;
    },
    setFilter(filter: SearchFilterClassInterface) {
      this.filter = filter;
    },
    setSort(sort: SortClassInterface): void {
      this.sort = sort;
    },
  },
});

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useAdvancedSearchData, import.meta.hot));
}
