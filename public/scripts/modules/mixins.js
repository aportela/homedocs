import { humanFileSize as humanFileSize } from './utils.js';

export const mixinFiles = {
    filters: {
        humanFileSize: function(size) {
            return(humanFileSize(size, true));
        }
    },
};

export const mixinDateTimes = {
    filters: {
        timestamp2HumanDateTime: function(timestamp) {
            if (timestamp) {
                return(dayjs.unix(timestamp).format("YYYY-MM-DD HH:mm:ss"));
            } else {
                return(null);
            }
        },
        timestampToNow: function(timestamp) {
            if (timestamp) {
                return(dayjs.unix(timestamp).format("YYYY-MM-DD HH:mm:ss").toNow());
            } else {
                return(null);
            }
        }
    },
};
