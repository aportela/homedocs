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
        signOut: function (callback) {
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
        searchRecent: function (count, callback) {
            const params = {
                count: count
            }
            Vue.http.post("api2/document/search-recent", params).then(
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
        search: function (currentPage, resultsPage, filter, sortBy, sortOrder, callback) {
            const params = {
                title: filter.title,
                description: filter.description,
                tags: filter.tags || []
            }
            params.currentPage = currentPage;
            params.resultsPage = resultsPage;
            params.sortBy = sortBy;
            params.sortOrder = sortOrder;
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
        },
        get: function(id, callback) {
            Vue.http.get("api2/document/" + id, {}).then(
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
        search: function (callback) {
            Vue.http.post("api2/tag/search", {}).then(
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