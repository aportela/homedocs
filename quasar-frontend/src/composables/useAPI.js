import { useAxios } from "src/composables/useAxios";

const { axios } = useAxios();

export function useAPI() {
  const api = {
    common: {
      initialState: function () {
        return axios.get("api2/initial_state", {});
      },
    },
    user: {
      signIn: function (email, password) {
        const params = {
          email: email,
          password: password,
        };
        return axios.post("api2/user/sign-in", params);
      },
      signOut: function () {
        return axios.post("api2/user/sign-out");
      },
      signUp: function (id, email, password) {
        const params = {
          id: id,
          email: email,
          password: password,
        };
        return axios.post("api2/user/sign-up", params);
      },
      getProfile: function () {
        return axios.get("api2/user/profile");
      },
      updateProfile: function (email, password) {
        const params = {
          email: email,
          password: password,
        };
        return axios.put("api2/user/profile", params);
      },
    },
    document: {
      searchRecent: function (count) {
        return new Promise((resolve, reject) => {
          const params = {
            count: count,
          };
          axios
            .post("api2/search/recent_documents", params)
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
            title: filter.text?.title || null,
            description: filter.text?.description || null,
            notesBody: filter.text?.notes || null,
            tags: filter.tags || [],
          };
          params.fromCreationTimestampCondition =
            filter.dates?.creationDate?.timestamps?.from || null;
          params.toCreationTimestampCondition =
            filter.dates?.creationDate?.timestamps?.to || null;
          params.fromLastUpdateTimestampCondition =
            filter.dates?.lastUpdate?.timestamps?.from || null;
          params.toLastUpdateTimestampCondition =
            filter.dates?.lastUpdate?.timestamps?.to || null;
          params.fromUpdatedOnTimestampCondition =
            filter.dates?.updatedOn?.timestamps?.from || null;
          params.toUpdatedOnTimestampCondition =
            filter.dates?.updatedOn?.timestamps?.to || null;
          params.currentPage = currentPage;
          params.resultsPage = resultsPage;
          params.sortBy = sortBy;
          params.sortOrder = sortOrder;
          axios
            .post("api2/search/document", params)
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
      getNotes: function (id) {
        return new Promise((resolve, reject) => {
          axios
            .get("api2/document/" + id + "/notes", {})
            .then((response) => {
              resolve(response);
            })
            .catch((error) => {
              reject(error);
            });
        });
      },
      getAttachments: function (id) {
        return new Promise((resolve, reject) => {
          axios
            .get("api2/document/" + id + "/attachments", {})
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
        return axios.get("api2/tag-cloud");
      },
      search: function () {
        return axios.get("api2/tags");
      },
    },
    stats: {
      documentCount: function () {
        return axios.get("api2/stats/total-published-documents");
      },
      attachmentCount: function () {
        return axios.get("api2/stats/total-uploaded-attachments");
      },
      attachmentDiskSize: function () {
        return axios.get("api2/stats/total-uploaded-attachments-disk-usage");
      },
      getActivityHeatMapData: function (fromTimestamp) {
        return axios.get("api2/stats/heatmap-activity-data", { params: { fromTimestamp: fromTimestamp || null } });
      },
    },
  };

  return { api };
};
