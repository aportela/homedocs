const template = `
    <div class="notification is-danger">
    <p class="has-text-weight-bold has-text-centered"><i class="fa-solid fa-bug"></i> API ERROR</p>
    <pre class="mt-3 has-background-dark has-text-light is-size-7">{{ message }}</pre>
    </div>
`;

export default {
  name: "homedocs-notification-api-error",
  template: template,
  props: ["message"],
};
