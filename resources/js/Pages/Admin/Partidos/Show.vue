<script setup>
import Content from "@/Components/Content.vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { usePartidos } from "@/composables/partidos/usePartidos";
import { useAxios } from "@/composables/axios/useAxios";
import { ref, onMounted, onBeforeMount, computed, watch } from "vue";
import { useAppStore } from "@/stores/aplicacion/appStore";
// import { useMenu } from "@/composables/useMenu";
import { buttonProps } from "element-plus";
import MiTable from "@/Components/MiTable.vue";
import axios from "axios";
import { toast } from "vue3-toastify";
// const { mobile, identificaDispositivo } = useMenu();
const props = defineProps({
    campeonato: {
        type: Object,
        required: true,
    },
    partido: {
        type: Object,
        required: true,
    },
    local_detalles: {
        type: Array,
        required: true,
    },
    visitante_detalles: {
        type: Array,
        required: true,
    },
    costo_tarjetas: {
        type: Object,
        required: true,
    },
    costo_derechos: {
        type: Object,
        required: true,
    },
});
const { props: props_page } = usePage();
const appStore = useAppStore();
onBeforeMount(async () => {
    appStore.startLoading();

    try {
        // await Promise.all([cargarCampeonatos(), cargarPartidos()]);
    } finally {
        appStore.stopLoading();
    }
});

const miTableLocal = ref(null);
const miTableVisitante = ref(null);
const headers = [
    {
        label: "N°",
        key: "carrera_jugador.nro",
        sortable: true,
        width: "3%",
        fixed: true,
    },
    {
        label: "FOTO",
        key: "foto",
        sortable: true,
        fixed: true,
    },
    {
        label: "JUGADOR",
        key: "carrera_jugador.jugador.nombres",
        sortable: true,
        fixed: true,
    },
    {
        label: "TITULAR",
        key: "titular",
        sortable: true,
        fixed: true,
    },
    {
        label: "GOLES",
        key: "goles",
        sortable: true,
    },
    {
        label: "AMARILLAS",
        key: "amarillas",
        sortable: true,
    },
    {
        label: "ROJAS",
        key: "rojas",
        sortable: true,
    },
];

onMounted(async () => {
    if (miTableLocal.value) {
        await miTableLocal.value.cargarDatos();
    }

    if (miTableVisitante.value) {
        await miTableVisitante.value.cargarDatos();
    }

    props.partido.total_local = props.costo_derechos.derecho;
    props.partido.total_visitante = props.costo_derechos.derecho;
    if (props.partido.estado != "FINALIZADO") {
        actualizarDatosPartido("total_local", props.costo_derechos.derecho);
        actualizarDatosPartido("total_visitante", props.costo_derechos.derecho);
    }
});

const totalGolesLocal = computed(() => {
    return props.local_detalles.reduce((acc, item) => {
        return acc + parseFloat(item.goles);
    }, 0);
});

const totalGolesVisitante = computed(() => {
    return props.visitante_detalles.reduce((acc, item) => {
        return acc + parseFloat(item.goles);
    }, 0);
});

watch([totalGolesLocal, totalGolesVisitante], () => {
    props.partido.goles_local = totalGolesLocal.value;
    props.partido.goles_visitante = totalGolesVisitante.value;
    actualizarDatosPartido("goles_local", totalGolesLocal.value);
    actualizarDatosPartido("goles_visitante", totalGolesVisitante.value);
});

const recargarJugadores = () => {
    axios
        .get(route("partidos.actualizarJugadores", props.partido.id))
        .then((response) => {
            // recargar los datos
            router.reload({
                only: ["local_detalles", "visitante_detalles"],
            });
        });
};

const actualizarDatosPartido = (col) => {
    if (!props.partido[col] && col != "pago_local" && col != "pago_visitante") {
        toast.info("No se enviaron datos");
        return;
    }

    if (!col == "pago_local" || col == "pago_visitante") {
        if (!props.partido[col] || parseFloat(props.partido[col]) < 0) {
            toast.info("El pago no puede estar vacío o menor a 0");
            return;
        }
    }

    const data = props.partido[col];
    axios
        .post(route("partidos.actualizaDatosPartido", props.partido.id), {
            _method: "PATCH",
            col: col,
            data: data,
        })
        .then((response) => {
            router.reload({
                only: ["partido"],
            });
        });
};

const actualizarDatosDetalle = (id, col, lv) => {
    let lista = lv == "local" ? props.local_detalles : props.visitante_detalles;
    console.log(lista);
    const index = lista.findIndex((item) => item.id == id);
    // console.log(id);
    // console.log(index);

    if (
        !lista[index][col] &&
        col != "pagado_amarillas" &&
        col != "pagado_rojas"
    ) {
        toast.info("No se enviaron datos");
        return;
    }

    if (!col == "pagado_amarillas" || col == "pagado_rojas") {
        if (!lista[index][col] || parseFloat(lista[index][col]) < 0) {
            toast.info("El pago no puede estar vacío o menor a 0");
            return;
        }
    }

    const data = lista[index][col];
    axios
        .post(route("partido_detalles.actualizaDatosDetalle", id), {
            _method: "PATCH",
            col: col,
            data: data,
        })
        .then((response) => {
            const nom_listado =
                lv == "local" ? local_detalles : visitante_detalles;
            router.reload({
                only: [nom_listado],
            });
        });
};

const { setPartido, limpiarPartido, form } = usePartidos();
const { axiosDelete } = useAxios();
</script>
<template>
    <Head title="Ver Partido"></Head>
    <Content>
        <template #header>
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="m-0"><i class="fa fa-table"></i> Ver Partido</h3>
                </div>
                <!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item">
                            <Link :href="route('inicio')">Inicio</Link>
                        </li>
                        <li class="breadcrumb-item">
                            <Link :href="route('partidos.index')"
                                >Partidos</Link
                            >
                        </li>
                        <li class="breadcrumb-item active">Ver Partido</li>
                    </ol>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </template>
        <div class="row">
            <div class="col-12">
                <h4 class="text-primary text-center fs-5">
                    {{ campeonato.periodo }} - {{ campeonato.gestion }}:
                    {{ campeonato.nombre }}
                </h4>
                <h4 class="text-primary text-center fs-6">
                    {{ partido.fecha_hora_t }}
                </h4>
            </div>
            <div class="col-12">
                <button
                    type="button"
                    class="btn btn-light border float-end px-3 ms-1"
                    title="Recargar Jugadores"
                    @click.prevent="recargarJugadores"
                >
                    <i class="fa fa-user-friends"></i>
                </button>
                <button class="btn btn-primary float-end px-3">
                    <i class="fa fa-flag-checkered"></i> Finalizar Partido
                </button>
            </div>
            <!-- LOCAL -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-principal text-white">
                        <div class="row">
                            <div class="col-8 align-items-end">
                                <h4 class="fs-6 text-white fw-bold">
                                    Local: {{ partido.ci_local.carrera.nombre }}
                                </h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="input-group">
                                            <span class="input-group-text px-1"
                                                ><i
                                                    class="fa fa-futbol me-1"
                                                ></i
                                                >Goles</span
                                            >
                                            <input
                                                type="number"
                                                class="form-control text-center"
                                                readonly
                                                v-model="totalGolesLocal"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="col-12">
                                    <div class="input-group">
                                        <span class="input-group-text px-1"
                                            ><i
                                                class="fa fa-money-bill me-1"
                                            ></i
                                            >Cancelado</span
                                        >
                                        <input
                                            type="number"
                                            class="form-control text-center"
                                            :class="{
                                                'bgInactivo text-white':
                                                    !partido.pago_local,
                                                bgActivo: partido.pago_local,
                                            }"
                                            v-model="partido.total_local"
                                        />
                                        <div class="input-group-text">
                                            <input
                                                type="checkbox"
                                                class="form-conrtol"
                                                :true-value="1"
                                                :false-value="0"
                                                v-model="partido.pago_local"
                                                @change="
                                                    actualizarDatosPartido(
                                                        'pago_local',
                                                    )
                                                "
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <MiTable
                            :tableClass="'bg-white mitabla'"
                            ref="miTableLocal"
                            :cols="headers"
                            :data="local_detalles"
                            :con-paginacion="false"
                            :syncOrderBy="'id'"
                            :syncOrderAsc="'DESC'"
                            table-responsive
                            :header-class="'bg__primary'"
                            fixed-header
                            table-height="23vh"
                        >
                            <template #foto="{ item }">
                                <div class="">
                                    <img
                                        :src="
                                            item.carrera_jugador.jugador
                                                .url_foto
                                        "
                                        alt=""
                                        class="rounded-circle"
                                        height="60px"
                                    />
                                </div>
                            </template>
                            <template
                                #[`carrera_jugador.jugador.nombres`]="{ item }"
                            >
                                <div class="db-block w-100">
                                    {{ item.carrera_jugador.jugador.nombres }}
                                    {{ item.carrera_jugador.jugador.apes }}
                                </div>
                                <div class="text-muted fw-bold d-block w-100">
                                    {{ item.carrera_jugador.posicion }}
                                </div>
                                <div class="text-muted d-block">
                                    {{ item.carrera_jugador.jugador.ci }}
                                </div>
                            </template>
                            <template #titular="{ item }">
                                <div class="w-100 text-center">
                                    <input
                                        type="checkbox"
                                        v-model="item.titular"
                                        style="height: 19px; width: 19px"
                                        :true-value="1"
                                        :false-value="0"
                                        @change="
                                            actualizarDatosDetalle(
                                                item.id,
                                                'titular',
                                                'local',
                                            )
                                        "
                                    />
                                </div>
                            </template>
                            <template #goles="{ item }">
                                <div class="w-100 text-center">
                                    <input
                                        type="number"
                                        v-model="item.goles"
                                        min="0"
                                        class="form-control text-center"
                                        @keyup="
                                            actualizarDatosDetalle(
                                                item.id,
                                                'goles',
                                                'visitante',
                                            )
                                        "
                                        @change="
                                            actualizarDatosDetalle(
                                                item.id,
                                                'goles',
                                                'local',
                                            )
                                        "
                                    />
                                </div>
                            </template>
                            <template #amarillas="{ item }">
                                <div class="w-100 text-center">
                                    <input
                                        type="number"
                                        v-model="item.amarillas"
                                        class="form-control text-center"
                                        @keyup="
                                            actualizarDatosDetalle(
                                                item.id,
                                                'amarillas',
                                                'local',
                                            )
                                        "
                                        @change="
                                            actualizarDatosDetalle(
                                                item.id,
                                                'amarillas',
                                                'local',
                                            )
                                        "
                                    />
                                </div>
                            </template>
                            <template #rojas="{ item }">
                                <div class="w-100 text-center">
                                    <input
                                        type="number"
                                        v-model="item.rojas"
                                        class="form-control text-center"
                                        @keyup="
                                            actualizarDatosDetalle(
                                                item.id,
                                                'rojas',
                                                'local',
                                            )
                                        "
                                        @change="
                                            actualizarDatosDetalle(
                                                item.id,
                                                'rojas',
                                                'local',
                                            )
                                        "
                                    />
                                </div>
                            </template>
                        </MiTable>
                    </div>
                </div>
            </div>

            <!-- VISITANTE -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-principal text-white">
                        <div class="row">
                            <div class="col-8 align-items-end">
                                <h4 class="fs-6 text-white fw-bold">
                                    Visitante:
                                    {{ partido.ci_visitante.carrera.nombre }}
                                </h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="input-group">
                                            <span class="input-group-text px-1"
                                                ><i
                                                    class="fa fa-futbol me-1"
                                                ></i
                                                >Goles</span
                                            >
                                            <input
                                                type="number"
                                                class="form-control text-center"
                                                readonly
                                                v-model="totalGolesVisitante"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="col-12">
                                    <div class="input-group">
                                        <span class="input-group-text px-1"
                                            ><i
                                                class="fa fa-money-bill me-1"
                                            ></i
                                            >Cancelado</span
                                        >
                                        <input
                                            type="number"
                                            class="form-control text-center"
                                            :class="{
                                                'bgInactivo text-white':
                                                    !partido.pago_visitante,
                                                bgActivo:
                                                    partido.pago_visitante,
                                            }"
                                            v-model="partido.total_visitante"
                                        />
                                        <div class="input-group-text">
                                            <input
                                                type="checkbox"
                                                class="form-conrtol"
                                                :true-value="1"
                                                :false-value="0"
                                                v-model="partido.pago_visitante"
                                                @change="
                                                    actualizarDatosPartido(
                                                        'pago_visitante',
                                                    )
                                                "
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <MiTable
                            :tableClass="'bg-white mitabla'"
                            ref="miTableVisitante"
                            :cols="headers"
                            :data="visitante_detalles"
                            :con-paginacion="false"
                            :syncOrderBy="'id'"
                            :syncOrderAsc="'DESC'"
                            table-responsive
                            :header-class="'bg__primary'"
                            fixed-header
                            table-height="23vh"
                        >
                            <template #foto="{ item }">
                                <div class="">
                                    <img
                                        :src="
                                            item.carrera_jugador.jugador
                                                .url_foto
                                        "
                                        alt=""
                                        class="rounded-circle"
                                        height="60px"
                                    />
                                </div>
                            </template>
                            <template
                                #[`carrera_jugador.jugador.nombres`]="{ item }"
                            >
                                <div class="db-block w-100">
                                    {{ item.carrera_jugador.jugador.nombres }}
                                    {{ item.carrera_jugador.jugador.apes }}
                                </div>
                                <div class="text-muted fw-bold d-block w-100">
                                    {{ item.carrera_jugador.posicion }}
                                </div>
                                <div class="text-muted d-block">
                                    {{ item.carrera_jugador.jugador.ci }}
                                </div>
                            </template>
                            <template #titular="{ item }">
                                <div class="w-100 text-center">
                                    <input
                                        type="checkbox"
                                        v-model="item.titular"
                                        style="height: 19px; width: 19px"
                                        :true-value="1"
                                        :false-value="0"
                                        @change="
                                            actualizarDatosDetalle(
                                                item.id,
                                                'titular',
                                                'visitante',
                                            )
                                        "
                                    />
                                </div>
                            </template>
                            <template #goles="{ item }">
                                <div class="w-100 text-center">
                                    <input
                                        type="number"
                                        v-model="item.goles"
                                        min="0"
                                        class="form-control text-center"
                                        @keyup="
                                            actualizarDatosDetalle(
                                                item.id,
                                                'goles',
                                                'visitante',
                                            )
                                        "
                                        @change="
                                            actualizarDatosDetalle(
                                                item.id,
                                                'goles',
                                                'visitante',
                                            )
                                        "
                                    />
                                </div>
                            </template>
                            <template #amarillas="{ item }">
                                <div class="w-100 text-center">
                                    <input
                                        type="number"
                                        v-model="item.amarillas"
                                        class="form-control text-center"
                                        @keyup="
                                            actualizarDatosDetalle(
                                                item.id,
                                                'amarillas',
                                                'visitante',
                                            )
                                        "
                                        @change="
                                            actualizarDatosDetalle(
                                                item.id,
                                                'amarillas',
                                                'visitante',
                                            )
                                        "
                                    />
                                </div>
                            </template>
                            <template #rojas="{ item }">
                                <div class="w-100 text-center">
                                    <input
                                        type="number"
                                        v-model="item.rojas"
                                        class="form-control text-center"
                                        @keyup="
                                            actualizarDatosDetalle(
                                                item.id,
                                                'rojas',
                                                'visitante',
                                            )
                                        "
                                        @change="
                                            actualizarDatosDetalle(
                                                item.id,
                                                'rojas',
                                                'visitante',
                                            )
                                        "
                                    />
                                </div>
                            </template>
                        </MiTable>
                    </div>
                </div>
            </div>
        </div>
    </Content>
</template>
