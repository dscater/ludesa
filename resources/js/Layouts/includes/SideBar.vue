<script setup>
import { onMounted, onUnmounted, ref, nextTick } from "vue";
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
const toggleSubMenu = (e) => {
    e.stopPropagation();
    const elem = e.currentTarget;
    if (
        elem.classList.contains("menu-is-opening") &&
        elem.classList.contains("menu-open")
    ) {
        elem.classList.remove("menu-is-opening");
        elem.classList.remove("menu-open");
        toggleSubMenuELem(elem, false);
    } else {
        elem.classList.add("menu-is-opening");
        elem.classList.add("menu-open");
        toggleSubMenuELem(elem, true);
    }
};

const route_current = ref("");
router.on("navigate", (event) => {
    route_current.value = route().current();
    closeSidebar();
});

onMounted(() => {
    configuracionStore.initConfiguracion();
    usuario.value = appStore.getUsuario;
    permisos.value = auth.user.permisos;
    // Selecciona el elemento del widget
    var sidebarSearchElement = $('[data-widget="sidebar-search"]');
    // Configura manualmente el texto de "no encontrado"
    sidebarSearchElement.data("notFoundText", "Sin resultados");
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
    <aside class="main-sidebar sidebar-light-primary elevation-1">
        <!-- Brand Logo -->
        <a
            :href="route('inicio')"
            class="brand-link d-flex justify-content-center align-items-center py-0"
            style="height: 57px"
        >
            <img
                :src="configuracionStore.oConfiguracion.url_logo"
                alt="Logo"
                class=""
                style="opacity: 1; height: 80%"
            />
            <span class="brand-text font-weight-600 ml-1">{{
                configuracionStore.oConfiguracion.nombre_sistema
            }}</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar p-0">
            <!-- Sidebar user panel (optional) -->
            <div class="user-panel mt-3 pb-2 d-flex">
                <div class="image">
                    <img
                        :src="usuario?.url_foto"
                        class="img-circle elevation-2"
                        alt="User Image"
                    />
                </div>
                <div class="info">
                    <Link :href="route('profile.edit')" class="d-block">
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
                    class="nav nav-pills nav-sidebar flex-column"
                    data-widget="treeview"
                    role="menu"
                    data-accordion="false"
                >
                    <ItemMenu
                        :label="'Inicio'"
                        :ruta="'inicio'"
                        :icon="'fa fa-home'"
                    ></ItemMenu>
                    <li
                        class="nav-header font-weight-bold"
                        v-if="
                            permisos == '*' ||
                            permisos.includes('usuarios.index') ||
                            permisos.includes('clientes.index') ||
                            permisos.includes('sucursals.index') ||
                            permisos.includes('tipo_certificados.index') ||
                            permisos.includes('certificados.index')
                        "
                    >
                        ADMINISTRACIÓN
                    </li>
                    <ItemMenu
                        v-if="
                            permisos == '*' || permisos.includes('pagos.index')
                        "
                        :label="'Arqueo de Caja'"
                        :ruta="'pagos.index'"
                        :icon="'fa fa-cash-register'"
                    ></ItemMenu>
                    <ItemMenu
                        v-if="
                            permisos == '*' ||
                            permisos.includes('recepcion_pagos.index')
                        "
                        :label="'Recepción de Pagos'"
                        :ruta="'recepcion_pagos.index'"
                        :icon="'fa fa-clipboard-check'"
                    ></ItemMenu>
                    <ItemMenu
                        v-if="
                            permisos == '*' || permisos.includes('cobros.index')
                        "
                        :label="'Cobros'"
                        :ruta="'cobros.index'"
                        :icon="'fa fa-hand-holding-usd'"
                    ></ItemMenu>
                    <ItemMenu
                        v-if="
                            permisos == '*' ||
                            permisos.includes('certificados.index')
                        "
                        :arrayRutaClassActive="[
                            'certificados.index',
                            'certificados.create',
                            'certificados.edit',
                        ]"
                        :label="'Certificados'"
                        :ruta="'certificados.index'"
                        :icon="'fa fa-clipboard-list'"
                    ></ItemMenu>
                    <ItemMenu
                        v-if="
                            permisos == '*' ||
                            permisos.includes('clientes.index')
                        "
                        :label="'Clientes'"
                        :ruta="'clientes.index'"
                        :icon="'fa fa-user-friends'"
                    ></ItemMenu>
                    <ItemMenu
                        v-if="
                            permisos == '*' ||
                            permisos.includes('tipo_certificados.index')
                        "
                        :label="'Tipo de Certificados'"
                        :ruta="'tipo_certificados.index'"
                        :icon="'fa fa-list'"
                    ></ItemMenu>
                    <ItemMenu
                        v-if="
                            permisos == '*' ||
                            permisos.includes('tramitadors.index')
                        "
                        :label="'Tramitadores'"
                        :ruta="'tramitadors.index'"
                        :icon="'fa fa-address-book'"
                    ></ItemMenu>
                    <ItemMenu
                        v-if="
                            permisos == '*' ||
                            permisos.includes('sucursals.index')
                        "
                        :label="'Sucursales'"
                        :ruta="'sucursals.index'"
                        :icon="'fa fa-building'"
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
                            permisos.includes('reportes.clientes') ||
                            permisos.includes('reportes.certificados') ||
                            permisos.includes(
                                'reportes.certificados_interno',
                            ) ||
                            permisos.includes('reportes.historial_accions') ||
                            permisos.includes('reportes.gcemitidos') ||
                            permisos.includes('reportes.gmemitidos')
                        "
                    >
                        REPORTES
                    </li>
                    <ItemMenu
                        v-if="
                            permisos == '*' ||
                            permisos.includes('reportes.gcemitidos')
                        "
                        :label="'Cantidad de Certificados Emitidos'"
                        :ruta="'reportes.gcemitidos'"
                        :icon="'fa fa-chart-bar'"
                    ></ItemMenu>
                    <ItemMenu
                        v-if="
                            permisos == '*' ||
                            permisos.includes('reportes.gmemitidos')
                        "
                        :label="'Ingresos por Certificados Emitidos'"
                        :ruta="'reportes.gmemitidos'"
                        :icon="'fa fa-chart-bar'"
                    ></ItemMenu>
                    <ItemMenu
                        v-if="
                            permisos == '*' ||
                            permisos.includes('reportes.certificados_interno')
                        "
                        :label="'Certificados Emitidos Interno'"
                        :ruta="'reportes.certificados_interno'"
                        :icon="'fa fa-file-pdf'"
                    ></ItemMenu>
                    <ItemMenu
                        v-if="
                            permisos == '*' ||
                            permisos.includes('reportes.certificados')
                        "
                        :label="'Certificados Emitidos Externo'"
                        :ruta="'reportes.certificados'"
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
                    <ItemMenu
                        v-if="
                            permisos == '*' ||
                            permisos.includes('reportes.clientes')
                        "
                        :label="'Lista de Clientes'"
                        :ruta="'reportes.clientes'"
                        :icon="'fa fa-file-pdf'"
                    ></ItemMenu>
                    <ItemMenu
                        v-if="
                            permisos == '*' ||
                            permisos.includes('reportes.historial_accions')
                        "
                        :label="'Log de Usuarios'"
                        :ruta="'reportes.historial_accions'"
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
