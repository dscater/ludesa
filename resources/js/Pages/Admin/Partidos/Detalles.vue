<script setup>
import Content from "@/Components/Content.vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { usePartidos } from "@/composables/partidos/usePartidos";
import { useAxios } from "@/composables/axios/useAxios";
import { ref, onMounted, onBeforeMount, computed, watch } from "vue";
import { useCampeonatoInscripcions } from "@/composables/campeonato_inscripcions/useCampeonatoInscripcions";
import { useAppStore } from "@/stores/aplicacion/appStore";
// import { useMenu } from "@/composables/useMenu";
import { buttonProps } from "element-plus";
import MiTable from "@/Components/MiTable.vue";
import axios from "axios";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import FormularioPagos from "../CampeonatoInscripcions/FormularioPagos.vue";
// const { mobile, identificaDispositivo } = useMenu();
const props = defineProps({
    campeonato: {
        type: Object,
        required: true,
    },
    partido: {
        type: Object,
        required: true,
    },
    deudas_local: {
        type: Object,
        required: true,
    },
    deudas_visitante: {
        type: Object,
        required: true,
    },
    local_detalles: {
        type: Array,
        required: true,
    },
    visitante_detalles: {
        type: Array,
        required: true,
    },
    costo_tarjetas: {
        type: Object,
        required: true,
    },
    costo_derechos: {
        type: Object,
        required: true,
    },
});
const list_local_detalles = ref(props.local_detalles);
const list_visitante_detalles = ref(props.visitante_detalles);

const {
    setCampeonatoInscripcion,
    limpiarCampeonatoInscripcion,
    form: formCI,
} = useCampeonatoInscripcions();

const { props: props_page } = usePage();
const muestra_formulario_pagos = ref(false);
const appStore = useAppStore();
onBeforeMount(async () => {
    appStore.startLoading();

    try {
        // await Promise.all([cargarCampeonatos(), cargarPartidos()]);
    } finally {
        appStore.stopLoading();
    }
});

const miTableLocal = ref(null);
const miTableVisitante = ref(null);
const headers = [
    {
        label: "N°",
        key: "carrera_jugador.nro",
        sortable: true,
        width: "3%",
        fixed: true,
    },
    {
        label: "FOTO",
        key: "foto",
        sortable: true,
        width: "3%",
        fixed: true,
    },
    {
        label: "JUGADOR",
        key: "carrera_jugador.jugador.nombres",
        sortable: true,
        fixed: true,
    },
    {
        label: "TITULAR",
        key: "titular",
        sortable: true,
        fixed: true,
    },
    {
        label: "GOLES",
        key: "goles",
        sortable: true,
    },
    {
        label: "AMARILLAS",
        key: "amarillas",
        sortable: true,
    },
    {
        label: "ROJAS",
        key: "rojas",
        sortable: true,
    },
];

onMounted(async () => {
    if (miTableLocal.value) {
        await miTableLocal.value.cargarDatos();
    }

    if (miTableVisitante.value) {
        await miTableVisitante.value.cargarDatos();
    }

    props.partido.total_local = props.costo_derechos.derecho;
    props.partido.total_visitante = props.costo_derechos.derecho;
    if (props.partido.estado != "FINALIZADO") {
        actualizarDatosPartido("total_local", props.costo_derechos.derecho);
        actualizarDatosPartido("total_visitante", props.costo_derechos.derecho);
    }
});

const totalGolesLocal = computed(() => {
    return list_local_detalles.value.reduce((acc, item) => {
        return acc + parseFloat(item.goles);
    }, 0);
});

const totalGolesVisitante = computed(() => {
    return list_visitante_detalles.value.reduce((acc, item) => {
        return acc + parseFloat(item.goles);
    }, 0);
});

watch([totalGolesLocal, totalGolesVisitante], () => {
    props.partido.goles_local = totalGolesLocal.value;
    props.partido.goles_visitante = totalGolesVisitante.value;
    actualizarDatosPartido("goles_local", totalGolesLocal.value);
    actualizarDatosPartido("goles_visitante", totalGolesVisitante.value);
});

const recargarJugadores = () => {
    axios
        .get(route("partidos.actualizarJugadores", props.partido.id))
        .then((response) => {
            // recargar los datos
            // router.reload({
            //     only: ["local_detalles", "visitante_detalles"],
            //     preserveScroll: true,
            // });
            list_local_detalles.value = response.data.local_detalles;
            list_visitante_detalles.value = response.data.visitante_detalles;
        });
};

const actualizarDatosPartido = (col) => {
    if (
        !props.partido[col] &&
        col != "pago_local" &&
        col != "pago_visitante" &&
        col != "goles_local" &&
        col != "goles_visitante"
    ) {
        toast.info("No se enviaron datos");
        return;
    }

    if (col == "goles_local" || col == "goles_visitante") {
        if (props.partido[col] < 0)
            toast.info("Los goles no pueden ser menores a 0");
        return;
    }

    if (col == "pago_local" || col == "pago_visitante") {
        if (col == "pago_local") {
            if (
                !props.partido["total_local"] ||
                parseFloat(props.partido["total_local"]) < 0
            ) {
                toast.info("El pago no puede estar vacío o menor a 0");
                return;
            }
        }
        if (col == "pago_visitante") {
            if (
                !props.partido["total_visitante"] ||
                parseFloat(props.partido["total_visitante"]) < 0
            ) {
                toast.info("El pago no puede estar vacío o menor a 0");
                return;
            }
        }
    }

    const data = props.partido[col];
    axios
        .post(route("partidos.actualizaDatosPartido", props.partido.id), {
            _method: "PATCH",
            col: col,
            data: data,
        })
        .then((response) => {
            router.reload({
                only: ["partido"],
            });
            toast.success("Registro éxitoso", { autoClose: 300 });
        });
};

const actualizarDatosDetalle = (id, col, lv) => {
    let lista =
        lv == "local"
            ? list_local_detalles.value
            : list_visitante_detalles.value;
    console.log(lista);
    const index = lista.findIndex((item) => item.id == id);
    if (index < 0) {
        return;
    }

    if (
        col != "pagado_amarillas" &&
        col != "pagado_rojas" &&
        col != "amarillas" &&
        col != "rojas" &&
        col != "goles" &&
        (lista[index][col] == "" || lista[index][col] < 0)
    ) {
        // console.log(col);
        // console.log(lista[index][col]);
        toast.info("No se enviaron datos");
        return;
    }

    if (col == "goles" && parseFloat(lista[index][col] < 0)) {
        // console.log(col);
        // console.log(lista[index][col]);
        toast.info(
            "No puedes dejar vacio el campo o ingresar un valor menor a 0",
        );
        if (lv == "local") {
            list_local_detalles.value[index][col] = 0;
        } else {
            list_visitante_detalles.value[index][col] = 0;
        }
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

    const data = lista[index][col];
    axios
        .post(route("partido_detalles.actualizaDatosDetalle", id), {
            _method: "PATCH",
            col: col,
            data: data,
        })
        .then((response) => {
            if (lv == "local") {
                list_local_detalles.value[index][col] =
                    response.data.partido_detalle[col];
                if (col == "amarillas") {
                    list_local_detalles.value[index]["total_amarillas"] =
                        response.data.partido_detalle["total_amarillas"];
                }
                if (col == "rojas") {
                    list_local_detalles.value[index]["total_rojas"] =
                        response.data.partido_detalle["total_rojas"];
                }
            } else {
                list_visitante_detalles.value[index][col] =
                    response.data.partido_detalle[col];
                if (col == "amarillas") {
                    list_visitante_detalles.value[index]["total_amarillas"] =
                        response.data.partido_detalle["total_amarillas"];
                }
                if (col == "rojas") {
                    list_visitante_detalles.value[index]["total_rojas"] =
                        response.data.partido_detalle["total_rojas"];
                }
            }
            toast.success("Proceso realizado con éxito", {
                autoClose: 500,
            });
        });
};

const finalizarPartido = () => {
    router.reload({
        only: ["partido"],
    });
    Swal.fire({
        title: "¿Quierés finalizar este partido?",
        html: `<strong>${props.partido.ci_local.carrera.nombre} (${totalGolesLocal.value})</strong> vs <strong>${props.partido.ci_visitante.carrera.nombre} (${totalGolesVisitante.value})</strong>`,
        showCancelButton: true,
        confirmButtonText: "Si, finalizar",
        cancelButtonText: "No, cancelar",
        denyButtonText: `No, cancelar`,
        customClass: {
            confirmButton: "btn-danger",
        },
    }).then(async (result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
            let respuesta = await axiosPost(
                route("partidos.finalizarPartido", props.partido.id),
                {
                    _method: "PUT",
                },
            );
            if (respuesta && respuesta.sw) {
                router.get(route("partidos.index"));
            }
        }
    });
};

const { setPartido, limpiarPartido, form } = usePartidos();
const { axiosDelete, axiosPost } = useAxios();

const deudasDerechosLocal = computed(() => {
    const local = props.deudas_local.local.reduce((acc, item) => {
        if (item.pago_local == 0 && item.total_local > 0) {
            return acc + parseFloat(item.total_local);
        }
        return acc;
    }, 0);

    const visitante = props.deudas_local.visitante.reduce((acc, item) => {
        if (item.pago_visitante == 0 && item.total_visitante > 0) {
            return acc + parseFloat(item.total_visitante);
        }
        return acc;
    }, 0);

    return local + visitante;
});
const deudasAmarillasLocal = computed(() => {
    const local = props.deudas_local.local.reduce((total, partido) => {
        return (
            total +
            partido.partido_detalles.reduce((acc, item) => {
                if (item.amarillas > 0 && item.pagado_amarillas == 0) {
                    return acc + parseFloat(item.total_amarillas);
                }

                return acc;
            }, 0)
        );
    }, 0);

    const visitante = props.deudas_local.visitante.reduce((total, partido) => {
        return (
            total +
            partido.partido_detalles.reduce((acc, item) => {
                if (item.amarillas > 0 && item.pagado_amarillas == 0) {
                    return acc + parseFloat(item.total_amarillas);
                }

                return acc;
            }, 0)
        );
    }, 0);

    return local + visitante;
});
const deudasRojasLocal = computed(() => {
    const local = props.deudas_local.local.reduce((total, partido) => {
        return (
            total +
            partido.partido_detalles.reduce((acc, item) => {
                if (item.rojas > 0 && item.pagado_rojas == 0) {
                    return acc + parseFloat(item.total_rojas);
                }

                return acc;
            }, 0)
        );
    }, 0);

    const visitante = props.deudas_local.visitante.reduce((total, partido) => {
        return (
            total +
            partido.partido_detalles.reduce((acc, item) => {
                if (item.rojas > 0 && item.pagado_rojas == 0) {
                    return acc + parseFloat(item.total_rojas);
                }

                return acc;
            }, 0)
        );
    }, 0);

    return local + visitante;
});

const deudasDerechosVisitante = computed(() => {
    const local = props.deudas_visitante.local.reduce((acc, item) => {
        if (item.pago_local == 0 && item.total_local > 0) {
            return acc + parseFloat(item.total_local);
        }
        return acc;
    }, 0);

    const visitante = props.deudas_visitante.visitante.reduce((acc, item) => {
        if (item.pago_visitante == 0 && item.total_visitante > 0) {
            return acc + parseFloat(item.total_visitante);
        }
        return acc;
    }, 0);

    return local + visitante;
});
const deudasAmarillasVisitante = computed(() => {
    const local = props.deudas_visitante.local.reduce((total, partido) => {
        return (
            total +
            partido.partido_detalles.reduce((acc, item) => {
                if (item.amarillas > 0 && item.pagado_amarillas == 0) {
                    return acc + parseFloat(item.total_amarillas);
                }

                return acc;
            }, 0)
        );
    }, 0);

    const visitante = props.deudas_visitante.visitante.reduce(
        (total, partido) => {
            return (
                total +
                partido.partido_detalles.reduce((acc, item) => {
                    if (item.amarillas > 0 && item.pagado_amarillas == 0) {
                        return acc + parseFloat(item.total_amarillas);
                    }

                    return acc;
                }, 0)
            );
        },
        0,
    );

    return local + visitante;
});
const deudasRojasVisitante = computed(() => {
    const local = props.deudas_visitante.local.reduce((total, partido) => {
        return (
            total +
            partido.partido_detalles.reduce((acc, item) => {
                if (item.rojas > 0 && item.pagado_rojas == 0) {
                    return acc + parseFloat(item.total_rojas);
                }

                return acc;
            }, 0)
        );
    }, 0);

    const visitante = props.deudas_visitante.visitante.reduce(
        (total, partido) => {
            return (
                total +
                partido.partido_detalles.reduce((acc, item) => {
                    if (item.rojas > 0 && item.pagado_rojas == 0) {
                        return acc + parseFloat(item.total_rojas);
                    }

                    return acc;
                }, 0)
            );
        },
        0,
    );

    return local + visitante;
});

const recargarDeudas = () => {
    router.reload({
        only: ["deudas_local", "deudas_visitante"],
    });
};

const mostrarDeudas = (item) => {
    limpiarCampeonatoInscripcion();
    setCampeonatoInscripcion(item);
    muestra_formulario_pagos.value = true;
};
</script>
<template>
    <Head title="Detalles Partido"></Head>
    <Content>
        <template #header>
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="m-0">
                        <i class="fa fa-table"></i> Detalles Partido
                    </h3>
                </div>
                <!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item">
                            <Link :href="route('inicio')">Inicio</Link>
                        </li>
                        <li class="breadcrumb-item">
                            <Link :href="route('partidos.index')"
                                >Partidos</Link
                            >
                        </li>
                        <li class="breadcrumb-item active">Detalles Partido</li>
                    </ol>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </template>
        <div class="row">
            <FormularioPagos
                v-if="muestra_formulario_pagos"
                :muestra_formulario="muestra_formulario_pagos"
                :form="formCI"
                :partido_id="partido.id"
                @envio-formulario="recargarDeudas"
                @cerrar-formulario="muestra_formulario_pagos = false"
            ></FormularioPagos>
            <div class="col-12">
                <h4 class="text-primary text-center fs-5">
                    {{ campeonato.periodo }} - {{ campeonato.gestion }}:
                    {{ campeonato.nombre }} ({{ campeonato.tipo }})
                </h4>
                <h4 class="text-primary text-center fs-6">
                    {{ partido.fecha_hora_t }}
                </h4>
            </div>
            <div class="col-12 mb-2">
                <a
                    :href="
                        route('reportes.r_partido_detalles') +
                        `?partido_id=${partido.id}`
                    "
                    class="float-end btn btn-primary ms-1"
                    target="_blank"
                    ><i class="fa fa-file-pdf"></i> Exportar</a
                >
                <Link
                    class="btn btn-light border float-end px-3 ms-1"
                    :href="route('partidos.index')"
                >
                    <i class="fa fa-arrow-left"></i> Volver
                </Link>
            </div>
            <!-- LOCAL -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-principal text-white">
                        <div class="row">
                            <div
                                class="col-12"
                                v-if="
                                    deudasDerechosLocal > 0 ||
                                    deudasAmarillasLocal > 0 ||
                                    deudasRojasLocal > 0
                                "
                            >
                                <div class="alert alert-danger text-white">
                                    <div class="row">
                                        <div class="col-12 fs-5">
                                            <i class="fa fa-info-circle"></i>
                                            El equipo tiene deudas acumuladas
                                        </div>
                                        <div class="col-4 text-end fw-bold">
                                            Por derecho de cancha:
                                        </div>
                                        <div class="col-8">
                                            Bs. {{ deudasDerechosLocal }}
                                        </div>
                                        <div class="col-4 text-end fw-bold">
                                            Por amarillas:
                                        </div>
                                        <div class="col-8">
                                            Bs. {{ deudasAmarillasLocal }}
                                        </div>
                                        <div class="col-4 text-end fw-bold">
                                            Por rojas:
                                        </div>
                                        <div class="col-8">
                                            Bs. {{ deudasRojasLocal }}
                                        </div>
                                        <div class="col-12">
                                            <button
                                                class="btn btn-primary"
                                                @click.prevent="
                                                    mostrarDeudas(
                                                        partido.ci_local,
                                                    )
                                                "
                                            >
                                                <i
                                                    class="fa fa-clipboard-list"
                                                ></i>
                                                Ver Registros
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 align-items-end">
                                <h4 class="fs-6 text-white fw-bold">
                                    Local: {{ partido.ci_local.carrera.nombre }}
                                </h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="input-group">
                                            <span class="input-group-text px-1"
                                                ><i
                                                    class="fa fa-futbol me-1"
                                                ></i
                                                >Goles</span
                                            >
                                            <div class="form-control">
                                                {{ totalGolesLocal }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <MiTable
                            :tableClass="'bg-white mitabla'"
                            ref="miTableLocal"
                            :cols="headers"
                            :data="list_local_detalles"
                            :con-paginacion="false"
                            :syncOrderBy="'id'"
                            :syncOrderAsc="'DESC'"
                            :header-class="'bg__primary'"
                            fixed-header
                            table-height="23vh"
                        >
                            <template #foto="{ item }">
                                <div class="">
                                    <img
                                        :src="
                                            item.carrera_jugador.jugador
                                                .url_foto
                                        "
                                        alt=""
                                        class="rounded-circle"
                                        height="60px"
                                    />
                                </div>
                            </template>
                            <template
                                #[`carrera_jugador.jugador.nombres`]="{ item }"
                            >
                                <div class="db-block w-100">
                                    {{ item.carrera_jugador.jugador.nombres }}
                                    {{ item.carrera_jugador.jugador.apes }}
                                </div>
                                <div class="text-muted fw-bold d-block w-100">
                                    {{ item.carrera_jugador.posicion }}
                                </div>
                                <div class="text-muted d-block">
                                    {{ item.carrera_jugador.jugador.ci }}
                                </div>
                            </template>
                            <template #titular="{ item }">
                                <div class="w-100 text-center">
                                    {{ item.titular ? "SI" : "NO" }}
                                </div>
                            </template>
                            <template #goles="{ item }">
                                <div class="w-100 text-center">
                                    {{ item.goles }}
                                </div>
                            </template>
                            <template #amarillas="{ item }">
                                <div class="w-100 text-center">
                                    {{ item.amarillas }}
                                </div>
                            </template>
                            <template #rojas="{ item }">
                                <div class="w-100 text-center">
                                    {{ item.rojas }}
                                </div>
                            </template>
                        </MiTable>
                    </div>
                </div>
            </div>

            <!-- VISITANTE -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-principal text-white">
                        <div class="row">
                            <div
                                class="col-12"
                                v-if="
                                    deudasDerechosVisitante > 0 ||
                                    deudasAmarillasVisitante > 0 ||
                                    deudasRojasVisitante > 0
                                "
                            >
                                <div class="alert alert-danger text-white">
                                    <div class="row">
                                        <div class="col-12 fs-5">
                                            <i class="fa fa-info-circle"></i>
                                            El equipo tiene deudas acumuladas
                                        </div>
                                        <div class="col-4 text-end fw-bold">
                                            Por derecho de cancha:
                                        </div>
                                        <div class="col-8">
                                            Bs. {{ deudasDerechosVisitante }}
                                        </div>
                                        <div class="col-4 text-end fw-bold">
                                            Por amarillas:
                                        </div>
                                        <div class="col-8">
                                            Bs. {{ deudasAmarillasVisitante }}
                                        </div>
                                        <div class="col-4 text-end fw-bold">
                                            Por rojas:
                                        </div>
                                        <div class="col-8">
                                            Bs. {{ deudasRojasVisitante }}
                                        </div>
                                        <div class="col-12">
                                            <button
                                                class="btn btn-primary"
                                                @click.prevent="
                                                    mostrarDeudas(
                                                        partido.ci_visitante,
                                                    )
                                                "
                                            >
                                                <i
                                                    class="fa fa-clipboard-list"
                                                ></i>
                                                Ver Registros
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 align-items-end">
                                <h4 class="fs-6 text-white fw-bold">
                                    Visitante:
                                    {{ partido.ci_visitante.carrera.nombre }}
                                </h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="input-group">
                                            <span class="input-group-text px-1"
                                                ><i
                                                    class="fa fa-futbol me-1"
                                                ></i
                                                >Goles</span
                                            >
                                            <input
                                                type="number"
                                                class="form-control text-center"
                                                readonly
                                                v-model="totalGolesVisitante"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <MiTable
                            :tableClass="'bg-white mitabla'"
                            ref="miTableVisitante"
                            :cols="headers"
                            :data="list_visitante_detalles"
                            :con-paginacion="false"
                            :syncOrderBy="'id'"
                            :syncOrderAsc="'DESC'"
                            :header-class="'bg__primary'"
                            fixed-header
                            table-height="23vh"
                        >
                            <template #foto="{ item }">
                                <div class="">
                                    <img
                                        :src="
                                            item.carrera_jugador.jugador
                                                .url_foto
                                        "
                                        alt=""
                                        class="rounded-circle"
                                        height="60px"
                                    />
                                </div>
                            </template>
                            <template
                                #[`carrera_jugador.jugador.nombres`]="{ item }"
                            >
                                <div class="db-block w-100">
                                    {{ item.carrera_jugador.jugador.nombres }}
                                    {{ item.carrera_jugador.jugador.apes }}
                                </div>
                                <div class="text-muted fw-bold d-block w-100">
                                    {{ item.carrera_jugador.posicion }}
                                </div>
                                <div class="text-muted d-block">
                                    {{ item.carrera_jugador.jugador.ci }}
                                </div>
                            </template>
                            <template #titular="{ item }">
                                <div class="w-100 text-center">
                                    {{ item.titular ? "SI" : "NO" }}
                                </div>
                            </template>
                            <template #goles="{ item }">
                                <div class="w-100 text-center">
                                    {{ item.goles }}
                                </div>
                            </template>
                            <template #amarillas="{ item }">
                                <div class="w-100 text-center">
                                    {{ item.amarillas }}
                                </div>
                            </template>
                            <template #rojas="{ item }">
                                <div class="w-100 text-center">
                                    {{ item.rojas }}
                                </div>
                            </template>
                        </MiTable>
                    </div>
                </div>
            </div>
        </div>
    </Content>
</template>
