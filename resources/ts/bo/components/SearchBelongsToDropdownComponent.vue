<template>
  <div
    ref="searchBelongsToDropdown"
    class="position-relative"
  >
    <!-- VUE SELECT -->
    <VueSelect
      :id="id"
      :loading="loading"
      v-model="modelParameters.selected"
      :multiple="selectParameters.multiple"
      :clearable="!selectParameters.required"
      :label="selectParameters.fieldName"
      @search="search"
      :options="modelParameters.list"
      :selectable="(option: VselectOption) =>
        (modelParameters.selected !== null)
          ? (Array.isArray(modelParameters.selected))
            ? !modelParameters.selected.includes(option)
            : modelParameters.selected.id !== option.id
          : true"
      :filterable="true"
      :required="selectParameters.required"
      :disabled="selectParameters.disabled"
      :searchable="true"
      :placeholder="selectParameters.placeholder"
      :close-on-select="!selectParameters.multiple"
    >
      <template #no-options>
        <div class="vs__no-options d-flex flex-column justify-content-center align-items-center">
          <span class="no-result-icon">
            <FontAwesomeIcon
              icon="fa-solid fa-triangle-exclamation"
              class="w-100 h-100"
            />
          </span>
          <p class="text-center m-0 pt-2">
            {{ trans.methods.__("global_no_result") }}
          </p>
          <p class="text-center m-0 pb-2">
            {{ trans.methods.__("global_try_search_again") }}
          </p>
        </div>
      </template>
      <template #list-footer>
        <ul
          class="pagination d-flex p-2"
          v-if="pageParameters.totalPage > 1"
        >
          <li class="page-item">
            <button
              type="button"
              @click="updatePage(pageParameters.page - 1)"
              :disabled="!pageParameters.hasPrevPage"
              class="page-link"
              :class="{'disabled': pageParameters.page === 1}"
            >
              <FontAwesomeIcon icon="fa-solid fa-chevron-left fa-2xs" />
            </button>
          </li>
          <li
            class="page-item"
            v-for="(page, pageIndex) in pageParameters.totalPage"
            :key="pageIndex"
          >
            <button
              @click="updatePage(page)"
              class="page-link btn rounded-0"
              :class="{'bg-primary text-white border-primary': pageParameters.page === page}"
              type="button"
            >
              {{ page }}
            </button>
          </li>
          <li class="page-item">
            <button
              type="button"
              @click="updatePage(pageParameters.page + 1)"
              :disabled="!pageParameters.hasNextPage"
              class="page-link"
              :class="{'disabled': pageParameters.page === pageParameters.totalPage}"
            >
              <FontAwesomeIcon icon="fa-solid fa-chevron-right fa-2xs" />
            </button>
          </li>
        </ul>
      </template>
      <template #open-indicator>
        <span class="dropdown-toggle" />
      </template>
      <template #spinner>
        <div
          v-if="loading"
          class="spinner-border text-secondary ms-1"
          role="status"
        >
          <span class="visually-hidden">{{ trans.methods.__('global_text_loading') }}</span>
        </div>
      </template>
    </VueSelect>
    <!-- OUTPUT HTML INPUT -->
    <template
      v-if="Array.isArray(modelParameters.selected) && modelParameters.selected !== null"
    >
      <input
        v-for="(selectedId, selectedIndex) in modelParameters.selected"
        :key="selectedIndex"
        :value="selectedId.id"
        :name="name + '[][id]'"
        type="hidden"
      >
    </template>
    <input
      v-else
      :value="modelParameters.selected?.id"
      :name="name"
      type="hidden"
    >
    <!-- MESSAGES -->
    <Transition name="fade">
      <p
        v-show="message"
        class="message"
      >
        {{ message }}
      </p>
    </Transition>
  </div>
</template>

<script lang="ts" setup>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import type { PropType } from "vue";
import { computed, nextTick, onMounted, reactive, ref, useAttrs } from "vue";
import VueSelect from "vue-select";
import errors from "../../modules/errors";
import route from "../../modules/route";
import trans from "../../modules/trans";
import { Tooltips } from "./../../modules/tooltips";

defineOptions({
  name: "SearchBelongsToDropdown",
});

// * ATTRIBUTES
const attrs = useAttrs();

// * REFS
const searchBelongsToDropdown = ref<HTMLDivElement|null>(null);

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
  multiple: {
    type: Boolean,
    default: false
  },
  fieldName: {
    type: String,
    default: "name"
  },
  modelTarget: {
    type: String,
    default: ""
  },
  modelListPaginate: {
    type: Object as PropType<LaravelPaginator>,
    default: () => { return {}; }
  },
  modelSelected: {
    type: Object as PropType<VselectOption|VselectOption[]|null>,
    default: () => { return {}; }
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  roundedBorder: {
    type: Boolean,
    default: true
  },
  placeholder: {
    type: String,
    default: ""
  },
  routeName: {
    type: String,
    default: ""
  },
});

// * DATA
const id = ref<string>(props.id);
const name = ref<string>(props.name);
const message = ref<string>("");
const loading = ref<boolean>(false);
const roundedBorderBs = ref<boolean>(props.roundedBorder);
const searchText = ref<string|null>(null);
const tooltips = ref<Tooltips|null>(null);

/** Model parameters. */
const modelParameters = reactive<{
  targetClass: string,
  list: LaravelModelList,
  selected: VselectOption|VselectOption[]|null,
}>({
  targetClass: props.modelTarget,
  list: props.modelListPaginate.data,
  selected: props.modelSelected,
});

/** Select parameters. */
const selectParameters = reactive<{
  multiple: boolean,
  required: boolean,
  disabled: boolean,
  placeholder: string,
  fieldName: string,
}>({
  multiple: props.multiple,
  required: props.required,
  disabled: props.disabled,
  placeholder: props.placeholder,
  fieldName: props.fieldName,
});

/** Page parameters. */
const pageParameters = reactive<{
  page: number,
  totalPage: number,
  itemPerPage: number,
  hasNextPage: boolean,
  hasPrevPage: boolean,
  routeName: string,
}>({
  page: props.modelListPaginate.current_page,
  totalPage: props.modelListPaginate.last_page,
  itemPerPage: props.modelListPaginate.per_page,
  hasPrevPage: false,
  hasNextPage: false,
  routeName: props.routeName,
});

const isUsedWithProps = computed<boolean>(() => attrs.json === undefined);

// * LIFECYCLE
onMounted(() => {
  const json = String(attrs.json ?? "{}"),
        data = JSON.parse(json);
  if (!isUsedWithProps.value) {
    /** Global parameters. */
    id.value = String(data.id);
    name.value = String(data.name);
    roundedBorderBs.value = Boolean(data.roundedBorder);
    /** Model parameters. */
    modelParameters.targetClass = data.targetClass;
    modelParameters.list = data.modelListPaginate.data;
    nextTick(() => {
      modelParameters.selected = data.modelSelected;
    });
    /** Select parameters. */
    selectParameters.multiple = Boolean(data.multiple);
    selectParameters.required = Boolean(data.required);
    selectParameters.disabled = Boolean(data.disabled);
    selectParameters.placeholder = String(data.placeholder);
    selectParameters.fieldName = String(data.fieldName);
    /** Page parameters. */
    pageParameters.page = data.modelListPaginate.current_page;
    pageParameters.totalPage = data.modelListPaginate.last_page;
    pageParameters.itemPerPage = data.modelListPaginate.per_page;
    pageParameters.hasPrevPage = false,
    pageParameters.hasNextPage = false,
    pageParameters.routeName = String(data.routeName);
  }
  customNodes();
  updateButtonStatus();
  initTooltips();
});

// * METHODS

/**
  * Update the page and search items on it.
  * @return void
  */
function updatePage(number: number): void {
  pageParameters.page = number;
  search();
}

/**
  * Customize clear button.
  * @return void
  */
function customNodes(): void {
  const btnClear = searchBelongsToDropdown.value?.querySelector(".vs__clear") as HTMLButtonElement|null;
  btnClear?.setAttribute("title", trans.methods.__("bo_delete_selection"));
  btnClear?.setAttribute("aria-label", trans.methods.__("bo_delete_selection"));
  btnClear?.setAttribute("data-bs-toggle", "tooltip");
  (!roundedBorderBs.value) ? searchBelongsToDropdown.value?.querySelector(".vs__dropdown-toggle")?.classList.add("rounded-0") : "";
}

/**
  * Update pagination buttons status.
  * @return void
  */
function updateButtonStatus(): void {
  pageParameters.hasPrevPage = (pageParameters.page > 1) ? true : false;
  pageParameters.hasNextPage = (pageParameters.page < pageParameters.totalPage) ? true : false;
}

/**
  * Get items from a specific page and actualize pagination.
  * @return void
  */
function search(search: string|false|null = null): void {
  loading.value = true;
  if (search !== false && search !== null) {
    if (search !== searchText.value)
      pageParameters.page = 1;
    searchText.value = search;
  }
  window.axios
    .get(getJsonRouteUrl())
    .then((response) => {
      modelParameters.list = response.data.data;
      pageParameters.totalPage = response.data.last_page;
      pageParameters.page = response.data.current_page;
      pageParameters.itemPerPage = response.data.per_page;
      updateButtonStatus();
    })
    .then(() => { loading.value = false; })
    .catch(errors.methods.ajaxErrorHandler);
}

/**
  * Return json route to get paginate models.
  * @return string
  */
function getJsonRouteUrl(): string {
  const routeUrl = route.methods.route(pageParameters.routeName);
  if (!routeUrl) {
    throw new Error("Undefined route " + pageParameters.routeName);
  }
  return routeUrl + "?targetModel=" + modelParameters.targetClass +
    "&page=" + pageParameters.page +
    ((searchText.value !== null) ? "&search=" + searchText.value : "");
}

/**
  * Initialise all tooltips in the component.
  * @return void
  */
function initTooltips(): void {
  setTimeout(() => {
    tooltips.value = Tooltips.make({
      type: "dom",
      elements: searchBelongsToDropdown.value!.querySelectorAll("[data-bs-toggle=\"tooltip\"]")
    });
  }, 500);
}
</script>

<style lang="scss" scopped>
@import "vue-select/dist/vue-select.css";
@import "bootstrap/scss/functions";
@import "bootstrap/scss/variables";
@import "bootstrap/scss/mixins";
@import "bootstrap/scss/helpers";
@import "bootstrap/scss/close";
@import "bootstrap/scss/spinners";
@import "bootstrap/scss/badge";

:root {
  --vs-dropdown-max-height: 30rem;
  --vs-dropdown-option--active-bg: var(--bs-primary);
}
.vs__dropdown-toggle, .vs__dropdown-option, .vs__no-options {
  padding: .375rem .75rem;
}
.vs__no-options .no-result-icon {
  width: 4rem;
}
.vs__dropdown-toggle {
  border: var(--bs-border-width) solid var(--bs-border-color);
  border-radius: 0.375rem;
  padding-top: 0 !important;
  background-color: var(--bs-body-bg);
}
.vs__selected-options, .vs__search, .vs--single .vs__selected {
  padding: 0 !important;
  margin: 0 !important;
}
.vs__open-indicator {
  display: none;
}
.vs__actions, .vs__dropdown-menu {
  padding: 0;
}
.vs__actions, .vs__search, .vs--single .vs__selected {
  margin: .375rem 0 0 0 !important;
}
.vs__dropdown-menu, .vs__search, .vs--single .vs__selected {
  color: var(--bs-body-color);
}
.vs__search {
  border: 0 !important;
}
.vs__dropdown-menu {
  border-color: var(--bs-primary-border-subtle);
  background-color: var(--bs-body-bg);
}
.vs__dropdown-toggle:has(.vs__search:focus) {
  border-color: var(--bs-primary-border-subtle) !important;
  outline: 0;
  box-shadow: 0 0 0 .25rem rgba(var(--bs-primary-rgb), var(--bs-focus-ring-opacity));
  transition: .15s;
}
.vs__dropdown-option--selected, .vs__dropdown-option--disabled {
  background-color: var(--bs-tertiary-bg);
  color: var(--bs-secondary);
  cursor: not-allowed;
}
.vs--multiple .vs__selected {
  @extend .badge;
  @extend .text-bg-secondary;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 1em !important;
  font-weight: initial;
  margin: .375rem .5rem 0 0 !important;
  padding: .19rem .25rem !important;
}
.vs__clear, .vs--multiple .vs__selected .vs__deselect {
  @extend .btn-close;
  padding: 0;
}
.vs__clear svg, .vs--multiple .vs__selected .vs__deselect svg {
  display: none;
}
.spinner-border {
  width: 1.25rem;
  height: 1.25rem;
}
.vs__fade-enter-active,
.vs__fade-leave-active {
  transition: none;
}
.vs--open .vs__dropdown-toggle {
  border-bottom-left-radius: 0 !important;
  border-bottom-right-radius: 0 !important;
}
</style>
