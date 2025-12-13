<template>
  <div
    ref="imagesInput"
    :class="`images-input images-input-${id} p-0`"
  >
    <!-- Main buttons -->
    <div class="row">
      <div class="col-12 d-flex form-group">
        <div class="input-group">
          <button
            @click.prevent="actualImages?.click()"
            class="btn btn-secondary"
            type="button"
            :title="trans.methods.__('bo_tooltip_image_input_modify_sources')"
            data-bs-tooltip="tooltip"
            :disabled="values.length >= itemLimit[1]"
          >
            <FontAwesomeIcon icon="fa-solid fa-folder-open" />
          </button>
          <input
            :id="id"
            ref="actualImages"
            @change="changedImages"
            type="file"
            class="d-none"
            name="multipleInputImages"
            accept=".jpg,.jpeg,.gif,.png"
            :aria-describedby="`Help${id}`"
            multiple
          >
          <input
            @click.prevent="actualImages?.click()"
            type="text"
            :value="
              trans.methods.__('bo_other_number_images', {
                number: values.length + '/' + itemLimit[1],
              })
            "
            class="form-control right-aligned"
            :title="trans.methods.__('bo_tooltip_image_input_modify_sources')"
            data-bs-tooltip="tooltip"
            :aria-describedby="`Help${id}`"
            :disabled="values.length >= itemLimit[1]"
            role="button"
            readonly
          >
          <button
            class="btn btn-primary btn-collapse collapsed"
            type="button"
            :title="trans.methods.__('bo_tooltip_image_input_show_hide_content')"
            data-bs-tooltip="tooltip"
            data-bs-toggle="collapse"
            data-bs-target="#multiple-images"
            aria-expanded="false"
            aria-controls="multiple-images"
            :disabled="!hasImages"
          >
            <FontAwesomeIcon icon="fa-solid fa-arrow-down" />
          </button>
          <button
            v-if="values.length > itemLimit[0]"
            @click.prevent="removeFiles"
            class="btn btn-danger"
            type="button"
            :title="trans.methods.__('bo_tooltip_image_input_remove_images')"
            data-bs-tooltip="tooltip"
            :disabled="values.length <= 0"
          >
            <FontAwesomeIcon icon="fa-solid fa-eraser" />
          </button>
        </div>
      </div>
    </div>
    <!-- End main buttons -->
    <!-- Content collapse -->
    <div
      class="collapse"
      id="multiple-images"
    >
      <div class="row w-100 mx-auto mb-2 py-1">
        <div
          v-for="n = 0 in values.length"
          :key="n"
          class="col-12 form-group text-center px-1"
        >
          <div class="d-flex justify-content-between align-items-center py-1">
            <HeavyDocumentInputComponent
              :id="values[n - 1].uniqueIdentifier"
              :file="values[n - 1]"
              :gameslug="String(model?.slug ?? 'default_folder')"
              :csrf="csrf"
              :simplecomponent="true"
            />
          </div>
          <p class="text-danger m-0">
            {{
              errors.methods.parseValidationInput(
                `${name.replace(/[\[\]']+/g, "")}.${n - 1}`,
                allErrors
              )
            }}
          </p>
        </div>
      </div>
      <!-- End content collapse -->
    </div>
    <Transition name="fade">
      <p
        v-if="message"
        class="m-0 text-danger"
      >
        {{ message }}
      </p>
    </Transition>
    <small
      :id="`Help${id}`"
      class="form-text text-body-secondary"
    >
      {{ helper }}
    </small>
  </div>
</template>

<script lang="ts" setup>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { onMounted, ref, useAttrs, nextTick } from "vue";
import errors from "./../../modules/errors";
import { Tooltips } from "./../../modules/tooltips";
import trans from "./../../modules/trans";
import HeavyDocumentInputComponent from "./HeavyDocumentInputComponent.vue";

defineOptions({
  name: "ImagesInputComponent"
});

// * ATTRIBUTES
const attrs = useAttrs();

// * REFS
const imagesInput = ref<HTMLDivElement|null>(null);
const actualImages = ref<HTMLInputElement|null>(null);

// * DATA
const id = ref<string>("");
const model = ref<LaravelModel|null>(null);
const name = ref<string>("");
const hasImages = ref<boolean>(false);
const values = ref<Array<ChunkFile>>([]);
const helper = ref<string>("");
const btnCollapse = ref<HTMLButtonElement|null>(null);
const loopLoadImages = ref<number>(0);
const itemLimit = ref<Array<number>>([0, 0]);
const csrf = ref<string>("");
const message = ref<string|null>("");
const allErrors = ref<Record<string, Array<string>>>({});
const tooltips = ref<Tooltips|null>(null);

// * LIFECYCLE
onMounted((): void => {
  const json = String(attrs.json ?? "{}"),
        data = JSON.parse(json);
  id.value = data.id;
  model.value = data.model;
  name.value = data.name;
  if (data.value.length > 0) {
    initImagesSaved(data.value);
  }
  helper.value = data.helper ?? "";
  itemLimit.value = data.limit;
  csrf.value = data.csrf;
  allErrors.value = data.errors ?? {};
  nextTick(() => {
    initComponent();
    initTooltips();
  });
});

/**
  * Assign previously images registered to respective input.
  * @return void
  */
function initImagesSaved(models: LaravelModelList): void {
  models.forEach((model: LaravelModel, modelIndex: number) => {
    let oldValue = {} as ChunkFile;
    oldValue.uuid = String(model.uuid);
    oldValue.label = String(model.label);
    oldValue.published = model.published ? true : false;
    oldValue.uniqueIdentifier = String((modelIndex) + "-" + Date.now());
    values.value.push(oldValue);
  });
}

/**
  * Initialise the component with previously images registered.
  * @return void
  */
function initComponent(): void {
  (values.value.length > 0) ?
    btnCollapse.value = document.querySelector("[data-bs-target=\"#multiple-images\"]") : "";
  hasImages.value = (values.value.length > 0) ? true : false;
}

/**
  * Remove all images.
  * @return void
  */
function removeFiles(): void {
  values.value.length = itemLimit.value[0];
  itemLimit.value[0] > 0
    ? (hasImages.value = true)
    : (hasImages.value = false);
  allErrors.value = {};
  tooltips.value?.refreshTooltips();
}

/**
  * Add images to exists image(s).
  * @return void
  */
function changedImages(e: Event): void {
  const el = e.target;
  if (!el || !(el instanceof HTMLInputElement)) {
    throw new Error(
      "confirmDoneJS can only be executed on an exising form"
    );
  }
  if (!el.files) {
    throw new Error();
  }
  loopLoadImages.value = values.value.length;
  loadImages([].slice.call(el.files));
  hasImages.value = true;
  if (btnCollapse.value?.classList.contains("collapsed")) {
    btnCollapse.value?.click();
  }
  tooltips.value?.refreshTooltips();
}

/**
  * Update inputs file.
  * @return void
  */
function loadImages(files: Array<File>): void {
  if (files.length > 0) {
    if (values.value.length + files.length <= itemLimit.value[1]) {
      let actualFile = files.shift() as unknown as ChunkFile;
      actualFile.published = false;
      actualFile.uniqueIdentifier = String(loopLoadImages.value + "-" + Date.now());
      values.value.push(actualFile);
      var dt = new DataTransfer();
      dt.items.add(actualFile as unknown as File);
      actualImages.value!.files = dt.files;
      loopLoadImages.value++;
      setTimeout(() => {
        loadImages(files);
      }, 200);
    } else {
      setErrorMessage("Pictures download limit exceeded");
    }
  } else {
    tooltips.value?.closeBootstrapTooltip();
    initTooltips();
    loopLoadImages.value = values.value.length;
  }
}

/**
  * Remove image source in the viewer.
  * @return void
  */
function setErrorMessage($message: string): void {
  message.value = $message;
  setTimeout(() => {
    message.value = null;
  }, 5000);
}

/**
  * Initialise all tooltips in the component.
  * @return void
  */
function initTooltips(): void {
  setTimeout(() => {
    tooltips.value = Tooltips.make({
      type: "dom",
      elements: imagesInput.value!.querySelectorAll("[data-bs-tooltip=\"tooltip\"]")
    });
  }, 500);
}
</script>

<style lang="scss" scopped>
.image-input,
.images-input {
  .right-aligned {
    overflow: hidden !important;
    direction: ltr !important;
  }
  .right-aligned:not(:focus) {
    text-align: left !important;
    text-overflow: ellipsis !important;
  }
  .btn-collapse svg {
    transform: rotate(180deg);
    transition: 0.3s;
  }
  .collapsed {
    svg {
      transform: rotate(0deg);
    }
  }
  #multiple-images .row {
    overflow-y: auto;
  }
}
</style>
