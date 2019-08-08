let module = {
    invalidFields: [],
    clear: function () {
        module.invalidFields = [];
    },
    setInvalid: function (field, message) {
        module.invalidFields.push({ field: field, message: message });
    },
    hasInvalidFields: function () {
        return (module.invalidFields.length > 0);
    },
    hasInvalidField: function (field) {
        return (module.invalidFields.findIndex(e => e.field == field) > -1);
    },
    getInvalidFieldMessage: function (field) {
        let idx = module.invalidFields.findIndex(e => e.field == field);
        if (idx > -1) {
            return (module.invalidFields[idx].message);
        } else {
            return (null);
        }
    }
};

export default module;