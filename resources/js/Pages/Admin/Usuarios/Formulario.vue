<script setup>
import MiModal from "@/Components/MiModal.vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { useUsuarios } from "@/composables/usuarios/useUsuarios";
import { watch, ref, computed, defineEmits, onMounted, nextTick } from "vue";
const props = defineProps({
    muestra_formulario: {
        type: Boolean,
        default: false,
    },
    form: {
        type: Object,
    },
});

const { oUsuario, limpiarUsuario } = useUsuarios();
const muestra_form = ref(props.muestra_formulario);
const enviando = ref(false);
const form = props.form;

const listExpedido = [
    { value: "LP", label: "La Paz" },
    { value: "CB", label: "Cochabamba" },
    { value: "SC", label: "Santa Cruz" },
    { value: "CH", label: "Chuquisaca" },
    { value: "OR", label: "Oruro" },
    { value: "PT", label: "Potosi" },
    { value: "TJ", label: "Tarija" },
    { value: "PD", label: "Pando" },
    { value: "BN", label: "Beni" },
];

const listTipos = ref([]);
const cargarTipoUsuarios = () => {
    axios.get(route("tipo_usuarios.listado")).then((response) => {
        listTipos.value = response.data;
    });
};

const listSucursals = ref([]);
const cargarSucursals = () => {
    axios.get(route("sucursals.listado")).then((response) => {
        listSucursals.value = response.data.sucursals;
    });
};

const foto = ref(null);

function cargaArchivo(e, key) {
    form[key] = null;
    form[key] = e.target.files[0];
}

const tituloDialog = computed(() => {
    return form.id == 0
        ? `<i class="fa fa-plus"></i> Nuevo Usuario`
        : `<i class="fa fa-edit"></i> Editar Usuario`;
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
        form["_method"] == "POST"
            ? route("usuarios.store")
            : route("usuarios.update", form.id);

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

const cargarListas = () => {
    cargarTipoUsuarios();
    cargarSucursals();
};

const options = ref([]);
const loading = ref(false);
onMounted(() => {
    foto.value.value = null;
    cargarListas();
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
                class="close"
                @click.prevent="cerrarFormulario()"
            >
                <span aria-hidden="true">×</span>
            </button>
        </template>

        <template #body>
            <form @submit.prevent="enviarFormulario()">
                <p class="text-muted text-xs mb-0">
                    Todos los campos con
                    <span class="text-danger">(*)</span> son obligatorios.
                </p>
                <div class="row">
                    <div class="col-md-4 mt-2">
                        <label class="required">Nombre(s)</label>
                        <input
                            type="text"
                            class="form-control"
                            :class="{
                                'parsley-error': form.errors?.nombre,
                            }"
                            v-model="form.nombre"
                        />
                        <ul
                            v-if="form.errors?.nombre"
                            class="list-unstyled text-danger"
                        >
                            <li class="parsley-required">
                                {{ form.errors?.nombre }}
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-4 mt-2">
                        <label class="required">Apellido Paterno</label>
                        <input
                            type="text"
                            class="form-control"
                            :class="{
                                'parsley-error': form.errors?.paterno,
                            }"
                            v-model="form.paterno"
                        />
                        <ul
                            v-if="form.errors?.paterno"
                            class="list-unstyled text-danger"
                        >
                            <li class="parsley-required">
                                {{ form.errors?.paterno }}
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-4 mt-2">
                        <label class="">Apellido Materno</label>
                        <input
                            type="text"
                            class="form-control"
                            :class="{
                                'parsley-error': form.errors?.materno,
                            }"
                            v-model="form.materno"
                        />
                        <ul
                            v-if="form.errors?.materno"
                            class="list-unstyled text-danger"
                        >
                            <li class="parsley-required">
                                {{ form.errors?.materno }}
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-4 mt-2">
                        <label class="required">Nro. de C.I.</label>
                        <el-input
                            type="text"
                            :class="{
                                'parsley-error': form.errors?.ci,
                            }"
                            v-model="form.ci"
                            autosize
                        ></el-input>
                        <ul
                            v-if="form.errors?.ci"
                            class="d-block text-danger list-unstyled"
                        >
                            <li class="parsley-required">
                                {{ form.errors?.ci }}
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-4 mt-2">
                        <label class="required">Expedido</label>
                        <select
                            class="form-control"
                            :class="{
                                'parsley-error': form.errors?.ci_exp,
                            }"
                            v-model="form.ci_exp"
                        >
                            <option value="">- Seleccione -</option>
                            <option
                                v-for="item in listExpedido"
                                :value="item.value"
                            >
                                {{ item.label }}
                            </option>
                        </select>
                        <ul
                            v-if="form.errors?.ci_exp"
                            class="d-block text-danger list-unstyled"
                        >
                            <li class="parsley-required">
                                {{ form.errors?.ci_exp }}
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-4 mt-2">
                        <label class="">Correo Electrónico</label>
                        <input
                            type="text"
                            class="form-control"
                            :class="{
                                'parsley-error': form.errors?.correo,
                            }"
                            v-model="form.correo"
                        />
                        <ul
                            v-if="form.errors?.correo"
                            class="list-unstyled text-danger"
                        >
                            <li class="parsley-required">
                                {{ form.errors?.correo }}
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-4 mt-2">
                        <label>Dirección</label>
                        <input
                            type="text"
                            class="form-control"
                            :class="{
                                'parsley-error': form.errors?.dir,
                            }"
                            v-model="form.dir"
                        />

                        <ul
                            v-if="form.errors?.dir"
                            class="list-unstyled text-danger"
                        >
                            <li class="parsley-required">
                                {{ form.errors?.dir }}
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-4 mt-2">
                        <label class="required">Celular</label>
                        <input
                            type="text"
                            class="form-control"
                            :class="{
                                'parsley-error': form.errors?.fono,
                            }"
                            v-model="form.fono"
                        />
                        <ul
                            v-if="form.errors?.fono"
                            class="list-unstyled text-danger"
                        >
                            <li class="parsley-required">
                                {{ form.errors?.fono }}
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-4 mt-2">
                        <label class="required">Seleccionar Tipo</label>
                        <select
                            class="form-control"
                            :class="{
                                'parsley-error': form.errors?.tipo,
                            }"
                            v-model="form.tipo"
                        >
                            <option value="">- Seleccione -</option>
                            <option v-for="item in listTipos" :value="item">
                                {{ item }}
                            </option>
                        </select>

                        <ul
                            v-if="form.errors?.tipo"
                            class="list-unstyled text-danger"
                        >
                            <li class="parsley-required">
                                {{ form.errors?.tipo }}
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-4 mt-2">
                        <label class="required">Seleccionar Sucursal</label>
                        <el-select
                            :class="{
                                'parsley-error': form.errors?.sucursal_id,
                            }"
                            no-data-text="Sin datos"
                            no-data-match="Sin resultados"
                            size="large"
                            placeholder="- Seleccione -"
                            v-model="form.sucursal_id"
                            filterable
                        >
                            <el-option
                                v-for="item in listSucursals"
                                :key="item.id"
                                :value="item.id"
                                :label="item.nombre"
                            ></el-option>
                        </el-select>
                        <ul
                            v-if="form.errors?.sucursal_id"
                            class="list-unstyled text-danger"
                        >
                            <li class="parsley-required">
                                {{ form.errors?.sucursal_id }}
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-4 mt-2">
                        <label class="">Foto</label>
                        <input
                            type="file"
                            class="form-control"
                            :class="{
                                'parsley-error': form.errors?.foto,
                            }"
                            @change="cargaArchivo($event, 'foto')"
                            ref="foto"
                        />
                        <ul
                            v-if="form.errors?.foto"
                            class="list-unstyled text-danger"
                        >
                            <li class="parsley-required">
                                {{ form.errors?.foto }}
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-4 mt-2">
                        <label class="required">Acceso</label>
                        <br />
                        <el-switch
                            size="large"
                            active-text="HABILITADO"
                            inactive-text="DESHABILITADO"
                            v-model="form.acceso"
                            :active-value="1"
                            :inactive-value="0"
                            style="
                                --el-switch-on-color: #13ce66;
                                --el-switch-off-color: #ff4949;
                            "
                        />
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
