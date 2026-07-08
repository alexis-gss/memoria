<template>
  <div
    ref="gamesSearch"
    class="games-search"
  >
    <!-- Filters -->
    <div
      class="row w-100 justify-content-center align-items-center bg-primary rounded-3 px-0 py-2 mx-auto mb-2"
      novalidate
    >
      <div class="col-12 gx-3">
        <!-- Filter by text -->
        <div class="d-flex border-bottom border-1 border-secondary pb-1 w-100">
          <input
            ref="searchInput"
            name="search"
            v-model="search"
            @input="setTextValue($event)"
            class="form-control border-0 rounded-3 text-bg-primary me-1 ps-2"
            :placeholder="trans.methods.__('fo_search', { games: `${paginationParameters.total}` })"
            type="text"
            maxlength="60"
            autocomplete="off"
            autofocus
          >
          <button
            @click="clearInputSearch()"
            class="btn btn-primary btn-action btn-clear d-flex align-items-center border-0 rounded-3"
            type="button"
            :title="trans.methods.__('fo_clear_search')"
            data-bs-toggle="tooltip"
            :disabled="!search.length && !selectedFolder.length && !selectedTag.length && !currentSort.length"
          >
            <FontAwesomeIcon icon="fa-solid fa-delete-left" />
          </button>
        </div>
      </div>
      <div class="col-12 gx-3">
        <div class="d-flex justify-content-center align-items-stretch w-100 mx-auto">
          <div class="d-flex flex-column flex-md-row justify-content-center align-items-center w-100">
            <!-- Filter by folders -->
            <div class="border-custom w-100 p-0 py-1 pe-1 pb-md-0">
              <select
                class="form-select border-0 rounded-3 bg-primary shadow-none text-bg-primary px-2 py-2 cursor-pointer"
                name="folder"
                :aria-label="trans.methods.__('fo_search_folder')"
                @change="setSelectedValue($event)"
              >
                <option
                  value="0"
                  selected
                >
                  {{ trans.methods.__("fo_search_folder") }}
                </option>
                <option
                  v-for="(folder, folderIndex) in modelsParameters.folders"
                  :key="folderIndex"
                  :value="folder.slug"
                  :selected="(selectedFolder.length && folder.slug === selectedFolder) ? true : false"
                >
                  {{ folder.nameLocale }}
                </option>
              </select>
            </div>
            <!-- Filter by tags -->
            <div class="border-custom w-100 p-0 pt-1 pe-1 px-md-1">
              <select
                class="form-select bg-primary rounded-3 border-0 shadow-none text-bg-primary px-2 py-2 cursor-pointer"
                name="tag"
                @change="setSelectedValue($event)"
                :aria-label="trans.methods.__('fo_search_tag')"
              >
                <option
                  value="0"
                  selected
                >
                  {{ trans.methods.__("fo_search_tag") }}
                </option>
                <option
                  v-for="(tag, tagIndex) in modelsParameters.tags"
                  :key="tagIndex"
                  :value="tag.slug"
                  :selected="(selectedTag.length && tag.slug === selectedTag) ? true : false"
                >
                  {{ tag.nameLocale }}
                </option>
              </select>
            </div>
          </div>
          <!-- Sort list -->
          <div class="games-search-sort ps-1 pt-1">
            <div class="position-relative w-100 h-100">
              <select
                v-model="currentSort"
                @change="setSortValue($event)"
                @mouseenter="isSortHovered = true"
                @mouseleave="isSortHovered = false"
                @focus="isSortHovered = true"
                @blur="isSortHovered = false"
                class="form-select bg-primary border-0 shadow-none text-bg-primary px-2 py-2 cursor-pointer position-absolute top-0 start-0 w-100 h-100 opacity-0 z-1"
                name="sort"
                :aria-label="trans.methods.__('fo_search_sort_aria_label')"
              >
                <option
                  v-for="(sortKey, sortIndex) in validSorts"
                  :key="sortIndex"
                  :value="sortKey"
                >
                  {{ trans.methods.__(`fo_search_sort_${sortKey}`) }}
                </option>
              </select>
              <button
                :class="[
                  'btn btn-primary btn-sort border-0 px-2 py-2 d-flex align-items-center justify-content-between w-100 h-100',
                  { active: isSortHovered },
                ]"
                tabIndex="-1"
              >
                <FontAwesomeIcon :icon="sortIcons[currentSort]" />
                <FontAwesomeIcon
                  icon="fa-solid fa-chevron-down"
                  class="fa-xs opacity-75 text-secondary"
                />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div
      class="position-relative overflow-hidden rounded-3"
      :inert="loading || paginationParameters.loading"
    >
      <!-- Loading new data -->
      <div
        v-if="loading || paginationParameters.loading"
        class="loading-screen position-absolute top-0 start-0 d-flex justify-content-center align-items-center bg-dark bg-opacity-25 w-100 h-100 z-1"
      >
        <div
          class="spinner-border text-light"
          role="status"
        >
          <span class="visually-hidden">{{ trans.methods.__("global_text_loading") }}</span>
        </div>
      </div>
      <!-- List of games -->
      <OverlayScrollbarsComponent
        class="nav-games-list rounded-3"
        @os-scroll="(instance: OverlayScrollbars) => { checkScroll(instance.elements().scrollOffsetElement); }"
      >
        <template v-if="modelsParameters.games.length > 0">
          <ul
            :class="['list-group rounded-0', {'pe-4': modelsParameters.games.length > 9}]"
            id="collapseGroup"
          >
            <li
              v-for="(game, key) in modelsParameters.games"
              :key="key"
              class="list-group-item border-0 rounded-2 bg-transparent p-0"
            >
              <a
                :href="getRouteGameShow(String(game.slug))"
                class="btn btn-secondary position-relative d-flex flex-row justify-content-between align-items-center border-0 text-light text-decoration-none rounded-0 w-100 p-2"
              >
                <div
                  class="d-flex flex-row justify-content-start align-items-center"
                >
                  <template
                    v-for="(folder, folderIndex) in modelsParameters.folders"
                    :key="folderIndex"
                  >
                    <span
                      v-if="folder.id === game.folder_id"
                      class="list-group-item-span z-1"
                      :style="`background-color:${folder.color}`"
                    />
                  </template>
                  <p class="text-start m-0 pe-2 z-2">{{ game.name }}</p>
                </div>
                <div class="d-flex justify-content-end align-items-center small opacity-75 z-1">
                  <template v-if="game.music">
                    <FontAwesomeIcon
                      icon="fa-solid fa-music"
                      class="fa-sm"
                    />
                    <span class="mx-1">|</span>
                  </template>
                  <span>{{ String((game.pictures as Array<Object>).length).padStart(2, '0') }}</span>
                </div>
              </a>
            </li>
          </ul>
        </template>
        <!-- NO RESULT -->
        <div
          v-if="!loading && modelsParameters.games.length <= 0"
          class="d-flex flex-column justify-content-center align-items-center border-0 bg-transparent text-light h-100 p-3 pt-0"
        >
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
          <button
            class="btn btn-primary"
            @click="clearInputSearch()"
            type="button"
          >
            {{ trans.methods.__("fo_clear_search") }}
          </button>
        </div>
      </OverlayScrollbarsComponent>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import type { OverlayScrollbars } from "overlayscrollbars";
import { OverlayScrollbarsComponent } from "overlayscrollbars-vue";
import { onMounted, ref, useAttrs, reactive, watch, nextTick } from "vue";
import errors from "./../../modules/errors";
import route from "./../../modules/route";
import trans from "./../../modules/trans";
import { Tooltips } from "./../../modules/tooltips";

defineOptions({
  name: "GamesSearchComponent"
});

// * ATTRIBUTES
const attrs = useAttrs();

// * REFS
const gamesSearch = ref<HTMLDivElement|null>(null);
const searchInput = ref<HTMLInputElement|null>(null);

// * DATA
const search = ref<string>("");
const selectedTag = ref<string>("");
const selectedFolder = ref<string>("");
const externalOrder = ref<string>("");
const externalGrid = ref<string>("");
const loading = ref<boolean>(false);
const tooltips = ref<Tooltips|null>(null);
const debounceDelay = ref<number>(300);
let debounceTimer: ReturnType<typeof setTimeout>|null = null;

/** Sort */
const defaultSort = ref<string>("alphabetical");
const currentSort = ref<string>(defaultSort.value);
const validSorts: Array<string> = ["alphabetical", "pictures", "music"];
const sortIcons: Record<string, Array<string>> = {
  alphabetical: ["fa-solid", "fa-arrow-down-a-z"],
  pictures: ["fa-solid", "fa-images"],
  music: ["fa-solid", "fa-music"],
};
const isSortHovered = ref<boolean>(false);

/** Models parameters. */
const modelsParameters = reactive<{
  games: LaravelModelList,
  folders: LaravelModelList,
  tags: LaravelModelList,
}>({
  games: [],
  folders: [],
  tags: [],
});

/** Pagination parameters. */
const paginationParameters = reactive<{
  page: number,
  lastPage: number,
  total: number,
  loading: boolean,
}>({
  page: 1,
  lastPage: 1,
  total: 0,
  loading: false,
});

const props = defineProps<{
  isActive?: boolean;
}>();

// * LIFECYCLE
onMounted((): void => {
  const json = String(attrs.json ?? "{}"),
        data = JSON.parse(json);

  /** Models parameters. */
  modelsParameters.games = data.games.data;
  modelsParameters.folders = data.allFolders;
  modelsParameters.tags = data.allTags;

  /** Pagination parameters. */
  paginationParameters.page = data.games.current_page;
  paginationParameters.lastPage = data.games.last_page;
  paginationParameters.total = data.games.total;

  /** Search parameters */
  search.value = data.params.text ? data.params.text : "";
  selectedFolder.value = data.params.folder ? data.params.folder : "";
  selectedTag.value = data.params.tag ? data.params.tag: "";

  /** Others */
  currentSort.value = getValidQueryParam("sort", validSorts, defaultSort.value);
  searchInput.value?.focus();

  /** Read order/grid from URL directly. */
  const urlParams = new URLSearchParams(window.location.search);
  externalOrder.value = urlParams.get("order") ?? "";
  externalGrid.value = urlParams.get("grid") ?? "";
  window.addEventListener("game-pictures:filters-changed", ((event: CustomEvent) => {
    externalOrder.value = event.detail.order ?? "";
    externalGrid.value = event.detail.grid ?? "";
  }) as EventListener);

  initTooltips();
  initButtons();
});

// * WATCHERS
watch(() => props.isActive, (active) => {
  if (active) {
    nextTick(() => {
      setTimeout(() => {
        searchInput.value?.focus();
      }, 50);
    });
  }
});

// * METHODS

/**
  * Check if all models are loaded,
  * if not, get next models.
  * @param {HTMLElement} event
  * @return {void}
  */
function checkScroll(event: HTMLElement): void {
  if (
    (event.offsetHeight + event.scrollTop) >= (event.scrollHeight - 60)
    && !loading.value
    && !paginationParameters.loading
    && paginationParameters.page < paginationParameters.lastPage
  ) {
    paginationParameters.page = paginationParameters.page + 1;
    ajaxGamesFiltered([selectedTag.value, selectedFolder.value], true);
  }
}

/**
  * Clear input search and selects.
  * @return {void}
  */
function clearInputSearch(): void {
  currentSort.value = defaultSort.value;
  searchInput.value!.value = search.value = selectedFolder.value = selectedTag.value = "";
  paginationParameters.page = 1;
  gamesSearch.value?.querySelectorAll("select").forEach((element: HTMLSelectElement) => {
    element.value = "0";
  });
  ajaxGamesFiltered();
}

/**
  * Set event on game buttons (folder/tags).
  * @return {void}
*/
function initButtons(): void {
  let breadcrumbs = document.querySelector(".btn-breadcrumbs");
  breadcrumbs?.addEventListener("click", () => {
    showNavigation();
  });
  let folder = document.querySelector(".game-folder");
  folder?.addEventListener("click", (event) => {
    setSelectedValue(event);
    showNavigation();
  });
  let tags = document.querySelectorAll(".game-tags");
  tags.forEach(tag => {
    tag.addEventListener("click", (event) => {
      setSelectedValue(event);
      showNavigation();
    });
  });
}

/**
  * Show the navigation panel.
  * @return {void}
  */
function showNavigation(): void {
  window.dispatchEvent(
    new CustomEvent("nav:open-panel", { detail: { panel: "games" } })
  );
}

/**
  * Set text value variables.
  * @param {Event} event Event on select.
  * @return {void}
  */
function setTextValue(event: Event): void {
  const target = event.target as HTMLInputElement;
  search.value = target.value;
  debouncedSearch([selectedTag.value, selectedFolder.value], search.value);
}

/**
  * Set selected folder/tag value variables.
  * @param {Event} event Event on select.
  * @return {void}
  */
function setSelectedValue(event: Event): void {
  const select = event.target as HTMLSelectElement;
  if (select.name == "folder") selectedFolder.value = select.value;
  if (select.name == "tag") selectedTag.value = select.value;

  // Reset debounce on select.
  if (debounceTimer) {
    clearTimeout(debounceTimer);
    debounceTimer = null;
  }

  paginationParameters.page = 1;
  ajaxGamesFiltered([selectedTag.value, selectedFolder.value], false, search.value);
}

/**
  * Set the sort value and trigger the search.
  * @param {Event} event
  * @return {void}
  */
function setSortValue(event: Event): void {
  const select = event.target as HTMLSelectElement;
  currentSort.value = select.value;
  paginationParameters.page = 1;
  ajaxGamesFiltered([selectedTag.value, selectedFolder.value], false, search.value);
}

/**
  * Debounced search.
  * @param {string[]} filters Selected filters.
  * @param {string} searchText Search text.
  * @return {Promise<void>}
  */
async function debouncedSearch(filters: string[], searchText: string): Promise<void> {
  // Clear old timer.
  if (debounceTimer) {
    clearTimeout(debounceTimer);
  }

  // Set new timer.
  debounceTimer = setTimeout(() => {
    performSearch(filters, searchText);
  }, debounceDelay.value);
}

/**
  * Get the search result with the filters and the text.
  * @param {string[]} filters Selected filters.
  * @param {string} searchText Search text.
  * @return {void}
  */
function performSearch(filters: string[], searchText: string): void {
  paginationParameters.page = 1;
  ajaxGamesFiltered(filters, false, searchText);
}

/**
  * Build query params from current filters and search text.
  * @param {string} searchText
  * @return {URLSearchParams}
  */
function buildUrlParams(searchText: string = search.value): URLSearchParams {
  const params = new URLSearchParams();
  if (searchText.length) params.set("text", searchText);
  if (selectedFolder.value.length) params.set("folder", selectedFolder.value);
  if (selectedTag.value.length) params.set("tag", selectedTag.value);
  if (currentSort.value !== defaultSort.value) params.set("sort", currentSort.value);
  if (externalOrder.value.length) params.set("order", externalOrder.value);
  if (externalGrid.value.length) params.set("grid", externalGrid.value);
  return params;
}

/**
  * Return a list of games which corresponds to the search from selects.
  * @param {string[]} filters Selected filters.
  * @param {boolean} pagination Pagination loader.
  * @param {string|null} search Words to search.
  * @return {void}
  */
function ajaxGamesFiltered(filters: string[] = [], pagination: boolean = false, search: string|null = null): void {
  const urlParams = buildUrlParams(search ?? "");
  history.replaceState("Object", "", location.pathname + (urlParams.toString().length ? "?" + urlParams.toString() : ""));
  (pagination) ? paginationParameters.loading = true : loading.value = true;
  window.axios
    .get(getRouteGamesFiltered(), {
      params: {
        filters_id: filters,
        page: paginationParameters.page,
        search: search,
        sort: currentSort.value,
      }
    })
    .then((reponse) => {
      paginationParameters.total = reponse.data.total;
      paginationParameters.lastPage = reponse.data.last_page;
      (pagination)
        ? modelsParameters.games = modelsParameters.games?.concat(reponse.data.data)
        : modelsParameters.games = reponse.data.data;
    })
    .then(() => {
      (pagination) ? paginationParameters.loading = false : loading.value = false;
    })
    .catch(errors.methods.ajaxErrorHandler);
}

/**
  * Return the route with the parameter slug given.
  * @return {string}
  */
function getRouteGamesFiltered(): string {
  const routeGamesFiltered = route.methods.route("fo.games.filtered");
  if (!routeGamesFiltered) {
    throw new Error("Undefined route fo.games.filtered");
  }
  return routeGamesFiltered;
}

/**
  * Return the route with the parameter slug given.
  * @param {string} slug Slug of the game.
  * @return {string}
  */
function getRouteGameShow(slug: string): string {
  const routeGameShow = route.methods.route("fo.games.show", {
    SLUG: slug,
  });
  if (!routeGameShow) {
    throw new Error("Undefined route fo.games.show");
  }

  const params = buildUrlParams();
  const url = new URL(routeGameShow);
  const queryString = params.toString();
  return url.origin + url.pathname + (queryString ? `?${queryString}` : "");
}

/**
 * Get a valid query parameter from the URL.
 * @param {string} key Key of the query parameter.
 * @param {string[]} allowedValues Allowed values for the query parameter.
 * @param {string} fallback Fallback value if the query parameter is not valid.
 * @return {string}
 */
function getValidQueryParam(key: string, allowedValues: Array<string>, fallback: string): string {
  const value = new URLSearchParams(window.location.search).get(key);
  return (value && allowedValues.includes(value)) ? value : fallback;
}

/**
  * Initialise all tooltips in the component.
  * @return {void}
  */
function initTooltips(): void {
  setTimeout(() => {
    tooltips.value = Tooltips.make({
      type: "dom",
      elements: gamesSearch.value!.querySelectorAll("[data-bs-toggle=\"tooltip\"]")
    });
  }, 500);
}
</script>

