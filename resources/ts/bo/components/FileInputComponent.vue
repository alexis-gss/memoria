<template>
  <div
    ref="fileInput"
    class="file-input-vue p-0"
  >
    <div class="input-group">
      <button
        @click.prevent="chooseAFile"
        class="btn btn-secondary z-1"
        type="button"
        data-bs-toggle="tooltip"
        :title="trans.methods.__('bo_tooltip_file_input_modify_source')"
      >
        <FontAwesomeIcon icon="fa-solid fa-folder-open" />
      </button>
      <input
        :id="parameters.id"
        ref="inputFile"
        @change="fileInputHasChanged"
        type="file"
        class="d-none"
        :name="`${fileParamIsFile ? parameters.name : ''}`"
        :accept="parameters.accept"
        :aria-describedby="`Help${parameters.id}`"
      >
      <input
        v-if="typeof fileOutput === 'string'"
        class="d-none"
        :name="parameters.name"
        type="text"
        :value="fileOutput"
      >
      <input
        @click.prevent="chooseAFile"
        type="text"
        role="button"
        :value="fileParameters.fileName"
        class="form-control"
        data-bs-toggle="tooltip"
        :title="trans.methods.__('bo_tooltip_file_input_modify_source')"
        :aria-describedby="`Help${parameters.id}`"
        readonly
      >
      <button
        v-if="!parameters.required && hasFile"
        @click.prevent="resetComponent"
        class="btn btn-danger"
        type="button"
        data-bs-toggle="tooltip"
        :title="trans.methods.__('bo_tooltip_file_input_remove')"
      >
        <FontAwesomeIcon icon="fa-solid fa-eraser" />
      </button>
    </div>
    <small
      :id="`Help${parameters.id}`"
      class="form-text text-body-secondary"
    >
      {{ parameters.helper }}
      <template v-if="fileParamIsFile && fileParameters.file">
        {{ trans.methods.__('bo_file_input_file_size', { size: humanFileSize((fileParameters.file as File).size) }) }}
      </template>
    </small>
  </div>
</template>

<script lang="ts" setup>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { computed, onMounted, reactive, ref, useAttrs } from "vue";
import { Tooltips } from "../../modules/tooltips";
import trans from "../../modules/trans";

type FileOutputValue = File | string | null;

// * CONSTANTS
const UNITS = [
  "byte",
  "kilobyte",
  "megabyte",
  "gigabyte",
  "terabyte",
  "petabyte",
];
const BYTES_PER_KB = 1024;

defineOptions({
  name: "FileInputComponent"
});

// * EMITS
const emits = defineEmits<{
  file: [file: File | null],
}>();

// * ATTRIBUTES
const attrs = useAttrs();

// * REFS
const fileInput = ref<HTMLDivElement | null>(null);
const inputFile = ref<HTMLInputElement | null>(null);

// * PROPS
const props = defineProps({
  id: {
    type: String,
    default: String(Math.pow(10, 16) / Math.random()),
  },
  name: {
    type: String,
    default: "",
  },
  /**
   * Pre-existing value.
   * Can be a plain string path or an object with at least { path: string, label?: string }.
   */
  value: {
    type: String,
    default: null,
  },
  helper: {
    type: String,
    default: "",
  },
  required: {
    type: Boolean,
    default: true,
  },
  placeholder: {
    type: String,
    default: "",
  },
  /**
   * Accepted MIME types / extensions for the native file picker.
   * Example: ".mp3,audio/mpeg" or ".pdf,application/pdf"
   */
  accept: {
    type: String,
    default: "*",
  },
});

// * DATA
const tooltips = ref<Tooltips | null>(null);
const hasFile = ref<boolean>(false);
const fileOutput = ref<FileOutputValue>(null);

const parameters = reactive<{
  id: string;
  name: string;
  helper: string;
  required: boolean;
  placeholder: string;
  accept: string;
}>({
  id: props.id,
  name: props.name,
  helper: props.helper,
  required: props.required,
  placeholder: props.placeholder,
  accept: props.accept,
});

const fileParameters = reactive<{
  file: File | null;
  filePath: string;
  fileName: string;
}>({
  file: null,
  filePath: "",
  fileName: "",
});

// * COMPUTED
const isUsedWithProps = computed<boolean>(() => attrs.json === undefined);
const fileParamIsFile = computed<boolean>(() => fileParameters.file instanceof File);

// * LIFECYCLE
onMounted((): void => {
  const json = String(attrs.json ?? "{}"),
        data = JSON.parse(json);

  if (!isUsedWithProps.value) {
    parameters.id          = data.id;
    parameters.name        = data.name;
    parameters.helper      = data.helper ?? "";
    parameters.required    = Boolean(data.required);
    parameters.placeholder = data.placeholder ?? "";
    parameters.accept      = data.accept ?? "*";

    resolveValue(data.value);
  } else {
    resolveValue(props.value);
  }

  initTooltips();
});

// * METHODS

/**
  * Resolve the initial value — accepts:
  *   - null / undefined  → no file
  *   - string            → plain file path
  *   - object            → model with at least { path: string, label?: string }
  * @return void
  */
function resolveValue(value: unknown): void {
  if (!value) {
    fileParameters.fileName = parameters.placeholder || trans.methods.__("bo_tooltip_file_input_modify_source");
    return;
  }

  let filePath = "";
  let fileLabel = "";

  if (typeof value === "string" && value.length) {
    filePath  = value;
    fileLabel = value.split("/").pop() ?? value;
  } else if (typeof value === "object" && value !== null && "path" in value) {
    const model = value as { path: string; label?: string };
    filePath  = model.path;
    fileLabel = model.label ?? model.path.split("/").pop() ?? model.path;
  }

  if (!filePath.length) {
    fileParameters.fileName = parameters.placeholder || trans.methods.__("bo_tooltip_file_input_modify_source");
    return;
  }

  fileParameters.filePath = filePath;
  fileParameters.fileName = fileLabel;
  fileOutput.value        = filePath;
  hasFile.value           = true;
}

/**
  * Open the native file picker.
  * @return void
  */
function chooseAFile(): void {
  inputFile.value!.click();
}

/**
  * Handle file input change.
  * @return void
  */
function fileInputHasChanged(): void {
  if (inputFile.value?.files && inputFile.value.files[0]) {
    const file = inputFile.value.files[0];
    useThis(file);
    fileOutput.value = file;
  }
}

/**
  * Persist a selected File.
  * @return void
  */
function useThis(file: File): void {
  fileParameters.file     = file;
  fileParameters.fileName = file.name;
  hasFile.value           = true;
  emits("file", file);
}

/**
  * Reset the component to its initial state.
  * @return void
  */
function resetComponent(): void {
  fileOutput.value            = null;
  inputFile.value!.value      = "";
  fileParameters.fileName     = parameters.placeholder || trans.methods.__("bo_tooltip_file_input_modify_source");
  fileParameters.filePath     = "";
  fileParameters.file         = null;
  hasFile.value               = false;
  emits("file", null);
}

/**
  * Convert bytes to a human-readable file size string.
  * @param sizeBytes
  * @return string
  */
function humanFileSize(sizeBytes: number | bigint): string {
  let size = Math.abs(Number(sizeBytes));
  let u = 0;
  while (size >= BYTES_PER_KB && u < UNITS.length - 1) {
    size /= BYTES_PER_KB;
    ++u;
  }
  return new Intl.NumberFormat([], {
    style: "unit",
    unit: UNITS[u],
    unitDisplay: "short",
    maximumFractionDigits: 1,
  }).format(size);
}

/**
  * Initialise all tooltips in the component.
  * @return void
  */
function initTooltips(): void {
  setTimeout(() => {
    tooltips.value = Tooltips.make({
      type: "dom",
      elements: fileInput.value!.querySelectorAll("[data-bs-toggle=\"tooltip\"]"),
    });
  }, 500);
}
</script>

<style lang="scss" scoped>
.file-input-vue {
  input.form-control {
    direction: ltr;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    cursor: pointer;
  }
}
</style>
