<script setup>
import App from "@/Layouts/App.vue";
defineOptions({
    layout: App,
});
import Content from "@/Components/Content.vue";
import { usePage, Head, Link } from "@inertiajs/vue3";
import { onMounted, onBeforeMount, ref, computed, nextTick } from "vue";
import { useAppStore } from "@/stores/aplicacion/appStore";
import { useDate } from "@/composables/useDate";
import Highcharts from "highcharts";
import "highcharts/modules/exporting";
import "highcharts/modules/accessibility";
Highcharts.setOptions({
    lang: {
        downloadPNG: "Descargar PNG",
        downloadJPEG: "Descargar JPEG",
        downloadPDF: "Descargar PDF",
        downloadSVG: "Descargar SVG",
        printChart: "Imprimir gráfico",
        contextButtonTitle: "Menú de exportación",
        viewFullscreen: "Pantalla completa",
        exitFullscreen: "Salir de pantalla completa",
    },
});

const { auth } = usePage().props;
const user = ref(auth.user);

const props_page = defineProps({
    array_infos: {
        type: Array,
    },
});

const cargarCarreras = () => {
    axios.get(route("carreras.listado")).then((response) => {
        listCarreras.value = response.data.carreras;
        listCarreras.value.unshift({
            id: "todos",
            nombre: "TODOS",
        });
    });
};

const cargarCampeonatos = () => {
    axios.get(route("campeonatos.listado")).then((response) => {
        listCampeonatos.value = response.data.campeonatos;
        listCampeonatos.value.unshift({
            id: "todos",
            nombre: "TODOS",
        });
    });
};
const appStore = useAppStore();
onBeforeMount(() => {
    cargarCarreras();
    cargarCampeonatos();
    appStore.startLoading();
});

const { props } = usePage();

const listCampeonatos = ref([]);
const listCarreras = ref([]);

const form1 = ref({
    campeonato_id: "todos",
    carrera_id: "todos",
    fecha_ini: useDate().getFechaActual(),
    fecha_fin: useDate().getFechaActual(),
});

const form2 = ref({
    campeonato_id: "todos",
    carrera_id: "todos",
    fecha_ini: useDate().getFechaActual(),
    fecha_fin: useDate().getFechaActual(),
});

const generarReporte1 = () => {
    axios
        .get(route("pagosCampeonato"), {
            params: form1.value,
        })
        .then((response) => {
            nextTick(() => {
                console.log(response.data);
                const containerId = `container`;
                const container = document.getElementById(containerId);
                // Verificar que el contenedor exista y tenga un tamaño válido
                if (container) {
                    const categorias = response.data.map(
                        (item) => item.concepto,
                    );
                    const pendientes = response.data.map((item) =>
                        Number(item.pendientes),
                    );
                    const cancelados = response.data.map((item) =>
                        Number(item.cancelados),
                    );
                    console.log(categorias);
                    console.log(pendientes);
                    console.log(cancelados);
                    renderChart1(
                        containerId,
                        categorias,
                        cancelados,
                        pendientes,
                    );
                } else {
                    console.error(`Contenedor ${containerId} no válido.`);
                }
            });
            // Create the chart
        });
};

const renderChart1 = (containerId, categories, cancelados, pendientes) => {
    Highcharts.chart(containerId, {
        chart: {
            type: "column",
        },
        title: {
            align: "center",
            text: `RESUMEN DE PAGOS`,
        },
        subtitle: {
            align: "center",
            text: `Montos cancelados y pendientes`,
        },
        accessibility: {
            announceNewData: {
                enabled: true,
            },
        },
        xAxis: {
            categories: categories,
        },
        yAxis: {
            min: 0,
            title: {
                text: "Monto (Bs.)",
            },
        },
        tooltip: {
            shared: true,
            valuePrefix: "Bs. ",
            valueDecimals: 2,
        },
        legend: {
            enabled: true,
        },
        plotOptions: {
            series: {
                depth: 100,
                borderWidth: 0,
                dataLabels: {
                    enabled: true,
                    // format: "{point.y}",
                    style: {
                        fontSize: "11px",
                        fontWeight: "bold",
                    },
                },
            },
        },
        series: [
            {
                name: "Cancelados",
                data: cancelados,
                color: "#22c55e",
            },
            {
                name: "Pendientes",
                data: pendientes,
                color: "#ef4444",
            },
        ],

        credits: {
            enabled: false,
        },
    });
};

const generarReporte2 = () => {
    axios
        .get(route("golesPorCarrera"), {
            params: form2.value,
        })
        .then((response) => {
            nextTick(() => {
                const containerId = `container2`;
                const container = document.getElementById(containerId);
                // Verificar que el contenedor exista y tenga un tamaño válido
                if (container) {
                    const categories = response.data.map(
                        (item) => item.carrera,
                    );
                    const goles = response.data.map((item) => item.goles);
                    renderChart2(containerId, categories, goles);
                } else {
                    console.error(`Contenedor ${containerId} no válido.`);
                }
            });
            // Create the chart
        });
};

const renderChart2 = (containerId, categories, goles) => {
    Highcharts.chart(containerId, {
        chart: {
            type: "bar",
            scrollablePlotArea: {
                minHeight: 600,
                scrollPositionY: 0,
            },
        },
        title: {
            align: "center",
            text: `GOLES POR CARRERA`,
        },
        subtitle: {
            align: "center",
            text: ``,
        },
        xAxis: {
            categories: categories,
            title: {
                text: "Carrera",
            },
        },
        yAxis: {
            min: 0,
            title: {
                text: "Goles",
            },
            allowDecimals: false,
        },
        legend: {
            enabled: true,
        },
        tooltip: {
            pointFormat: "<b>{point.y}</b> goles",
        },
        plotOptions: {
            series: {
                dataLabels: {
                    enabled: true,
                },
            },
        },
        series: [
            {
                name: "Goles",
                data: goles,
            },
        ],
    });
};

onMounted(() => {
    generarReporte1();
    generarReporte2();
    appStore.stopLoading();
});
</script>
<template>
    <Head title="Inicio"></Head>
    <Content>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h3 class="m-0"><i class="fa fa-home"></i> Inicio</h3>
                </div>
                <!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item active">Inicio</li>
                    </ol>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </template>

        <div class="row">
            <div class="col-lg-3 col-6" v-for="item in array_infos">
                <!-- small box -->
                <div class="small-box" :class="[item.color]">
                    <div class="inner">
                        <h3 class="">{{ item.cantidad }}</h3>

                        <p class="font-weight-600">{{ item.label }}</p>
                    </div>
                    <div class="small-box-icon">
                        <i class="text-dark fa" :class="[item.icon]"></i>
                    </div>
                    <Link
                        :href="route(item.url)"
                        class="small-box-footer bg-item link-light link-underline-opacity-0 link-underline-opacity-50-hover"
                        >Ver más <i class="fa fa-arrow-alt-circle-right"></i
                    ></Link>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-6">
                                        <label>Desde</label>
                                        <input
                                            type="date"
                                            v-model="form1.fecha_ini"
                                            class="form-control"
                                            @change="generarReporte1"
                                        />
                                    </div>
                                    <div class="col-6">
                                        <label>Hasta</label>
                                        <input
                                            type="date"
                                            v-model="form1.fecha_fin"
                                            class="form-control"
                                            @change="generarReporte1"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label>Campeonato*</label>
                                <el-select
                                    v-model="form1.campeonato_id"
                                    no-data-text="Sin datos"
                                    no-match-text="Sin resultados"
                                    filterable
                                    @change="generarReporte1"
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
                            <div class="col-md-3">
                                <label>Carrera*</label>
                                <el-select
                                    v-model="form1.carrera_id"
                                    no-data-text="Sin datos"
                                    no-match-text="Sin resultados"
                                    filterable
                                    @change="generarReporte1"
                                >
                                    <el-option
                                        v-for="item in listCarreras"
                                        :key="item.id"
                                        :value="item.id"
                                        :label="item.nombre"
                                    >
                                    </el-option>
                                </el-select>
                            </div>
                            <div class="col-12">
                                <div id="container"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-6">
                                        <label>Desde</label>
                                        <input
                                            type="date"
                                            v-model="form2.fecha_ini"
                                            class="form-control"
                                            @change="generarReporte2"
                                        />
                                    </div>
                                    <div class="col-6">
                                        <label>Hasta</label>
                                        <input
                                            type="date"
                                            v-model="form2.fecha_fin"
                                            class="form-control"
                                            @change="generarReporte2"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label>Campeonato*</label>
                                <el-select
                                    v-model="form2.campeonato_id"
                                    no-data-text="Sin datos"
                                    no-match-text="Sin resultados"
                                    filterable
                                    @change="generarReporte2"
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
                            <div class="col-md-6">
                                <label>Carrera*</label>
                                <el-select
                                    v-model="form2.carrera_id"
                                    no-data-text="Sin datos"
                                    no-match-text="Sin resultados"
                                    filterable
                                    @change="generarReporte1"
                                >
                                    <el-option
                                        v-for="item in listCarreras"
                                        :key="item.id"
                                        :value="item.id"
                                        :label="item.nombre"
                                    >
                                    </el-option>
                                </el-select>
                            </div>
                            <div class="col-12">
                                <div id="container2"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Content>
</template>
<style scoped>
.item_btn {
    margin: 10px;
}

.contenido_item i {
    color: black;
}

.contenido_item {
    transition: all 0.8s;
    color: black;
    padding: 10px;
    cursor: pointer;
    background-color: rgb(248, 229, 229);
    border: solid 2px rgb(243, 211, 211);
    border-radius: 10px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: bold;
    font-size: 1.3em;
    flex-direction: column;
}
</style>
