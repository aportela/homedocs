export default {
    user: {
        signIn: function (email, password, callback) {
            const params = {
                email: email,
                password: password
            }
            Vue.http.post("api2/user/sign-in", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                },
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        },
        signOut: function(callback) {
            Vue.http.post("api2/user/sign-out", {}).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                },
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        },
        signUp: function (id, email, password, callback) {
            const params = {
                id: id,
                email: email,
                password: password
            }
            Vue.http.post("api2/user/sign-up", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                },
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        }
    },
    document: {
        search: function(callback) {
            const params = {
            }
            Vue.http.post("api2/document/search", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                },
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );

        }
    },
    tag: {
        search: function(callback) {
            const params = {
            }
            Vue.http.post("api2/tag/search", params).then(
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                },
                response => {
                    if (callback && typeof callback === "function") {
                        callback(response);
                    }
                }
            );
        }
    }
};