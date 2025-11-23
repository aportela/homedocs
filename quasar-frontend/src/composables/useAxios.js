import axios from "axios";
import { useSessionStore } from "src/stores/session";
import { useServerEnvironmentStore } from "src/stores/serverEnvironment";

export function useAxios() {
  const session = useSessionStore();

  const serverEnvironment = useServerEnvironmentStore();

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
          session.setJWT(apiResponseJWT);
        }
      }
      if (response.data.initialState) {
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

  const bgDownload = async (url, fileName = "fileName") => {
    try {
      const startTime = Date.now();
      const response = await axios.get(url, {
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

  return { axios, bgDownload };
}
