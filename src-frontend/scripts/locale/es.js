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
            }
        }
    }
}