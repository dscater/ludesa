<script setup>
import Content from "@/Components/Content.vue";
import { computed, onBeforeMount, onMounted, ref } from "vue";
import { Head, usePage, Link } from "@inertiajs/vue3";
import { useAppStore } from "@/stores/aplicacion/appStore";
const appStore = useAppStore();

onBeforeMount(() => {
    appStore.startLoading();
});

const cargarListas = async () => {
    await Promise.all([
        cargarSucursals(),
        cargarUsers(),
        cargarTipoCertificados(),
        // cargarClientes(),
        // cargarTipoPagos(),
    ]);
};

const listClientes = ref([]);
const listSucursals = ref([]);
const listUsers = ref([]);
const listTipoPagos = ref([]);
const listTipoCertificados = ref([]);

const cargarClientes = async () => {
    const response = await axios.get(route("clientes.listado"));
    listClientes.value = response.data.clientes;
    listClientes.value.unshift({
        id: "todos",
        full_name: "TODOS",
        full_ci: "",
    });
};

const cargarSucursals = async () => {
    const response = await axios.get(route("sucursals.listado"));
    listSucursals.value = response.data.sucursals;
    listSucursals.value.unshift({
        id: "todos",
        nombre: "TODOS",
    });
};
const cargarUsers = async () => {
    const response = await axios.get(route("usuarios.byTipo"), {
        params: {
            tipo: "MÉDICO",
        },
    });
    listUsers.value = response.data.usuarios;
    listUsers.value.unshift({
        id: "todos",
        full_name: "TODOS",
    });
};
const cargarTipoPagos = async () => {
    const response = await axios.get(route("tipo_pagos.listado"));
    listTipoPagos.value = response.data;
    listTipoPagos.value.unshift({
        value: "todos",
        label: "TODOS",
    });
};
const cargarTipoCertificados = async () => {
    const response = await axios.get(route("tipo_certificados.listado"));
    listTipoCertificados.value = response.data.tipo_certificados;
    listTipoCertificados.value.unshift({
        id: "todos",
        nombre: "TODOS",
    });
};

onMounted(async () => {
    await cargarListas();
    appStore.stopLoading();
});

onBeforeMount(() => {});

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

const obtenerFechaActual = () => {
    const fecha = new Date();
    const anio = fecha.getFullYear();
    const mes = String(fecha.getMonth() + 1).padStart(2, "0"); // Mes empieza desde 0
    const dia = String(fecha.getDate()).padStart(2, "0"); // Día del mes
    return `${anio}-${mes}-${dia}`;
};

const form = ref({
    cliente_id: "todos",
    sucursal_id: "todos",
    user_id: "todos",
    tipo_pago: "todos",
    tipo_certificado_id: "todos",
    fecha_ini: obtenerFechaActual(),
    fecha_fin: obtenerFechaActual(),
    formato: "pdf",
});

const generando = ref(false);
const txtBtn = computed(() => {
    if (generando.value) {
        return "Generando Reporte...";
    }
    return "Generar Reporte";
});

const generarReporte = () => {
    generando.value = true;
    const url = route("reportes.r_certificados_interno", form.value);
    window.open(url, "_blank");
    setTimeout(() => {
        generando.value = false;
    }, 500);
};
</script>
<template>
    <Head title="Reporte Certificados Emitidos Interno"></Head>
    <Content>
        <template #header>
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Certificados Emitidos Interno</h1>
                </div>
                <!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <Link :href="route('inicio')">Inicio</Link>
                        </li>
                        <li class="breadcrumb-item active">
                            Reportes - Certificados Emitidos Interno
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
                                <!-- <div class="col-md-12">
                                    <label>Seleccionar Cliente*</label>
                                    <el-select
                                        v-model="form.cliente_id"
                                        filterable
                                    >
                                        <el-option
                                            v-for="item in listClientes"
                                            :key="item.id"
                                            :value="item.id"
                                            :label="`${item.full_name} ${item.full_ci ? ' - ' + item.full_ci : ''}`"
                                        >
                                        </el-option>
                                    </el-select>
                                </div> -->
                                <div class="col-md-12">
                                    <label>Sucursal*</label>
                                    <el-select
                                        v-model="form.sucursal_id"
                                        filterable
                                    >
                                        <el-option
                                            v-for="item in listSucursals"
                                            :key="item.id"
                                            :value="item.id"
                                            :label="item.nombre"
                                        >
                                        </el-option>
                                    </el-select>
                                </div>
                                <div class="col-md-12">
                                    <label>Seleccionar Médico*</label>
                                    <el-select
                                        v-model="form.user_id"
                                        filterable
                                    >
                                        <el-option
                                            v-for="item in listUsers"
                                            :key="item.id"
                                            :value="item.id"
                                            :label="item.full_name"
                                        >
                                        </el-option>
                                    </el-select>
                                </div>
                                <!-- <div class="col-md-12">
                                    <label>Seleccionar Tipo de Pago*</label>
                                    <el-select
                                        v-model="form.tipo_pago"
                                        filterable
                                    >
                                        <el-option
                                            v-for="item in listTipoPagos"
                                            :key="item.value"
                                            :value="item.value"
                                            :label="item.label"
                                        >
                                        </el-option>
                                    </el-select>
                                </div> -->
                                <div class="col-md-12">
                                    <label>Tipo de Certificado*</label>
                                    <el-select
                                        v-model="form.tipo_certificado_id"
                                        filterable
                                    >
                                        <el-option
                                            v-for="item in listTipoCertificados"
                                            :key="item.id"
                                            :value="item.id"
                                            :label="item.nombre"
                                        >
                                        </el-option>
                                    </el-select>
                                </div>
                                <div class="col-12 mt-1">
                                    <label>Rango de Fechas</label>
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
