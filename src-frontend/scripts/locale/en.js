export default {
  en: {
    pages: {
      common: {
        labels: {
          projectPage: "Project page",
          byAuthor: "by alex",
        },
      },
      signIn: {
        labels: {
          headerField: "Sign In",
          emailField: "Email",
          passwordField: "Password",
          submitButton: "Sign In",
          doNotHaveAccount: "Don't have an account ?",
          createAnAccount: "Click here to sign up",
        },
        errorMessages: {
          emailNotRegistered: "Invalid email",
          incorrectPassword: "Invalid password",
          APIMissingEmail: "API Error: missing email",
          APIMissingPassword: "API Error: missing password",
        },
      },
      signUp: {
        labels: {
          headerField: "Sign Up",
          emailField: "Email",
          passwordField: "Password",
          submitButton: "Sign Up",
          alreadyHaveAnAccount: "Already have an account ?",
          signInWithAccount: "Click here to sign in",
          accountCreated: "Your account has been created",
        },
        errorMessages: {
          emailAlreadyInUse: "Email already used",
          APIMissingEmail: "API Error: missing email",
          APIMissingPassword: "API Error: missing password",
        },
      },
      appMenu: {
        labels: {
          home: "Home",
          add: "Add",
          search: "Search",
          signOut: "Sign Out",
        },
      },
      dashBoard: {
        labels: {
          add: "Add",
          addHint: "Click here for add new document",
          search: "Search",
          searchHint: "Click here for advanced search",
          recentDocuments: "Recent documents",
          recentDocumentsHeaderTitle: "Title",
          recentDocumentsHeaderCreated: "Created on",
          recentDocumentsHeaderFiles: "Files",
          recentDocumentsShowWarningNoDocuments:
            "No document has been created yet",
          browseByTags: "Browse by tags",
          browseByTagsShowWarningNoTags: "No tag has been created yet",
          clickHereToRefresh: "Click here to refresh",
          loadingData: "Loading data...",
          errorloadingData: "Error loading data",
        },
      },
      search: {
        labels: {
          headerField: "Advanced search",
          conditions: "Conditions",
          results: "Results",
          searchOnTitle: "Search on title",
          searchOnTitleInputPlaceholder: "Type title condition search",
          searchOnDescription: "Search on description",
          searchOnDescriptionInputPlaceholder:
            "Type description condition search",
          createdOn: "Created on",
          tags: "Tags",
          submitButton: "Search",
          resultsHeaderCreated: "Created on",
          resultsHeaderTitle: "Title",
          resultsHeaderDescription: "Description",
          resultsHeaderFiles: "Files",
          warning: "Warning",
          noResultsForConditions:
            "No results found for current search conditions",
        },
      },
    },
    components: {
      dateSelector: {
        anyDate: "Any date",
        today: "Today",
        yesterday: "Yesterday",
        lastWeek: "Last week",
        lastMonth: "Last month",
        last3Months: "Last 3 months",
        last6Months: "Last 6 months",
        lastYear: "Last year",
      },
      inputTags: {
        addTag: "Add tag",
        warningTagAlreadyExists: "Tag already exists",
      },
      pager: {
        previousPage: "Previous page",
        clickPreviousNavigationPage: "Click for navigate to previous page",
        nextPage: "Next page",
        clickNextNavigationPage: "Click for navigate to next page",
        resultsPage: "results/page",
        allResultsDisablePagination: "All results (disable pagination)",
        page: "Page",
        of: "of",
        totalResults: "total result/s",
      },
    },
  },
};
