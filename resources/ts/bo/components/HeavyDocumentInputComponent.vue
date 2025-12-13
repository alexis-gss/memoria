<template>
  <div
    ref="imageHeavyInput"
    :class="`image-heavy-input image-heavy-input-${id} row w-100 mx-auto`"
  >
    <!-- CHOOSE FILE -->
    <div
      class="col-12 pe-3"
      :class="[{'col-md-6 mb-3': !isUsedWithProps}]"
    >
      <label
        v-if="!isUsedWithProps"
        for="documentInputFile"
        class="col-form-label"
      >
        <b>{{ trans.methods.__('bo_label_choose_picture') }} *</b>
      </label>
      <div :class="[{'d-none': isUploading}]">
        <div class="input-group">
          <button
            @click.prevent="inputFile?.click()"
            class="btn btn-sm btn-secondary"
            type="button"
            data-bs-tooltip="tooltip"
            :title="trans.methods.__('bo_tooltip_image_input_modify_source')"
          >
            <FontAwesomeIcon icon="fa-solid fa-folder-open" />
          </button>
          <input
            type="text"
            class="form-control form-control-sm"
            role="button"
            ref="inputFile"
            :value="
              chunkFile?.label
                ? chunkFile?.label
                : trans.methods.__('bo_label_choose_picture')
            "
            data-bs-tooltip="tooltip"
            :title="trans.methods.__('bo_tooltip_image_input_modify_source')"
            readonly
          >
          <button
            v-if="chunkFileLoaded"
            class="btn btn-sm btn-success"
            type="button"
            :title="trans.methods.__('bo_tooltip_image_input_saved')"
            data-bs-tooltip="tooltip"
          >
            <FontAwesomeIcon icon="fa-solid fa-check" />
          </button>
          <button
            @click.prevent="viewImageSource()"
            class="btn btn-sm btn-warning"
            type="button"
            data-bs-toggle="modal"
            :data-bs-target="`#modalViewer${id}`"
            :title="trans.methods.__('bo_tooltip_image_input_preview_image')"
            data-bs-tooltip="tooltip"
          >
            <FontAwesomeIcon icon="fa-solid fa-eye" />
          </button>
        </div>
        <Transition name="fade">
          <p
            v-if="message"
            class="m-0 text-danger"
          >
            {{ message }}
          </p>
        </Transition>
      </div>
      <div
        class="progress w-100 my-1"
        :class="[{'d-none': !isUploading}]"
      >
        <div
          class="progress-bar progress-bar-striped progress-bar-animated"
          ref="progressBar"
          role="progressbar"
          aria-label="Progress bar"
          aria-valuenow="0"
          aria-valuemin="0"
          aria-valuemax="100"
        >
          {{ filePercent }}%
        </div>
      </div>
      <small
        v-if="!isUsedWithProps"
        class="form-text text-body-secondary"
      >
        {{ helper }}
      </small>
    </div>
    <input
      class="d-none"
      :id="'inputUuid-' + id"
      name="uuid[]"
    >
    <input
      class="d-none"
      :id="'inputLabel-' + id"
      name="label[]"
    >
  </div>
  <!-- VIEWER MODAL -->
  <div
    :id="`modalViewer${id}`"
    class="modal"
    tabindex="-1"
    role="dialog"
    data-bs-backdrop="static"
    data-bs-keyboard="false"
  >
    <div
      class="modal-dialog modal-xl d-flex align-items-center h-100 my-0 py-4"
    >
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            {{ trans.methods.__('bo_label_preview_image') }}
          </h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="modal"
            :aria-label="trans.methods.__('bo_tooltip_image_input_close')"
            :title="trans.methods.__('bo_tooltip_image_input_close')"
            data-bs-tooltip="tooltip"
          />
        </div>
        <div class="modal-body">
          <div
            v-if="viewerLoadImage"
            class="text-center w-100"
          >
            <div
              class="spinner-border text-secondary"
              role="status"
            >
              <span class="visually-hidden">
                {{ trans.methods.__('bo_tooltip_viewer_loading') }}
              </span>
            </div>
          </div>
          <img
            class="mw-100 mh-100"
            :class="[{'d-none': viewerLoadImage}]"
            ref="imgViewer"
            src=""
          >
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import Resumable from "resumablejs";
import type { PropType } from "vue";
import { computed, onMounted, ref, useAttrs, nextTick } from "vue";
import route from "./../../modules/route";
import { Tooltips } from "./../../modules/tooltips";
import trans from "./../../modules/trans";

defineOptions({
  name: "HeavyDocumentInputComponent"
});

// * ATTRIBUTES
const attrs = useAttrs();

// * REFS
const imageHeavyInput = ref<HTMLDivElement|null>(null);
const inputFile = ref<HTMLDivElement|null>(null);
const progressBar = ref<HTMLDivElement|null>(null);
const imgViewer = ref<HTMLImageElement|null>(null);

// * PROPS
const props = defineProps({
  id: {
    type: String,
    default: String(Math.pow(10, 16) / Math.random())
  },
  file: {
    type: Object as PropType<ChunkFile>,
    default: null,
  },
  csrf: {
    type: String,
    default: "",
  },
  helper: {
    type: String,
    default: "",
  },
  gameslug: {
    type: String,
    default: "",
  }
});

// * DATA
const id = ref<string>(props.id);
const message = ref<string|null>(null);
const success = ref<boolean>(false);
const isUploading = ref<boolean>(false);
const filePercent = ref<number>(0);
const resumableJS = ref<ResumableJS|null>(null);
const chunkFile = ref<ChunkFile|null>(null);
const chunkFileLoaded = ref<boolean>(false);
const csrf = ref<string>(props.csrf);
const helper = ref<string>(props.helper);
const gameSlug = ref<string>(props.gameslug);
const viewerLoadImage = ref<boolean>(false);
const inputUuid = ref<HTMLInputElement|null>(null);
const inputLabel = ref<HTMLInputElement|null>(null);
const tooltips = ref<Tooltips|null>(null);

// * COMPUTED
const isUsedWithProps = computed<boolean>(() => attrs.json === undefined);

// * LIFECYCLE
onMounted((): void => {
  const json = String(attrs.json ?? "{}"),
        data = JSON.parse(json);
  if (!isUsedWithProps.value) {
    id.value = data.id;
    csrf.value = data.csrf;
    helper.value = data.helper;
    gameSlug.value = data.gameslug;
  }
  initResumable();
  if (props.file !== null) {
    if (!props.file.published) {
      resumableJS.value?.addFile(props.file as unknown as File);
      resumableJS.value?.upload();
    } else {
      chunkFile.value = props.file;
      setTimeout(() => editImageAttribute(), 100);
    }
  }
  nextTick(() => {
    setInputsFields();
    initTooltips();
  });
});

/**
  * Init resumableJS.
  * @return void
  */
function initResumable(): void {
  resumableJS.value = new Resumable({
    chunkSize: 8 * 1024 * 1024, // 8MB.
    simultaneousUploads: 5,
    maxFiles: 75,
    testChunks: false,
    target: getUploadDocumentRoute(),
    query: {
      gameSlug: gameSlug.value,
    },
    headers: {
      "X-CSRF-TOKEN": csrf.value,
    },
  });
  resumableJS.value.on("fileAdded", fileAdded);
  resumableJS.value.on("fileSuccess", fileSuccess);
  resumableJS.value.on("fileError", fileError);
  resumableJS.value.on("fileProgress", () => {
    if (resumableJS.value !== null) {
      filePercent.value = Math.floor(resumableJS.value.progress() * 100);
      progressBar.value!.style.width = filePercent.value + "%";
    }
  });
  resumableJS.value?.assignBrowse(inputFile.value as Element, false);
}

/**
  * Get upload heavy document route.
  * @return string
  */
function getUploadDocumentRoute(): string {
  const uploadDocumentRoute = route.methods.route("bo.pictures.upload");
  if (!uploadDocumentRoute) {
    throw new Error("Undefined route bo.pictures.upload");
  }
  return uploadDocumentRoute;
}

/**
  * Add file who will be store.
  * @param file The file would like to store.
  * @return void
  */
function fileAdded(): void {
  chunkFileLoaded.value = false;
  isUploading.value = true;
  setTimeout(() => {
    progressBar.value!.style.width = filePercent.value + "%";
  }, 10);
  tooltips.value?.closeBootstrapTooltip();
  resumableJS.value?.upload();
}

/**
  * Run method if the file is stored.
  * @param file The file stored.
  * @param message Set a new successful message.
  * @return void
  */
function fileSuccess(file: { file: File }, message: string): void {
  let result = JSON.parse(message);
  chunkFile.value = file.file as unknown as ChunkFile;
  chunkFile.value.uuid = result.uid;
  chunkFile.value.label = file.file.name.replace(/\.[^/.]+$/, "");
  success.value = true;
  chunkFileLoaded.value = true;
  progressBar.value!.innerHTML = String(0 + "%");
  setTimeout(() => {
    isUploading.value = false;
    nextTick(() => {
      editImageAttribute();
      tooltips.value?.closeBootstrapTooltip();
      initTooltips();
    });
  }, 800);
}

/**
  * Set a new error message.
  * @return void
  */
function fileError(): void {
  message.value = trans.methods.__("bo_other_chunk_failed");
}

/**
  * View document source in the modal.
  * @return void
  */
function viewImageSource(): void {
  viewerLoadImage.value = true;
  setTimeout(() => {
    imgViewer.value!.src =
      "/storage/pictures/" +
      gameSlug.value +
      "/" +
      chunkFile.value?.uuid +
      ".webp";
    imgViewer.value!.onload = () => {
      viewerLoadImage.value = false;
    };
  }, 10);
}

/**
  * Set inputs fields.
  * @return void
  */
function setInputsFields(): void {
  inputUuid.value = document.getElementById(
    "inputUuid-" + id.value
  ) as HTMLInputElement|null;
  inputLabel.value = document.getElementById(
    "inputLabel-" + id.value
  ) as HTMLInputElement|null;
}

/**
  * Edit document data.
  * @return void
  */
function editImageAttribute(): void {
  if (inputUuid.value != null && inputLabel.value != null) {
    inputUuid.value.value = chunkFile.value?.uuid ?? "";
    inputLabel.value.value = chunkFile.value?.label ?? "";
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
      elements: imageHeavyInput.value!.querySelectorAll("[data-bs-toggle=\"tooltip\"]")
    });
  }, 500);
}
</script>
