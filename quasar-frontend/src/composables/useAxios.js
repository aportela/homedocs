import axios from "axios";
import { SERVER_API_BASE_PATH } from "src/constants";
import { useSessionStore } from "src/stores/session";
import { useServerEnvironmentStore } from "src/stores/serverEnvironment";

const sessionStore = useSessionStore();

const serverEnvironment = useServerEnvironmentStore();

const axiosInstance = axios.create({
  baseURL: SERVER_API_BASE_PATH,
});

axiosInstance.interceptors.request.use(
  (config) => {
    if (sessionStore.jwt) {
      config.headers["HOMEDOCS-JWT"] = sessionStore.jwt;
      config.withCredentials = true;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  },
);

axiosInstance.interceptors.response.use(
  (response) => {
    // warning: axios received header names in lowercase
    const apiResponseJWT = response.headers["homedocs-jwt"] || null;
    if (apiResponseJWT) {
      if (apiResponseJWT !== sessionStore.jwt) {
        sessionStore.setJWT(apiResponseJWT);
      }
    }
    if (response.data.serverEnvironment) {
      serverEnvironment.set(
        response.data?.serverEnvironment?.allowSignUp,
        response.data?.serverEnvironment?.environment,
        response.data?.serverEnvironment?.maxUploadFileSize,
      );
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
      if (error.response?.status === 401) {
        if (sessionStore.jwt) {
          sessionStore.setJWT(null);
        }
      }
      error.isAPIError = true;
      error.customAPIErrorDetails = {
        method: error.config?.method || "N/A",
        url: error.request?.responseURL || "N/A",
        httpCode: error.response?.status || "N/A",
        httpStatus: error.response?.statusText || "Unknown error",
        request: {
          params: {
            query: error.config.params || null,
            data: error.config.data || null,
          },
        },
        response: error.response.data,
      };
      return Promise.reject(error);
    }
  },
);

export function useAxios() {

  const bgDownload = async (url, fileName = "fileName") => {
    try {
      const startTime = Date.now();
      const response = await axiosInstance.get(url, {
        responseType: "blob",
      });
      const blob = new Blob([response.data]);
      const tmpLink = document.createElement("a");
      const urlBlob = URL.createObjectURL(blob);
      tmpLink.href = urlBlob;
      tmpLink.download = fileName;
      document.body.appendChild(tmpLink);
      tmpLink.click();
      document.body.removeChild(tmpLink);
      URL.revokeObjectURL(urlBlob);
      const endTime = Date.now();
      return {
        fileName: fileName,
        url: url,
        mimeType: blob.type,
        length: blob.size,
        msTime: endTime - startTime,
      };
    } catch (error) {
      throw error;
    }
  };

  return { axiosInstance, bgDownload };
}
