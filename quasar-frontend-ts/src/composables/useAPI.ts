import { axiosInstance } from "src/composables/useAxios";
import { type Sort as SortInterface } from "src/types/sort";
import { type Pager as PagerInterface } from "src/types/pager";
import { type Document } from "src/types/document";
import { type SearchFilter as SearchFilterInterface } from "src/types/search-filter";

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
    setProfile: function (email: string, password: string) {
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
    search: function (pager: PagerInterface, filter: SearchFilterInterface, sort: SortInterface, returnFragments: boolean) {
      const params = {
        pager: pager,
        filter: filter,
        sort: sort,
        returnFragments: returnFragments,
      };
      return axiosInstance.post("/search/document", params);
    },
    add: function (document: Document) {
      const params = {
        id: document.id,
        title: document.title,
        description: document.description || null,
        tags: document.tags || [],
        attachments: document.attachments || [],
        notes: document.notes || [],
      };
      return axiosInstance.post("/document/" + document.id, params);
    },
    update: function (document: Document) {
      const params = {
        id: document.id,
        title: document.title,
        description: document.description || null,
        tags: document.tags || [],
        attachments: document.attachments || [],
        notes: document.notes || [],
      };
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
