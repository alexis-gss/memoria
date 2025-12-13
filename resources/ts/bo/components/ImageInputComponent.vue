<template>
  <div
    ref="imageInput"
    class="image-input-vue p-0"
  >
    <!-- INPUT GROUP -->
    <div class="row">
      <!-- INPUT GROUP -->
      <div :class="['col-12 form-group', {'col-md-6': parameters.preview}]">
        <label
          v-if="parameters.preview && parameters.showLabels"
          :for="parameters.id"
          class="col-form-label fw-bold"
        >
          {{ trans.methods.__("bo_label_choose_picture") + ((parameters.required) ? ' *' : '') }}
          <span
            :title="trans.methods.__('bo_tooltip_image_input_choose_file')"
            data-bs-tooltip="tooltip"
          >
            <FontAwesomeIcon icon="fa-solid fa-circle-info" />
          </span>
        </label>
        <div class="input-group">
          <button
            @click.prevent="chooseAFile"
            class="btn btn-outline-secondary z-1"
            type="button"
            data-bs-tooltip="tooltip"
            :title="trans.methods.__('bo_tooltip_image_input_modify_source')"
          >
            <FontAwesomeIcon icon="fa-solid fa-folder-open" />
          </button>
          <!-- OUTPUT HTML INPUT -->
          <input
            :id="parameters.id"
            ref="inputFileImage"
            @change="pictureInputHasChanged"
            type="file"
            class="d-none"
            :name="`${fileParamIsFile ? parameters.name : ''}`"
            accept=".jpg,.jpeg,.gif,.png"
            :aria-describedby="`Help${parameters.id}`"
          >
          <input
            v-if="typeof fileOutput === 'string'"
            class="d-none"
            :name="parameters.name"
            type="text"
            :value="fileOutput"
          >
          <!-- END OUTPUT HTML INPUT -->
          <!-- FILENAME DISPLAY -->
          <input
            @click.prevent="chooseAFile"
            type="text"
            role="button"
            :value="fileParameters.fileName"
            class="form-control right-aligned"
            data-bs-tooltip="tooltip"
            :title="trans.methods.__('bo_tooltip_image_input_modify_source')"
            :aria-describedby="`Help${parameters.id}`"
            readonly
          >
          <button
            v-if="!parameters.required && hasPicture"
            @click.prevent="resetComponent"
            class="btn btn-outline-danger"
            type="button"
            data-bs-tooltip="tooltip"
            :title="trans.methods.__('bo_tooltip_image_input_remove_picture')"
          >
            <FontAwesomeIcon icon="fa-solid fa-eraser" />
          </button>
          <button
            v-if="!parameters.preview && fileDataUrl"
            class="btn btn-warning"
            type="button"
            data-bs-toggle="modal"
            :data-bs-target="`#ModalPreview${parameters.id}`"
            data-bs-tooltip="tooltip"
            :title="trans.methods.__('bo_tooltip_image_input_preview_image')"
          >
            <FontAwesomeIcon icon="fa-solid fa-eye" />
          </button>
          <button
            @click.prevent="prepareImageEditor()"
            :disabled="!hasPicture"
            :class="['btn', !hasCroppedPicture ? 'btn-primary' : 'btn-success']"
            type="button"
            :title="trans.methods.__('bo_tooltip_image_input_resize_image')"
            data-bs-tooltip="tooltip"
            data-bs-toggle="modal"
            :data-bs-target="`#Modal${parameters.id}`"
          >
            <FontAwesomeIcon icon="fa-solid fa-crop" />
          </button>
          <span
            v-if="hasCroppedPicture"
            class="input-group-text text-success"
            :title="trans.methods.__('bo_tooltip_image_input_image_resized')"
            data-bs-tooltip="tooltip"
          >
            <FontAwesomeIcon icon="fa-solid fa-wand-magic" />
          </span>
        </div>
        <small
          :id="`Help${parameters.id}`"
          class="form-text text-body-secondary"
        >
          {{ parameters.helper }}
        </small>
      </div>
      <!-- END INPUT GROUP -->
      <!-- INLINE PREVIEW PART -->
      <div
        v-if="parameters.preview"
        :class="['col-12 form-group d-flex flex-column', {'col-md-6': parameters.preview}]"
      >
        <p
          v-if="fileDataUrl && parameters.showLabels"
          :id="`PreviewHelp${parameters.id}`"
          class="col-form-label fw-bold m-0"
        >
          {{ trans.methods.__("bo_label_preview_image") }}
        </p>
        <div>
          <img
            v-if="fileDataUrl"
            class="img-fluid"
            :src="fileDataUrl"
            :alt="trans.methods.__('bo_other_preview_image_placeholder')"
            :aria-describedby="`PreviewHelp${parameters.id}`"
          >
        </div>
        <small
          v-if="fileParamIsFile"
          class="form-text text-body-secondary"
        >
          <span>{{ humanFileSize((fileParameters.file as File).size) }}</span>
          {{ `largeur ${fileParameters.dimensions.width}px` }}
          {{ `hauteur ${fileParameters.dimensions.height}px` }}
        </small>
      </div>
      <!-- END INLINE PREVIEW PART -->
    </div>
    <!-- END INPUT GROUP -->

    <!-- CROP MODAL -->
    <div
      :id="`Modal${parameters.id}`"
      class="modal"
      tabindex="-1"
      role="dialog"
      data-bs-backdrop="static"
      data-bs-keyboard="false"
      @dragstart.prevent="() => false"
      @ondrop.prevent="() => false"
      @dragover.prevent="() => false"
    >
      <div
        class="modal-dialog modal-xl"
        role="document"
      >
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ trans.methods.__("bo_title_edit_image_before_import") }}
            </h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              :aria-label="trans.methods.__('bo_tooltip_image_input_close_without_saving')"
              data-bs-tooltip="tooltip"
              :title="trans.methods.__('bo_tooltip_image_input_close_without_saving')"
            />
          </div>
          <div class="modal-body">
            <div class="container">
              <div class="row">
                <div class="col text-center">
                  <div class="btn-group me-4 mt-2">
                    <button
                      class="btn btn-warning"
                      :title="trans.methods.__('bo_tooltip_image_input_reset_image')"
                      data-bs-tooltip="tooltip"
                      @click.prevent="cropperReset"
                    >
                      <FontAwesomeIcon icon="fa-solid fa-sync-alt" />
                    </button>
                  </div>
                  <div class="btn-group me-4 mt-2">
                    <button
                      class="btn btn-primary"
                      :title="trans.methods.__('bo_tooltip_image_input_zoom_in')"
                      data-bs-tooltip="tooltip"
                      @click.prevent="cropperInstance?.zoom(0.1)"
                    >
                      <FontAwesomeIcon icon="fa-solid fa-search-plus" />
                    </button>
                    <a
                      class="btn btn-primary"
                      :title="trans.methods.__('bo_tooltip_image_input_zoom_out')"
                      data-bs-tooltip="tooltip"
                      @click.prevent="cropperInstance?.zoom(-0.1)"
                    >
                      <FontAwesomeIcon icon="fa-solid fa-search-minus" />
                    </a>
                  </div>
                  <div class="btn-group me-4 mt-2">
                    <a
                      class="btn btn-primary"
                      :title="trans.methods.__('bo_tooltip_image_input_move_left')"
                      data-bs-tooltip="tooltip"
                      @click.prevent="cropperInstance?.move(-10, 0)"
                    >
                      <FontAwesomeIcon icon="fa-solid fa-arrow-left" />
                    </a>
                    <a
                      class="btn btn-primary"
                      :title="trans.methods.__('bo_tooltip_image_input_move_right')"
                      data-bs-tooltip="tooltip"
                      @click.prevent="cropperInstance?.move(10, 0)"
                    >
                      <FontAwesomeIcon icon="fa-solid fa-arrow-right" />
                    </a>
                    <a
                      class="btn btn-primary"
                      :title="trans.methods.__('bo_tooltip_image_input_move_up')"
                      data-bs-tooltip="tooltip"
                      @click.prevent="cropperInstance?.move(0, -10)"
                    >
                      <FontAwesomeIcon icon="fa-solid fa-arrow-up" />
                    </a>
                    <a
                      class="btn btn-primary"
                      :title="trans.methods.__('bo_tooltip_image_input_move_down')"
                      data-bs-tooltip="tooltip"
                      @click.prevent="cropperInstance?.move(0, 10)"
                    >
                      <FontAwesomeIcon icon="fa-solid fa-arrow-down" />
                    </a>
                  </div>
                  <div class="btn-group me-4 mt-2">
                    <a
                      class="btn btn-primary"
                      :title="trans.methods.__('bo_tooltip_image_input_mirror_horizontal')"
                      data-bs-tooltip="tooltip"
                      @click.prevent="cropperScale('H')"
                    >
                      <FontAwesomeIcon icon="fa-solid fa-arrows-alt-h" />
                    </a>
                    <a
                      class="btn btn-primary"
                      :title="trans.methods.__('bo_tooltip_image_input_mirror_vertical')"
                      data-bs-tooltip="tooltip"
                      @click.prevent="cropperScale('V')"
                    >
                      <FontAwesomeIcon icon="fa-solid fa-arrows-alt-v" />
                    </a>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col">
                  <div class="form-group d-flex flex-row">
                    <div class="rotate-button">
                      <div class="btn-group mt-1">
                        <a
                          class="btn btn-primary"
                          :title="trans.methods.__('bo_tooltip_image_input_counterclockwise')"
                          data-bs-tooltip="tooltip"
                          @click.prevent="cropperRotate(-45)"
                        >
                          <FontAwesomeIcon icon="fa-solid fa-undo-alt" />
                        </a>
                        <a
                          class="btn btn-primary"
                          :title="trans.methods.__('bo_tooltip_image_input_clockwise')"
                          data-bs-tooltip="tooltip"
                          @click.prevent="cropperRotate(45)"
                        >
                          <FontAwesomeIcon icon="fa-solid fa-redo-alt" />
                        </a>
                      </div>
                    </div>
                    <div class="mt-1 ms-4 rotate-drag">
                      <label
                        for="range"
                        class="form-label me-4"
                      >
                        {{
                          trans.methods.__("bo_other_preview_rotation_degrees", {
                            deg: String(cropperParameters.rotation),
                          })
                        }}
                      </label>
                      <input
                        ref="rotationSlider"
                        @input.stop="cropperParameters.rotation = Number.parseInt(($event.target as HTMLInputElement).value)"
                        @change.stop="cropperParameters.rotation = Number.parseInt(($event.target as HTMLInputElement).value)"
                        type="range"
                        class="form-range"
                        id="range"
                        min="0"
                        max="360"
                        :value="cropperParameters.rotation"
                      >
                    </div>
                  </div>
                </div>
              </div>
              <div class="row mt-3">
                <div class="col-12 col-md-6 p-0 mb-3">
                  <img
                    class="image-modification"
                    ref="imgEditor"
                    src=""
                  >
                </div>
                <div
                  ref="previewBox"
                  class="col-12 col-md-6 p-0 preview-img"
                >
                  <div
                    :id="`Preview${parameters.id}`"
                    :style="previewStyle"
                  />
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
              :aria-label="trans.methods.__('bo_tooltip_image_input_modal_close_without_saving')"
              data-bs-tooltip="tooltip"
              :title="trans.methods.__('bo_tooltip_image_input_modal_close_without_saving')"
            >
              {{ trans.methods.__("bo_other_close") }}
            </button>
            <button
              @click.prevent="exportCropperFile"
              type="button"
              class="btn btn-primary"
              data-bs-dismiss="modal"
              :title="trans.methods.__('bo_tooltip_image_input_modal_close_with_saving')"
              data-bs-tooltip="tooltip"
            >
              {{ trans.methods.__("crud.actions.save").charAt(0).toUpperCase() + trans.methods.__("crud.actions.save").slice(1) }}
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- END CROP MODAL -->

    <!-- PREVIEW MODAL -->
    <div
      v-if="!parameters.preview && fileDataUrl"
      :id="`ModalPreview${parameters.id}`"
      class="modal"
      tabindex="-1"
      role="dialog"
      data-bs-backdrop="static"
      data-bs-keyboard="false"
    >
      <div
        class="modal-dialog modal-xl"
        role="document"
      >
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ trans.methods.__("bo_label_preview_image") }}
            </h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              :aria-label="trans.methods.__('bo_tooltip_image_input_close_without_saving')"
              data-bs-tooltip="tooltip"
              :title="trans.methods.__('bo_tooltip_image_input_close_without_saving')"
            />
          </div>
          <div class="modal-body">
            <div class="container">
              <div class="row">
                <div class="col-12 text-center d-flex flex-column">
                  <div>
                    <img
                      v-if="fileDataUrl"
                      class="img-fluid"
                      :src="fileDataUrl"
                      :alt="trans.methods.__('bo_other_preview_image_placeholder')"
                      :aria-describedby="`PreviewHelp${parameters.id}`"
                    >
                  </div>
                  <small
                    v-if="fileParamIsFile"
                    class="form-text text-body-secondary"
                  >
                    <span>{{ humanFileSize((fileParameters.file as File).size) }}</span>
                    {{ `largeur ${fileParameters.dimensions.width}px` }}
                    {{ `hauteur ${fileParameters.dimensions.height}px` }}
                  </small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- END PREVIEW MODAL -->
  </div>
</template>

<script lang="ts" setup>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import Cropper from "cropperjs";
import { computed, nextTick, onMounted, reactive, ref, useAttrs, watch } from "vue";
import { Tooltips } from "./../../modules/tooltips";
import trans from "./../../modules/trans";

/** If string the it is an absolute file path. */
type FileOutputValue = File|string|null;
type MirrorScale = 1|-1;

type CropperParameters = {
  /** Rotate the image to an absolute degree. */
  rotation: number,
  /** The scaling factor applies to the abscissa of the image. */
  scaleX: MirrorScale,
  /** The scaling factor applies to the ordinate of the image. */
  scaleY: MirrorScale
};

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
  name: "ImageInputComponent"
});

// * EMITS
const emits = defineEmits<{
  browseFile: [intId: string],
  imageFile: [file: File | null],
}>();

// * ATTRIBUTES
const attrs = useAttrs();

// * REFS
const imageInput = ref<HTMLDivElement|null>(null);
const previewBox = ref<HTMLDivElement|null>(null);
/** The inputfile to select a picture and send in form. */
const inputFileImage = ref<HTMLInputElement|null>(null);
const imgEditor = ref<HTMLImageElement|null>(null);

// * PROPS
const props = defineProps({
  id: {
    type: String,
    default: String(Math.pow(10, 16) / Math.random())
  },
  name: {
    type: String,
    default: ""
  },
  value: {
    type: String,
    default: ""
  },
  helper: {
    type: String,
    default: ""
  },
  required: {
    type: Boolean,
    default: true
  },
  /** Show above input file and picture bold text. */
  showLabels: {
    type: Boolean,
    default: true
  },
  /** Directly display the image preview or within a BS modal. */
  preview: {
    type: Boolean,
    default: true
  },
  /** Asks the component to just emit a browse event and not open the input file picker. */
  browseEvent: {
    type: Boolean,
    default: false
  },
  /** Picture dimmensions */
  width: {
    type: Number,
    default: 0
  },
  height: {
    type: Number,
    default: 0
  },
});

// * DATA
const tooltips = ref<Tooltips | null>(null);
/** If there is an actual picture file. */
const hasPicture = ref<boolean>(false);
const hasCroppedPicture = ref<boolean>(false);
const previewStyle = ref<string>("");

const parameters = reactive<{
  id: string,
  name: string,
  helper: string,
  required: boolean,
  showLabels: boolean,
  preview: boolean,
  browseEvent: boolean,
  /** Wanted picture dimensions. */
  dimensions: {
    height: number;
    width: number;
  },
}>({
  id:props.id,
  name:props.name,
  helper:props.helper,
  required:props.required,
  showLabels:props.showLabels,
  preview:props.preview,
  browseEvent:props.browseEvent,
  /** Wanted picture dimensions. */
  dimensions: {
    width: props.width,
    height: props.height,
  },
});

/** Internal file we are working on. */
const fileParameters = reactive<{
  file: File|null,
  filePath: string,
  fileName: string,
  /** Actual picture dimensions. */
  dimensions: {
    height: number;
    width: number;
  },
}>({
  file:null,
  filePath: "",
  fileName: "",
  dimensions:{
    height: 0,
    width: 0,
  },
});

/** The picture as data Url in order to display and crop. */
const fileDataUrl = ref<string|null>(null);

/**
 * The file we shall output :
 * - String when having an already stored picture
 * - File when we have a File fron the input or cropped component
 * - null when noting
 */
const fileOutput = ref<FileOutputValue>(null);

/** Cropper parameters and instance. */
const cropperParameters = reactive<CropperParameters>({
  rotation: 0,
  scaleX: 1,
  scaleY: 1,
});
const cropperInstance = ref<Cropper|null>(null);

/** Keep previous prop value in order to detect changes. */
const previousPropValue = ref<string>(props.value);

// * COMPUTED
const cropperOptions = computed<Cropper.Options>(() => {
  return {
    autoCrop: true,
    background: true,
    autoCropArea: 1,
    viewMode: 0,
    dragMode: "move",
    movable: true,
    rotatable: true,
    scalable: true,
    zoomable: true,
    zoomOnTouch: false,
    zoomOnWheel: false,
    cropBoxMovable: true,
    cropBoxResizable: false,
    toggleDragModeOnDblclick: false,
    aspectRatio: parameters.dimensions.width / parameters.dimensions.height,
    preview: `#Preview${parameters.id}`
  };
});
const isUsedWithProps = computed<boolean>(() => attrs.json === undefined);
const fileParamIsFile = computed<boolean>(() => fileParameters.file instanceof File);

// * LIFECYCLE
onMounted((): void => {
  const json = String(attrs.json ?? "{}"),
        data = JSON.parse(json);
  if (!isUsedWithProps.value) {
    parameters.id = data.id;
    parameters.name = data.name;
    parameters.helper = data.helper ?? "";
    parameters.dimensions.width = Number.parseInt(String(data.width));
    parameters.dimensions.height = Number.parseInt(String(data.height));
    parameters.preview = Boolean(data.preview);
    parameters.required = Boolean(data.required);
    parameters.showLabels = Boolean(data.showLabels);
    parameters.browseEvent = Boolean(data.browseEvent);
    let value = data.value;
    if(typeof value === "string" && value.length) {
      fileParameters.filePath = value.replace(/^\/|^/, "");
      fileParameters.fileName = value.replace(/^\/|^/, "");
      fileOutput.value = fileParameters.filePath;
    } else {
      fileParameters.fileName = data.placeholder ?? "image";
    }
  } else {
    assignFromProps();
  }
  fetchPictureFromPath(fileParameters.filePath);
  initTooltips();
});

// * WATCHERS

/**
  * Watch props value changes.
  * @return void
  */
watch(props, (newValue: typeof props): void => {
  // * Ignore non prop usage.
  if(!isUsedWithProps.value) {
    return;
  }
  assignFromProps();
  previousPropValue.value = newValue.value;
},{deep:true});

/**
  * Watch previous props value changes.
  * @return void
  */
watch(previousPropValue, (): void => {
  // * Ignore non prop usage.
  if(!isUsedWithProps.value) {
    return;
  }
  cropperReset();
  hasCroppedPicture.value = false;
  fetchPictureFromPath(fileParameters.filePath)!.then((file) => useThis(file));
});

/**
  * Crop parameters changes.
  * @return void
  */
watch(cropperParameters, (newValue: CropperParameters): void => {
  if (cropperInstance.value === null) {
    return;
  }
  cropperInstance.value.rotateTo(newValue.rotation);
}, { deep: true });

/**
  * The output has changed, so need to change inputFileImage values.
  * @return void
  */
watch(fileOutput, (newValue: FileOutputValue): void => {
  // Dont use input type file if we have a string as output (already stored picture).
  if (typeof newValue === "string") {
    // * Reset input file.
    inputFileImage.value!.value = "";
    return;
  }

  // * Just add a file to output.
  if (!(newValue instanceof File)) {
    return;
  }

  const container = new DataTransfer();
  container.items.add(newValue);
  inputFileImage.value!.files = container.files;
  emits("imageFile", newValue);
});

/**
  * Watch file output changes.
  * @return void
  */
watch(fileDataUrl, (newValue): void => {
  if(newValue === null) {
    return;
  }
  fileParameters.dimensions = {...parameters.dimensions};
});

// * METHODS

/**
  * Assign data from pops.
  * @return void
  */
function assignFromProps(): void {
  fileParameters.filePath = props.value.replace(/^\/|^/, "");
  fileParameters.fileName = props.value.replace(/^\/|^/, "");
  fileOutput.value = fileParameters.filePath;

  const mapParam = <T extends typeof parameters>(dest: T, prop: keyof T, value: ValueOf<T>) => {
    dest[prop] = value;
  };
  for(const key of ["id", "name", "helper", "required", "showLabels", "preview", "browseEvent"]) {
    const paramKey = key as keyof typeof parameters;
    mapParam<typeof parameters>(parameters, paramKey, props[paramKey as never]);
  }
  mapParam<typeof parameters>(parameters, "dimensions", {height: props.height, width: props.width});
}

/**
  * Vertical or Horizontal scale cropped file from cropper.
  * @return void
  */
function cropperScale(direction: "H"|"V"): void {
  if (cropperInstance.value === null) {
    return;
  }
  const dir: "X"|"Y" = direction === "H" ? "X" : "Y";
  cropperParameters[`scale${dir}`] = (-1 / cropperParameters[`scale${dir}`]) as MirrorScale;
  cropperInstance.value[`scale${dir}`](cropperParameters[`scale${dir}`]);
}

/**
  * Rotate cropped file from cropper.
  * @return void
  */
function cropperRotate(deg: number): void {
  deg += cropperParameters.rotation;
  cropperParameters.rotation = Math.min(Math.max(deg, 0), 360);
}

/**
  * Reset cropped file from cropper.
  * @return void
  */
function cropperReset(): void {
  cropperInstance.value?.reset();
  cropperParameters.rotation = 0;
  cropperParameters.scaleX = 1;
  cropperParameters.scaleY = 1;
}

/**
  * Export cropped file from cropper.
  * @return void
  */
function exportCropperFile(): void {
  if (!cropperInstance.value) {
    return;
  }
  hasCroppedPicture.value = true;
  fileDataUrl.value =
    cropperInstance.value.getCroppedCanvas({
      fillColor: "rgba(0, 0, 0, 0)",
      width: parameters.dimensions.width,
      height: parameters.dimensions.height,
      imageSmoothingEnabled: true,
      imageSmoothingQuality: "high",
    }).toDataURL("image/png");
  const file = new File(
    [dataUrltoBlob(fileDataUrl.value)],
    fileParameters.fileName,
    {
      type: "image/png",
      lastModified: new Date().getTime(),
    }
  );
  useThis(file);
  fileOutput.value = file;
}

/**
  * Click in input file picker.
  * @return void
  */
function chooseAFile(): void {
  emits("browseFile", parameters.id);
  if (parameters.browseEvent) {
    return;
  }
  inputFileImage.value!.click();
}

/**
  * Input file has changed.
  * @return void
  */
function pictureInputHasChanged(): void {
  if (inputFileImage?.value?.files && inputFileImage!.value!.files[0]) {
    cropperReset();
    hasCroppedPicture.value = false;
    useThis(inputFileImage!.value!.files[0]);
    fileOutput.value = inputFileImage!.value!.files[0];
  }
}

/**
  * Fetch the picture from file Path and save it as a File in the file input.
  * @return Promise<File>|void
  */
function fetchPictureFromPath(filePath: string): Promise<File>|void {
  if(!filePath.length) {
    return;
  }
  return new Promise<File>((accept, deny) => nextTick(() => {
    const prefixedFilePath: string = filePath.replace(/^\/|^/, "/"),
          stringFileName: string = prefixedFilePath.substring(prefixedFilePath.lastIndexOf("/")).replace(/^\/|^/, "");

    fetch(prefixedFilePath)
      .then((response) => response.blob())
      .then((blob: Blob) => {
        const file = new File([blob], stringFileName, {
          type: "image/png",
          lastModified: new Date().getTime(),
        });
        setFileAsDataUrl(file);
        accept(file);
      }).catch(() => deny("Failed to fetch picture"));
  }));
}

/**
  * Persist a File added from input or while component init.
  * @return void
  */
function useThis(file: File): void {
  setFileAsDataUrl(file);
  fileParameters.fileName = file.name;
  fileParameters.file = file;
  hasPicture.value = true;
}

/**
  * Set a file as DataUrl in order to use it within the component.
  * @return void
  */
function setFileAsDataUrl(file: File): void
{
  const reader = new FileReader();
  reader.addEventListener(
    "load",
    () => {
      /** On convertit l'image en une chaîne de caractères base64. */
      fileDataUrl.value = String(reader.result);
      fileParameters.fileName = file.name;
      fileParameters.file = file;
      hasPicture.value = true;
    },
    false
  );

  reader.readAsDataURL(file);
  reader.onloadend = () => {
    fetchImageDimensions(String(reader.result));
  };
}

/**
  * Init Cropper.
  * @return void
  */
function prepareImageEditor(): void {
  const createCropper = () => {
    const imgEditorEl = imgEditor.value;
    if (!imgEditorEl) {
      throw new Error("imgEditor required");
    }
    if (cropperInstance.value) {
      cropperInstance.value.destroy();
    }
    cropperInstance.value = new Cropper(imgEditorEl, cropperOptions.value);
  };

  if (fileParameters.file === null) {
    throw new Error("File nust not be null");
  }

  imgEditor.value!.src = URL.createObjectURL(fileParameters.file);

  nextTick(() => {
    createCropper();
    previewStyleCompute();
  });
  return;
}

/**
  * Reset the entire component state.
  * @return void
  */
function resetComponent(): void {
  cropperReset();
  fileDataUrl.value = null;
  fileOutput.value = null;
  inputFileImage.value!.value = "";
  fileParameters.fileName = trans.methods.__("vue.ImageInputComponent.choose_a_file");
  fileParameters.filePath = "";
  fileParameters.file = null;
  fileParameters.dimensions.height = 0;
  fileParameters.dimensions.width = 0;
  hasPicture.value = false;
  hasCroppedPicture.value = false;
  emits("imageFile", null);
}

/**
  * Download prefixed path picture and fetch its dimensions.
  * @return void
  */
function fetchImageDimensions(pictureSrc: string): void {
  const img = new Image();
  try {
    img.onload = () => {
      fileParameters.dimensions.width = img.width;
      fileParameters.dimensions.height = img.height;
    };
    img.src = pictureSrc;
  } catch (e) {
    fileParameters.dimensions.width = img.width;
    fileParameters.dimensions.height = img.height;
  }
}

/**
  * Computed the priew picture style for adaptative dimensions.
  * @return string
  */
function previewStyleCompute(): void {
  if (previewBox.value === null) {
    return ;
  }
  const style = window.getComputedStyle(previewBox.value),
        targetWidth = Number.parseFloat(style.width) / 1.2,
        targetHeight =
          (parameters.dimensions.height * Number.parseFloat(style.width)) /
          parameters.dimensions.width /
          1.2;
  previewStyle.value = `width: ${targetWidth.toFixed(3)}px;height:${targetHeight.toFixed(3)}px;`;
}

/**
  * Transform data url to blob.
  * @return Blob
  */
function dataUrltoBlob(dataUrl: string): Blob {
  const arr = dataUrl.split(",");
  if (!arr[0] || !arr[1]) {
    throw new Error("invalid data url");
  }
  const mime = arr[0].match(/:(.*?);/);
  if (!mime || mime.length < 2) {
    throw new Error("invalid mime");
  }
  const bstr = window.atob(arr[1]);
  let n = bstr.length;
  const u8arr = new Uint8Array(n);
  while (n--) {
    u8arr[n] = bstr.charCodeAt(n);
  }
  return new Blob([u8arr], { type: mime[1] });
}

/**
  * Human file size change.
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
      elements: imageInput.value!.querySelectorAll("[data-bs-tooltip=\"tooltip\"]")
    });
  }, 500);
}
</script>

<style lang="scss" scopped>
@import "cropperjs/dist/cropper.css";

.image-input-vue {
  /* Ensure the size of the image fit the container perfectly. */
  .image-modification {
    display: block;

    /* This rule is very important, please don't ignore this. */
    max-width: 100%;
  }
  .rotate-button {
    display: flex;
    flex-direction: column;
  }
  .rotate-drag {
    display: flex;
    flex-direction: column-reverse;
    width: 100%;
    & > input {
      width: 100%;
    }
  }
  .preview-img {
    position: relative;
    & > div {
      position: relative;
      overflow: hidden;
      border: 1px solid black;
      top: 50%;
      left: 50%;
      transform: translateX(-50%) translateY(-50%);
    }
  }
  input.right-aligned {
    direction: ltr !important;
    overflow: hidden !important;
  }
  input.right-aligned :not(:focus) {
    direction: rtl !important;
    text-align: left !important;
    text-overflow: ellipsis !important;
  }
}
</style>
