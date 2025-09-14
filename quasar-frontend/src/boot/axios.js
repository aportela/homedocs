import { boot } from "quasar/wrappers";
import axios from "axios";
import { useSessionStore } from "stores/session";

const session = useSessionStore();
if (!session.isLoaded) {
  session.load();
}

axios.interceptors.request.use(
  (config) => {
    if (session.getJWT) {
      config.headers["HOMEDOCS-JWT"] = session.getJWT;
      config.withCredentials = true;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  },
);

axios.interceptors.response.use(
  (response) => {
    // warning: axios lowercase received header names
    const apiResponseJWT = response.headers["homedocs-jwt"] || null;
    if (apiResponseJWT) {
      if (apiResponseJWT && apiResponseJWT != session.getJWT) {
        session.signIn(apiResponseJWT);
      }
    }
    return response;
  },
  (error) => {
    if (!error) {
      return Promise.reject({
        response: {
          status: 0,
          statusText: "undefined",
        },
      });
    } else {
      if (!error.response) {
        error.response = {
          status: 0,
          statusText: "undefined",
        };
      }
      return Promise.reject(error);
    }
  },
);

const api = {
  common: {
    initialState: function () {
      return new Promise((resolve, reject) => {
        axios
          .get("api2/initial_state", {})
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
  },
  user: {
    signIn: function (email, password) {
      return new Promise((resolve, reject) => {
        const params = {
          email: email,
          password: password,
        };
        axios
          .post("api2/user/sign-in", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    signOut: function () {
      return new Promise((resolve, reject) => {
        axios
          .post("api2/user/sign-out", {})
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    signUp: function (id, email, password) {
      return new Promise((resolve, reject) => {
        const params = {
          id: id,
          email: email,
          password: password,
        };
        axios
          .post("api2/user/sign-up", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    updateProfile: function (email, password) {
      return new Promise((resolve, reject) => {
        const params = {
          email: email,
          password: password,
        };
        axios
          .post("api2/user/update-profile", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    }
  },
  document: {
    searchRecent: function (count) {
      return new Promise((resolve, reject) => {
        const params = {
          count: count,
        };
        axios
          .post("api2/document/search-recent", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    search: function (currentPage, resultsPage, filter, sortBy, sortOrder) {
      return new Promise((resolve, reject) => {
        const params = {
          title: filter.title,
          description: filter.description,
          notesBody: filter.notesBody,
          tags: filter.tags || [],
        };
        if (filter.fromTimestamp) {
          params.fromTimestampCondition = filter.fromTimestamp;
        }
        if (filter.toTimestamp) {
          params.toTimestampCondition = filter.toTimestamp;
        }
        params.currentPage = currentPage;
        params.resultsPage = resultsPage;
        params.sortBy = sortBy;
        params.sortOrder = sortOrder;
        axios
          .post("api2/document/search", params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
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
          files: document.files || [],
          notes: document.notes || [],
        };
        if (document.description) {
          params.description = document.description;
        }
        axios
          .post("api2/document/" + document.id, params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
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
          files: document.files || [],
          notes: document.notes || [],
        };
        if (document.description) {
          params.description = document.description;
        }
        axios
          .put("api2/document/" + document.id, params)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    remove: function (id) {
      return new Promise((resolve, reject) => {
        axios
          .delete("api2/document/" + id, {})
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    get: function (id) {
      return new Promise((resolve, reject) => {
        axios
          .get("api2/document/" + id, {})
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    addFile: function (id, file) {
      return new Promise((resolve, reject) => {
        let formData = new FormData();
        formData.append("file", file);
        axios
          .post("api2/file/" + id, formData)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    removeFile: function (id) {
      return new Promise((resolve, reject) => {
        axios
          .delete("api2/file/" + id)
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
  },
  tag: {
    getCloud: function () {
      return new Promise((resolve, reject) => {
        axios
          .get("api2/tag-cloud", {})
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    search: function () {
      return new Promise((resolve, reject) => {
        axios
          .post("api2/tag/search", {})
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
  },
  stats: {
    documentCount: function () {
      return new Promise((resolve, reject) => {
        axios
          .get("api2/stats/total-published-documents", {})
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    attachmentCount: function () {
      return new Promise((resolve, reject) => {
        axios
          .get("api2/stats/total-uploaded-attachments", {})
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    attachmentDiskSize: function () {
      return new Promise((resolve, reject) => {
        axios
          .get("api2/stats/total-uploaded-attachments-disk-usage", {})
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
    getActivityHeatMapData: function () {
      return new Promise((resolve, reject) => {
        axios
          .get("api2/stats/heatmap-activity-data", {})
          .then((response) => {
            resolve(response);
          })
          .catch((error) => {
            reject(error);
          });
      });
    },
  },
};

export default boot(({ app }) => {
  // for use inside Vue files (Options API) through this.$axios and this.$api
  app.config.globalProperties.$axios = axios;
  // ^ ^ ^ this will allow you to use this.$axios (for Vue Options API form)
  //       so you won't necessarily have to import axios in each vue file
  app.config.globalProperties.$api = api;
  // ^ ^ ^ this will allow you to use this.$api (for Vue Options API form)
  //       so you can easily perform requests against your app's API
});

export { axios, api };
