<template>
  <div
    ref="gamesRanking"
    :class="`ranks-${id} position-relative`"
  >
    <Transition name="fade">
      <p
        v-if="message"
        class="d-flex align-items-center text-danger mb-2"
      >
        <span>{{ Object.values(message)[0] }}&nbsp;</span>
      </p>
    </Transition>
    <vue-nestable
      :value="ranks"
      @change="updateRank"
      @input="ranks = $event"
      :hooks="{ beforeMove: beforeMove }"
    >
      <template
        #default="{
          item,
        }: { item: RankObject }"
      >
        <vue-nestable-handle
          class="d-flex justify-content-between align-items-center border rounded bg-body p-1"
        >
          <div class="d-flex justify-content-start align-items-center">
            <button class="btn btn-sm border-0 disabled opacity-100">
              <FontAwesomeIcon icon="fa-solid fa-grip-vertical" />
            </button>
            <p class="m-0">
              <span class="badge rounded-pill text-bg-secondary">
                {{ item.rank }}
              </span>
            </p>
            &nbsp;
            <p class="m-0">
              {{ item.game_name }}
            </p>
          </div>
          <div
            class="d-flex justify-content-end align-items-center"
          >
            <div class="input-group">
              <a
                :href="getFrontShowGameRoute(item.game_slug)"
                class="btn btn-sm btn-info"
                target="_blank"
                :title="trans.methods.__('crud.other.access_link')"
                data-bs-tooltip="tooltip"
              >
                <FontAwesomeIcon icon="fa-solid fa-arrow-up-right-from-square" />
              </a>
              <a
                :href="getShowGameRoute(item.game_id)"
                class="btn btn-sm btn-warning"
                :title="trans.methods.__('bo_tooltip_ranking_see_game')"
                data-bs-tooltip="tooltip"
              >
                <FontAwesomeIcon icon="fa-solid fa-eye" />
              </a>
              <a
                :href="getEditGameRoute(item.game_id)"
                class="btn btn-sm btn-primary"
                :title="trans.methods.__('bo_tooltip_ranking_update_game')"
                data-bs-tooltip="tooltip"
              >
                <FontAwesomeIcon icon="fa-solid fa-pencil" />
              </a>
              <button
                @click="deleteRank($event, item as never as RankObject)"
                class="btn btn-sm btn-danger confirmDelete"
                :title="trans.methods.__('bo_tooltip_ranking_delete_game')"
                data-bs-tooltip="tooltip"
                ref="confirmDelete"
              >
                <FontAwesomeIcon icon="fa-solid fa-xmark" />
              </button>
            </div>
          </div>
        </vue-nestable-handle>
      </template>
    </vue-nestable>
    <div
      v-if="loading"
      class="loading position-absolute top-0 start-0 d-flex justify-content-center align-items-center rounded-1 w-100 h-100"
    >
      <div
        class="spinner-border text-secondary"
        role="status"
      >
        <span class="visually-hidden">
          {{ trans.methods.__("bo_tooltip_viewer_loading") }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { onMounted, ref, useAttrs, nextTick } from "vue";
import { VueNestable, VueNestableHandle } from "vue3-nestable";
import route from "./../../modules/route";
import { Tooltips } from "./../../modules/tooltips";
import errors from "././../../modules/errors";
import sweetalert from "././../../modules/sweetalert";
import trans from "././../../modules/trans";

defineOptions({
  name: "GamesRankingComponent"
});

// * ATTRIBUTES
const attrs = useAttrs();

// * REFS
const gamesRanking = ref<HTMLDivElement|null>(null);
const confirmDelete = ref<HTMLButtonElement|null>(null);

// * DATA
const id = ref<string>("");
const csrf = ref<string|null>(null);
const ranks = ref<Array<RankObject>>([]);
const message = ref<string>("");
const loading = ref<boolean>(false);
const tooltips = ref<Tooltips|null>(null);

// * LIFECYCLE
onMounted((): void => {
  const json = String(attrs.json ?? "{}"),
        data = JSON.parse(json);
  id.value = data.id;
  ranks.value = data.rankModels;
  csrf.value = document.querySelector("meta[name=\"csrf-token\"]")!.getAttribute("content");
  nextTick(() => {
    tooltips.value = Tooltips.make({
      type: "dom",
      elements: gamesRanking.value!.querySelectorAll("[data-bs-tooltip=\"tooltip\"]")
    });
  });
});

// * METHODS

/**
  * Check if there is an input or textearea.
  * @return boolean
  */
function beforeMove({pathTo}: {pathTo: Array<number>}): boolean {
  if (pathTo.length == 2) {
    return false;
  }
  return true;
}

/**
  * Update data's ranks.
  * @return void
  */
function updateRank(): void {
  const routeRank = route.methods.route("bo.ranks.saveOrder");
  if (!routeRank) {
    throw new Error("Undefined route bo.ranks.saveOrder");
  }
  window.axios
    .post(routeRank, { ranks: assignRank(ranks.value) })
    .catch(errors.methods.ajaxErrorHandler);
  nextTick(() => {
    tooltips.value?.refreshTooltips();
  });
}

/**
  * Assign the new order and parent id to each rank.
  * @return Array<RankObject>
  */
function assignRank(ranks: Array<RankObject>): Array<RankObject> {
  ranks.forEach((element, index) => {
    element.rank = index + 1;
  });
  return ranks;
}

/**
  * Return front show route for a game.
  * @return string
  */
function getFrontShowGameRoute(slug: string): string {
  const routeShowGame = route.methods.route("fo.games.show", {
    SLUG: slug,
  });
  if (!routeShowGame) {
    throw new Error("Undefined route fo.games.show");
  }
  return routeShowGame;
}

/**
  * Return show route for a game.
  * @return string
  */
function getShowGameRoute(id: number): string {
  const routeShowGame = route.methods.route("bo.games.show", {
    ID: id,
  });
  if (!routeShowGame) {
    throw new Error("Undefined route bo.games.show");
  }
  return routeShowGame;
}

/**
  * Return edit route for a game.
  * @return string
  */
function getEditGameRoute(id: number): string {
  const routeGameEdit = route.methods.route("bo.games.edit", {
    ID: id,
  });
  if (!routeGameEdit) {
    throw new Error("Undefined route bo.games.edit");
  }
  return routeGameEdit;
}

/**
  * Return destroy route for a game.
  * @return string
  */
function getDestroyGameRoute(id: number): string {
  const routeDestroy = route.methods.route("bo.ranks.destroy", {
    ID: id,
  });
  if (!routeDestroy) {
    throw new Error("Undefined route bo.ranks.destroy");
  }
  return routeDestroy;
}

/**
  * Delete specific game from the ranking.
  * @return void|boolean
  */
function deleteRank(e: Event, model: RankObject): void|boolean {
  const btnConfirmDelete = confirmDelete.value as HTMLButtonElement;
  let promise:Promise<boolean>|null = null;
  (async () => {
    if (promise !== null && await promise === false) {
      return;
    }
    promise = new Promise((resolve: (value: boolean) => void) => {
      sweetalert.methods.confirm(
        btnConfirmDelete,
        function (response) {
          resolve(response.isConfirmed);
        },
        { icon: "warning" },
        undefined,
        trans.methods.__("crud.sweetalert.delete_element", {"modelName": model.game_name}),
        undefined,
        undefined,
      );
      return false;
    });
    if (await promise) {
      loading.value = true;
      window.axios
        .post(getDestroyGameRoute(model.id), { id: model.id, _method: "DELETE" })
        .then((reponse) => {
          ranks.value = reponse.data;
          loading.value = false;
          updateRank();
          nextTick(() => {
            tooltips.value?.refreshTooltips();
          });
        })
        .catch(errors.methods.ajaxErrorHandler);
    }
  })();
}
</script>

<style lang="scss" scopped>
.ranks-games {
  .loading {
    z-index: 5;
    background-color: rgb(0 0 0 / 15%);
  }
  .badge {
    color: var(--bs-body-bg);
  }
  .fade-enter-active,
  .fade-leave-active {
    transition: opacity 0.5s ease;
  }
  .fade-enter-from,
  .fade-leave-to {
    opacity: 0;
  }
  .nestable-list {
    padding: 0;
    list-style: none;
  }
  .nestable {
    position: relative;
    [draggable="true"] {
      cursor: move;
    }
    .nestable-rtl {
      direction: rtl;
    }
    .nestable .nestable-list {
      padding: 0 0 0 23px;
      margin: 0;
      list-style-type: none;
    }
    .nestable-rtl .nestable-list {
      padding: 0 40px 0 0;
    }
    .nestable > .nestable-list {
      padding: 0;
    }
    .nestable-item .nestable-list,
    .nestable-item-copy .nestable-list {
      margin: 10px 0 0 20px;
    }
    .nestable-drag-layer > .nestable-list {
      position: absolute;
      top: 0;
      left: 0;
      padding: 0;
      background-color: rgb(106 127 233 / 27.4%);
    }
    .nestable-item,
    .nestable-item-copy {
      margin: 10px 0 0;
    }
    .nestable-item {
      position: relative;
      .nestable-content {
        border-radius: var(--bs-border-radius) !important;
      }
    }
    .nestable-item:first-child,
    .nestable-item-copy:first-child {
      margin-top: 0;
    }
    .nestable-item.is-dragging .nestable-list {
      pointer-events: none;
    }
    .nestable-item.is-dragging * {
      opacity: 0;
    }
    .nestable-item.is-dragging::before {
      position: absolute;
      border: 1px dashed rgb(73 100 241);
      border-radius: 5px;
      background-color: rgb(106 127 233 / 27.4%);
      content: " ";
      inset: 0;
    }
    .nestable-drag-layer {
      position: fixed;
      z-index: 100;
      top: 0;
      left: 0;
      pointer-events: none;
    }
    .nestable-rtl .nestable-drag-layer {
      right: 0;
      left: auto;
    }
    .nestable-rtl .nestable-drag-layer > .nestable-list {
      padding: 0;
    }
    .nestable-handle {
      display: inline-block;
      width: 100%;
    }
  }
}
</style>
