<template>
  <div
    ref="colorPicker"
    :id="`colorPicker${id}`"
    class="input-group color-picker-component w-fit"
  >
    <div class="d-flex justify-content-start align-items-center">
      <input
        ref="fakePicker"
        :id="id"
        :name="name"
        type="color"
        class="form-control form-control-color"
        :value="exportColor"
        @click.prevent="togglePicker()"
        :disabled="disabled||isNull"
      >
      <div
        v-if="nullable"
        class="form-check ms-5"
      >
        <input
          ref="nullableInput"
          class="form-check-input"
          type="checkbox"
          id="colorPickerNullable"
          @click="isNull=!isNull"
        >
        <label
          class="form-check-label"
          for="colorPickerNullable"
        >
          {{ trans.methods.__('bo_other_color_empty') }}
        </label>
      </div>
      <div :class="['position-absolute top-100 left-0 z-2', displayPicker ? 'd-inline-block' : 'd-none']">
        <Sketch
          ref="popupPicker"
          v-model="internalColor"
          :preset-colors="presetColors"
        />
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import type { Payload } from "@ckpack/vue-color";
import { Sketch } from "@ckpack/vue-color";
import type { ColorInput } from "@ctrl/tinycolor";
import type { PropType } from "vue";
import { computed, onMounted, ref, useAttrs, watch } from "vue";
import { Tooltips } from "./../../modules/tooltips";
import trans from "./../../modules/trans";

// * EMITS
const emits = defineEmits<{
  updateColor: [inputColor: HTMLInputElement|null],
}>();

defineOptions({
  name: "ColorPickerComponent"
});

// * ATTRIBUTES
const attrs = useAttrs();

// * REFS
const colorPicker = ref<HTMLDivElement|null>(null);
const fakePicker = ref<HTMLInputElement|null>(null);
const popupPicker = ref<HTMLInputElement|null>(null);
const nullableInput = ref<HTMLInputElement|null>(null);

// * PROPS
const props = defineProps({
  id: {
    type: String,
    default: String(Math.pow(10, 16) / Math.random())
  },
  name: {
    type: String,
    default: "",
  },
  /** Set default colors. */
  presetColors: {
    type: Array as PropType<Array<string>>,
    default: () => { return [
      "#D0021B", "#F5A623", "#F8E71C", "#8B572A", "#7ED321", "#417505", "#BD10E0", "#9013FE",
      "#4A90E2", "#0016FF", "#50E3C2", "#B8E986", "#000000", "#4A4A4A", "#9B9B9B", "#FFFFFF",
    ]; }
  },
  value: {
    type: String,
    default: "",
  },
  nullable: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

// * DATA
const id = ref<string>(props.id);
const name = ref<string>(props.name);
const presetColors = ref<Array<string>>(props.presetColors);
const exportColor = ref<string|null>(props.value);
const internalColor = ref<ColorInput>(props.value);
const displayPicker = ref<boolean>(false);
const nullable = ref<boolean>(props.nullable);
const disabled = ref<boolean>(props.disabled);
const isNull = ref<boolean>(false);
const tooltips = ref<Tooltips|null>(null);

// * COMPUTED
const isUsedWithProps = computed<boolean>(() => attrs.json === undefined);

// * LIFECYCLE
onMounted((): void => {
  const json = String(attrs.json ?? "{}"),
        data = JSON.parse(json);
  if (!isUsedWithProps.value) {
    id.value = String(data.id);
    name.value = String(data.name);
    internalColor.value = String(data.value);
    nullable.value = Boolean(data.nullable);
    disabled.value = Boolean(data.disabled);
    (data.presetColors) ? presetColors.value = data.presetColors : "";
  }
  initTooltips();
});

// * WATCHERS

/**
  * Internal color changes.
  * @return void
  */
watch(internalColor, (): void => {
  if (typeof internalColor.value === "string") {
    exportColor.value = internalColor.value;
    return;
  }
  exportColor.value = (internalColor.value as unknown as Payload).hex;
});

/**
 * Nullable input changes,
 * Set the input color to null or default color (here the first preset color).
  * @return void
 */
watch(isNull, (): void => {
  if (!nullableInput.value?.checked) {
    exportColor.value = presetColors.value[0];
    internalColor.value = presetColors.value[0];
    return;
  }
  exportColor.value = null;
});

/**
  * Emitting events when popup picker is hidden.
  * @return void
  */
watch(displayPicker, (): void => {
  if (displayPicker.value === false)
    emits("updateColor", fakePicker.value);
});

// * METHODS

/**
  * Display or not the popup picker.
  * @return void
  */
function togglePicker(): void {
  displayPicker.value = !displayPicker.value;
  (displayPicker.value) ? document.addEventListener("click", displayPickerColor) : document.removeEventListener("click", displayPickerColor);
}

/**
  * Hide the popup picker when click outside.
  * @return void
  */
function displayPickerColor(e: Event): void {
  if ((e.target as HTMLElement).closest(`#colorPicker${id.value}`)?.id !== colorPicker.value?.id) {
    togglePicker();
  }
}

/**
  * Initialise all tooltips in the component.
  * @return void
  */
function initTooltips(): void {
  setTimeout(() => {
    tooltips.value = Tooltips.make({
      type: "dom",
      elements: colorPicker.value!.querySelectorAll("[data-bs-toggle=\"tooltip\"]")
    });
  }, 500);
}
</script>

<style lang="scss" scopped>
@import "bootstrap/scss/functions";
@import "bootstrap/scss/variables";
@import "bootstrap/scss/mixins";

.color-picker-component {
  position: relative;
  .vc-sketch {
    background-color: var(--bs-tertiary-bg);
  }
  .vc-sketch-sliders, .vc-sketch-field {
    margin-top: 10px;
    padding: 0;
  }
  .vc-sketch-sliders {
    justify-content: center;
    align-items: center;
    display: flex;
    height: 16px;
  }
  .vc-sketch-hue-wrap {
    width: 100%;
  }
  .vc-sketch-alpha-wrap, .vc-sketch-color-wrap, .vc-sketch-field--single {
    display: none;
  }
  .vc-sketch-color-wrap .vc-checkerboard {
    background-image: none !important;
  }
  .vc-sketch-active-color, .vc-sketch-saturation-wrap {
    border-radius: 3px;
  }
  .vc-sketch-hue-wrap, .vc-hue-pointer, .vc-hue-picker {
    height: 100%;
  }
  .vc-hue-picker {
    margin: 0;
  }
  .vc-saturation-circle {
    box-shadow: 0 0 0 1.2px #fff,inset 0 0 1px 1px rgba(0,0,0,.3),0 0 1px 2px rgba(0,0,0,.4)
  }
  .vc-editable-input {
    display: flex;
    flex-direction: row-reverse;
    align-items: center;
    justify-content: center;
  }
  .vc-input__label, .vc-input__input {
    font-size: 1rem !important;
  }
  .vc-input__label {
    color: var(--bs-body-color) !important;
  }
  .vc-input__input {
    padding: .375rem .75rem !important;
    margin-left: 0.5rem;
    color: var(--bs-body-color);
    background-color: var(--bs-body-bg);
    border: var(--bs-border-width) solid var(--bs-border-color) !important;
    border-radius: var(--bs-border-radius);
    box-shadow: none !important;
    transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out !important;
  }
  .vc-input__input:focus {
    color: var(--bs-body-color);
    background-color: var(--bs-body-bg);
    border-color: var(--bs-primary-border-subtle) !important;
    outline: 0;
    box-shadow: 0 0 0 .25rem rgba(var(--bs-primary-rgb), var(--bs-focus-ring-opacity)) !important;
  }
  .vc-sketch-presets {
    border: none !important;
  }
}
</style>
