<script setup>
import Content from "@/Components/Content.vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { usePartidos } from "@/composables/partidos/usePartidos";
import { useAxios } from "@/composables/axios/useAxios";
import { ref, onMounted, onBeforeMount, computed } from "vue";
import { useAppStore } from "@/stores/aplicacion/appStore";
// import { useMenu } from "@/composables/useMenu";
import Formulario from "./Formulario.vue";
import MiPaginacion from "@/Components/MiPaginacion.vue";
import { buttonProps } from "element-plus";
import { useDate } from "@/composables/useDate.js";
// const { mobile, identificaDispositivo } = useMenu();
const { props: props_page } = usePage();
const appStore = useAppStore();
onBeforeMount(async () => {
    appStore.startLoading();

    try {
        await Promise.all([cargarCampeonatos(), cargarPartidos()]);
    } finally {
        appStore.stopLoading();
    }
});

onMounted(() => {});

const { setPartido, limpiarPartido, form } = usePartidos();
const { axiosDelete, axiosPost } = useAxios();

const listCampeonatos = ref([]);

const multiSearch = ref({
    search: "",
    campeonato_id: "",
    fecha_ini: useDate().getFechaActual(),
    fecha_fin: useDate().getFechaActual(),
    estado: "",
    filtro: [],
});
const campeonatoSeleccionado = computed(() => {
    return listCampeonatos.value.filter(
        (elem) => elem.id == multiSearch.value.campeonato_id,
    )[0];
});

const listPartidos = ref([]);
const loadingLista = ref(false);
const currentPage = ref(1);
const perPage = ref(24);
const total_registros = ref(0);
const cambioDePagina = async (value) => {
    loadingLista.value = true;
    currentPage.value = value;
    cargarPartidos();
};

const cargarPartidos = async () => {
    loadingLista.value = true;
    try {
        const res = await axios.get(route("partidos.paginado"), {
            params: {
                perPage: perPage.value,
                page: currentPage.value,
                campeonato_id: multiSearch.value.campeonato_id,
                fecha_ini: multiSearch.value.fecha_ini,
                fecha_fin: multiSearch.value.fecha_fin,
                estado: multiSearch.value.estado,
                porCampeonato: true,
            },
        });
        listPartidos.value = res.data.data;
        total_registros.value = res.data.total;
    } catch (e) {
        console.log(e);
    } finally {
        loadingLista.value = false;
    }
};

const detectarCambiosFiltros = () => {
    currentPage.value = 1;
    cargarPartidos();
    if (multiSearch.value.campeonato_id) {
        form.campeonato = listCampeonatos.value.filter(
            (item) => item.id == multiSearch.value.campeonato_id,
        )[0];
    } else {
        limpiarPartido();
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

const agregarRegistro = () => {
    limpiarPartido();
    if (multiSearch.value.campeonato_id) {
        form.campeonato = listCampeonatos.value.filter(
            (item) => item.id == multiSearch.value.campeonato_id,
        )[0];
        form.campeonato_id = form.campeonato.id;
    }
    muestra_formulario.value = true;
};

const editarRegistro = (item) => {
    limpiarPartido();
    muestra_formulario.value = true;
    setPartido(item);
};

const updateDatatable = async () => {
    limpiarPartido();
    muestra_formulario.value = false;
    if (multiSearch.value.campeonato_id) {
        cargarPartidos();
    }
};

const updateDatos = () => {
    if (multiSearch.value.campeonato_id) {
        cargarPartidos();
    }
};

const eliminarPartido = (item) => {
    Swal.fire({
        title: "¿Quierés eliminar este registro?",
        html: `<strong>${item.ci_local.carrera.nombre}</strong> vs <strong>${item.ci_visitante.carrera.nombre}</strong>`,
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
                route("partidos.destroy", item.id),
            );
            if (respuesta && respuesta.sw) {
                updateDatatable();
            }
        }
    });
};

const iniciarPartido = (item) => {
    if (item.estado == "INICIADO") {
        router.get(route("partidos.ver", item.id));
    } else {
        Swal.fire({
            title: "¿Quierés iniciar este partido?",
            html: `<strong>${item.ci_local.carrera.nombre}</strong> vs <strong>${item.ci_visitante.carrera.nombre}</strong>`,
            showCancelButton: true,
            confirmButtonText: "Si, iniciar",
            cancelButtonText: "No, cancelar",
            denyButtonText: `No, cancelar`,
            customClass: {
                confirmButton: "btn-danger",
            },
        }).then(async (result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                let respuesta = await axiosPost(
                    route("partidos.iniciarPartido", item.id),
                    {
                        _method: "PUT",
                    },
                );
                if (respuesta && respuesta.sw) {
                    router.get(route("partidos.ver", item.id));
                }
            }
        });
    }
};
</script>
<template>
    <Head title="Partidos"></Head>
    <Content>
        <template #header>
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="m-0"><i class="fa fa-table"></i> Partidos</h3>
                </div>
                <!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item">
                            <Link :href="route('inicio')">Inicio</Link>
                        </li>
                        <li class="breadcrumb-item active">Partidos</li>
                    </ol>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </template>
        <div class="row">
            <div class="col-8">
                <div class="row">
                    <div class="col-12">
                        <div class="input-group">
                            <button
                                class="btn btn-light border"
                                type="button"
                                @click="cargarPartidos"
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
                                    @change="detectarCambiosFiltros"
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
                    <div class="col-12">
                        <div class="row">
                            <div class="col-md-4">
                                <span class="text-xs text-muted">Desde</span>
                                <input
                                    type="date"
                                    class="form-control"
                                    v-model="multiSearch.fecha_ini"
                                    @change="detectarCambiosFiltros"
                                    @keyup="detectarCambiosFiltros"
                                />
                            </div>
                            <div class="col-md-4">
                                <span class="text-xs text-muted">Hasta</span>
                                <input
                                    type="date"
                                    class="form-control"
                                    v-model="multiSearch.fecha_fin"
                                    @change="detectarCambiosFiltros"
                                    @keyup="detectarCambiosFiltros"
                                />
                            </div>
                            <div class="col-md-4">
                                <span class="text-xs text-muted">Estado</span>
                                <el-select
                                    v-model="multiSearch.estado"
                                    placeholder="Estado del Partido"
                                    filterable
                                    clearable
                                    @change="detectarCambiosFiltros"
                                >
                                    <el-option
                                        v-for="item in [
                                            'PENDIENTE',
                                            'INICIADO',
                                            'FINALIZADO',
                                        ]"
                                        :key="item"
                                        :value="item"
                                        :label="item"
                                    ></el-option>
                                </el-select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <button
                    v-if="
                        campeonatoSeleccionado &&
                        campeonatoSeleccionado.estado == 'VIGENTE' &&
                        (props_page.auth?.user.permisos == '*' ||
                            props_page.auth?.user.permisos.includes(
                                'partidos.create',
                            ))
                    "
                    type="button"
                    class="btn btn-primary text-sm w-100"
                    :disabled="!multiSearch.campeonato_id"
                    @click="agregarRegistro"
                >
                    <i class="fa fa-plus"></i> Nuevo Partido
                </button>
                <a
                    v-if="
                        multiSearch.campeonato_id &&
                        (props_page.auth?.user.permisos == '*' ||
                            props_page.auth?.user.permisos.includes(
                                'reportes.r_fixture',
                            ))
                    "
                    :disabled="!multiSearch.campeonato_id"
                    class="btn btn-success text-sm w-100"
                    :href="
                        route('reportes.r_fixture') +
                        `?fecha_ini=${multiSearch.fecha_ini}&fecha_fin=${multiSearch.fecha_fin}&campeonato_id=${multiSearch.campeonato_id}`
                    "
                    target="_blank"
                >
                    <i class="fa fa-table"></i> Exportar Fixture
                </a>
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
                v-for="item in listPartidos"
                :key="item.id"
            >
                <div class="card mt-2">
                    <div class="card-header bg-principal">
                        <div class="row">
                            <div class="col-12">
                                <button
                                    type="button"
                                    class="btn btn-danger btn-sm fs-8 float-end"
                                    @click.prevent="eliminarPartido(item)"
                                    v-if="item.estado == 'PENDIENTE'"
                                >
                                    <i class="fa fa-trash"></i>
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-warning btn-sm fs-8 float-end me-1"
                                    @click.prevent="editarRegistro(item)"
                                    v-if="item.estado == 'PENDIENTE'"
                                >
                                    <i class="fa fa-edit"></i>
                                </button>
                                <h4 class="fw-bold fs-6 text-primary">
                                    {{ item.fecha_hora_t }}
                                </h4>
                                <h4
                                    class="fs-8 fw-bold mb-0"
                                    :class="{
                                        'text-success':
                                            item.estado == 'INICIADO',
                                        'text-danger':
                                            item.estado == 'FINALIZADO',
                                        'text-warning':
                                            item.estado == 'PENDIENTE',
                                    }"
                                >
                                    <i class="fa fa-circle"></i>
                                    {{ item.estado }}
                                </h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body py-2">
                        <div class="row">
                            <div class="col-12 border-bottom">
                                <div class="row">
                                    <div class="col-5 text-center">
                                        <img
                                            :src="
                                                item.ci_local.carrera.url_logo
                                            "
                                            height="50"
                                            width="50"
                                            class="img-fluid mb-1"
                                            alt="Logo"
                                        />
                                        <h4 class="fw-bold fs-6 text-primary">
                                            {{ item.ci_local.carrera.nombre }}
                                        </h4>
                                    </div>
                                    <div class="col-2 text-center">VS</div>
                                    <div class="col-5 text-center">
                                        <img
                                            :src="
                                                item.ci_visitante.carrera
                                                    .url_logo
                                            "
                                            height="50"
                                            width="50"
                                            class="img-fluid mb-1"
                                            alt="Logo"
                                        />
                                        <h4 class="fw-bold fs-6 text-primary">
                                            {{
                                                item.ci_visitante.carrera.nombre
                                            }}
                                        </h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="row">
                                    <div
                                        class="col-6 text-center py-2 fs-5"
                                        title="Jugadores Inscritos"
                                    >
                                        <div class="fw-bold">
                                            {{ item.goles_local }}
                                        </div>
                                        <div class="fw-bold">
                                            <i class="fa fa-futbol"></i>
                                        </div>
                                    </div>
                                    <div
                                        class="col-6 text-center py-2 fs-5 border-start"
                                        title="Jugadores Inscritos"
                                    >
                                        <div class="fw-bold">
                                            {{ item.goles_visitante }}
                                        </div>
                                        <div class="fw-bold">
                                            <i class="fa fa-futbol"></i>
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
                                    class="btn btn-primary btn-sm text-xs float-end ms-1"
                                    v-if="
                                        item.estado == 'PENDIENTE' &&
                                        (props_page.auth?.user.permisos ==
                                            '*' ||
                                            props_page.auth?.user.permisos.includes(
                                                'partidos.iniciarPartido',
                                            ))
                                    "
                                    @click.prevent="iniciarPartido(item)"
                                >
                                    <span
                                        ><i
                                            class="fa fa-flag-checkered me-1"
                                        ></i
                                        >Iniciar Partido</span
                                    >
                                </button>

                                <Link
                                    class="btn btn-primary btn-sm text-xs float-end ms-1"
                                    v-if="
                                        item.estado == 'INICIADO' &&
                                        (props_page.auth?.user.permisos ==
                                            '*' ||
                                            props_page.auth?.user.permisos.includes(
                                                'partidos.iniciarPartido',
                                            ))
                                    "
                                    :href="route('partidos.ver', item.id)"
                                >
                                    <span
                                        ><i class="fa fa-arrow-right me-1"></i
                                        >Volver al Partido</span
                                    >
                                </Link>

                                <Link
                                    v-if="
                                        item.estado == 'FINALIZADO' &&
                                        (props_page.auth?.user.permisos ==
                                            '*' ||
                                            props_page.auth?.user.permisos.includes(
                                                'partidos.detalles',
                                            ))
                                    "
                                    class="btn btn-info btn-sm text-xs float-end"
                                    :href="route('partidos.detalles', item.id)"
                                >
                                    <i class="fa fa-clipboard-list"></i>
                                    Detalles
                                </Link>
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
    </Content>
</template>
