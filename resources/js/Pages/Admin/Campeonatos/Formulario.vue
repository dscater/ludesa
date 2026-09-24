<script setup>
import MiModal from "@/Components/MiModal.vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { watch, ref, computed, onMounted, nextTick } from "vue";
const props = defineProps({
    muestra_formulario: {
        type: Boolean,
        default: false,
    },
    form: {
        type: Object,
    },
});

const muestra_form = ref(props.muestra_formulario);
const enviando = ref(false);
const form = props.form;

const tituloDialog = computed(() => {
    return form.id == 0
        ? `<i class="fa fa-plus"></i> Nuevo Campeonato`
        : `<i class="fa fa-edit"></i> Editar Campeonato`;
});

const textBtn = computed(() => {
    if (enviando.value) {
        return `<i class="fa fa-spin fa-spinner"></i> Enviando...`;
    }
    if (form.id == 0) {
        return `<i class="fa fa-save"></i> Guardar`;
    }
    return `<i class="fa fa-edit"></i> Actualizar`;
});

const enviarFormulario = () => {
    enviando.value = true;
    let url =
        form.id == 0
            ? route("campeonatos.store")
            : route("campeonatos.update", form.id);

    form.post(url, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: (response) => {
            console.log("correcto");
            const success =
                response.props.flash.success ?? "Proceso realizado con éxito";
            Swal.fire({
                icon: "success",
                title: "Correcto",
                html: `<strong>${success}</strong>`,
                confirmButtonText: `Aceptar`,
                customClass: {
                    confirmButton: "btn-alert-success",
                },
            });

            document
                .getElementsByTagName("body")[0]
                .classList.remove("modal-open");
            emits("envio-formulario");
        },
        onError: (err, code) => {
            console.log(code ?? "");
            console.log(form.errors);
            if (form.errors) {
                const error =
                    "Existen errores en el formulario, por favor verifique";
                Swal.fire({
                    icon: "info",
                    title: "Error",
                    html: `<strong>${error}</strong>`,
                    confirmButtonText: `Aceptar`,
                    customClass: {
                        confirmButton: "btn-error",
                    },
                });
            } else {
                const error =
                    "Ocurrió un error inesperado contactese con el Administrador";
                Swal.fire({
                    icon: "info",
                    title: "Error",
                    html: `<strong>${error}</strong>`,
                    confirmButtonText: `Aceptar`,
                    customClass: {
                        confirmButton: "btn-error",
                    },
                });
            }
            console.log("error: " + err.error);
        },
        onFinish: () => {
            enviando.value = false;
        },
    });
};

const emits = defineEmits(["cerrar-formulario", "envio-formulario"]);

watch(muestra_form, (newVal) => {
    if (!newVal) {
        emits("cerrar-formulario");
    }
});

const cerrarFormulario = () => {
    muestra_form.value = false;
    document.getElementsByTagName("body")[0].classList.remove("modal-open");
};

const listTipoCampeonatos = ref([]);
const cargarTipoCampeonatos = () => {
    axios.get(route("tipo_campeonatos.listado")).then((response) => {
        listTipoCampeonatos.value = response.data.tipo_campeonatos;
    });
};

onMounted(() => {
    cargarTipoCampeonatos();
});
</script>

<template>
    <MiModal
        :open_modal="muestra_form"
        @close="cerrarFormulario"
        :size="'modal-xl'"
        :header-class="'bg-principal'"
        :footer-class="'justify-content-end'"
    >
        <template #header>
            <h4 class="modal-title text-white" v-html="tituloDialog"></h4>
            <button
                type="button"
                class="btn-close btn-close-white"
                @click.prevent="cerrarFormulario()"
            ></button>
        </template>

        <template #body>
            <form @submit.prevent="enviarFormulario()">
                <p class="text-muted text-xs mb-0">
                    Todos los campos con
                    <span class="text-danger">(*)</span> son obligatorios.
                </p>
                <div class="row">
                    <div class="col-md-4 mt-2">
                        <label class="required">Nombre del Campeonato</label>
                        <el-input
                            type="text"
                            :class="{
                                'parsley-error': form.errors?.nombre,
                            }"
                            v-model="form.nombre"
                            autosize
                        ></el-input>
                        <ul
                            v-if="form.errors?.nombre"
                            class="d-block text-danger list-unstyled"
                        >
                            <li class="parsley-required">
                                {{ form.errors?.nombre }}
                            </li>
                        </ul>
                    </div>

                    <div class="col-md-4 mt-2">
                        <label class="required">Periodo</label>
                        <input
                            type="number"
                            class="form-control"
                            v-model="form.periodo"
                            autosize
                        />
                        <ul
                            v-if="form.errors?.periodo"
                            class="d-block text-danger list-unstyled"
                        >
                            <li class="parsley-required">
                                {{ form.errors?.periodo }}
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-4 mt-2">
                        <label class="required">Año/Gestión</label>
                        <input
                            type="number"
                            class="form-control"
                            v-model="form.gestion"
                            autosize
                        />
                        <ul
                            v-if="form.errors?.gestion"
                            class="d-block text-danger list-unstyled"
                        >
                            <li class="parsley-required">
                                {{ form.errors?.gestion }}
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-4 mt-2">
                        <label class="required"
                            >Modalidad/Tipo Campeonato</label
                        >
                        <el-select
                            v-model="form.tipo"
                            placeholder="- Seleccione -"
                            no-data-text="Sin datos"
                            no-match-text="Sin resultados"
                            filterable
                        >
                            <el-option
                                v-for="item in listTipoCampeonatos"
                                :key="item.value"
                                :value="item.value"
                                :label="item.label"
                            ></el-option>
                        </el-select>
                        <ul
                            v-if="form.errors?.tipo"
                            class="d-block text-danger list-unstyled"
                        >
                            <li class="parsley-required">
                                {{ form.errors?.tipo }}
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-4 mt-2">
                        <label class="">Descripción</label>
                        <el-input
                            type="textarea"
                            :class="{
                                'parsley-error': form.errors?.descripcion,
                            }"
                            v-model="form.descripcion"
                            autosize
                        ></el-input>
                        <ul
                            v-if="form.errors?.descripcion"
                            class="d-block text-danger list-unstyled"
                        >
                            <li class="parsley-required">
                                {{ form.errors?.descripcion }}
                            </li>
                        </ul>
                    </div>
                </div>
            </form>
        </template>
        <template #footer>
            <button
                type="button"
                class="btn btn-default"
                @click.prevent="cerrarFormulario()"
            >
                Cerrar
            </button>
            <button
                type="button"
                class="btn btn-primary"
                :disabled="enviando"
                @click.prevent="enviarFormulario"
                v-html="textBtn"
            ></button>
        </template>
    </MiModal>
</template>
