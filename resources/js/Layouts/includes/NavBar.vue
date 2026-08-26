<script setup>
// Composables
import { usePage, Link } from "@inertiajs/vue3";
import { onMounted, onUnmounted, ref } from "vue";
import { useSideBar } from "@/composables/useSidebar.js";
import { useConfiguracionStore } from "@/stores/configuracion/configuracionStore";
import LoginUser from "@/Components/LoginUser.vue";
import { useLoginUserStore } from "@/stores/login_users/loginUserStore";
const loginUserStore = useLoginUserStore();
const configuracionStore = useConfiguracionStore();
const muestra_login_user = ref(false);
const accion_login_user = ref(0);

const { props } = usePage();
const { toggleSidebar } = useSideBar();

const salir = () => {
    Swal.fire({
        icon: "question",
        title: "Cerrar sesión",
        html: `¿Esta seguro(a) de cerrar sesión?`,
        showCancelButton: true,
        confirmButtonText: "Si, salir",
        cancelButtonText: "Cancelar",
        denyButtonText: `Cancelar`,
        customClass: {
            confirmButton: "btn-success",
        },
    }).then(async (result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
            axios
                .post(route("logout"))
                .then((response) => {})
                .finally(() => {
                    window.location.href = "/";
                });
        }
    });
};

onMounted(() => {});

onUnmounted(() => {});
</script>
<template>
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a
                    class="nav-link toggleButton"
                    href="#"
                    role="button"
                    @click="toggleSidebar"
                    ><i class="fas fa-bars"></i
                ></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <span class="nav-link">
                    <i class="fa fa-building"></i>
                    {{ loginUserStore.getLoginUser?.sucursal?.nombre }}
                    <i
                        class="fa fa-cog cursor-pointer"
                        title="Cambiar Sucursal"
                        @click="
                            muestra_login_user = true;
                            accion_login_user = 1;
                        "
                    ></i>
                </span>
            </li>
            <!-- <li class="nav-item d-none d-sm-inline-block">
                <Link :href="route('pagos.create')" class="nav-link">Nuevo Pago</Link>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <Link :href="route('trabajos.create')" class="nav-link">Nuevo Trabajo</Link>
            </li> -->
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a
                    class="nav-link"
                    data-widget="fullscreen"
                    href="#"
                    role="button"
                    @click.prevent="salir"
                >
                    <i class="fas fa-power-off"></i>
                </a>
            </li>
        </ul>
        <LoginUser
            :muestra_formulario="muestra_login_user"
            :accion_formulario="accion_login_user"
            @envio-formulario="
                muestra_login_user = false;
                accion_login_user = 0;
            "
            @cerrar-formulario="
                muestra_login_user = false;
                accion_login_user = 0;
            "
        ></LoginUser>
    </nav>
    <!-- /.navbar -->
</template>
