<template>
  <div
    ref="gamesSearch"
    class="nav-games"
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
            class="btn btn-primary btn-clear d-flex align-items-center border-0 rounded-3"
            type="button"
            :title="trans.methods.__('fo_clear_search')"
            data-bs-toggle="tooltip"
          >
            <FontAwesomeIcon icon="fa-solid fa-xmark" />
          </button>
        </div>
      </div>
      <div class="col-12 gx-3">
        <div class="row w-100 mx-auto">
          <!-- Filter by folders -->
          <div class="col-12 col-md-6 border-custom p-0 py-1 pe-md-1 pb-md-0">
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
          <div class="col-12 col-md-6 p-0 pt-1 ps-md-1">
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
          class="spinner-border text-white"
          role="status"
        >
          <span class="visually-hidden">{{ trans.methods.__("global_text_loading") }}</span>
        </div>
      </div>
      <!-- List of games -->
      <OverlayScrollbarsComponent
        class="nav-games-list rounded-3"
        @os-scroll="(event) => { checkScroll(event.elements().scrollOffsetElement); }"
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
                <span>{{ (game.pictures as Array<Object>).length }}</span>
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
import { OverlayScrollbarsComponent } from "overlayscrollbars-vue";
import { defineOptions, onMounted, ref, useAttrs, reactive } from "vue";
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
const urlParams = new URLSearchParams(window.location.search);
const search = ref<string>("");
const selectedTag = ref<string>("");
const selectedFolder = ref<string>("");
const loading = ref<boolean>(false);
const tooltips = ref<Tooltips|null>(null);

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
  searchInput.value?.focus();

  initTooltips();
  initButtons();
});

// * METHODS

/**
  * Check if all models are loaded,
  * if not, get next models.
  * @return void
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
  * @return void
  */
function clearInputSearch(): void {
  searchInput.value!.value = search.value = selectedFolder.value = selectedTag.value = "";
  paginationParameters.page = 1;
  document.querySelectorAll("select").forEach((element: HTMLSelectElement) => {
    element.value = "0";
  });
  urlParams.delete("text");
  urlParams.delete("folder");
  urlParams.delete("tag");
  ajaxGamesFiltered();
}

/**
  * Set event on game buttons (folder/tags).
  * @return LaravelModelList
*/
function initButtons(): void {
  let folder = document.querySelector(".game-folder");
  let tags = document.querySelectorAll(".game-tags");
  folder?.addEventListener("click", (e) => {
    setSelectedValue(e);
    showNavigation();
  });
  tags.forEach(tag => {
    tag.addEventListener("click", (e) => {
      setSelectedValue(e);
      showNavigation();
    });
  });
}

/**
  * Show the navigation panel.
  * @return void
  */
function showNavigation(): void {
  let menuModal = document.querySelector(".nav-modal");
  let menuFilter = document.querySelector(".nav-filter");
  menuModal?.classList.remove("nav-modal-hidden");
  menuFilter?.classList.remove("nav-filter-hidden");
}

/**
  * Set text value variables.
  * @param event Event on select.
  * @return void
  */
function setTextValue(event: Event|null): void {
  const target = event?.target as HTMLInputElement|null;
  search.value = target?.value ? target?.value : "";
  paginationParameters.page = 1;
  urlParams.delete("text");
  (search.value.length) ? urlParams.set("text", search.value) : "";
  ajaxGamesFiltered([selectedTag.value, selectedFolder.value], false, search.value);
}

/**
  * Set selected folder/tag value variables.
  * @param event Event on select.
  * @return void
  */
function setSelectedValue(event: Event): void {
  const select = event.target as HTMLSelectElement;
  if (select.name == "folder") selectedFolder.value = select.value;
  if (select.name == "tag") selectedTag.value = select.value;
  paginationParameters.page = 1;
  urlParams.delete(select.name);
  (select.value.length) ? urlParams.set(select.name, select.value) : "";
  ajaxGamesFiltered([selectedTag.value, selectedFolder.value], false, search.value);
}

/**
  * Return a list of games which corresponds to the search from selects.
  * @param filters    Selected filters.
  * @param pagination Pagination loader.
  * @return void
  */
function ajaxGamesFiltered(filters: string[] = [], pagination: boolean = false, search: string|null = null): void {
  history.replaceState("Object", "", location.pathname + (urlParams.toString().length ? "?" + urlParams.toString() : ""));
  (pagination) ? paginationParameters.loading = true : loading.value = true;
  window.axios
    .post(getRouteGamesFiltered(), {
      filters_id: filters,
      page: paginationParameters.page,
      search: search
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
  * @return string
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
  * @param slug Slug of the game.
  * @return string
  */
function getRouteGameShow(slug: string): string {
  const routeGameShow = route.methods.route("fo.games.show", {
    SLUG: slug,
  });
  if (!routeGameShow) {
    throw new Error("Undefined route fo.games.show");
  }
  return routeGameShow;
}

/**
  * Initialise all tooltips in the component.
  * @return void
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

<style lang="scss" scopped>
@import "bootstrap/scss/functions";
@import "bootstrap/scss/variables";
@import "bootstrap/scss/mixins";
@import "bootstrap/scss/placeholders";
@import "overlayscrollbars/overlayscrollbars.css";

.nav-games {
  .loading-screen {
    transition: .3s;
  }
  .form-control {
    height: 40px;
  }
  .form-control::placeholder {
    color: var(--bs-light);
  }
  .border-custom {
    border-bottom: solid 1px var(--bs-secondary) !important;

    @include media-breakpoint-up(md) {
      border-right: solid 1px var(--bs-secondary) !important;
      border-bottom: 0 !important;
    }
  }
  .btn-clear {
    border-top-right-radius: var(--bs-border-radius-lg) !important;
  }
  .no-result-icon {
    width: 4rem;
    height: 4rem;
  }
  .placeholder-glow .placeholder {
    height: 24px;
  }
  .os-scrollbar {
    --os-size: 1rem;
    --os-track-bg: #14171a;
    --os-track-bg-hover: #14171a;
    --os-track-bg-active: #14171a;
    --os-handle-bg: var(--bs-primary);
    --os-handle-bg-hover: #4d6075;
    --os-handle-bg-active: #485a6e;
  }
}
</style>
