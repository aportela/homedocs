interface Pager {
  currentPageIndex: number;
  resultsPage: number;
  totalResults: number;
  totalPages: number;
};


class PagerClass implements Pager {
  currentPageIndex: number;
  resultsPage: number;
  totalResults: number;
  totalPages: number;

  constructor(currentPageIndex: number, resultsPage: number, totalResults: number, totalPages: number) {
    this.currentPageIndex = currentPageIndex;
    this.resultsPage = resultsPage;
    this.totalResults = totalResults;
    this.totalPages = totalPages;
  }
};

export { type Pager, PagerClass };
