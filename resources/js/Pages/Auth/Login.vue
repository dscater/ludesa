<script>
import "@/assets/css/variables.css";
import Login from "@/Layouts/Login.vue";
import { computed, onMounted, ref } from "vue";
export default {
    layout: Login,
};
</script>
<script setup>
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";

import { useConfiguracion } from "@/composables/configuracion/useConfiguracion";
const { props } = usePage();
const { oConfiguracion } = useConfiguracion();
const form = useForm({
    usuario: "",
    password: "",
});

var url_assets = "";
const enviando = ref(false);
const errors = ref([]);
var url_principal = "";
const muestra_password = ref(false);
const enviarFormulario = () => {
    enviando.value = true;
    axios
        .post(route("login"), form)
        .then((response) => {
            form.usuario = "";
            form.password = "";
            if (response.data.codigo == true) {
                Swal.fire({
                    icon: "info",
                    title: "Atención",
                    html: `Te enviamos un código de verificación a tu correo para que puedas iniciar sesión`,
                    confirmButtonText: `Aceptar`,
                    customClass: {
                        confirmButton: "btn-alert-primary",
                    },
                });
                // ABRIR MODAL CÓDIGO
                oUserVerificado.value = response.data.user;
                muestraFormCodigo.value = true;
                emits("cerrar-formulario");
            } else {
                // ENVIAR AL INICIO
                Swal.fire({
                    icon: "success",
                    title: "Correcto",
                    html: `<strong>Sesión iniciada correctamente</strong>`,
                    showConfirmButton: false,
                    timer: 1500,
                });
                setTimeout(() => {
                    window.location.href = route("inicio");
                });
            }
        })
        .catch((error) => {
            if (error.response?.status === 422) {
                errors.value = error.response.data.errors || {};
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    html: `<strong>${error.message}</strong>`,
                    confirmButtonText: `Aceptar`,
                    customClass: {
                        confirmButton: "btn-error",
                    },
                });
            }
        })
        .finally(() => {
            enviando.value = false;
        });
};
const textBtn = computed(() => {
    if (enviando.value) {
        return `<i class="fa fa-spin fa-spinner"></i> Ingresando...`;
    }
    return `<i class="fa fa-sign-in-alt"></i> Ingresar`;
});

onMounted(() => {
    url_assets = props.url_assets;
    url_principal = props.url_principal;
});
</script>

<template>
    <Head title="Login"></Head>
    <div class="container-fluid contenedor_login">
        <!-- BEGIN login -->
        <div class="row">
            <div
                class="col-md-6 offset-md-3 col-lg-4 offset-lg-4 col-xl-3 offset-xl-4 my-5"
            >
                <div class="card mt-5">
                    <div class="card-header text-center">
                        <img
                            :src="oConfiguracion.url_logo"
                            alt="Logo"
                            class="logo_login mb-2"
                            width="180px"
                            lazy
                        />
                        <br />
                        <h1 class="h3 font-weight-bold text1">
                            {{ oConfiguracion.nombre_sistema }}
                        </h1>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="enviarFormulario()">
                            <h5 class="w-100 text-center h5 text-dark">
                                Iniciar Sesión
                            </h5>
                            <div class="row">
                                <div class="col-12">
                                    <div class="input-group form-floating">
                                        <div class="input-group-prepend">
                                            <span
                                                class="input-group-text bg-primary"
                                            >
                                                <i class="fa fa-user"></i>
                                            </span>
                                        </div>
                                        <input
                                            type="text"
                                            name="usuario"
                                            id="usuario"
                                            class="form-control"
                                            placeholder="Usuario"
                                            v-model="form.usuario"
                                            autofocus
                                            ref="inputUsuario"
                                            @keypress.enter="enviarFormulario"
                                        />
                                        <label
                                            for="usuario"
                                            class="d-flex align-items-center text-gray-600 fs-13px ml-5"
                                            style="z-index: 100"
                                            >Usuario</label
                                        >
                                    </div>
                                    <ul
                                        v-if="errors?.usuario"
                                        class="text-danger list-unstyled mb-0"
                                    >
                                        <li class="parsley-required">
                                            {{ errors?.usuario[0] }}
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-12">
                                    <div class="input-group form-floating mt-3">
                                        <div class="input-group-prepend">
                                            <span
                                                class="input-group-text bg-primary"
                                            >
                                                <i class="fa fa-key"></i>
                                            </span>
                                        </div>
                                        <input
                                            :type="
                                                muestra_password
                                                    ? 'text'
                                                    : 'password'
                                            "
                                            name="password"
                                            id="password"
                                            class="form-control"
                                            v-model="form.password"
                                            autocomplete="false"
                                            placeholder="Contraseña"
                                            @keypress.enter="enviarFormulario"
                                        />
                                        <label
                                            for="password"
                                            class="d-flex align-items-center text-gray-600 fs-13px ml-5"
                                            style="z-index: 100"
                                            >Contraseña</label
                                        >
                                        <div class="input-group-append">
                                            <button
                                                class="btn btn-default"
                                                type="button"
                                                @click="
                                                    muestra_password =
                                                        !muestra_password
                                                "
                                            >
                                                <i
                                                    class="fa"
                                                    :class="[
                                                        muestra_password
                                                            ? 'fa-eye'
                                                            : 'fa-eye-slash',
                                                    ]"
                                                ></i>
                                            </button>
                                        </div>
                                    </div>
                                    <ul
                                        v-if="errors?.password"
                                        class="text-danger list-unstyled"
                                    >
                                        <li class="parsley-required">
                                            {{ errors?.password[0] }}
                                        </li>
                                    </ul>
                                </div>
                                <div class="w-100" v-if="form.errors?.usuario">
                                    <span
                                        class="invalid-feedback alert alert-danger"
                                        style="display: block"
                                        role="alert"
                                    >
                                        <strong>{{ errors.usuario }}</strong>
                                    </span>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <button
                                        type="button"
                                        class="btn btn-primary w-100"
                                        @click.prevent="enviarFormulario"
                                        :disabled="enviando"
                                        v-html="textBtn"
                                    ></button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- END login-content -->
                </div>
            </div>
        </div>
        <!-- END login -->
    </div>
</template>

<style scoped>
body #app .contenedor_login {
    min-height: 100vh;
    width: 100vw;
    background-color: var(--bgGrayLight);
}
</style>
