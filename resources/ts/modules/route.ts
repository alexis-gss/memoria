const route = {
    methods: {
        /**
         * Define a route by the data given,
         * ex: route('name', { 'parameter': 'value' }).
         * @param {string} routeName
         * @param {Record<string, string | number>} parameters
         * @return {string | null}
         */
        route(
            routeName: string,
            parameters: Record<string, string | number> = {}
        ): string | null {
            const routes = route.flattenProperties(window.__SYSTEM._routes);
            const routeUrl =
                Object.keys(routes).indexOf(routeName) !== -1
                    ? (routes[routeName] as string)
                    : null;
            if (!routeUrl) {
                return routeUrl;
            }
            // copy parameters
            const iParameters = Object.assign({}, parameters),
                origin = window.location.origin,
                hash = window.location.hash;
            let pathname = routeUrl.replace(origin, ""),
                search = window.location.search;

            // Replace in path name.
            for (const paramName in iParameters) {
                const paramValue = parameters[paramName];
                if (pathname.indexOf(paramName) !== -1) {
                    pathname = pathname.replace(
                        paramName,
                        paramValue as string
                    );
                }
                delete parameters[paramName];
            }

            for (const paramName in parameters) {
                const paramValue = parameters[paramName];
                if (search.indexOf(paramName + "=") >= 0) {
                    const prefix = search.substring(
                        0,
                        search.indexOf(paramName + "=")
                    );
                    let suffix = search.substring(
                        search.indexOf(paramName + "=")
                    );
                    suffix = suffix.substring(suffix.indexOf("=") + 1);
                    suffix =
                        suffix.indexOf("&") >= 0
                            ? suffix.substring(suffix.indexOf("&"))
                            : "";
                    search = `${prefix}${paramName}=${paramValue}${suffix}`;
                } else if (search.indexOf("?") < 0) {
                    search += `?${paramName}=${paramValue}`;
                } else {
                    search += `&${paramName}=${paramValue}`;
                }
            }
            return `${origin}${pathname}${search}${hash}`;
        },
    },
    /**
     * Recursively flattens a nested object into a single layer.
     * @param {Record<PropertyKey, unknown>} obj
     * @param {string | null} parent
     * @param {Record<PropertyKey, unknown>} res
     * @return
     */
    flattenProperties(
        obj: Record<PropertyKey, unknown>,
        parent: string | null = null,
        res: Record<PropertyKey, unknown> = {}
    ): Record<PropertyKey, unknown> {
        for (const key of Object.keys(obj)) {
            const propName = parent ? parent + "." + key : key;
            if (obj[key] instanceof Object) {
                route.flattenProperties(
                    obj[key] as Record<PropertyKey, unknown>,
                    propName,
                    res
                );
            } else {
                res[propName] = obj[key];
            }
        }
        return res;
    },
};

export default route;
