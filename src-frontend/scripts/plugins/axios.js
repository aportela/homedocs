import axios from "axios";

export default {
  install: (app, options) => {
    let axiosInstance = axios.create(options);
    const jwt = app.config.globalProperties.$localStorage.get("jwt");
    axiosInstance.interceptors.request.use(
      (config) => {
        if (jwt) {
          config.headers["HOMEDOCS-JWT"] = jwt;
          config.withCredentials = true;
        }
        return config;
      },
      (error) => {
        return Promise.reject(error);
      }
    );
    axiosInstance.interceptors.response.use(
      (response) => {
        // warning: axios lowercase received header names
        const apiResponseJWT = response.headers["homedocs-jwt"] || null;
        if (apiResponseJWT) {
          if (apiResponseJWT && apiResponseJWT != jwt) {
            app.config.globalProperties.$localStorage.set(
              "jwt",
              apiResponseJWT
            );
          }
        }
        return response;
      },
      (error) => {
        // helper for checking invalid fields on api response
        error.isFieldInvalid = function (fieldName) {
          return (
            error.response.data.invalidOrMissingParams.indexOf(fieldName) > -1
          );
        };

        error.response.getApiErrorData = function () {
          return JSON.stringify(
            {
              url: error.request.responseURL,
              response: error.response,
            },
            null,
            "\t"
          );
        };

        return Promise.reject(error);
      }
    );
    app.config.globalProperties.$axios = axiosInstance;
  },
};
