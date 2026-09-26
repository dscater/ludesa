<script setup>
import Content from "@/Components/Content.vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { useCampeonatoInscripcions } from "@/composables/campeonato_inscripcions/useCampeonatoInscripcions";
import { useAxios } from "@/composables/axios/useAxios";
import { ref, onMounted, onBeforeMount } from "vue";
import { useAppStore } from "@/stores/aplicacion/appStore";
// import { useMenu } from "@/composables/useMenu";
import Formulario from "./Formulario.vue";
import CarreraJugador from "./CarreraJugador.vue";
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
            cargarCampeonatoInscripcions(),
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
const multiSearch = ref({
    search: "",
    campeonato_id: "",
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
    cargarCampeonatoInscripcions();
};

const cargarCampeonatoInscripcions = async () => {
    loadingLista.value = true;
    try {
        const res = await axios.get(route("campeonato_inscripcions.paginado"), {
            params: {
                perPage: perPage.value,
                page: currentPage.value,
                campeonato_id: multiSearch.value.campeonato_id,
                porCampeonato: true,
            },
        });
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
    cargarCampeonatoInscripcions();
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

const muestra_formulario = ref(false);
const muestra_formulario_carrera_jugador = ref(false);

const agregarRegistro = () => {
    limpiarCampeonatoInscripcion();
    if (multiSearch.value.campeonato_id) {
        form.campeonato = listCampeonatos.value.filter(
            (item) => item.id == multiSearch.value.campeonato_id,
        )[0];
        form.campeonato_id = form.campeonato.id;
    }
    muestra_formulario.value = true;
};

const editarRegistro = (item) => {
    limpiarCampeonatoInscripcion();
    muestra_formulario.value = true;
    setCampeonatoInscripcion(item);
};

const updateDatatable = async () => {
    limpiarCampeonatoInscripcion();
    muestra_formulario.value = false;
    muestra_formulario_carrera_jugador.value = false;
    if (multiSearch.value.campeonato_id) {
        cargarCampeonatoInscripcions();
    }
};

const updateDatos = () => {
    if (multiSearch.value.campeonato_id) {
        cargarCampeonatoInscripcions();
    }
};

const eliminarCampeonatoInscripcion = (item) => {
    Swal.fire({
        title: "¿Quierés eliminar este registro?",
        html: `<strong>${item.carrera.nombre}</strong>`,
        showCancelButton: true,
        confirmButtonText: "Si, eliminar",
        cancelButtonText: "No, cancelar",
        denyButtonText: `No, cancelar`,
        customClass: {
            confirmButton: "btn-danger",
        },
    }).then(async (result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
            let respuesta = await axiosDelete(
                route("campeonato_inscripcions.destroy", item.id),
            );
            if (respuesta && respuesta.sw) {
                updateDatatable();
            }
        }
    });
};

const mostrarJugadores = (item) => {
    limpiarCampeonatoInscripcion();
    setCampeonatoInscripcion(item);
    muestra_formulario_carrera_jugador.value = true;
};
</script>
<template>
    <Head title="Inscripción de Carreras"></Head>
    <Content>
        <template #header>
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="m-0">
                        <i class="fa fa-list"></i> Inscripción de Carreras
                    </h3>
                </div>
                <!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item">
                            <Link :href="route('inicio')">Inicio</Link>
                        </li>
                        <li class="breadcrumb-item active">
                            Inscripción de Carreras
                        </li>
                    </ol>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </template>
        <div class="row">
            <div class="col-8">
                <div class="input-group">
                    <button
                        class="btn btn-light border"
                        type="button"
                        @click="cargarCampeonatoInscripcions"
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
                <button
                    v-if="
                        props_page.auth?.user.permisos == '*' ||
                        props_page.auth?.user.permisos.includes(
                            'campeonato_inscripcions.create',
                        )
                    "
                    type="button"
                    class="btn btn-primary text-sm w-100"
                    :disabled="!multiSearch.campeonato_id"
                    @click="agregarRegistro"
                >
                    <i class="fa fa-plus"></i> Nueva Inscripción
                </button>
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
                                <button
                                    type="button"
                                    class="btn btn-danger btn-sm fs-8 float-end"
                                    @click.prevent="
                                        eliminarCampeonatoInscripcion(item)
                                    "
                                >
                                    <i class="fa fa-trash"></i>
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-warning btn-sm fs-8 float-end me-1"
                                    @click.prevent="editarRegistro(item)"
                                >
                                    <i class="fa fa-edit"></i>
                                </button>
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
                                    <div
                                        class="col-8 text-center py-2 fs-5"
                                        title="Jugadores Inscritos"
                                    >
                                        <div class="fw-bold">
                                            {{ item?.carrera_jugadors.length }}
                                        </div>
                                        <div class="fw-bold">
                                            <i class="fa fa-user-friends"></i>
                                        </div>
                                    </div>
                                    <div
                                        class="col-4 border-start py-3 text-md text-center"
                                    >
                                        <div>{{ item.pts }}</div>
                                        <div>Pts.</div>
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
                                    @click.prevent="mostrarJugadores(item)"
                                >
                                    <i class="fa fa-clipboard-list"></i>
                                    Jugadores
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
        <CarreraJugador
            v-if="muestra_formulario_carrera_jugador"
            :muestra_formulario="muestra_formulario_carrera_jugador"
            :form="form"
            @envio-formulario="updateDatos"
            @cerrar-formulario="muestra_formulario_carrera_jugador = false"
        ></CarreraJugador>
    </Content>
</template>
