<script setup>
import { Link, router, usePage } from "@inertiajs/vue3";
import { computed, onMounted, onUnmounted, ref } from "vue";
const { auth, url_assets } = usePage().props;
const user = ref(auth.user);
const requisito = ref(auth.requisito);
const toggleUsuario = ref(false);
const showUsuario = ref(false);

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
                    window.location.href = route("portal.index");
                });
        }
    });
};

const isFixed = ref(false);
const handleScroll = () => {
    const y = window.scrollY;
    isFixed.value = y > 130;
    //agregar o quitar una clase al body
    if (isFixed.value) {
        document.body.classList.add("fixedContentEstudiante");
    } else {
        document.body.classList.remove("fixedContentEstudiante");
    }
};

const scrollTop = () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
};

const url_logo = computed(() => {
    let url = "";
    if (user.value.inscripcion.unidad == "ANAPOL") {
        url = url_assets + "imgs/ANAPOL.webp";
    }
    if (user.value.inscripcion.unidad == "FATESCIPOL") {
        url = url_assets + "imgs/FATESCIPOL.webp";
    }
    if (user.value.inscripcion.unidad == "ESBAPOLMUS") {
        url = url_assets + "imgs/ESBAPOLMUS.webp";
    }
    return url;
});

const rutaActual = ref("");
onMounted(() => {
    rutaActual.value = route().current();
    window.addEventListener("scroll", handleScroll);
});
onUnmounted(() => {
    window.removeEventListener("scroll", handleScroll);
});

router.on("navigate", () => {
    rutaActual.value = route().current();
});
</script>
<template>
    <div
        class="main"
        :class="[
            toggleUsuario ? 'toggle' : '',
            showUsuario ? 'show' : '',
            isFixed ? 'fixed-content' : '',
        ]"
    >
        <div class="usuario sidebar overflow-auto">
            <div class="logo">
                <div class="logo_unidad">
                    <img :src="url_logo" alt="" />
                    {{ user.inscripcion.unidad }}
                </div>
            </div>
            <div class="container info_usuario">
                <div class="row mt-3 text-final pb-3">
                    <div class="col-12 text-center">
                        <img
                            :src="user.url_foto"
                            alt="Foto"
                            class="fotoPostulante"
                        />
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12 text-center">
                        {{ user.inscripcion.codigoPre }}
                    </div>
                    <div class="col-12 text-center text-final">
                        <b class="text-principal">Código</b>
                    </div>
                </div>
                <div class="row mt-2" v-if="user.inscripcion.codigoInsc">
                    <div class="col-12 text-center">
                        {{ user.inscripcion.codigoInsc }}
                    </div>
                    <div class="col-12 text-center text-final">
                        <b class="text-principal">Código Inscripción</b>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12 text-center">
                        {{ user.inscripcion.unidad }}
                    </div>
                    <div class="col-12 text-center text-final">
                        <b class="text-principal"
                            >Unidad Académica al que Postula</b
                        >
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12 text-center">
                        {{ user.inscripcion.postulante.full_ci }}
                    </div>
                    <div class="col-12 text-center text-final">
                        <b class="text-principal">Cédula de Identidad</b>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12 text-center">
                        {{ user.inscripcion.postulante.full_name }}
                    </div>
                    <div class="col-12 text-center text-final">
                        <b class="text-principal">Nombre Completo</b>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12 text-center">
                        {{ user.inscripcion.postulante.fecha_nac_t }}
                    </div>
                    <div class="col-12 text-center text-final">
                        <b class="text-principal">Fecha de Nacimiento</b>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12 text-center">
                        {{ user.inscripcion.lugar_preins }}
                    </div>
                    <div class="col-12 text-center text-final">
                        <b class="text-principal">Lugar de Inscripción</b>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12 text-center">
                        {{ user.inscripcion.sede }}
                    </div>
                    <div class="col-12 text-center text-final">
                        <b class="text-principal">Sede de Evaluación</b>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12 text-center">
                        {{ user.inscripcion.postulante.cel }}
                    </div>
                    <div class="col-12 text-center text-final">
                        <b class="text-principal">Celular</b>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12 text-center">
                        {{ user.inscripcion.correo }}
                    </div>
                    <div class="col-12 text-center text-final">
                        <b class="text-principal">Correo</b>
                    </div>
                </div>
                <div class="row mt-2" v-if="user.inscripcion.codigoInsc">
                    <div class="col-12 text-center">
                        <div class="dropdown">
                            <button
                                class="btn btn-principal dropdown-toggle"
                                type="button"
                                data-toggle="dropdown"
                                aria-expanded="false"
                            >
                                <p class="w-100 text-wrap mb-0">
                                    Procedimiento de la Evaluación Médica,
                                    Odontológica y Psicológica
                                </p>
                            </button>
                            <div class="dropdown-menu w-100">
                                <Link
                                    class="dropdown-item"
                                    :href="
                                        route('evaluacionMedica') +
                                        '#evaluacion2'
                                    "
                                    >EVALUACIÓN MÉDICA</Link
                                >
                                <Link
                                    class="dropdown-item"
                                    :href="
                                        route('evaluacionMedica') +
                                        '#evaluacion3'
                                    "
                                    >EVALUACIÓN PSICOLÓGICA</Link
                                >
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-2 pt-4 pb-3">
                    <div class="col-12 text-center font-weight-bold h4">
                        <span class="text-secundario"> S.A.D. - UNIPOL </span>
                    </div>
                </div>
            </div>
        </div>
        <div
            class="principal"
            :class="[toggleUsuario ? 'toggle' : '', showUsuario ? 'show' : '']"
        >
            <div class="header p-0">
                <button
                    class="toggle_usuario"
                    @click="showUsuario = !showUsuario"
                >
                    <i
                        class="fa"
                        :class="[
                            showUsuario == true
                                ? 'fa-angle-left'
                                : 'fa-angle-right',
                        ]"
                    ></i>
                </button>
                <button
                    class="toggle_usuario_normal"
                    @click="toggleUsuario = !toggleUsuario"
                >
                    <i
                        class="fa"
                        :class="[
                            toggleUsuario == true
                                ? 'fa-angle-right'
                                : 'fa-angle-left',
                        ]"
                    ></i>
                </button>
                <div class="contenedor_logos border_bot">
                    <div class="logo_admision">
                        <img :src="url_assets + 'imgs/ADMINICION.png'" alt="" />
                    </div>
                </div>
            </div>
            <div
                class="navbar-nav"
                :class="[isFixed ? 'fixed-top shadow' : '']"
            >
                <div class="container-fluid">
                    <div class="row justify-content-center">
                        <div class="col-12 text-center p-0">
                            <ul class="list-unstyled menu_postulante">
                                <li
                                    :class="[
                                        rutaActual == 'inicio' ? 'active' : '',
                                    ]"
                                >
                                    <Link class="" :href="route('inicio')">
                                        <i class="fa fa-home"></i>
                                        <span class="desc_menu">Inicio</span>
                                    </Link>
                                </li>
                                <li
                                    :class="[
                                        rutaActual == 'evaluaciones'
                                            ? 'active'
                                            : '',
                                    ]"
                                    v-if="
                                        user?.inscripcion.estado == 'INSCRITO'
                                    "
                                >
                                    <Link
                                        class=""
                                        :href="route('evaluaciones')"
                                    >
                                        <i class="fa fa-clipboard-check"></i>
                                        <span class="desc_menu"
                                            >Evaluaciones</span
                                        >
                                    </Link>
                                </li>
                                <li
                                    :class="[
                                        rutaActual == 'vestibulares'
                                            ? 'active'
                                            : '?',
                                    ]"
                                    v-if="
                                        user?.inscripcion.estado == 'INSCRITO'
                                    "
                                >
                                    <Link
                                        class=""
                                        :href="route('vestibulares')"
                                    >
                                        <i class="fa fa-clipboard-check"></i>
                                        <span class="desc_menu"
                                            >Prefacultativos</span
                                        >
                                    </Link>
                                </li>
                                <li
                                    :class="[
                                        rutaActual == 'inscripcions.index'
                                            ? 'active'
                                            : '',
                                    ]"
                                    v-if="!requisito"
                                >
                                    <Link
                                        class=""
                                        :href="route('inscripcions.index')"
                                    >
                                        <i class="fa fa-clipboard-list"></i>
                                        <span class="desc_menu"
                                            >Inscripción</span
                                        >
                                    </Link>
                                </li>
                                <li>
                                    <a
                                        href="#"
                                        class="w-100"
                                        @click.prevent="salir()"
                                    >
                                        <i class="fa fa-power-off"></i>
                                        <span class="desc_menu"
                                            >Cerrar Sesión</span
                                        >
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="contenidoPostulante"
                :class="[
                    toggleUsuario ? 'content-100' : '',
                    showUsuario ? 'content-100' : '',
                ]"
            >
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <slot />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer d-flex">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <p class="text-center text-white pt-4">
                            <b>UNIPOL</b> {{ new Date().getFullYear() }} &copy;
                            Todos los derechos reservados
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
.main {
    min-height: 100vh;
    position: relative;
    background-color: var(--bgDarkPrincipal);
    max-width: 100vw;
    overflow: hidden;
}

/* MENU */
.menu_postulante {
    display: flex;
    justify-content: center;
    height: 100%;
    width: 90%;
    margin: auto;
}

.menu_postulante li {
    display: flex;
    align-items: center;
    padding: 0px;
    margin: 0px;
    padding: 0px 10px;
    flex: 1;
}

.menu_postulante li a:hover {
    color: var(--bg7);
}
.menu_postulante li.active:hover,
.menu_postulante li.active,
.menu_postulante li.active a {
    background-color: var(--bg6) !important;
    color: white !important;
    border-radius: 13px 13px 0px 00px;
}

.menu_postulante li.active:hover {
    background-color: var(--bg6);
}

.menu_postulante li i {
    font-size: 0.9em;
}
.menu_postulante li a {
    display: flex;
    width: 100%;
    height: 100%;
    align-items: center;
    justify-content: center;
    color: white;
    transition: all 0.3s;
    font-weight: 600;
    padding: 7px 0px;
}

/* SIDEBAR */
.sidebar {
    z-index: 999;
    position: fixed;
    padding: 0px;
    width: 320px;
    height: 100vh;
    max-height: 100vh;
    background-color: var(--bgDarkPrincipal);
    overflow: auto;
    /* box-shadow: 2px 5px 3px rgb(0, 0, 0); */
    box-shadow: 0px 1px 1px var(--bg6);

    transition: all 0.3s;
    color: white;
}
.text-final {
    /* box-shadow: 0px 2px 2px rgb(49, 49, 49); */
    box-shadow: 0px 1px 1px var(--bg6);
}

body .main .sidebar b,
body .main .sidebar b.text-principal,
body .main .sidebar .text-principal,
body .main .sidebar .font-weight-bold {
    color: var(--bg1) !important;
}

.dropdown-menu.show {
    position: relative !important;
    top: 0;
    left: 0;
    display: block;
    transform: none !important;
}

.dropdown-item:hover {
    color: var(--bgDarkPrincipal);
}
.dropdown-item {
    color: white;
}

.sidebar .logo img {
    width: 90px;
    margin: auto;
}

.sidebar .logo {
    width: 100%;
}
.logo_unidad {
    color: white;
    font-weight: bold;
    background-color: var(--bg3);
    width: 100%;
    display: flex;
    text-align: center;
    justify-content: center;
    flex-direction: column;
}

.sidebar .info_usuario img.fotoPostulante {
    width: 90px;
    border-radius: 50%;
    border: solid 2px var(--bgGray);
}

/* PRINCIPAL */
.principal {
    position: relative;
    margin-left: 320px;
    transition: all 0.3s;
    min-width: calc(100vw - 340px);
    min-height: calc(100vh - 68px);
}

.main.toggle .sidebar {
    left: -320px;
}

.main.toggle .footer,
.main.toggle .principal {
    margin-left: 0px;
}
.main.show .sidebar {
    left: 0px;
}

.main.show .principal {
    margin-left: 0px;
}

.main.show .toggle_usuario {
    left: 325px !important;
}
.main.fixed-content .toggle_usuario_normal,
.main.fixed-content .toggle_usuario {
    position: fixed;
    z-index: 999;
    top: 51px !important;
}

.main.toggle .principal.toggle .navbar-nav,
.main.toggle .principal.toggle .toggle_usuario_normal,
.main.toggle .principal.toggle .header {
    left: 0px;
}

.main.fixed-content .toggle_usuario_normal {
    left: 320px;
}

/* HEADER */
.header {
    position: relative;
    background-color: var(--bg3);
    height: 103px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-bottom: 1px solid var(--bgGray);
}

.header img {
    height: 90px;
}
.navbar-nav {
    padding-top: 2px;
    background-color: var(--bg3);
}

.menu_postulante a .desc_menu {
    margin-left: 4px;
}

button.link {
    background-color: transparent;
    border: none;
}
.link {
    color: white;
    font-weight: bold;
}

.fixed-top {
    left: 320px;
}

.footer {
    margin-left: 320px;
    background-color: var(--bg3);
}
.toggle_usuario,
.toggle_usuario_normal {
    background-color: var(--bg6t);
    color: white;
    border: none;
    font-size: 1.3em;
    position: absolute;
    top: 0;
    left: 5px;
    width: 30px;
    transition: all 0.3s;
}

.toggle_usuario {
    display: none !important;
}

@media (max-width: 990px) {
    .main {
        width: 100vw;
    }
    .desc_menu {
        font-size: 0.6em;
        /* display: none; */
    }
    .menu_postulante {
        width: 100%;
    }

    .menu_postulante li {
        width: 100%;
    }
    .menu_postulante li a {
        height: auto;
        width: 100%;
        flex-direction: column;
    }

    .toggle_usuario {
        display: block !important;
    }

    .header {
        height: 60px;
    }

    .logo_admision img {
        height: 40px;
    }

    .footer,
    .header,
    .navbar-nav,
    .principal {
        margin-left: 0px;
        max-width: 100vw;
    }

    .fixed-top {
        left: 0px;
    }

    .sidebar {
        left: -320px;
    }

    .toggle_usuario_normal {
        display: none !important;
    }
}
</style>
