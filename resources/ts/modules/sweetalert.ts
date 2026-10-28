import type { SweetAlertOptions, SweetAlertResult } from "sweetalert2";
import Swal from "sweetalert2";
import * as trans from "../modules/trans";

export default {
    methods: {
        /**
         * Show a confirm sweetalert popup.
         * @param {Object} self
         * @param {(response: SweetAlertResult<unknown>) => void} after
         * @param {SweetAlertOptions} options
         * @param {string} title
         * @param {string} text
         * @param {string} btnAccept
         * @param {string} btnDeny
         * @param {string} btnColor
         * @return {boolean}
         */
        confirm(
            self: object,
            after: (response: SweetAlertResult<unknown>) => void = () => {},
            options: SweetAlertOptions = {},
            title: string = trans.default.methods.__("crud.sweetalert.are_you_sure"),
            text: string = trans.default.methods.__("crud.sweetalert.data_lost"),
            btnAccept: string = trans.default.methods.__("crud.sweetalert.confirm"),
            btnDeny: string = trans.default.methods.__("crud.sweetalert.cancel"),
            btnColor: string = "danger",
        ): boolean {
            if (!self) {
                throw Error("Self is needed to set \"this\" on callback");
            }
            if (!after) {
                throw Error("Callback is needed when using confirm");
            }
            const icon = options.icon ?? "warning";
            Swal.fire({
                title: title,
                html: text,
                color: getComputedStyle(document.body).getPropertyValue("--bs-body-color"),
                icon: icon,
                showCancelButton: true,
                confirmButtonText: btnAccept,
                cancelButtonText: btnDeny,
                showCloseButton: true,
                allowEscapeKey: true,
                reverseButtons: true,
                // * Bootstrap Styling
                customClass: {
                    confirmButton: `btn mx-1 btn-${btnColor}`,
                    cancelButton: "btn btn-secondary mx-1",
                },
                buttonsStyling: false,
                background: getComputedStyle(document.body).getPropertyValue("--bs-body-bg"),
            }).then((result) => {
                if (result.isConfirmed) {
                    after.apply(self, [result]);
                }
            });
            return false;
        },
        /**
         * Show a message in sweetalert popup.
         * @param {string} title
         * @param {Object} self
         * @param {SweetAlertOptions} options
         * @return {boolean}
         */
        message(title = "", self: object, options: SweetAlertOptions = {}): boolean {
            if (!self) {
                throw Error("Self is needed to set \"this\" on callback");
            }
            const icon = options.icon ?? "warning";
            Swal.fire({
                title: title,
                color: getComputedStyle(document.body).getPropertyValue("--bs-body-color"),
                icon: icon,
                toast: true,
                width: "fit-content",
                position: "bottom-start",
                showConfirmButton: false,
                showCloseButton: true,
                background: getComputedStyle(document.body).getPropertyValue("--bs-body-bg"),
                timer: 8000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener("mouseenter", Swal.stopTimer);
                    toast.addEventListener("mouseleave", Swal.resumeTimer);
                },
            });
            return false;
        },
    },
};
