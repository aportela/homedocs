import { axiosInstance } from "src/composables/useAxios";

const api = {
  common: {
    getServerEnvironment: () => axiosInstance.get("/server_environment"),
  },
  auth: {
    login: function (email: string, password: string) {
      const params = {
        email: email,
        password: password,
      };
      return axiosInstance.post("/auth/login", params);
    },
    logout: () => axiosInstance.post("/auth/logout"),
    register: function (id: string, email: string, password: string) {
      const params = {
        id: id,
        email: email,
        password: password,
      };
      return axiosInstance.post("/auth/register", params);
    },
  },
  user: {
    getProfile: () => axiosInstance.get("/user/profile"),
    updateProfile: function (email: string, password: string) {
      const params = {
        email: email,
        password: password,
      };
      return axiosInstance.put("/user/profile", params);
    },
  },
  document: {
    searchRecent: function (count: number) {
      const params = {
        count: count,
      };
      return axiosInstance.post("/search/recent_documents", params);
    },
    search: function (currentPage: number, resultsPage: number, filter, sortBy, sortOrder) {
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
      return axiosInstance.post("/search/document", params);
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
      return axiosInstance.post("/document/" + document.id, params);
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
      return axiosInstance.put("/document/" + document.id, params);
    },
    remove: (id: string) => axiosInstance.delete("/document/" + id),
    get: (id: string) => axiosInstance.get("/document/" + id),
    getNotes: (id: string) => axiosInstance.get("/document/" + id + "/notes"),
    getAttachments: (id: string) =>
      axiosInstance.get("/document/" + id + "/attachments"),
    addAttachment: function (id: string, file) {
      const formData = new FormData();
      formData.append("file", file);
      return axiosInstance.post("/attachment/" + id, formData);
    },
    removeFile: (id: string) => axiosInstance.delete("/attachment/" + id),
  },
  tag: {
    getCloud: () => axiosInstance.get("/tag-cloud"),
    search: () => axiosInstance.get("/tags"),
  },
  stats: {
    documentCount: () =>
      axiosInstance.get("/stats/total-published-documents"),
    attachmentCount: () =>
      axiosInstance.get("/stats/total-uploaded-attachments"),
    attachmentDiskSize: () =>
      axiosInstance.get("/stats/total-uploaded-attachments-disk-usage"),
    getActivityHeatMapData: (fromTimestamp: number) =>
      axiosInstance.get("/stats/heatmap-activity-data", {
        params: { fromTimestamp: fromTimestamp || null },
      }),
  },
};

export { api };
