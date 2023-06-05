export default {
    es: {
        pages: {
            common: {
                labels: {
                    projectPage: 'Página del proyecto',
                    byAuthor: 'por alex'
                }
            },
            signIn: {
                labels: {
                    headerField: 'Iniciar sesión',
                    emailField: 'Correo electrónico',
                    passwordField: 'Contraseña',
                    submitButton: 'Iniciar sesión',
                    doNotHaveAccount: '¿ No dispone de una cuenta ?',
                    createAnAccount: 'Haga click aquí para crear una',
                },
                errorMessages: {
                    emailNotRegistered: 'Correo electrónico incorrecto',
                    incorrectPassword: 'Contraseña incorrecta',
                    APIMissingEmail: 'Error de API: falta el correo electrónico',
                    APIMissingPassword: 'Error de API: falta la contraseña'
                }
            },
            signUp: {
                labels: {
                    headerField: 'Crear cuenta',
                    emailField: 'Correo electrónico',
                    passwordField: 'Contraseña',
                    submitButton: 'Crear cuenta',
                    alreadyHaveAnAccount: '¿ Ya dispone de una cuenta ?',
                    signInWithAccount: 'Haga click aquí para iniciar sesión',
                    accountCreated: 'La cuenta se ha creado con éxito'
                },
                errorMessages: {
                    emailAlreadyInUse: 'El correo electrónico ya está en uso',
                    APIMissingEmail: 'Error de API: falta el correo electrónico',
                    APIMissingPassword: 'Error de API: falta la contraseña'
                }
            },
            appMenu: {
                labels: {
                    home: 'Inicio',
                    add: 'Añadir',
                    search: 'Buscar',
                    signOut: 'Salir'
                }
            },
            dashBoard: {
                labels: {
                    add: 'Añadir',
                    addHint: 'Haga click aquí para añadir un nuevo documento',
                    search: 'Buscar',
                    searchHint: 'Haga click aquí para buscar documentos',
                    recentDocuments: 'Documentos recientes',
                    recentDocumentsHeaderTitle: 'Título',
                    recentDocumentsHeaderCreated: 'Creado el',
                    recentDocumentsHeaderFiles: 'Adjuntos',
                    recentDocumentsShowWarningNoDocuments: 'Aún no ha creado ningún documento',
                    browseByTags: 'Explorar por etiquetas',
                    browseByTagsShowWarningNoTags: 'Aún no ha creado ninguna etiqueta',
                    clickHereToRefresh: 'Haga click aquí para refrescar',
                    loadingData: 'Cargando datos...',
                    errorloadingData: 'Error cargando datos'
                }
            }
        }
    }
}