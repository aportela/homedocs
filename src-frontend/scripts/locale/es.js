export default {
  es: {
    pages: {
      common: {
        labels: {
          projectPage: "Página del proyecto",
          byAuthor: "por alex",
        },
      },
      signIn: {
        labels: {
          headerField: "Iniciar sesión",
          emailField: "Correo electrónico",
          passwordField: "Contraseña",
          submitButton: "Iniciar sesión",
          doNotHaveAccount: "¿ No dispone de una cuenta ?",
          createAnAccount: "Haga click aquí para crear una",
        },
        errorMessages: {
          emailNotRegistered: "Correo electrónico incorrecto",
          incorrectPassword: "Contraseña incorrecta",
          APIMissingEmail: "Error de API: falta el correo electrónico",
          APIMissingPassword: "Error de API: falta la contraseña",
        },
      },
      signUp: {
        labels: {
          headerField: "Crear cuenta",
          emailField: "Correo electrónico",
          passwordField: "Contraseña",
          submitButton: "Crear cuenta",
          alreadyHaveAnAccount: "¿ Ya dispone de una cuenta ?",
          signInWithAccount: "Haga click aquí para iniciar sesión",
          accountCreated: "La cuenta se ha creado con éxito",
        },
        errorMessages: {
          emailAlreadyInUse: "El correo electrónico ya está en uso",
          APIMissingEmail: "Error de API: falta el correo electrónico",
          APIMissingPassword: "Error de API: falta la contraseña",
        },
      },
      appMenu: {
        labels: {
          home: "Inicio",
          add: "Añadir",
          search: "Buscar",
          signOut: "Salir",
        },
      },
      dashBoard: {
        labels: {
          add: "Añadir",
          addHint: "Haga click aquí para añadir un nuevo documento",
          search: "Buscar",
          searchHint: "Haga click aquí para buscar documentos",
          recentDocuments: "Documentos recientes",
          recentDocumentsHeaderTitle: "Título",
          recentDocumentsHeaderCreated: "Creado el",
          recentDocumentsHeaderFiles: "Adjuntos",
          recentDocumentsShowWarningNoDocuments:
            "Aún no ha creado ningún documento",
          browseByTags: "Explorar por etiquetas",
          browseByTagsShowWarningNoTags: "Aún no ha creado ninguna etiqueta",
          clickHereToRefresh: "Haga click aquí para refrescar",
          loadingData: "Cargando datos...",
          errorloadingData: "Error cargando datos",
        },
      },
      search: {
        labels: {
          headerField: "Búsqueda avanzada",
          conditions: "Condiciones",
          results: "Resultados",
          searchOnTitle: "Buscar en el título",
          searchOnTitleInputPlaceholder:
            "Teclee la condición de búsqueda del título",
          searchOnDescription: "Buscar en descripción",
          searchOnDescriptionInputPlaceholder:
            "Teclee la condición de búsqueda de la descripción",
          createdOn: "Creado el",
          tags: "Etiquetas",
          submitButton: "Buscar",
          resultsHeaderCreated: "Creado el",
          resultsHeaderTitle: "Título",
          resultsHeaderDescription: "Descripcion",
          resultsHeaderFiles: "Adjuntos",
          warning: "Atención",
          noResultsForConditions:
            "No existen resultados para las condiciones de búsqueda especificadas",
        },
      },
      document: {
        labels: {
          headerAddNew: "Añadir nuevo documento",
          headerUpdate: "Actualizar documento",
          headerCreatedOn: "Documento creado el",
          title: "Título",
          titleInputPlaceholder: "Teclee el título del documento",
          description: "Descripción",
          descriptionInputPlaceholder:
            "Teclee una descripción (opcional) para el documento",
          tags: "Etiquetas",
          attachments: "Archivos",
          addNewAttachment: "Añadir nuevo",
          uploading: "Subiendo",
          files: "archivo/s...",
          fileUploadedOn: "Creado el",
          fileName: "Nombre",
          fileSize: "Tamaño",
          fileActions: "Acciones",
          buttonFileOpenPreview: "Abrir/Previsualizar",
          buttonFileDownload: "Descargar",
          buttonFileRemove: "Eliminar",
          buttonSaveDocument: "Guardar",
          buttonDeleteDocument: "Eliminar documento",
          modalRemoveDocumentFileHeader: "Eliminar archivo del documento",
          warning: "ATENCIÓN:",
          removeDocumentFileModalMessage:
            "Está a punto de eliminar un archivo del documento, esta operación no se puede deshacer.",
          modalDeleteDocumentHeader: "Eliminar documento",
          deleteDocumentModalMessage:
            "Está a punto de eliminar el documento y sus archivos, esta operación no se puede deshacer.",
        },
        errorMessages: {
          invalidDocumentTitle:
            "Título del documento inválido (campo requerido, longitud máxima 128 caracteres)",
          invalidDocumentDescription:
            "Descripción del documento inválida (campo no requerido, longitud máxima 4096 caracteres)",
          errorUploadingFile: "No se puede subir el archivo ",
          errorUploadingFileMaxUploadFileSize:
            " (tamaño máximo aceptado por el servidor: ",
          errorUploadingFileBytesFileSize:
            " bytes, tamaño actual del archivo: ",
          errorUploadingFileBytes: " bytes)",
          errorUploadingFileServerError: " (error del servidor)",
        },
      },
    },
    components: {
      confirmModal: {
        doYouWishToContinue: "¿ Desea continuar ?",
        okButton: "Si",
        cancelButton: "No",
      },
      dateSelector: {
        anyDate: "Cualquier fecha",
        today: "Hoy",
        yesterday: "Ayer",
        lastWeek: "Última semana",
        lastMonth: "Último mes",
        last3Months: "Último trimestre",
        last6Months: "Último semestre",
        lastYear: "Último año",
      },
      inputTags: {
        addTag: "Añadir etiqueta",
        warningTagAlreadyExists: "La etiqueta ya existe",
        inputPlaceholder: "Teclee la etiqueta (confirme con enter)",
        buttonAddHint: "Haga click aquí para añadir etiquetas",
      },
      pager: {
        previousPage: "Página anterior",
        clickPreviousNavigationPage:
          "Haga click aquí para navegar a la página anterior",
        nextPage: "Página siguiente",
        clickNextNavigationPage:
          "Haga click aquí para navegar a la siguiente página",
        resultsPage: "resultados/página",
        allResultsDisablePagination: "Todos los resultados (sin paginación)",
        page: "Página",
        of: "de",
        totalResults: "total resultado/s",
      },
    },
  },
};
