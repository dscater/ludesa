<script setup>
import MiModal from "@/Components/MiModal.vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { useUsuarios } from "@/composables/usuarios/useUsuarios";
import { watch, ref, computed, defineEmits, onMounted, nextTick } from "vue";
import { useLoginUserStore } from "@/stores/login_users/loginUserStore";
// TOAST
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
const props = defineProps({
    muestra_formulario: {
        type: Boolean,
        default: false,
    },
    accion_formulario: {
        type: Number,
        default: 0,
    },
});

const { auth } = usePage().props;
const { oUsuario, limpiarUsuario } = useUsuarios();
const accion_form = ref(props.accion_formulario);
const muestra_form = ref(props.muestra_formulario);
const enviando = ref(false);
const loginUserStore = useLoginUserStore();
const sucursal_id = ref("");
const verifica_pagos = ref(auth?.user.tipo == "SECRETARIA" ? 1 : 0);
const errors = ref(null);
watch(
    () => props.muestra_formulario,
    (newValue) => {
        muestra_form.value = newValue;
        if (muestra_form.value) {
            cargarSucursals();
            sucursal_id.value = loginUserStore.getLoginUser?.sucursal_id || "";
            verifica_pagos.value =
                loginUserStore.getLoginUser?.verifica_pagos || 0;
            if (auth?.user.tipo == "SECRETARIA") {
                verifica_pagos.value = 1;
            }
            document
                .getElementsByTagName("body")[0]
                .classList.add("modal-open");
        } else {
            document
                .getElementsByTagName("body")[0]
                .classList.remove("modal-open");
        }
    },
);
watch(
    () => props.accion_formulario,
    (newValue) => {
        accion_form.value = newValue;
    },
);

const listSucursals = ref([]);
const cargarSucursals = () => {
    axios.get(route("sucursals.listado")).then((response) => {
        listSucursals.value = response.data.sucursals;
    });
};

const tituloDialog = computed(() => {
    return accion_form.value == 0
        ? `<i class="fa fa-clipboard-check"></i> Asignar Sucursal`
        : `<i class="fa fa-clipboard-check"></i> Asignar Sucursal`;
});

const textBtn = computed(() => {
    if (enviando.value) {
        return `<i class="fa fa-spin fa-spinner"></i> Enviando...`;
    }
    if (accion_form.value == 0) {
        return `<i class="fa fa-save"></i> Guardar`;
    }
    return `<i class="fa fa-edit"></i> Guardar Cambios`;
});

const salir = () => {
    Swal.fire({
        icon: "question",
        title: "Cerrar sesión",
        html: `¿Esta seguro(a) de cerrar sesión?`,
        showCancelButton: true,
        confirmButtonText: "Si, salir",
        cancelButtonText: "Cancelar",
        denyButtonText: `Cancelar`,
        customClass: {
            confirmButton: "btn-success",
        },
    }).then(async (result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
            axios
                .post(route("logout"))
                .then((response) => {})
                .finally(() => {
                    window.location.href = "/";
                });
        }
    });
};

const enviarFormulario = () => {
    if (sucursal_id.value != "") {
        const sucursalSeleccionada = listSucursals.value.filter(
            (item) => item.id === sucursal_id.value,
        )[0];
        Swal.fire({
            title: "¿Quierés asignar esta sucursal?",
            html: `<strong>Sucursal: </strong> ${sucursalSeleccionada.nombre}`,
            showCancelButton: true,
            confirmButtonText: "Si, asignar",
            cancelButtonText: "No, cancelar",
            denyButtonText: `No, cancelar`,
            customClass: {
                confirmButton: "btn-success",
            },
        }).then(async (result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                enviando.value = true;
                await loginUserStore.asignaLoginuser(
                    sucursal_id.value,
                    verifica_pagos.value,
                );
                enviando.value = false;

                if (loginUserStore.login_user) {
                    Swal.fire({
                        icon: "success",
                        title: "Correcto",
                        html: `<strong>Sucursal asignada éxitosamente</strong>`,
                        showConfirmButton: false,
                        timer: 1500,
                    });
                    emits("envio-formulario");
                } else {
                    toast.error(
                        "No se pudo asignar la sucursal intente mas tarde o comuniquese con el Administrador",
                    );
                }
            }
        });
    } else {
        toast.error("Selecciona una sucursal");
    }
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

const cargarListas = () => {};

const options = ref([]);
const loading = ref(false);
onMounted(() => {
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
            <!-- <button
                type="button"
                class="close"
                @click.prevent="cerrarFormulario()"
            >
                <span aria-hidden="true">×</span>
            </button> -->
        </template>

        <template #body>
            <form @submit.prevent="enviarFormulario()">
                <div class="row">
                    <div class="col-lg-4 mt-2 offset-lg-4 col-md-8 offset-md-2">
                        <label class="required">Seleccionar Sucursal</label>
                        <select class="form-control" v-model="sucursal_id">
                            <option value="">- Seleccione -</option>
                            <option
                                v-for="item in listSucursals"
                                :value="item.id"
                            >
                                {{ item.nombre }}
                            </option>
                        </select>
                        <span class="text-muted text-xs mb-0 w-100 text-center">
                            La sucursal seleccionada será asignada a todos los
                            registros que realice
                        </span>
                        <ul
                            v-if="errors?.sucursal_id"
                            class="d-block text-danger list-unstyled"
                        >
                            <li class="parsley-required">
                                {{ errors?.sucursal_id }}
                            </li>
                        </ul>
                    </div>

                    <div class="col-lg-4 mt-2 offset-lg-4 col-md-8 offset-md-2">
                        <label class="required">Recepción de Pagos</label>
                        <br />
                        <el-switch
                            size="large"
                            active-text="SI"
                            inactive-text="NO"
                            v-model="verifica_pagos"
                            :active-value="1"
                            :inactive-value="0"
                            :disabled="auth?.user.tipo == 'SECRETARIA'"
                            style="
                                --el-switch-on-color: #13ce66;
                                --el-switch-off-color: #ff4949;
                            "
                        />
                        <ul
                            v-if="errors?.verifica_pagos"
                            class="d-block text-danger list-unstyled"
                        >
                            <li class="parsley-required">
                                {{ errors?.verifica_pagos }}
                            </li>
                        </ul>
                    </div>
                </div>
            </form>
        </template>
        <template #footer>
            <button
                v-if="!loginUserStore.getLoginUser?.sucursal_id"
                type="button"
                class="btn btn-default"
                :disabled="enviando"
                @click.prevent="salir"
            >
                <i class="fa fa-power-off"></i> Salir
            </button>
            <button
                v-else
                type="button"
                class="btn btn-default"
                @click.prevent="cerrarFormulario"
            >
                <i class="fa fa-times"></i> Cancelar
            </button>
            <button
                type="button"
                class="btn btn-success"
                :disabled="enviando"
                @click.prevent="enviarFormulario"
                v-html="textBtn"
            ></button>
        </template>
    </MiModal>
</template>
