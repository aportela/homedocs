export default {
    install: (app, options) => {
        let invalidFields = [];
        let a = 1;
        app.config.globalProperties.$validator = {
            clear: function () {
                invalidFields = [];
            },
            setInvalid: function (field, message) {
                invalidFields.push({ field: field, message: message });
            },
            hasInvalidFields: function () {
                return (invalidFields.length > 0);
            },
            hasInvalidField: function (field) {
                return (invalidFields.findIndex(e => e.field == field) > -1);
            },
            getInvalidFieldMessage: function (field) {
                let idx = invalidFields.findIndex(e => e.field == field);
                if (idx > -1) {
                    return (invalidFields[idx].message);
                } else {
                    return (null);
                }
            }
        };
    }
}