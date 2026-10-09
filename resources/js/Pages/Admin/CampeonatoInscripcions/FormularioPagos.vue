<script setup>
import MiModal from "@/Components/MiModal.vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { watch, ref, computed, onMounted, nextTick } from "vue";
import { useAxios } from "@/composables/axios/useAxios";
import MiTable from "@/Components/MiTable.vue";
const { axiosDelete } = useAxios();
const { props: props_page } = usePage();
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
    partido_id: {
        type: Number,
        default: null,
    },
});

const muestra_form = ref(props.muestra_formulario);
const enviando = ref(false);
const form = props.form;

const tituloDialog = computed(() => {
    return `<i class="fa fa-money-bill"></i> Deudas en el campeonato`;
});

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

const listDeudas = ref([]);
const cargarDeudas = () => {
    axios
        .get(route("campeonato_inscripcions.deudas", form.id), {
            params: {
                partido_id: props.partido_id,
            },
        })
        .then((response) => {
            listDeudas.value = response.data.deudas;
        });
};

const actualizarDatosPartido = (partido, col) => {
    if (col == "pago_local" || col == "pago_visitante") {
        if (col == "pago_local") {
            if (
                !partido["total_local"] ||
                parseFloat(partido["total_local"]) < 0
            ) {
                toast.info("El pago no puede estar vacío o menor a 0");
                return;
            }
        }
        if (col == "pago_visitante") {
            if (
                !partido["total_visitante"] ||
                parseFloat(partido["total_visitante"]) < 0
            ) {
                toast.info("El pago no puede estar vacío o menor a 0");
                return;
            }
        }
    }

    const data = partido[col];
    axios
        .post(route("partidos.actualizaDatosPartido", partido.id), {
            _method: "PATCH",
            col: col,
            data: data,
        })
        .then((response) => {
            toast.success("Registro actualizado correctamente", {
                autoClose: 300,
            });
            emits("envio-formulario");

            router.reload({
                only: ["partido"],
            });
        });
};

const actualizarDatosDetalle = (id, col, lv, index_partido) => {
    let lista =
        lv == "local"
            ? listDeudas.value.local[index_partido].partido_detalles
            : listDeudas.value.visitante[index_partido].partido_detalles;
    // console.log(lista);
    const index = lista.findIndex((item) => item.id == id);
    // console.log(id);
    // console.log(index);

    if (
        !lista[index][col] &&
        col != "total_amarillas" &&
        col != "total_rojas" &&
        col != "pagado_amarillas" &&
        col != "pagado_rojas" &&
        col != "amarillas" &&
        col != "rojas"
    ) {
        toast.info("No se enviaron datos");
        return;
    }

    if (col == "pagado_amarillas" || col == "pagado_rojas") {
        if (col == "pagado_amarillas")
            if (
                !lista[index]["total_amarillas"] ||
                parseFloat(lista[index]["total_amarillas"]) < 0
            ) {
                toast.info("El pago no puede estar vacío o menor a 0");
                return;
            }

        if (col == "pagado_rojas")
            if (
                !lista[index]["total_rojas"] ||
                parseFloat(lista[index]["total_rojas"]) < 0
            ) {
                toast.info("El pago no puede estar vacío o menor a 0");
                return;
            }
    }

    if (col == "total_amarillas" || col == "total_rojas") {
        console.log(col);
        console.log(lista[index][col]);
        if (col == "total_amarillas")
            if (parseFloat(lista[index]["total_amarillas"]) < 0) {
                toast.info("El pago no puede estar vacío o menor a 0");
                return;
            }

        if (col == "total_rojas")
            if (parseFloat(lista[index]["total_rojas"]) < 0) {
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
            if (lv == "local") {
                listDeudas.value.local[index_partido].partido_detalles[index][
                    col
                ] = response.data.partido_detalle[col];
                if (col == "amarillas") {
                    listDeudas.value.local[index_partido].partido_detalles[
                        index
                    ]["total_amarillas"] =
                        response.data.partido_detalle["total_amarillas"];
                }
                if (col == "rojas") {
                    listDeudas.value.local[index_partido].partido_detalles[
                        index
                    ]["total_rojas"] =
                        response.data.partido_detalle["total_rojas"];
                }
            } else {
                listDeudas.value.visitante[index_partido].partido_detalles[
                    index
                ][col] = response.data.partido_detalle[col];
                if (col == "amarillas") {
                    listDeudas.value.visitante[index_partido].partido_detalles[
                        index
                    ]["total_amarillas"] =
                        response.data.partido_detalle["total_amarillas"];
                }
                if (col == "rojas") {
                    listDeudas.value.visitante[index_partido].partido_detalles[
                        index
                    ]["total_rojas"] =
                        response.data.partido_detalle["total_rojas"];
                }
            }

            toast.success("Registro actualizado correctamente", {
                autoClose: 300,
            });
            emits("envio-formulario");
        });
};

const deudasDerechos = computed(() => {
    const local = listDeudas.value?.local ?? [];
    const visitante = listDeudas.value?.visitante ?? [];

    const totalLocal = local.reduce((acc, item) => {
        if (item.pago_local == 0 && item.total_local > 0) {
            return acc + parseFloat(item.total_local || 0);
        }

        return acc;
    }, 0);

    const totalVisitante = visitante.reduce((acc, item) => {
        if (item.pago_visitante == 0 && item.total_visitante > 0) {
            return acc + parseFloat(item.total_visitante || 0);
        }

        return acc;
    }, 0);

    return totalLocal + totalVisitante;
});

const deudasAmarillas = computed(() => {
    const local = listDeudas.value?.local ?? [];
    const visitante = listDeudas.value?.visitante ?? [];

    const totalLocal = local.reduce((total, partido) => {
        return (
            total +
            (partido.partido_detalles ?? []).reduce((acc, item) => {
                if (item.amarillas > 0 && item.pagado_amarillas == 0) {
                    return acc + parseFloat(item.total_amarillas || 0);
                }

                return acc;
            }, 0)
        );
    }, 0);

    const totalVisitante = visitante.reduce((total, partido) => {
        return (
            total +
            (partido.partido_detalles ?? []).reduce((acc, item) => {
                if (item.amarillas > 0 && item.pagado_amarillas == 0) {
                    return acc + parseFloat(item.total_amarillas || 0);
                }

                return acc;
            }, 0)
        );
    }, 0);

    return totalLocal + totalVisitante;
});

const deudasRojas = computed(() => {
    const local = listDeudas.value?.local ?? [];
    const visitante = listDeudas.value?.visitante ?? [];

    const totalLocal = local.reduce((total, partido) => {
        return (
            total +
            (partido.partido_detalles ?? []).reduce((acc, item) => {
                if (item.rojas > 0 && item.pagado_rojas == 0) {
                    return acc + parseFloat(item.total_rojas || 0);
                }

                return acc;
            }, 0)
        );
    }, 0);

    const totalVisitante = visitante.reduce((total, partido) => {
        return (
            total +
            (partido.partido_detalles ?? []).reduce((acc, item) => {
                if (item.rojas > 0 && item.pagado_rojas == 0) {
                    return acc + parseFloat(item.total_rojas || 0);
                }

                return acc;
            }, 0)
        );
    }, 0);

    return totalLocal + totalVisitante;
});

const actualizaCampeonatoInscripcionPago = () => {
    axios
        .patch(route("campeonato_inscripcions.actualizaPago", form.id), {
            pago_inscripcion: form.pago_inscripcion,
            _method: "patch",
        })
        .then((response) => {
            toast.success("Registro actualizado correctamente", {
                autoClose: 300,
            });
            emits("envio-formulario");
        });
};

onMounted(() => {
    cargarDeudas();
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
                <div class="col-12 border-top mt-1">
                    <div class="row">
                        <div
                            class="col-12 fw-bold text-center border-bottom pb-2 d-flex align-items-center justify-content-center"
                            :class="{
                                'bgInactivo text-white': !form.pago_inscripcion,
                                bgActivo: form.pago_inscripcion,
                            }"
                        >
                            Por inscripción Bs. {{ form.total_inscripcion }}
                            <input
                                type="checkbox"
                                class="ms-1"
                                v-model="form.pago_inscripcion"
                                :true-value="1"
                                :false-value="0"
                                style="height: 18px; width: 18px"
                                @change="actualizaCampeonatoInscripcionPago"
                                v-if="
                                    props_page.auth?.user.permisos == '*' ||
                                    props_page.auth?.user.permisos.includes(
                                        'campeonato_inscripcions.actualizaPago',
                                    )
                                "
                            />
                        </div>
                        <div class="col-md-4 fw-bold text-center">
                            Total por derecho de cancha<br />
                            Bs. {{ deudasDerechos }}
                        </div>
                        <div
                            class="col-md-4 fw-bold text-center border-start border-end"
                        >
                            Total por Amarillas<br />
                            Bs. {{ deudasAmarillas }}
                        </div>
                        <div class="col-md-4 fw-bold text-center">
                            Total por Rojas<br />
                            Bs. {{ deudasRojas }}
                        </div>
                    </div>
                </div>
                <div
                    class="col-12 mt-2 border-top"
                    v-for="(item, index_partido) in listDeudas.local"
                    :key="item.id"
                >
                    <div class="row">
                        <div class="col-12 text-primary fw-bold py-2">
                            <i class="fa fa-calendar-alt"></i> Fecha Partido:
                            {{ item.fecha_hora_t }}
                        </div>
                        <div class="col-12">
                            <div class="input-group">
                                <span class="input-group-text px-1"
                                    ><i class="fa fa-money-bill me-1"></i
                                    >Derecho de Cancha</span
                                >
                                <span
                                    class="form-control text-center"
                                    :class="{
                                        'bgInactivo text-white':
                                            !item.pago_local,
                                        bgActivo: item.pago_local,
                                    }"
                                    >{{ item.total_local }}</span
                                >
                                <div
                                    class="input-group-text"
                                    v-if="
                                        props_page.auth?.user.permisos == '*' ||
                                        props_page.auth?.user.permisos.includes(
                                            'partidos.actualizaDatosPartido',
                                        )
                                    "
                                >
                                    <input
                                        type="checkbox"
                                        class="form-conrtol"
                                        :true-value="1"
                                        :false-value="0"
                                        v-model="item.pago_local"
                                        @change="
                                            actualizarDatosPartido(
                                                item,
                                                'pago_local',
                                            )
                                        "
                                    />
                                </div>
                            </div>
                        </div>
                        <div
                            class="col-12"
                            style="max-height: 40vh; overflow: auto"
                        >
                            <table class="table table-bordered table-hovered">
                                <thead>
                                    <tr>
                                        <th>N°</th>
                                        <th></th>
                                        <th>Jugador</th>
                                        <th>Bs. Amarillas</th>
                                        <th>Bs. Rojas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template
                                        v-if="item.partido_detalles.length > 0"
                                        v-for="jugador in item.partido_detalles"
                                        :key="jugador.id"
                                    >
                                        <tr
                                            v-if="
                                                jugador.total_amarillas > 0 ||
                                                jugador.total_rojas > 0
                                            "
                                            class="text-xs"
                                        >
                                            <td>
                                                {{
                                                    jugador.carrera_jugador.nro
                                                }}
                                            </td>
                                            <td>
                                                <img
                                                    :src="
                                                        jugador.carrera_jugador
                                                            .jugador.url_foto
                                                    "
                                                    alt=""
                                                    class="rounded-circle"
                                                    height="60px"
                                                />
                                            </td>
                                            <td>
                                                <div class="db-block w-100">
                                                    {{
                                                        jugador.carrera_jugador
                                                            .jugador.nombres
                                                    }}
                                                    {{
                                                        jugador.carrera_jugador
                                                            .jugador.apes
                                                    }}
                                                </div>
                                                <div
                                                    class="text-muted fw-bold d-block w-100"
                                                >
                                                    {{
                                                        jugador.carrera_jugador
                                                            .posicion
                                                    }}
                                                </div>
                                                <div class="text-muted d-block">
                                                    {{
                                                        jugador.carrera_jugador
                                                            .jugador.ci
                                                    }}
                                                </div>
                                            </td>
                                            <td>
                                                <div
                                                    class="w-100 text-center form-control text-xs"
                                                >
                                                    <i
                                                        class="fa fa-square text-yellow"
                                                    ></i>
                                                    {{ jugador.amarillas }}
                                                </div>
                                                <div
                                                    class="w-100 text-center"
                                                    v-if="
                                                        jugador.total_amarillas >
                                                        0
                                                    "
                                                >
                                                    <div class="input-group">
                                                        <span
                                                            class="input-group-text px-1"
                                                        >
                                                            <i
                                                                class="fa fa-money-bill"
                                                            ></i>
                                                        </span>
                                                        <input
                                                            type="number"
                                                            class="form-control text-center"
                                                            :class="{
                                                                'bg-danger text-white':
                                                                    !jugador.pagado_amarillas &&
                                                                    jugador.total_amarillas >
                                                                        0,
                                                                'bgActivo text-dark':
                                                                    jugador.pagado_amarillas &&
                                                                    jugador.total_amarillas >
                                                                        0,
                                                            }"
                                                            v-model="
                                                                jugador.total_amarillas
                                                            "
                                                            :disabled="
                                                                !(
                                                                    props_page
                                                                        .auth
                                                                        ?.user
                                                                        .permisos ==
                                                                        '*' ||
                                                                    props_page.auth?.user.permisos.includes(
                                                                        'partido_detalles.actualizaDatosDetalle',
                                                                    )
                                                                )
                                                            "
                                                            @keyup="
                                                                actualizarDatosDetalle(
                                                                    jugador.id,
                                                                    'total_amarillas',
                                                                    'local',
                                                                    index_partido,
                                                                )
                                                            "
                                                            @change="
                                                                actualizarDatosDetalle(
                                                                    jugador.id,
                                                                    'total_amarillas',
                                                                    'local',
                                                                    index_partido,
                                                                )
                                                            "
                                                        />
                                                        <div
                                                            class="input-group-text"
                                                        >
                                                            <input
                                                                type="checkbox"
                                                                v-model="
                                                                    jugador.pagado_amarillas
                                                                "
                                                                :true-value="1"
                                                                :false-value="0"
                                                                v-if="
                                                                    props_page
                                                                        .auth
                                                                        ?.user
                                                                        .permisos ==
                                                                        '*' ||
                                                                    props_page.auth?.user.permisos.includes(
                                                                        'partido_detalles.actualizaDatosDetalle',
                                                                    )
                                                                "
                                                                @change="
                                                                    actualizarDatosDetalle(
                                                                        jugador.id,
                                                                        'pagado_amarillas',
                                                                        'local',
                                                                        index_partido,
                                                                    )
                                                                "
                                                            />
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div
                                                    class="w-100 text-center form-control text-xs"
                                                >
                                                    <i
                                                        class="fa fa-square text-danger"
                                                    ></i>
                                                    {{ jugador.rojas }}
                                                </div>
                                                <div
                                                    class="w-100 text-center"
                                                    v-if="
                                                        jugador.total_rojas > 0
                                                    "
                                                >
                                                    <div class="input-group">
                                                        <span
                                                            class="input-group-text px-1"
                                                        >
                                                            <i
                                                                class="fa fa-money-bill"
                                                            ></i>
                                                        </span>
                                                        <input
                                                            type="number"
                                                            class="form-control text-center"
                                                            :class="{
                                                                'bg-danger text-white':
                                                                    !jugador.pagado_rojas &&
                                                                    jugador.total_rojas >
                                                                        0,
                                                                'bgActivo text-dark':
                                                                    jugador.pagado_rojas &&
                                                                    jugador.total_rojas >
                                                                        0,
                                                            }"
                                                            v-model="
                                                                jugador.total_rojas
                                                            "
                                                            :disabled="
                                                                !(
                                                                    props_page
                                                                        .auth
                                                                        ?.user
                                                                        .permisos ==
                                                                        '*' ||
                                                                    props_page.auth?.user.permisos.includes(
                                                                        'partido_detalles.actualizaDatosDetalle',
                                                                    )
                                                                )
                                                            "
                                                            @keyup="
                                                                actualizarDatosDetalle(
                                                                    jugador.id,
                                                                    'total_rojas',
                                                                    'local',
                                                                    index_partido,
                                                                )
                                                            "
                                                            @change="
                                                                actualizarDatosDetalle(
                                                                    jugador.id,
                                                                    'total_rojas',
                                                                    'local',
                                                                    index_partido,
                                                                )
                                                            "
                                                        />
                                                        <div
                                                            class="input-group-text"
                                                        >
                                                            <input
                                                                type="checkbox"
                                                                v-model="
                                                                    jugador.pagado_rojas
                                                                "
                                                                :true-value="1"
                                                                :false-value="0"
                                                                v-if="
                                                                    props_page
                                                                        .auth
                                                                        ?.user
                                                                        .permisos ==
                                                                        '*' ||
                                                                    props_page.auth?.user.permisos.includes(
                                                                        'partido_detalles.actualizaDatosDetalle',
                                                                    )
                                                                "
                                                                @change="
                                                                    actualizarDatosDetalle(
                                                                        jugador.id,
                                                                        'pagado_rojas',
                                                                        'local',
                                                                        index_partido,
                                                                    )
                                                                "
                                                            />
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                    <template v-else>
                                        <tr>
                                            <td
                                                colspan="4"
                                                class="text-center text-muted"
                                            >
                                                SIN AMARILLAS/ROJAS EN EL
                                                PARTIDO
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div
                    class="col-12 mt-2 border-top"
                    v-for="(item, index_partido) in listDeudas.visitante"
                    :key="item.id"
                >
                    <div class="row">
                        <div class="col-12 text-primary fw-bold py-2">
                            <i class="fa fa-calendar-alt"></i> Fecha Partido:
                            {{ item.fecha_hora_t }}
                        </div>
                        <div class="col-12">
                            <div class="input-group">
                                <span class="input-group-text px-1"
                                    ><i class="fa fa-money-bill me-1"></i
                                    >Derecho de Cancha</span
                                >
                                <span
                                    class="form-control text-center"
                                    :class="{
                                        'bgInactivo text-white':
                                            !item.pago_visitante,
                                        bgActivo: item.pago_visitante,
                                    }"
                                    >{{ item.total_visitante }}</span
                                >
                                <div
                                    class="input-group-text"
                                    v-if="
                                        props_page.auth?.user.permisos == '*' ||
                                        props_page.auth?.user.permisos.includes(
                                            'partidos.actualizaDatosPartido',
                                        )
                                    "
                                >
                                    <input
                                        type="checkbox"
                                        class="form-conrtol"
                                        :true-value="1"
                                        :false-value="0"
                                        v-model="item.pago_visitante"
                                        @change="
                                            actualizarDatosPartido(
                                                item,
                                                'pago_visitante',
                                            )
                                        "
                                    />
                                </div>
                            </div>
                        </div>
                        <div
                            class="col-12"
                            style="max-height: 40vh; overflow: auto"
                        >
                            <table class="table table-bordered table-hovered">
                                <thead>
                                    <tr>
                                        <th>N°</th>
                                        <th></th>
                                        <th>Jugador</th>
                                        <th>Bs. Amarillas</th>
                                        <th>Bs. Rojas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template
                                        v-if="item.partido_detalles.length > 0"
                                        v-for="jugador in item.partido_detalles"
                                        :key="jugador.id"
                                    >
                                        <tr
                                            v-if="
                                                jugador.total_amarillas > 0 ||
                                                jugador.total_rojas > 0
                                            "
                                            class="text-xs"
                                        >
                                            <td>
                                                {{
                                                    jugador.carrera_jugador.nro
                                                }}
                                            </td>
                                            <td>
                                                <img
                                                    :src="
                                                        jugador.carrera_jugador
                                                            .jugador.url_foto
                                                    "
                                                    alt=""
                                                    class="rounded-circle"
                                                    height="60px"
                                                />
                                            </td>
                                            <td>
                                                <div class="db-block w-100">
                                                    {{
                                                        jugador.carrera_jugador
                                                            .jugador.nombres
                                                    }}
                                                    {{
                                                        jugador.carrera_jugador
                                                            .jugador.apes
                                                    }}
                                                </div>
                                                <div
                                                    class="text-muted fw-bold d-block w-100"
                                                >
                                                    {{
                                                        jugador.carrera_jugador
                                                            .posicion
                                                    }}
                                                </div>
                                                <div class="text-muted d-block">
                                                    {{
                                                        jugador.carrera_jugador
                                                            .jugador.ci
                                                    }}
                                                </div>
                                            </td>
                                            <td>
                                                <div
                                                    class="w-100 text-center form-control text-xs"
                                                >
                                                    <i
                                                        class="fa fa-square text-yellow"
                                                    ></i>
                                                    {{ jugador.amarillas }}
                                                </div>
                                                <div
                                                    class="w-100 text-center"
                                                    v-if="
                                                        jugador.total_amarillas >
                                                        0
                                                    "
                                                >
                                                    <div class="input-group">
                                                        <span
                                                            class="input-group-text px-1"
                                                        >
                                                            <i
                                                                class="fa fa-money-bill"
                                                            ></i>
                                                        </span>
                                                        <input
                                                            type="number"
                                                            class="form-control text-center"
                                                            :class="{
                                                                'bg-danger text-white':
                                                                    !jugador.pagado_amarillas &&
                                                                    jugador.total_amarillas >
                                                                        0,
                                                                'bgActivo text-dark':
                                                                    jugador.pagado_amarillas &&
                                                                    jugador.total_amarillas >
                                                                        0,
                                                            }"
                                                            v-model="
                                                                jugador.total_amarillas
                                                            "
                                                            :disabled="
                                                                !(
                                                                    props_page
                                                                        .auth
                                                                        ?.user
                                                                        .permisos ==
                                                                        '*' ||
                                                                    props_page.auth?.user.permisos.includes(
                                                                        'partido_detalles.actualizaDatosDetalle',
                                                                    )
                                                                )
                                                            "
                                                            @keyup="
                                                                actualizarDatosDetalle(
                                                                    jugador.id,
                                                                    'total_amarillas',
                                                                    'visitante',
                                                                    index_partido,
                                                                )
                                                            "
                                                            @change="
                                                                actualizarDatosDetalle(
                                                                    jugador.id,
                                                                    'total_amarillas',
                                                                    'visitante',
                                                                    index_partido,
                                                                )
                                                            "
                                                        />
                                                        <div
                                                            class="input-group-text"
                                                            v-if="
                                                                props_page.auth
                                                                    ?.user
                                                                    .permisos ==
                                                                    '*' ||
                                                                props_page.auth?.user.permisos.includes(
                                                                    'partido_detalles.actualizaDatosDetalle',
                                                                )
                                                            "
                                                        >
                                                            <input
                                                                type="checkbox"
                                                                v-model="
                                                                    jugador.pagado_amarillas
                                                                "
                                                                :true-value="1"
                                                                :false-value="0"
                                                                @change="
                                                                    actualizarDatosDetalle(
                                                                        jugador.id,
                                                                        'pagado_amarillas',
                                                                        'visitante',
                                                                        index_partido,
                                                                    )
                                                                "
                                                            />
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div
                                                    class="w-100 text-center form-control text-xs"
                                                >
                                                    <i
                                                        class="fa fa-square text-danger"
                                                    ></i>
                                                    {{ jugador.rojas }}
                                                </div>
                                                <div
                                                    class="w-100 text-center"
                                                    v-if="
                                                        jugador.total_rojas > 0
                                                    "
                                                >
                                                    <div class="input-group">
                                                        <span
                                                            class="input-group-text px-1"
                                                        >
                                                            <i
                                                                class="fa fa-money-bill"
                                                            ></i>
                                                        </span>
                                                        <input
                                                            type="number"
                                                            class="form-control text-center"
                                                            :class="{
                                                                'bg-danger text-white':
                                                                    !jugador.pagado_rojas &&
                                                                    jugador.total_rojas >
                                                                        0,
                                                                'bgActivo text-dark':
                                                                    jugador.pagado_rojas &&
                                                                    jugador.total_rojas >
                                                                        0,
                                                            }"
                                                            :disabled="
                                                                !(
                                                                    props_page
                                                                        .auth
                                                                        ?.user
                                                                        .permisos ==
                                                                        '*' ||
                                                                    props_page.auth?.user.permisos.includes(
                                                                        'partido_detalles.actualizaDatosDetalle',
                                                                    )
                                                                )
                                                            "
                                                            v-model="
                                                                jugador.total_rojas
                                                            "
                                                            @keyup="
                                                                actualizarDatosDetalle(
                                                                    jugador.id,
                                                                    'total_rojas',
                                                                    'visitante',
                                                                    index_partido,
                                                                )
                                                            "
                                                            @change="
                                                                actualizarDatosDetalle(
                                                                    jugador.id,
                                                                    'total_rojas',
                                                                    'visitante',
                                                                    index_partido,
                                                                )
                                                            "
                                                        />
                                                        <div
                                                            class="input-group-text"
                                                            v-if="
                                                                props_page.auth
                                                                    ?.user
                                                                    .permisos ==
                                                                    '*' ||
                                                                props_page.auth?.user.permisos.includes(
                                                                    'partido_detalles.actualizaDatosDetalle',
                                                                )
                                                            "
                                                        >
                                                            <input
                                                                type="checkbox"
                                                                v-model="
                                                                    jugador.pagado_rojas
                                                                "
                                                                :true-value="1"
                                                                :false-value="0"
                                                                @change="
                                                                    actualizarDatosDetalle(
                                                                        jugador.id,
                                                                        'pagado_rojas',
                                                                        'visitante',
                                                                        index_partido,
                                                                    )
                                                                "
                                                            />
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                    <template v-else>
                                        <tr>
                                            <td
                                                colspan="4"
                                                class="text-center text-muted"
                                            >
                                                SIN AMARILLAS/ROJAS EN EL
                                                PARTIDO
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
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
