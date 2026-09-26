<script setup>
import MiModal from "@/Components/MiModal.vue";
import { useForm, usePage } from "@inertiajs/vue3";
import { watch, ref, computed, onMounted, nextTick } from "vue";
import { useAxios } from "@/composables/axios/useAxios";
const { axiosDelete } = useAxios();
// TOAST
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
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
    return `<i class="fa fa-plus"></i> Jugadores por Carrera`;
});

const textBtn = computed(() => {
    if (enviando.value) {
        return `<i class="fa fa-spin fa-spinner"></i> Enviando...`;
    }
    if (formCarreraJugador.id == 0) {
        return `<i class="fa fa-save"></i> Guardar`;
    }
    return `<i class="fa fa-edit"></i> Actualizar`;
});

const enviarFormulario = async () => {
    enviando.value = true;

    const url =
        formCarreraJugador.id == 0
            ? route("carrera_jugadors.store")
            : route("carrera_jugadors.update", formCarreraJugador.id);

    formCarreraJugador.campeonato_inscripcion_id = form.id;
    formCarreraJugador.campeonato_id = form.campeonato_id;
    formCarreraJugador.carrera_id = form.carrera_id;
    try {
        const response = await axios.post(url, formCarreraJugador);

        // Respuesta correcta del controlador
        if (response.data.sw) {
            Swal.fire({
                icon: "success",
                title: "Correcto",
                html: `<strong>${
                    response.data.message ?? "Proceso realizado con éxito"
                }</strong>`,
                confirmButtonText: "Aceptar",
                customClass: {
                    confirmButton: "btn-alert-success",
                },
            });

            await actualizaListaCarreraJugadors();

            if (response.data.url_blank) {
                window.open(response.data.url_blank, "_blank");
            }

            emits("envio-formulario");
            cancelarRegistro();
        }
    } catch (error) {
        console.log(error);

        /**
         * Errores de validación Laravel
         * Ejemplo:
         * {
         *   message: "...",
         *   errors: {
         *      monto: ["El campo monto es obligatorio"]
         *   }
         * }
         */
        if (error.response?.status === 422) {
            const errors = error.response.data.errors ?? {};
            formCarreraJugador.errors = error.response.data.errors;
            const errores = Object.values(errors)
                .flat()
                .map((error) => `<li>${error}</li>`)
                .join("");

            Swal.fire({
                icon: "error",
                title: "Errores de validación",
                html: `
                    <p>Existen errores en el formulario:</p>
                    <ul style="text-align:left;">
                        ${errores}
                    </ul>
                `,
                confirmButtonText: "Aceptar",
                customClass: {
                    confirmButton: "btn-error",
                },
            });
        } else {
            const mensaje =
                error.response?.data?.message ??
                "Ocurrió un error inesperado, contacte con el administrador.";

            Swal.fire({
                icon: "error",
                title: "Error",
                text: mensaje,
                confirmButtonText: "Aceptar",
                customClass: {
                    confirmButton: "btn-error",
                },
            });
        }
    } finally {
        // Equivalente a onFinish de Inertia
        enviando.value = false;
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

const listJugadors = ref([]);
const jugadorIdFiltro = ref(null);
const cargarJugadors = () => {
    axios
        .get(route("jugadors.listado"), {
            params: {
                campeonato_id: form.campeonato_id,
                sin_inscripcion: true,
                jugador_id: formCarreraJugador.jugador_id,
            },
        })
        .then((response) => {
            listJugadors.value = response.data.jugadors;
        });
};

const listPosicions = ref([]);
const cargarPosicions = () => {
    axios.get(route("posicions.listado")).then((response) => {
        listPosicions.value = response.data.posicions;
    });
};

const listCarreraJugadors = ref([]);
const cargarCarreraJugadors = () => {
    axios
        .get(route("carrera_jugadors.listado"), {
            params: {
                carrera_id: form.carrera_id,
                campeonato_inscripcion_id: form.id,
            },
        })
        .then((response) => {
            listCarreraJugadors.value = response.data.carrera_jugadors;
        });
};

const actualizaListaCarreraJugadors = () => {
    cargarCarreraJugadors();
};

const initialState = {
    id: 0,
    campeonato_id: "",
    carrera_id: "",
    campeonato_inscripcion_id: "",
    jugador_id: "",
    posicion: "",
    nro: "",
    fecha_registro: "",
    _method: "POST",
};
const mostrarFormulario = ref(false);

const nuevoCarreraJugador = () => {
    cargarJugadors();
    cancelarRegistro();
    toggleFormulario(true);
};

const toggleFormulario = (sw = true) => {
    mostrarFormulario.value = sw;
    if (sw) {
        formCarreraJugador.campeonato_inscripcion_id =
            form.campeonato_inscripcion_id;
        formCarreraJugador.campeonato_id = form.campeonato_id;
        formCarreraJugador.carrera_id = form.carrera_id;
    }
};

const cancelarRegistro = () => {
    formCarreraJugador.clearErrors();
    formCarreraJugador.reset();
    formCarreraJugador.defaults({ ...initialState });
    toggleFormulario(false);
};

const formCarreraJugador = useForm({ ...initialState });

const setIngresoCarreraJugador = (item = null) => {
    formCarreraJugador.clearErrors();
    formCarreraJugador.reset();
    Object.assign(formCarreraJugador, item);
    formCarreraJugador._method = "PUT";
};
const editarCarreraJugador = (item) => {
    setIngresoCarreraJugador(item);
    cargarJugadors();
    toggleFormulario(true);
};

const eliminarCarreraJugador = (item) => {
    Swal.fire({
        // icon: "question",
        title: "¿Quierés eliminar este registro?",
        html: `<strong>${item.jugador.nombres} ${item.jugador.apes}</strong><br/><b>Posición: </b>${item.posicion}<br/><b>Casaca: </b>${item.nro}`,
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
                route("carrera_jugadors.destroy", item.id),
            );
            if (respuesta && respuesta.sw) {
                await actualizaListaCarreraJugadors();
                emits("envio-formulario");
            }
        }
    });
};

onMounted(() => {
    cargarCarreraJugadors();
    // cargarJugadors();
    cargarPosicions();
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
            <div class="row">
                <div class="col-12">
                    <h4 class="text-center text-primary fs-5">
                        {{ form.campeonato.periodo }} -
                        {{ form.campeonato.gestion }}:
                        {{ form.campeonato.nombre }} ({{
                            form.campeonato.tipo
                        }})
                    </h4>
                    <h4 class="text-center fs-6">
                        Carrera: {{ form.carrera.nombre }}
                    </h4>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="row">
                        <div class="col-12">
                            <button
                                type="button"
                                class="btn btn-primary"
                                @click.prevent="nuevoCarreraJugador"
                                v-if="!mostrarFormulario"
                            >
                                <i class="fa fa-plus"></i> Agregar Jugador
                            </button>
                            <button
                                type="button"
                                class="btn btn-light border"
                                @click="cancelarRegistro"
                                v-if="mostrarFormulario"
                            >
                                <i class="fa fa-times"></i> Cancelar
                            </button>
                        </div>
                        <div class="col-12 my-2" v-if="mostrarFormulario">
                            <form @submit="enviarFormulario">
                                <div class="row">
                                    <div class="col-12">
                                        <h5
                                            class="fs-7 text-center fw-bold bg6 py-2"
                                        >
                                            {{
                                                formCarreraJugador.id == 0
                                                    ? "Nuevo"
                                                    : "Editar"
                                            }}
                                            Registro
                                        </h5>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <label>Jugador</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fa fa-user"></i>
                                            </span>
                                            <div
                                                class="form-control border-0 p-0"
                                            >
                                                <el-select
                                                    v-model="
                                                        formCarreraJugador.jugador_id
                                                    "
                                                    class="el-select-input-group-right"
                                                    no-data-text="Sin datos"
                                                    no-match-text="Sin resultados"
                                                    placeholder="Seleccionar Jugador"
                                                    filterable
                                                >
                                                    <el-option
                                                        v-for="item in listJugadors"
                                                        :key="item.id"
                                                        :value="item.id"
                                                        :label="`${item.nombres} ${item.apes} - ${item.ci}`"
                                                    ></el-option>
                                                </el-select>
                                            </div>
                                        </div>
                                        <ul
                                            v-if="
                                                formCarreraJugador.errors
                                                    ?.jugador_id
                                            "
                                            class="d-block text-danger list-unstyled"
                                        >
                                            <li class="parsley-required">
                                                {{
                                                    formCarreraJugador.errors
                                                        ?.jugador_id[0]
                                                }}
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Nro. de Casaca </label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-tshirt"></i>
                                            </span>
                                            <div
                                                class="form-control border-0 p-0"
                                            >
                                                <input
                                                    v-model="
                                                        formCarreraJugador.nro
                                                    "
                                                    class="form-control"
                                                />
                                            </div>
                                        </div>
                                        <ul
                                            v-if="
                                                formCarreraJugador.errors?.nro
                                            "
                                            class="d-block text-danger list-unstyled"
                                        >
                                            <li class="parsley-required">
                                                {{
                                                    formCarreraJugador.errors
                                                        ?.nro[0]
                                                }}
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Posición</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fa fa-futbol"></i>
                                            </span>
                                            <div
                                                class="form-control border-0 p-0"
                                            >
                                                <el-select
                                                    v-model="
                                                        formCarreraJugador.posicion
                                                    "
                                                    class="el-select-input-group-right"
                                                    no-data-text="Sin datos"
                                                    no-match-text="Sin resultados"
                                                    placeholder="Seleccionar Posición"
                                                    filterable
                                                >
                                                    <el-option
                                                        v-for="item in listPosicions"
                                                        :key="item.value"
                                                        :value="item.value"
                                                        :label="`${item.label}`"
                                                    ></el-option>
                                                </el-select>
                                            </div>
                                        </div>
                                        <ul
                                            v-if="
                                                formCarreraJugador.errors
                                                    ?.posicion
                                            "
                                            class="d-block text-danger list-unstyled"
                                        >
                                            <li class="parsley-required">
                                                {{
                                                    formCarreraJugador.errors
                                                        ?.posicion[0]
                                                }}
                                            </li>
                                        </ul>
                                    </div>
                                    <div
                                        class="col-md-2 d-flex align-items-end mt-2"
                                    >
                                        <button
                                            :disabled="enviando"
                                            @click="enviarFormulario"
                                            class="btn btn-success w-100"
                                            v-html="textBtn"
                                        ></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>N° Casaca</th>
                                <th>Posición</th>
                                <th>Nombre</th>
                                <th>Fecha Registro</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-if="listCarreraJugadors.length > 0">
                                <tr
                                    v-for="item in listCarreraJugadors"
                                    :key="item.id"
                                >
                                    <td>{{ item.nro }}</td>
                                    <td>{{ item.posicion }}</td>
                                    <td>
                                        {{ item.jugador.nombres }}
                                        {{ item.jugador.apes }}
                                    </td>
                                    <td>{{ item.fecha_registro_t }}</td>
                                    <td>
                                        <button
                                            type="button"
                                            class="btn btn-warning btn-sm fs-7"
                                            @click.prevent="
                                                editarCarreraJugador(item)
                                            "
                                        >
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button
                                            type="button"
                                            class="btn btn-danger btn-sm fs-7"
                                            @click.prevent="
                                                eliminarCarreraJugador(item)
                                            "
                                        >
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <template v-else>
                                <tr>
                                    <td colspan="5">SIN REGISTROS</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>
        <template #footer>
            <button
                type="button"
                class="btn btn-default"
                @click.prevent="cerrarFormulario()"
            >
                Cerrar
            </button>
        </template>
    </MiModal>
</template>
