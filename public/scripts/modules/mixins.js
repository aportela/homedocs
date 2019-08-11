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
            return(dayjs.unix(timestamp).format("YYYY-MM-DD HH:mm:ss"));
        }
    },
};
