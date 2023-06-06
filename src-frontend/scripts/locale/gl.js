export default {
  gl: {
    pages: {
      common: {
        labels: {
          projectPage: "Páxina do proxecto",
          byAuthor: "por alex",
        },
      },
      signIn: {
        labels: {
          headerField: "Iniciar sesión",
          emailField: "Correo electrónico",
          passwordField: "Contrasinal",
          submitButton: "Iniciar sesión",
          doNotHaveAccount: "¿ Non dispón de unha conta ?",
          createAnAccount: "Faga click aquí pra crear unha",
        },
        errorMessages: {
          emailNotRegistered: "Correo electrónico incorrecto",
          incorrectPassword: "Contrasinal incorrecta",
          APIMissingEmail: "Error de API: falta o correo electrónico",
          APIMissingPassword: "Error de API: falta a contrasinal",
        },
      },
      signUp: {
        labels: {
          headerField: "Crear conta",
          emailField: "Correo electrónico",
          passwordField: "Contrasinal",
          submitButton: "Crear conta",
          alreadyHaveAnAccount: "¿ Xa dispón de unha conta ?",
          signInWithAccount: "Faga click aquí pra iniciar sesión",
          accountCreated: "A conta creouse con éxito",
        },
        errorMessages: {
          emailAlreadyInUse: "O correo electrónico xa está en uso",
          APIMissingEmail: "Error de API: falta o correo electrónico",
          APIMissingPassword: "Error de API: falta a contrasinal",
        },
      },
      appMenu: {
        labels: {
          home: "Inicio",
          add: "Engadir",
          search: "Procurar",
          signOut: "Saír",
        },
      },
      dashBoard: {
        labels: {
          add: "Engadir",
          addHint: "Faga click eiquí pra engadir un novo documento",
          search: "Procurar",
          searchHint: "Faga click eiquí pra procurar documentos",
          recentDocuments: "Documentos recentes",
          recentDocumentsHeaderTitle: "Título",
          recentDocumentsHeaderCreated: "Creado o",
          recentDocumentsHeaderFiles: "Adxuntos",
          recentDocumentsShowWarningNoDocuments:
            "Aínda non engadiu ningún ningún documento",
          browseByTags: "Explorar por etiquetas",
          browseByTagsShowWarningNoTags: "Aínda non engadiu ningunha etiqueta",
          clickHereToRefresh: "Faga click eiquí pra refrescar",
          loadingData: "Cargando datos...",
          errorloadingData: "Error cargando datos",
        },
      },
      search: {
        labels: {
          headerField: "Procura avanzada",
          conditions: "Condicións",
          results: "Resultados",
          searchOnTitle: "Procurar no título",
          searchOnTitleInputPlaceholder:
            "Teclee a condición de procura do título",
          searchOnDescription: "Procurar en descripción",
          searchOnDescriptionInputPlaceholder:
            "Teclee a condición de procura da descripción",
          createdOn: "Creado o",
          tags: "Etiquetas",
          submitButton: "Procurar",
          resultsHeaderCreated: "Creado o",
          resultsHeaderTitle: "Título",
          resultsHeaderDescription: "Descripcion",
          resultsHeaderFiles: "Adxuntos",
          warning: "Atención",
          noResultsForConditions:
            "Non existen resultados pra as condicións de procura especificadas",
        },
      },
    },
    components: {
      dateSelector: {
        anyDate: "Calquera data",
        today: "Hoxe",
        yesterday: "Onte",
        lastWeek: "Última semana",
        lastMonth: "Último mes",
        last3Months: "Último trimestre",
        last6Months: "Último semestre",
        lastYear: "Último ano",
      },
      inputTags: {
        addTag: "Engadir etiqueta",
        warningTagAlreadyExists: "A etiqueta xa existe",
      },
      pager: {
        previousPage: "Páxina anterior",
        clickPreviousNavigationPage:
          "Faga click eiquí pra navegar a páxina anterior",
        nextPage: "Páxina seguinte",
        clickNextNavigationPage:
          "Faga click eiquí pra navegar a seguinte páxina",
        resultsPage: "resultados/páxina",
        allResultsDisablePagination: "Todo-los resultados (sen paxinación)",
        page: "Páxina",
        of: "de",
        totalResults: "total resultado/s",
      },
    },
  },
};
