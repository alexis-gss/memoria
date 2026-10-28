export {};

import type VueTagsInput from "@sipec/vue3-tags-input";
import type { AxiosStatic } from "axios";
import type * as echarts from "echarts";
import type Resumable from "resumablejs";
import type Swal from "sweetalert2";

// * Recursive List<string> / List<number>
type NestedStringList = string | { [key: string]: NestedStringList };
type NestedNumberList = number | { [key: string]: NestedNumberList };

// * Recursive List<Model>
type Model = {
    [key: string]:
        | string
        | number
        | boolean
        | object
        | NestedStringList
        | NestedNumberList;
};
type ModelList = Array<Model>;

type RankObj = {
    id: number;
    rank: number;
    game_id: number;
    game_name: string;
    game_slug: string;
    created_at: Date;
    updated_at: Date;
};

interface UploadFile extends Blob {
    readonly lastModified: number;
    readonly webkitRelativePath: string;
    uuid: string;
    label: string;
    published: boolean;
    uniqueIdentifier: string;
}

interface PaginatorLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Paginator<T = Model> {
    current_page: number;
    data: Array<T>;
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: Array<PaginatorLink>;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

type VselectOptionType = {
    id: number;
    name: string;
};

declare global {
    type CustomTag = LaravelModel & VueTagsInput.ITag;
    type LaravelModel = Model;
    type LaravelModelList = ModelList;
    type LaravelPaginator<T = Model> = Paginator<T>;
    type ResumableJS = Resumable;
    type ChunkFile = UploadFile;
    type RankObject = RankObj;
    type VselectOption = VselectOptionType;
    type ValueOf<T> = T[keyof T];
    type TranslationNode = string | { [key: string]: TranslationNode };

    interface Window {
        axios: AxiosStatic;
        Echarts: typeof echarts;
        Swal: typeof Swal;
        vueDebug: boolean | undefined;
        __SYSTEM: {
            _locale: string;
            _routes: Record<string, string>;
            _translations: {
                json: Record<string, string>;
                php: TranslationNode;
            };
        };
    }
}
