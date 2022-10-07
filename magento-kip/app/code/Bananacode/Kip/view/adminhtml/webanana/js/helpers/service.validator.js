/**
 *
 * @param value
 * @returns {*|boolean}
 */
export const inputEmail = (value) => {
  return value && /(^\w.*@\w+\.\w)/.test(value);
};

/**
 *
 * @param value
 * @returns {boolean}
 */
export const inputEmpty = (value) => {
  return !value || value === "" || value === null;
};

/**
 *
 * @param value
 * @returns {boolean}
 */
export const inputPassword = (value) => {
  return value.length >= 5;
};

/**
 *
 * @param form
 * @returns {{properties: {}}}
 */
export const serialize = (form) => {
  // Setup our serialized data
  const serialized = {
    properties: {},
  };
  const replace = "properties[";

  // Loop through each field in the form
  for (let i = 0; i < form.elements.length; i++) {
    const field = form.elements[i];

    // Don't serialize fields without a name, submits, buttons, file and reset inputs, and disabled fields
    if (
      !field.name ||
      field.disabled ||
      field.type === "file" ||
      field.type === "reset" ||
      field.type === "submit" ||
      field.type === "button"
    )
      continue;

    // If a multi-select, get all selections
    if (field.type === "select-multiple") {
      for (let n = 0; n < field.options.length; n++) {
        if (!field.options[n].selected) continue;

        if (field.name.includes(replace)) {
          serialized.properties[
            field.name.replace(replace, "").replace("]", "")
          ] = field.options[n].value;
        } else {
          serialized[field.name] = field.options[n].value;
        }
      }
    } else if (
      (field.type !== "checkbox" && field.type !== "radio") ||
      field.checked
    ) {
      if (field.name.includes(replace)) {
        serialized.properties[
          field.name.replace(replace, "").replace("]", "")
        ] = field.value;
      } else {
        serialized[field.name] = field.value;
      }
    }
  }

  return serialized;
};
