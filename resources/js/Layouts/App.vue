<script setup>
// includes
import { ref, onMounted, onBeforeMount } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import Footer from "./includes/Footer.vue";
import NavBar from "./includes/NavBar.vue";
import SideBar from "./includes/SideBar.vue";
import { useAppStore } from "@/stores/aplicacion/appStore";
import LoginUser from "@/Components/LoginUser.vue";
import { useLoginUserStore } from "@/stores/login_users/loginUserStore";
import { useConfiguracionStore } from "@/stores/configuracion/configuracionStore";
const configuracionStore = useConfiguracionStore();
const appStore = useAppStore();
const { auth } = usePage().props;

const loginUserStore = useLoginUserStore();
const muestra_login_user = ref(false);
const accion_login_user = ref(0);

let inactivityTimer = null;
// const INACTIVITY_LIMIT = 60 * 1000; // 1 minuto en ms
let INACTIVITY_LIMIT = 60 * 5000; // 5 minuto en ms

// Función para cerrar sesión
const logout = () => {
    axios
        .post(route("logout"))
        .then((response) => {})
        .finally(() => {
            window.location.href = "/";
        });
};

// Resetear temporizador
const resetTimer = () => {
    if (inactivityTimer) clearTimeout(inactivityTimer);
    inactivityTimer = setTimeout(logout, INACTIVITY_LIMIT);
};

onMounted(() => {
    // Inicializar info de usuario
    appStore.initUserInfo();

    // Eventos que consideran actividad del usuario
    // window.addEventListener("mousemove", resetTimer);
    // window.addEventListener("keydown", resetTimer);
    // window.addEventListener("click", resetTimer);

    // resetTimer();
});

const verificaSucursalUSuario = async () => {
    await loginUserStore.verificaLoginUser();
    if (!loginUserStore.login_user) {
        muestra_login_user.value = true;
    }
};

onBeforeMount(async () => {
    appStore.initUserInfo();
    await verificaSucursalUSuario();
    // window.removeEventListener("mousemove", resetTimer);
    // window.removeEventListener("keydown", resetTimer);
    // window.removeEventListener("click", resetTimer);
    // if (inactivityTimer) clearTimeout(inactivityTimer);
});
</script>
<template>
    <div class="loading" :class="[appStore.getLoading == true ? 'show' : '']">
        <!-- <div class="loading show"> -->
        <template v-if="$slots.loading">
            <slot name="loading"></slot>
        </template>
        <template v-else>
            <i class="fa fa-spin fa-spinner fa-4x text1"></i>
            <br />
            <span class="font-weight-bold text1 h4">
                {{ configuracionStore.oConfiguracion.nombre_sistema }}
            </span>
        </template>
    </div>

    <LoginUser
        :muestra_formulario="muestra_login_user"
        :accion_formulario="accion_login_user"
        @envio-formulario="muestra_login_user = false"
    ></LoginUser>

    <div class="wrapper" v-if="auth.user.tipo != 'POSTULANTE'">
        <NavBar></NavBar>
        <SideBar></SideBar>
        <slot />
        <Footer></Footer>
    </div>
    <div v-else>
        <slot />
    </div>
</template>
