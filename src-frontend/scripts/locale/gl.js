export default {
    gl: {
        pages: {
        common: {
                labels: {
                    projectPage: 'Páxina do proxecto',
                    byAuthor: 'por alex'
                }
            },
            signIn: {
                labels: {
                    headerField: 'Iniciar sesión',
                    emailField: 'Correo electrónico',
                    passwordField: 'Contrasinal',
                    submitButton: 'Iniciar sesión',
                    doNotHaveAccount: '¿ Non dispón de unha conta ?',
                    createAnAccount: 'Faga click aquí pra crear unha',
                },
                errorMessages: {
                    emailNotRegistered: 'Correo electrónico incorrecto',
                    incorrectPassword: 'Contrasinal incorrecta',
                    APIMissingEmail: 'Error de API: falta o correo electrónico',
                    APIMissingPassword: 'Error de API: falta a contrasinal'
                }
            },
            signUp: {
                labels: {
                    headerField: 'Crear conta',
                    emailField: 'Correo electrónico',
                    passwordField: 'Contrasinal',
                    submitButton: 'Crear conta',
                    alreadyHaveAnAccount: '¿ Xa dispón de unha conta ?',
                    signInWithAccount: 'Faga click aquí pra iniciar sesión',
                    accountCreated: 'A conta creouse con éxito'
                },
                errorMessages: {
                    emailAlreadyInUse: 'O correo electrónico xa está en uso',
                    APIMissingEmail: 'Error de API: falta o correo electrónico',
                    APIMissingPassword: 'Error de API: falta a contrasinal'
                }
            }
        }
    }
}