<script setup>
import Content from "@/Components/Content.vue";
import { computed, onBeforeMount, onMounted, ref } from "vue";
import { Head, usePage, Link } from "@inertiajs/vue3";
import { useAppStore } from "@/stores/aplicacion/appStore";
import MiTable from "@/Components/MiTable.vue";
const appStore = useAppStore();

onBeforeMount(() => {
    appStore.startLoading();
});

const cargarListas = () => {
    cargarCampeonatos();
};

onMounted(() => {
    cargarListas();
    appStore.stopLoading();
});

const miTable = ref(null);
const listFormatos = ref([
    {
        icon: "fa fa-file-pdf",
        value: "pdf",
        label: "PDF",
    },
    {
        icon: "fa fa-file-excel",
        value: "excel",
        label: "EXCEL",
    },
]);

const form = ref({
    tipo: "",
    campeonato_id: "",
});

const generando = ref(false);
const txtBtn = computed(() => {
    if (generando.value) {
        return "Generando Reporte...";
    }
    return "Generar Reporte";
});

const listCampeonatos = ref([]);

const generarReporte = () => {
    generando.value = true;
    const url = route("reportes.r_porteros", form.value);
    window.open(url, "_blank");
    setTimeout(() => {
        generando.value = false;
    }, 500);
};

const listGoleadores = ref([]);
const headers = [
    {
        label: "N°",
        key: "ranking",
        sortable: true,
        width: "3%",
        fixed: true,
    },
    {
        label: "Foto",
        key: "foto",
        sortable: true,
        width: "3%",
        fixed: true,
    },
    {
        label: "JUGADOR",
        key: "jugador.nombres",
        sortable: true,
        fixed: true,
    },

    {
        label: "CARRERA",
        key: "carrera.nombre",
        sortable: true,
        fixed: true,
    },
    {
        label: "GOLES RECIBIDOS",
        key: "goles_recibidos",
        sortable: true,
        fixed: true,
    },
];

const cargaGoleadores = () => {
    listGoleadores.value = [];
    if (form.value.campeonato_id == "") return;
    axios
        .get(route("campeonato_inscripcions.porteros"), {
            params: {
                campeonato_id: form.value.campeonato_id,
            },
        })
        .then((response) => {
            listGoleadores.value = response.data.carrera_jugadors;
        });
};

const nombreCampeonato = computed(() => {
    const campeonato = listCampeonatos.value.find(
        (c) => c.id == form.value.campeonato_id,
    );
    if (campeonato) {
        return `${campeonato.periodo} - ${campeonato.gestion}: ${campeonato.nombre} (${campeonato.tipo})`;
    }
    return "";
});

const estadoCampeonato = computed(() => {
    const campeonato = listCampeonatos.value.find(
        (c) => c.id == form.value.campeonato_id,
    );
    if (campeonato) {
        return campeonato.estado;
    }
    return "";
});

const cargarCampeonatos = () => {
    axios.get(route("campeonatos.listado")).then((response) => {
        listCampeonatos.value = response.data.campeonatos;
    });
};
</script>
<template>
    <Head title="Reporte Tabla de Porteros"></Head>
    <Content>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4 class="m-0">Tabla de Porteros</h4>
                </div>
                <!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item">
                            <Link :href="route('inicio')">Inicio</Link>
                        </li>
                        <li class="breadcrumb-item active">
                            Reportes - Tabla de Porteros
                        </li>
                    </ol>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </template>
        <div class="row">
            <div class="col-md-6 mx-auto mb-2">
                <div class="card">
                    <div class="card-body">
                        <form @submit.prevent="generarReporte">
                            <div class="row">
                                <div class="col-md-12">
                                    <label>Seleccionar campeonato*</label>
                                    <el-select
                                        v-model="form.campeonato_id"
                                        no-data-text="Sin datos"
                                        no-match-text="Sin resultados"
                                        placeholder="Seleccionar campeonato"
                                        filterable
                                        @change="cargaGoleadores"
                                    >
                                        <el-option
                                            v-for="item in listCampeonatos"
                                            :key="item.id"
                                            :value="item.id"
                                            :label="
                                                item.id == 'todos'
                                                    ? item.nombre
                                                    : `${item.periodo} - ${item.gestion}: ${item.nombre} (${item.tipo})`
                                            "
                                        >
                                        </el-option>
                                    </el-select>
                                </div>
                                <div class="col-md-12 text-center mt-3">
                                    <button
                                        class="btn btn-primary"
                                        block
                                        @click="generarReporte"
                                        :disabled="generando"
                                        v-text="txtBtn"
                                    ></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <h4 class="my-2 text-center fs-6">TABLA DE GOLEADORES</h4>
                <h4 class="my-2 text-center fs-6">{{ nombreCampeonato }}</h4>
            </div>
            <div class="col-12">
                <MiTable
                    :tableClass="'bg-white mitabla'"
                    ref="miTableLocal"
                    :cols="headers"
                    :data="listGoleadores"
                    :con-paginacion="false"
                    :syncOrderBy="'id'"
                    :syncOrderAsc="'DESC'"
                    :header-class="'bg__primary'"
                    fixed-header
                    table-height="40vh"
                >
                    <template #foto="{ item }">
                        <div class="">
                            <img
                                :src="item.jugador.url_foto"
                                alt=""
                                class="rounded-circle"
                                height="60px"
                            />
                        </div>
                    </template>
                    <template #[`jugador.nombres`]="{ item }">
                        <div class="">
                            {{ item.jugador.nombres }} {{ item.jugador.apes }}
                        </div>
                        <div
                            class="text-muted d-block"
                            v-if="
                                estadoCampeonato == 'FINALIZADO' &&
                                item.posicion == 1
                            "
                        >
                            (CAMPEÓN)
                        </div>
                    </template>
                </MiTable>
            </div>
        </div>
    </Content>
</template>
