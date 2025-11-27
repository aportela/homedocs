interface Pager {
  currentPage: number;
  resultsPage: number;
  totalResults: number;
  totalPages: number;
};


class PagerClass implements Pager {
  currentPage: number;
  resultsPage: number;
  totalResults: number;
  totalPages: number;

  constructor(currentPage: number, resultsPage: number, totalResults: number, totalPages: number) {
    this.currentPage = currentPage;
    this.resultsPage = resultsPage;
    this.totalResults = totalResults;
    this.totalPages = totalPages;
  }
};

export { type Pager, PagerClass };
