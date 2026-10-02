<script setup>
import Content from "@/Components/Content.vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { useCampeonatoInscripcions } from "@/composables/campeonato_inscripcions/useCampeonatoInscripcions";
import { useAxios } from "@/composables/axios/useAxios";
import { ref, onMounted, onBeforeMount } from "vue";
import { useAppStore } from "@/stores/aplicacion/appStore";
// import { useMenu } from "@/composables/useMenu";
import Formulario from "./Formulario.vue";
import FormularioPagos from "./FormularioPagos.vue";
import MiPaginacion from "@/Components/MiPaginacion.vue";
import { buttonProps } from "element-plus";
// const { mobile, identificaDispositivo } = useMenu();
const { props: props_page } = usePage();
const appStore = useAppStore();
onBeforeMount(async () => {
    appStore.startLoading();

    try {
        await Promise.all([
            cargarCampeonatos(),
            cargarCarreras(),
            cargarPagos(),
        ]);
    } finally {
        appStore.stopLoading();
    }
});

onMounted(() => {});

const { setCampeonatoInscripcion, limpiarCampeonatoInscripcion, form } =
    useCampeonatoInscripcions();
const { axiosDelete } = useAxios();

const listCampeonatos = ref([]);
const listCarreras = ref([]);
const multiSearch = ref({
    search: "",
    campeonato_id: "",
    carrera_id: "",
    fecha_ini: "",
    fecha_fin: "",
    filtro: [],
});
const listCampeonatoInscripcions = ref([]);
const loadingLista = ref(false);
const currentPage = ref(1);
const perPage = ref(24);
const total_registros = ref(0);
const cambioDePagina = async (value) => {
    loadingLista.value = true;
    currentPage.value = value;
    cargarPagos();
};

const cargarPagos = async () => {
    loadingLista.value = true;
    try {
        const res = await axios.get(
            route("campeonato_inscripcions.paginadoPagos"),
            {
                params: {
                    perPage: perPage.value,
                    page: currentPage.value,
                    campeonato_id: multiSearch.value.campeonato_id,
                    carrera_id: multiSearch.value.carrera_id,
                    fecha_ini: multiSearch.value.fecha_ini,
                    fecha_fin: multiSearch.value.fecha_fin,
                    porCampeonato: true,
                },
            },
        );
        listCampeonatoInscripcions.value = res.data.data;
        total_registros.value = res.data.total;
    } catch (e) {
        console.log(e);
    } finally {
        loadingLista.value = false;
    }
};

const detectarCambioSelect = () => {
    currentPage.value = 1;
    cargarPagos();
    if (multiSearch.value.campeonato_id) {
        form.campeonato = listCampeonatos.value.filter(
            (item) => item.id == multiSearch.value.campeonato_id,
        )[0];
    } else {
        limpiarCampeonatoInscripcion();
    }
};

const cargarCampeonatos = async () => {
    try {
        const res = await axios.get(route("campeonatos.listado"));
        listCampeonatos.value = res.data.campeonatos;
    } catch (e) {
        console.log(e);
    } finally {
    }
};

const cargarCarreras = async () => {
    try {
        const res = await axios.get(route("carreras.listado"));
        listCarreras.value = res.data.carreras;
    } catch (e) {
        console.log(e);
    } finally {
    }
};

const muestra_formulario = ref(false);
const muestra_formulario_pagos = ref(false);

const updateDatatable = async () => {
    limpiarCampeonatoInscripcion();
    muestra_formulario.value = false;
    muestra_formulario_pagos.value = false;
    if (multiSearch.value.campeonato_id) {
        cargarPagos();
    }
};

const updateDatos = () => {
    if (multiSearch.value.campeonato_id) {
        cargarPagos();
        muestra_formulario_pagos.value = false;
    }
};

const mostrarDeudas = (item) => {
    limpiarCampeonatoInscripcion();
    setCampeonatoInscripcion(item);
    muestra_formulario_pagos.value = true;
};
</script>
<template>
    <Head title="Pagos Pendientes"></Head>
    <Content>
        <template #header>
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="m-0">
                        <i class="fa fa-list"></i> Pagos Pendientes
                    </h3>
                </div>
                <!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item">
                            <Link :href="route('inicio')">Inicio</Link>
                        </li>
                        <li class="breadcrumb-item active">Pagos Pendientes</li>
                    </ol>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </template>
        <div class="row">
            <div class="col-4">
                <span class="text-muted text-xs">Seleccionar Campeonato</span>
                <div class="input-group">
                    <button
                        class="btn btn-light border"
                        type="button"
                        @click="cargarPagos"
                        title="Actualizar"
                    >
                        <i class="fa fa-sync"></i>
                    </button>
                    <div class="form-control border-0 p-0">
                        <el-select
                            v-model="multiSearch.campeonato_id"
                            size="large"
                            class="el-select-input-group-right"
                            placeholder="Campeonato"
                            no-data-text="Sin Datos"
                            no-match-text="Sin Resultados"
                            filterable
                            @change="detectarCambioSelect"
                        >
                            <el-option
                                v-for="item in listCampeonatos"
                                :key="item.id"
                                :value="item.id"
                                :label="`${item.periodo} - ${item.gestion}: ${item.nombre} (${item.tipo})`"
                            ></el-option>
                        </el-select>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <span class="text-muted text-xs">Seleccionar Carrera</span>
                <div class="input-group">
                    <div class="form-control border-0 p-0">
                        <el-select
                            v-model="multiSearch.carrera_id"
                            size="large"
                            class="el-select-input-group-right"
                            placeholder="Carrera"
                            no-data-text="Sin Datos"
                            no-match-text="Sin Resultados"
                            filterable
                            clearable
                            @change="detectarCambioSelect"
                        >
                            <el-option
                                v-for="item in listCarreras"
                                :key="item.id"
                                :value="item.id"
                                :label="`${item.nombre}`"
                            ></el-option>
                        </el-select>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="row">
                    <div class="col-6">
                        <span class="text-muted text-xs">Desde:</span>
                        <input
                            type="date"
                            v-model="multiSearch.fecha_ini"
                            class="form-control"
                        />
                    </div>
                    <div class="col-6">
                        <span class="text-muted text-xs">Hasta:</span>
                        <input
                            type="date"
                            v-model="multiSearch.fecha_fin"
                            class="form-control"
                        />
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12 mt-2" v-if="!multiSearch.campeonato_id">
                <h4 class="fs-5 text-muted text-center">
                    SELECCIONA UN CAMPEONATO...
                </h4>
            </div>

            <div class="col-12 mt-3">
                Total registros: {{ total_registros }}
                <MiPaginacion
                    :pagination-class="'float-end mb-0'"
                    :total-data="total_registros"
                    :current-page="currentPage"
                    :per-page="perPage"
                    @updatePage="cambioDePagina"
                ></MiPaginacion>
            </div>
            <div class="col-12 text-center" v-if="loadingLista">
                <div class="text-muted fw-bold fs-3">Cargando...</div>
            </div>
            <div
                class="col-md-4 col-lg-3 col-sm-6"
                v-for="item in listCampeonatoInscripcions"
                :key="item.id"
            >
                <div class="card mt-2">
                    <div class="card-header bg-principal">
                        <div class="row">
                            <div class="col-12">
                                <h4 class="fw-bold fs-6 text-primary">
                                    {{ item.carrera.nombre }}
                                </h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body py-2">
                        <div class="row">
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-12 border-bottom py-2">
                                        Inscripción: Bs.
                                        <span class="fw-bold fs-5">{{
                                            item.total_inscripcion
                                        }}</span>
                                        <span
                                            class="badge"
                                            :class="{
                                                'bg-danger':
                                                    !item.pago_inscripcion,
                                                'bg-success':
                                                    item.pago_inscripcion,
                                            }"
                                            v-text="
                                                item.pago_inscripcion
                                                    ? 'CANCELADO'
                                                    : 'PENDIENTE'
                                            "
                                        ></span>
                                    </div>
                                    <div
                                        class="col-4 text-center py-2"
                                        title="Deuda Derecho Cancha"
                                    >
                                        <div class="fw-bold fs-5">
                                            Bs. {{ item.deuda_partidos }}
                                        </div>
                                        <div class="fw-bold text-sm">
                                            <i class="fa fa-table"></i>
                                            Derecho de Cancha
                                        </div>
                                    </div>
                                    <div
                                        class="col-4 text-center py-2"
                                        title="Deuda Amarillas"
                                    >
                                        <div class="fw-bold fs-5">
                                            Bs. {{ item.deuda_amarillas }}
                                        </div>
                                        <div class="fw-bold text-sm">
                                            <i
                                                class="fa fa-square text-yellow"
                                            ></i>
                                            Amarillas
                                        </div>
                                    </div>
                                    <div
                                        class="col-4 text-center py-2"
                                        title="Deuda Rojas"
                                    >
                                        <div class="fw-bold fs-5">
                                            Bs. {{ item.deuda_rojas }}
                                        </div>
                                        <div class="fw-bold text-sm">
                                            <i
                                                class="fa fa-square text-danger"
                                            ></i>
                                            Rojas
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-12">
                                <button
                                    type="button"
                                    class="btn btn-primary btn-sm text-xs float-end"
                                    @click.prevent="mostrarDeudas(item)"
                                >
                                    <i class="fa fa-clipboard-list"></i>
                                    Ver Registros
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 mt-3">
                Total registros: {{ total_registros }}
                <MiPaginacion
                    :pagination-class="'float-end'"
                    :total-data="total_registros"
                    :current-page="currentPage"
                    :per-page="perPage"
                    @updatePage="cambioDePagina"
                ></MiPaginacion>
            </div>
        </div>
        <Formulario
            v-if="muestra_formulario"
            :muestra_formulario="muestra_formulario"
            :form="form"
            @envio-formulario="updateDatatable"
            @cerrar-formulario="muestra_formulario = false"
        ></Formulario>
        <FormularioPagos
            v-if="muestra_formulario_pagos"
            :muestra_formulario="muestra_formulario_pagos"
            :form="form"
            @envio-formulario="cargarPagos()"
            @cerrar-formulario="muestra_formulario_pagos = false"
        ></FormularioPagos>
    </Content>
</template>
