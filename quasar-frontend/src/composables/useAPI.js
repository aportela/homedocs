import { useAxios } from "src/composables/useAxios";

const { axios } = useAxios();

export function useAPI() {
  const api = {
    common: {
      initialState: () => axios.get("api3/initial_state"),
    },
    user: {
      signIn: function (email, password) {
        const params = {
          email: email,
          password: password,
        };
        return axios.post("api3/user/sign-in", params);
      },
      signOut: () => axios.post("api3/user/sign-out"),
      signUp: function (id, email, password) {
        const params = {
          id: id,
          email: email,
          password: password,
        };
        return axios.post("api3/user/sign-up", params);
      },
      getProfile: () => axios.get("api3/user/profile"),
      updateProfile: function (email, password) {
        const params = {
          email: email,
          password: password,
        };
        return axios.put("api3/user/profile", params);
      },
    },
    document: {
      searchRecent: function (count) {
        const params = {
          count: count,
        };
        return axios.post("api3/search/recent_documents", params);
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
        return axios.post("api3/search/document", params);
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
        return axios.post("api3/document/" + document.id, params);
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
        return axios.put("api3/document/" + document.id, params);
      },
      remove: (id) => axios.delete("api3/document/" + id),
      get: (id) => axios.get("api3/document/" + id),
      getNotes: (id) => axios.get("api3/document/" + id + "/notes"),
      getAttachments: (id) => axios.get("api3/document/" + id + "/attachments"),
      addAttachment: function (id, file) {
        let formData = new FormData();
        formData.append("file", file);
        return axios.post("api3/attachment/" + id, formData);
      },
      removeFile: (id) => axios.delete("api3/attachment/" + id),
    },
    tag: {
      getCloud: () => axios.get("api3/tag-cloud"),
      search: () => axios.get("api3/tags"),
    },
    stats: {
      documentCount: () => axios.get("api3/stats/total-published-documents"),
      attachmentCount: () => axios.get("api3/stats/total-uploaded-attachments"),
      attachmentDiskSize: () =>
        axios.get("api3/stats/total-uploaded-attachments-disk-usage"),
      getActivityHeatMapData: (fromTimestamp) =>
        axios.get("api3/stats/heatmap-activity-data", {
          params: { fromTimestamp: fromTimestamp || null },
        }),
    },
  };

  return { api };
}
