<template>
  <button
    ref="passwordVisibility"
    @click.prevent="toggleVisibility()"
    @keydown="keydown"
    :class="`btn btn-primary rounded-0 rounded-end password-visibility password-visibility-${id} h-100 border-0 p-0 overflow-hidden`"
    :aria-pressed="visible"
  >
    <span
      :class="[
        'text-bg-primary reveal d-flex justify-content-center align-items-center h-100',
        {'d-none': visible}
      ]"
      :title="trans.methods.__('bo_tooltip_password_hide')"
      data-bs-tooltip="tooltip"
    >
      <FontAwesomeIcon
        icon="fas fa-eye-slash"
        :aria-hidden="true"
      />
    </span>
    <span
      ref="passwordVisibilitySpan"
      :class="[
        'text-bg-primary reveal d-flex justify-content-center align-items-center h-100',
        {'d-none': !visible}
      ]"
      :title="trans.methods.__('bo_tooltip_password_show')"
      data-bs-tooltip="tooltip"
    >
      <FontAwesomeIcon
        icon="fas fa-eye"
        :aria-hidden="true"
      />
    </span>
  </button>
</template>

<script lang="ts" setup>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { onMounted, ref, useAttrs } from "vue";
import { Tooltips } from "./../../modules/tooltips";
import trans from "./../../modules/trans";

defineOptions({
  name: "PasswordVisibilityComponent"
});

// * ATTRIBUTES
const attrs = useAttrs();

// * REFS
const passwordVisibility = ref<HTMLButtonElement|null>(null);

// * DATA
const id = ref<string>("");
const input = ref<HTMLInputElement>(document.createElement("input"));
const visible = ref<boolean>(true);
const tooltips = ref<Tooltips | null>(null);

// * LIFECYCLE
onMounted((): void => {
  const json = String(attrs.json ?? "{}"),
        data = JSON.parse(json);
  id.value = data.id;
  input.value = document.getElementById(id.value) as HTMLInputElement;
  if (!input.value) {
    throw new Error(`Cannot find input id '${id.value}'`);
  }
  if (input.value.tagName !== "INPUT") {
    throw new Error(`id '${id.value}' is not an input`);
  }
  if ((input.value as HTMLInputElement).type !== "password") {
    throw new Error(`id '${id.value}' is not an input password`);
  }
  initTooltips();
});

// * METHODS

/**
  * Set event on key down.
  * @return void
  */
function keydown(e: KeyboardEvent): void {
  if (
    e.key === " " ||
    e.code === "Space" ||
    e.keyCode === 32 ||
    e.key === "Enter" ||
    e.code === "Enter" ||
    e.keyCode === 13
  ) {
    toggleVisibility();
    e.preventDefault();
  }
}

/**
  * Update password status (visible, hidden).
  * @return void
  */
function toggleVisibility(): void {
  input.value.type = (input.value.type === "password") ? "text" : "password";
  visible.value = (input.value.type === "password") ? true  : false;
}

/**
  * Initialise all tooltips in the component.
  * @return void
  */
function initTooltips(): void {
  setTimeout(() => {
    tooltips.value = Tooltips.make({
      type: "dom",
      elements: passwordVisibility.value!.querySelectorAll("[data-bs-tooltip=\"tooltip\"]")
    });
  }, 500);
}
</script>

<style lang="scss" scopped>
.password-visibility .reveal {
  width: 52px;
}
</style>
