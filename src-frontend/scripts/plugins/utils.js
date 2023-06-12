import dayjs from "dayjs";

export default {
  install: (app, options) => {
    app.config.globalProperties.$utils = {
      /**
       * uuid v4 generator (require ES6 crypto api)
       * (broofa) https://stackoverflow.com/a/2117523
       * @returns
       */
      uuid: function () {
        return ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, (c) =>
          (
            c ^
            (crypto.getRandomValues(new Uint8Array(1))[0] & (15 >> (c / 4)))
          ).toString(16)
        );
      },
      /**
       * get human file size
       * (mpen) https://stackoverflow.com/a/14919494
       * @param {*} bytes
       * @param {*} si
       * @returns
       */
      humanFileSize: function (bytes, si) {
        var thresh = si ? 1000 : 1024;
        if (Math.abs(bytes) < thresh) {
          return bytes + " B";
        }
        var units = si
          ? ["kB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"]
          : ["KiB", "MiB", "GiB", "TiB", "PiB", "EiB", "ZiB", "YiB"];
        var u = -1;
        do {
          bytes /= thresh;
          ++u;
        } while (Math.abs(bytes) >= thresh && u < units.length - 1);
        return bytes.toFixed(1) + " " + units[u];
      },
      isImage: function (filename) {
        if (filename) {
          return filename.match(/.(jpg|jpeg|png|gif)$/i);
        } else {
          return false;
        }
      },
      timestamp2HumanDateTime: function (timestamp) {
        if (timestamp) {
          return dayjs.unix(timestamp).format("YYYY-MM-DD HH:mm:ss");
        } else {
          return null;
        }
      },
    };
  },
};
