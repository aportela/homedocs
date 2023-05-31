export default {
    install: (app, options) => {
        app.config.globalProperties.$api = {
            user: {
                signIn: function (email, password) {
                    return new Promise((resolve, reject) => {
                        const params = {
                            email: email,
                            password: password
                        }
                        app.config.globalProperties.$axios.post("api2/user/sign-in", params).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                },
                signOut: function () {
                    return new Promise((resolve, reject) => {
                        app.config.globalProperties.$axios.post("api2/user/sign-out", {}).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                },
                signUp: function (id, email, password) {
                    return new Promise((resolve, reject) => {
                        const params = {
                            id: id,
                            email: email,
                            password: password
                        }
                        app.config.globalProperties.$axios.post("api2/user/sign-up", params).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                }
            },
            document: {
                searchRecent: function (count) {
                    return new Promise((resolve, reject) => {
                        const params = {
                            count: count
                        }
                        app.config.globalProperties.$axios.post("api2/document/search-recent", params).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                },
                search: function (currentPage, resultsPage, filter, sortBy, sortOrder) {
                    return new Promise((resolve, reject) => {
                        const params = {
                            title: filter.title,
                            description: filter.description,
                            tags: filter.tags || []
                        }
                        if (filter.fromTimestampCondition) {
                            params.fromTimestampCondition = filter.fromTimestampCondition;
                        }
                        if (filter.toTimestampCondition) {
                            params.toTimestampCondition = filter.toTimestampCondition;
                        }
                        params.currentPage = currentPage;
                        params.resultsPage = resultsPage;
                        params.sortBy = sortBy;
                        params.sortOrder = sortOrder;
                        app.config.globalProperties.$axios.post("api2/document/search", params).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                },
                add: function (document) {
                    return new Promise((resolve, reject) => {
                        const params = {
                            id: document.id,
                            title: document.title,
                            tags: document.tags || [],
                            files: document.files || []
                        }
                        if (document.description) {
                            params.description = document.description;
                        }
                        app.config.globalProperties.$axios.post("api2/document/" + document.id, params).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                },
                update: function (document) {
                    return new Promise((resolve, reject) => {
                        const params = {
                            id: document.id,
                            title: document.title,
                            tags: document.tags || [],
                            files: document.files || []
                        }
                        if (document.description) {
                            params.description = document.description;
                        }
                        app.config.globalProperties.$axios.put("api2/document/" + document.id, params).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                },
                remove: function (id) {
                    ç
                    return new Promise((resolve, reject) => {
                        app.config.globalProperties.$axios.delete("api2/document/" + id, {}).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                },
                get: function (id) {
                    return new Promise((resolve, reject) => {
                        app.config.globalProperties.$axios.get("api2/document/" + id, {}).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                },
                addFile: function (id, file) {
                    return new Promise((resolve, reject) => {
                        let formData = new FormData();
                        formData.append('file', file);
                        app.config.globalProperties.$axios.post("api2/file/" + id, formData).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                }
            },
            tag: {
                getCloud: function () {
                    return new Promise((resolve, reject) => {
                        app.config.globalProperties.$axios.get("api2/tag-cloud", {}).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                },
                search: function () {
                    return new Promise((resolve, reject) => {
                        app.config.globalProperties.$axios.post("api2/tag/search", {}).then(response => {
                            resolve(response);
                        }).catch(error => {
                            reject(error);
                        });
                    });
                }
            }
        };
    }
}