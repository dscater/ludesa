<script setup>
import { onMounted, onUnmounted, ref, nextTick, reactive } from "vue";
import { router, usePage, Link } from "@inertiajs/vue3";
import ItemMenu from "@/Components/ItemMenu.vue";
import { useSideBar } from "@/composables/useSidebar.js";
import { useAppStore } from "@/stores/aplicacion/appStore";
import { useConfiguracionStore } from "@/stores/configuracion/configuracionStore";
const { closeSidebar, toggleSubMenuELem } = useSideBar();
const { auth } = usePage().props;
const configuracionStore = useConfiguracionStore();
const appStore = useAppStore();
const usuario = ref(null);
const permisos = ref([]);
const route_current = ref("");

const toggleSubMenu = (menu) => {
    openMenus[menu] = !openMenus[menu];
};

const sincronizarMenus = () => {
    Object.keys(openMenus).forEach((key) => {
        openMenus[key] = false;
    });

    if (
        route_current.value == "campeonatos.index" ||
        route_current.value == "campeonato_inscripcions.index" ||
        route_current.value == "campeonato_inscripcions.pagos"
    ) {
        openMenus.campeonatos = true;
    }

    // if (
    //     route_current.value == "reportes.usuarios" ||
    // ) {
    //     openMenus.reportes = true;
    // }
};

const openMenus = reactive({
    usuarios: false,
    campeonatos: false,
    reportes: false,
});

router.on("navigate", (event) => {
    route_current.value = route().current();
    sincronizarMenus();
    closeSidebar();
});

onMounted(() => {
    usuario.value = appStore.getUsuario;
    permisos.value = auth.user.permisos;
    route_current.value = route().current();
    sincronizarMenus();
});

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

onUnmounted(() => {});
</script>
<template>
    <!-- Main Sidebar Container -->
    <aside class="app-sidebar shadow bgWhite">
        <!-- Brand Logo -->
        <div class="sidebar-brand bg1">
            <a
                :href="route('inicio')"
                class="brand-link d-flex justify-content-center align-items-center py-0"
            >
                <img
                    :src="configuracionStore.oConfiguracion.url_logo"
                    alt="Logo"
                    class="rounded-circle"
                    style="max-height: 51px"
                />
                <span class="brand-text font-weight-600 ml-1 text-white">{{
                    configuracionStore.oConfiguracion.nombre_sistema
                }}</span>
            </a>
        </div>
        <!-- Sidebar -->
        <div class="sidebar-wrapper">
            <!-- Sidebar user panel (optional) -->
            <div class="user-panel mt-3 pb-2 d-flex border-bottom">
                <div class="image">
                    <img
                        :src="usuario?.url_foto"
                        class="rounded-circle elevation-2 user-image"
                        alt="User Image"
                    />
                </div>
                <div class="info">
                    <Link
                        :href="route('profile.edit')"
                        class="d-block text-decoration-none"
                    >
                        <div class="nombre">
                            {{ usuario?.nombre }} {{ usuario?.paterno }}
                            {{ usuario?.materno }}
                        </div>
                        <div class="tipo">{{ usuario?.tipo }}</div>
                    </Link>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul
                    class="nav sidebar-menu flex-column"
                    data-bs-toggle="treeview"
                    role="navigation"
                    aria-label="Main navigation"
                    data-accordion="false"
                    id="navigation"
                >
                    <ItemMenu
                        :label="'Inicio'"
                        :ruta="'inicio'"
                        :icon="'fa fa-home'"
                    ></ItemMenu>
                    <ItemMenu
                        v-if="
                            permisos == '*' ||
                            permisos.includes('partidos.index')
                        "
                        :label="'Partidos'"
                        :array-ruta-class-active="[
                            'partidos.index',
                            'partidos.ver',
                        ]"
                        :ruta="'partidos.index'"
                        :icon="'fa fa-table'"
                    ></ItemMenu>
                    <li
                        class="nav-item"
                        v-if="
                            permisos == '*' ||
                            permisos.includes('campeonatos.index') ||
                            permisos.includes('campeonato_inscripcions.index')
                        "
                        :class="{ 'menu-open': openMenus.campeonatos }"
                    >
                        <a
                            href="#"
                            class="nav-link"
                            :class="[
                                route_current == 'campeonatos.index' ||
                                route_current == 'campeonato_inscripcions.index'
                                    ? 'active menu-is-opening menu-open'
                                    : '',
                            ]"
                            @click.prevent="toggleSubMenu('campeonatos')"
                        >
                            <i class="nav-icon fa fa-list"></i>
                            <p>
                                Campeonatos
                                <i class="nav-arrow fa fa-chevron-right"></i>
                            </p>
                        </a>
                        <ul
                            class="nav nav-treeview"
                            role="navigation"
                            aria-label="Navigation 4"
                            :style="{
                                maxHeight: openMenus.campeonatos
                                    ? '500px'
                                    : '0px',
                            }"
                        >
                            <ItemMenu
                                v-if="
                                    permisos == '*' ||
                                    permisos.includes('campeonatos.index') ||
                                    permisos.includes(
                                        'campeonato_inscripcions.index',
                                    )
                                "
                                :label="'Lista de Campeonatos'"
                                :ruta="'campeonatos.index'"
                                :icon="'fa fa-angle-right'"
                            ></ItemMenu>
                            <ItemMenu
                                v-if="
                                    permisos == '*' ||
                                    permisos.includes(
                                        'campeonato_inscripcions.index',
                                    )
                                "
                                :label="'Inscripciones'"
                                :ruta="'campeonato_inscripcions.index'"
                                :icon="'fa fa-angle-right'"
                            ></ItemMenu>
                            <ItemMenu
                                v-if="
                                    permisos == '*' ||
                                    permisos.includes(
                                        'campeonato_inscripcions.pagos',
                                    )
                                "
                                :label="'Pagos Pendientes'"
                                :ruta="'campeonato_inscripcions.pagos'"
                                :icon="'fa fa-angle-right'"
                            ></ItemMenu>
                        </ul>
                    </li>
                    <ItemMenu
                        v-if="
                            permisos == '*' ||
                            permisos.includes('jugadors.index')
                        "
                        :label="'Jugadores'"
                        :ruta="'jugadors.index'"
                        :icon="'fa fa-user-friends'"
                    ></ItemMenu>
                    <ItemMenu
                        v-if="
                            permisos == '*' ||
                            permisos.includes('carreras.index')
                        "
                        :label="'Carreras'"
                        :ruta="'carreras.index'"
                        :icon="'fa fa-list-alt'"
                    ></ItemMenu>
                    <ItemMenu
                        v-if="
                            permisos == '*' ||
                            permisos.includes('usuarios.index')
                        "
                        :label="'Usuarios'"
                        :ruta="'usuarios.index'"
                        :icon="'fa fa-users'"
                    ></ItemMenu>
                    <li
                        class="nav-header font-weight-bold"
                        v-if="
                            permisos == '*' ||
                            permisos.includes('reportes.usuarios') ||
                            permisos.includes('reportes.carreras') ||
                            permisos.includes('reportes.carrera_jugadors') ||
                            permisos.includes('reportes.posicion') ||
                            permisos.includes('reportes.resultado_partidos') ||
                            permisos.includes('reportes.goleadores') ||
                            permisos.includes('reportes.porteros')
                        "
                    >
                        REPORTES
                    </li>
                    <ItemMenu
                        v-if="
                            permisos == '*' ||
                            permisos.includes('reportes.posicions')
                        "
                        :label="'Tabla de Posiciones'"
                        :ruta="'reportes.posicions'"
                        :icon="'fa fa-file-pdf'"
                    ></ItemMenu>
                    <ItemMenu
                        v-if="
                            permisos == '*' ||
                            permisos.includes('reportes.resultado_partidos')
                        "
                        :label="'Resultado de Partidos'"
                        :ruta="'reportes.resultado_partidos'"
                        :icon="'fa fa-file-pdf'"
                    ></ItemMenu>
                    <ItemMenu
                        v-if="
                            permisos == '*' ||
                            permisos.includes('reportes.pagos_pendientes')
                        "
                        :label="'Pagos Pendientes'"
                        :ruta="'reportes.pagos_pendientes'"
                        :icon="'fa fa-file-pdf'"
                    ></ItemMenu>
                    <ItemMenu
                        v-if="
                            permisos == '*' ||
                            permisos.includes('reportes.goleadores')
                        "
                        :label="'Tabla de Goleadores'"
                        :ruta="'reportes.goleadores'"
                        :icon="'fa fa-file-pdf'"
                    ></ItemMenu
                    ><ItemMenu
                        v-if="
                            permisos == '*' ||
                            permisos.includes('reportes.porteros')
                        "
                        :label="'Tabla de Porteros'"
                        :ruta="'reportes.porteros'"
                        :icon="'fa fa-file-pdf'"
                    ></ItemMenu>
                    <ItemMenu
                        v-if="
                            permisos == '*' ||
                            permisos.includes('reportes.carrera_jugadors')
                        "
                        :label="'Jugadores Inscritos'"
                        :ruta="'reportes.carrera_jugadors'"
                        :icon="'fa fa-file-pdf'"
                    ></ItemMenu>
                    <ItemMenu
                        v-if="
                            permisos == '*' ||
                            permisos.includes('reportes.carreras')
                        "
                        :label="'Lista de Carreras'"
                        :ruta="'reportes.carreras'"
                        :icon="'fa fa-file-pdf'"
                    ></ItemMenu>
                    <ItemMenu
                        v-if="
                            permisos == '*' ||
                            permisos.includes('reportes.usuarios')
                        "
                        :label="'Lista de Usuarios'"
                        :ruta="'reportes.usuarios'"
                        :icon="'fa fa-file-pdf'"
                    ></ItemMenu>
                    <li class="nav-header font-weight-bold">OTROS</li>
                    <ItemMenu
                        v-if="
                            permisos == '*' ||
                            permisos.includes('configuracions.index')
                        "
                        :label="'Configuración Sistema'"
                        :ruta="'configuracions.index'"
                        :icon="'fa fa-cog'"
                    ></ItemMenu>
                    <!-- <ItemMenu
                        :label="'Perfil'"
                        :ruta="'profile.edit'"
                        :icon="'fa fa-id-card'"
                    ></ItemMenu> -->
                    <li class="nav-item">
                        <a
                            href="#"
                            class="nav-link"
                            @click.prevent="salir()"
                            ref="link"
                        >
                            <i class="nav-icon fa fa-power-off"></i>
                            <p>Salir</p>
                        </a>
                    </li>
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>
</template>
<style scoped></style>
