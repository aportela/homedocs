import { useAxios } from "src/composables/useAxios";

const { axios } = useAxios();

const basePath = "api3";

export function useAPI() {
  const api = {
    common: {
      initialState: () => axios.get(basePath + "/initial_state"),
    },
    auth: {
      login: function (email, password) {
        const params = {
          email: email,
          password: password,
        };
        return axios.post(basePath + "/auth/login", params);
      },
      logout: () => axios.post(basePath + "/auth/logout"),
      register: function (id, email, password) {
        const params = {
          id: id,
          email: email,
          password: password,
        };
        return axios.post(basePath + "/auth/register", params);
      },
    },
    user: {
      getProfile: () => axios.get(basePath + "/user/profile"),
      updateProfile: function (email, password) {
        const params = {
          email: email,
          password: password,
        };
        return axios.put(basePath + "/user/profile", params);
      },
    },
    document: {
      searchRecent: function (count) {
        const params = {
          count: count,
        };
        return axios.post(basePath + "/search/recent_documents", params);
      },
      search: function (currentPage, resultsPage, filter, sortBy, sortOrder) {
        const params = {
          title: filter.text?.title || null,
          description: filter.text?.description || null,
          notesBody: filter.text?.notesBody || null,
          attachmentsFilename: filter.text?.attachmentsFilename || null,
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
        return axios.post(basePath + "/search/document", params);
      },
      add: function (document) {
        const params = {
          id: document.id,
          title: document.title,
          tags: document.tags || [],
          attachments: document.attachments || [],
          notes: document.notes || [],
        };
        if (document.description) {
          params.description = document.description;
        }
        return axios.post(basePath + "/document/" + document.id, params);
      },
      update: function (document) {
        const params = {
          id: document.id,
          title: document.title,
          tags: document.tags || [],
          attachments: document.attachments || [],
          notes: document.notes || [],
        };
        if (document.description) {
          params.description = document.description;
        }
        return axios.put(basePath + "/document/" + document.id, params);
      },
      remove: (id) => axios.delete(basePath + "/document/" + id),
      get: (id) => axios.get(basePath + "/document/" + id),
      getNotes: (id) => axios.get(basePath + "/document/" + id + "/notes"),
      getAttachments: (id) => axios.get(basePath + "/document/" + id + "/attachments"),
      addAttachment: function (id, file) {
        let formData = new FormData();
        formData.append("file", file);
        return axios.post(basePath + "/attachment/" + id, formData);
      },
      removeFile: (id) => axios.delete(basePath + "/attachment/" + id),
    },
    tag: {
      getCloud: () => axios.get(basePath + "/tag-cloud"),
      search: () => axios.get(basePath + "/tags"),
    },
    stats: {
      documentCount: () => axios.get(basePath + "/stats/total-published-documents"),
      attachmentCount: () => axios.get(basePath + "/stats/total-uploaded-attachments"),
      attachmentDiskSize: () =>
        axios.get(basePath + "/stats/total-uploaded-attachments-disk-usage"),
      getActivityHeatMapData: (fromTimestamp) =>
        axios.get(basePath + "/stats/heatmap-activity-data", {
          params: { fromTimestamp: fromTimestamp || null },
        }),
    },
  };

  return { basePath, api };
}
