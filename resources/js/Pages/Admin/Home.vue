<script setup>
import App from "@/Layouts/App.vue";
defineOptions({
    layout: App,
});
import Content from "@/Components/Content.vue";
import { usePage, Head, Link } from "@inertiajs/vue3";
import { onMounted, onBeforeMount, ref, computed, nextTick } from "vue";
import { useAppStore } from "@/stores/aplicacion/appStore";
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

const appStore = useAppStore();
onBeforeMount(() => {
    appStore.startLoading();
});

const { props } = usePage();

const filtroGrafico1 = ref([
    {
        value: "semanal",
        label: "Ultimos 7 días",
    },
    {
        value: "meses",
        label: "Por meses",
    },
    {
        value: "gestion",
        label: "Por Años",
    },
]);

const form1 = ref({
    tipo: "semanal",
});

const generarReporte1 = () => {
    axios
        .get(route("certificadosEmitidosLinea"), {
            params: form1.value,
        })
        .then((response) => {
            nextTick(() => {
                const containerId = `container`;
                const container = document.getElementById(containerId);
                // Verificar que el contenedor exista y tenga un tamaño válido
                if (container) {
                    renderChart1(
                        containerId,
                        response.data.categories,
                        response.data.total_final,
                        response.data.data,
                    );
                } else {
                    console.error(`Contenedor ${containerId} no válido.`);
                }
            });
            // Create the chart
        });
};

const renderChart1 = (containerId, categories, total_final, data) => {
    Highcharts.chart(containerId, {
        chart: {
            type: "line",
        },
        title: {
            align: "center",
            text: `CERTIFICADOS EMITIDOS`,
        },
        subtitle: {
            align: "center",
            text: `Total emitidos: ${total_final}`,
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
            title: {
                text: "TOTAL",
            },
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
        tooltip: {
            useHTML: true,
            formatter: function () {
                return `
                    <div style="text-align:center;">
                        <div style="display:inline-block; width:12px; height:12px; background:${this.point.color}; border-radius:50%; margin-right:5px;"></div>
                        <strong style="color:${this.point.color};">${this.point.series.name}</strong>
                        <br>
                        <span class="text-md"><strong>Total:</strong> ${this.point.y}</span>
                    </div>
                    `;
            },
        },

        series: [
            {
                name: "Certificados emitidos",
                data: data,
            },
        ],
    });
};

const generarReporte2 = () => {
    axios.get(route("cantidadTramitesNormal")).then((response) => {
        nextTick(() => {
            const containerId = `container2`;
            const container = document.getElementById(containerId);
            // Verificar que el contenedor exista y tenga un tamaño válido
            if (container) {
                renderChart2(
                    containerId,
                    response.data.categories,
                    response.data.total_final,
                    response.data.data,
                );
            } else {
                console.error(`Contenedor ${containerId} no válido.`);
            }
        });
        // Create the chart
    });
};

const renderChart2 = (containerId, categories, total_final, data) => {
    Highcharts.chart(containerId, {
        chart: {
            type: "pie",
        },
        title: {
            align: "center",
            text: `NORMAL/TRÁMITE`,
        },
        subtitle: {
            align: "center",
            text: `Total: ${total_final}`,
        },
        accessibility: {
            announceNewData: {
                enabled: true,
            },
        },
        yAxis: {
            title: {
                text: "TOTAL",
            },
        },
        legend: {
            enabled: true,
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: "pointer",
                dataLabels: [
                    {
                        enabled: true,
                        distance: 20,
                    },
                    {
                        enabled: true,
                        distance: -25,
                        format: "{point.percentage:.1f}%",
                        style: {
                            fontSize: "0.75em",
                            textOutline: "none",
                            opacity: 1,
                        },
                        filter: {
                            operator: ">",
                            property: "percentage",
                            value: 10,
                        },
                    },
                ],
            },
        },
        tooltip: {
            useHTML: true,
            formatter: function () {
                return `
                    <div style="text-align:center;">
                        <div style="display:inline-block; width:12px; height:12px; background:${this.point.color}; border-radius:50%; margin-right:5px;"></div>
                        <strong style="color:${this.point.color};">${this.point.series.name}</strong>
                        <br>
                        <span class="text-md"><strong>Total:</strong> ${this.point.y}</span>
                    </div>
                    `;
            },
        },

        series: [
            {
                name: "Certificados emitidos",
                data: data,
            },
        ],
    });
};

onMounted(() => {
    // generarReporte1();
    // generarReporte2();
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

        <!-- <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <select
                                    v-model="form1.tipo"
                                    class="form-control text-sm"
                                    @change="generarReporte1"
                                >
                                    <option
                                        v-for="item in filtroGrafico1"
                                        :value="item.value"
                                    >
                                        {{ item.label }}
                                    </option>
                                </select>
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
                        <div id="container2"></div>
                    </div>
                </div>
            </div>
        </div> -->
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
