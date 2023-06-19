// This is just an example,
// so you can safely delete all default props below

export default {
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
        doNotHaveAccount: "Don't have an account yet ?",
        createAnAccount: "Click here to sign up",
      },
      errorMessages: {
        emailNotRegistered: "Email not registered",
        incorrectPassword: "Invalid password",
        APIMissingEmailParam: "API Error: missing email param",
        APIMissingPasswordParam: "API Error: missing password param",
        APIMissingParam: "API Error: missing param",
        APIFatalError: "API Error: fatal error",
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
    document: {
      labels: {
        headerAddNew: "Add new document",
        headerUpdate: "Update/view document",
        headerCreatedOn: "Document created on",
        title: "Title",
        titleInputPlaceholder: "Type document title",
        description: "Description",
        descriptionInputPlaceholder: "Type (optional) document description",
        tags: "Tags",
        attachments: "Files",
        addNewAttachment: "Add new",
        uploading: "Uploading",
        files: "file/s...",
        fileUploadedOn: "Created on",
        fileName: "Name",
        fileSize: "Size",
        fileActions: "Actions",
        buttonFileOpenPreview: "Open/Preview",
        buttonFileDownload: "Download",
        buttonFileRemove: "Remove",
        buttonSaveDocument: "Save",
        buttonDeleteDocument: "Delete this document",
        modalRemoveDocumentFileHeader: "Remove document file",
        warning: "WARNING:",
        removeDocumentFileModalMessage:
          "You are about to remove a file from document, this operation cannot be undone.",
        modalDeleteDocumentHeader: "Delete document",
        deleteDocumentModalMessage:
          "You are about to delete the document and the files, this operation cannot be undone.",
      },
      errorMessages: {
        invalidDocumentTitle:
          "Invalid document title (field is required, max length 128 chars)",
        invalidDocumentDescription:
          "Invalid document description (field is not required, max length 4096 chars)",
        errorUploadingFile: "Can not upload local file ",
        errorUploadingFileMaxUploadFileSize:
          " (max upload size supported by server: ",
        errorUploadingFileBytesFileSize: " bytes, current file size: ",
        errorUploadingFileBytes: " bytes)",
        errorUploadingFileServerError: " (server error)",
      },
    },
  },
  components: {
    confirmModal: {
      doYouWishToContinue: "Do you wish to continue ?",
      okButton: "Ok",
      cancelButton: "Cancel",
    },
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
      inputPlaceholder: "Type tag name (confirm with return)",
      buttonAddHint: "Click here to add tag",
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
};
