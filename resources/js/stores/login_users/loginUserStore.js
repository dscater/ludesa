import axios from "axios";
import { defineStore } from "pinia";
// TOAST
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
export const useLoginUserStore = defineStore("loginUser", {
    state: () => ({
        login_user: null,
    }),
    actions: {
        async verificaLoginUser() {
            try {
                const res = await axios.get(
                    route("login_users.verificaSucursal"),
                );
                if (res) {
                    const loginUser = res.data.login_user;
                    this.login_user = loginUser;
                    return this.login_user;
                }
            } catch (error) {
                console.log(error);
                toast.error(
                    "Ocurrió un error al verificar la sucursal del usuario",
                );
            }
        },
        async asignaLoginuser(sucursal_id, verifica_pagos) {
            try {
                const res = await axios.post(
                    route("login_users.asignaSucursal"),
                    {
                        sucursal_id,
                        verifica_pagos,
                    },
                );
                if (res) {
                    const loginUser = res.data.login_user;
                    this.login_user = loginUser;
                    return this.login_user;
                }
            } catch (error) {
                console.log(error);
                toast.error(
                    "Ocurrió un error al asignar la sucursal del usuario",
                );
            }
        },
    },
    getters: {
        getLoginUser() {
            return this.login_user;
        },
    },
});
