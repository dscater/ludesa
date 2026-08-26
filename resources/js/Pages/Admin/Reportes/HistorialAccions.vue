<script setup>
import Content from "@/Components/Content.vue";
import { computed, onBeforeMount, onMounted, ref } from "vue";
import { Head, usePage, Link } from "@inertiajs/vue3";
import { useAppStore } from "@/stores/aplicacion/appStore";
const appStore = useAppStore();

onBeforeMount(() => {
    appStore.startLoading();
});

const cargarListas = () => {
    cargarUsuarios();
};

onMounted(() => {
    cargarListas();
    appStore.stopLoading();
});

const getFechaAtual = () => {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, "0");
    const day = String(today.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
};
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

const listModulos = ref([
    {
        value: "todos",
        label: "TODOS",
    },
    {
        value: "CERTIFICADOS",
        label: "CERTIFICADOS",
    },
    {
        value: "PAGOS",
        label: "PAGOS",
    },
    {
        value: "TRAMITADORES",
        label: "TRAMITADORES",
    },
    {
        value: "CLIENTES",
        label: "CLIENTES",
    },
    {
        value: "TIPO DE CERTIFICADOS",
        label: "TIPO DE CERTIFICADOS",
    },
    {
        value: "SUCURSALES",
        label: "SUCURSALES",
    },
    {
        value: "USUARIOS",
        label: "USUARIOS",
    },
]);
const form = ref({
    user_id: "todos",
    fecha_ini: getFechaAtual(),
    fecha_fin: getFechaAtual(),
    modulo: "todos",
    formato: "pdf",
});

const generando = ref(false);
const txtBtn = computed(() => {
    if (generando.value) {
        return "Generando Reporte...";
    }
    return "Generar Reporte";
});

const listUsers = ref([]);

const generarReporte = () => {
    generando.value = true;
    const url = route("reportes.r_historial_accions", form.value);
    window.open(url, "_blank");
    setTimeout(() => {
        generando.value = false;
    }, 500);
};

const cargarUsuarios = () => {
    axios.get(route("usuarios.listado")).then((response) => {
        listUsers.value = response.data.usuarios;
        listUsers.value.unshift({
            id: "todos",
            full_name: "TODOS",
            tipo: "",
        });
    });
};
</script>
<template>
    <Head title="Reporte Historial de Acciones"></Head>
    <Content>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Historial de Acciones</h1>
                </div>
                <!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <Link :href="route('inicio')">Inicio</Link>
                        </li>
                        <li class="breadcrumb-item active">
                            Reportes - Historial de Acciones
                        </li>
                    </ol>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </template>
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <form @submit.prevent="generarReporte">
                            <div class="row">
                                <div class="col-md-12">
                                    <label>Seleccionar usuario*</label>
                                    <el-select
                                        v-model="form.user_id"
                                        filterable
                                    >
                                        <el-option
                                            v-for="item in listUsers"
                                            :key="item.id"
                                            :value="item.id"
                                            :label="`${item.full_name} ${item.tipo ? ' - ' + item.tipo : ''}`"
                                        >
                                        </el-option>
                                    </el-select>
                                </div>
                                <div class="col-md-12">
                                    <label>Seleccionar módulo*</label>
                                    <el-select v-model="form.modulo" filterable>
                                        <el-option
                                            v-for="item in listModulos"
                                            :key="item.value"
                                            :value="item.value"
                                            :label="item.label"
                                        >
                                        </el-option>
                                    </el-select>
                                </div>
                                <div class="col-12 mt-3">
                                    <label>Rango de fechas</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <input
                                                type="date"
                                                class="form-control"
                                                v-model="form.fecha_ini"
                                            />
                                        </div>
                                        <div class="col-md-6">
                                            <input
                                                type="date"
                                                class="form-control"
                                                v-model="form.fecha_fin"
                                            />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 text-center mt-2">
                                    <el-radio-group v-model="form.formato">
                                        <el-radio
                                            v-for="item in listFormatos"
                                            :value="item.value"
                                            size="large"
                                            ><i :class="item.icon"></i>
                                            {{ item.label }}</el-radio
                                        >
                                    </el-radio-group>
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
        </div>
    </Content>
</template>
