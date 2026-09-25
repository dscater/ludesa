import { useForm } from "@inertiajs/vue3";

export const useJugadors = () => {
    const initialState = {
        id: 0,
        nombres: "",
        apes: "",
        ci: "",
        correo: "",
        fono: "",
        dir: "",
        foto: "",
        fecha_registro: "",
        url_foto: "",
        _method: "POST",
    };

    const form = useForm({ ...initialState });

    const setJugador = (item) => {
        form.clearErrors();
        form.reset();
        Object.assign(form, item);
        form._method = "PUT";
    };

    const limpiarJugador = () => {
        form.clearErrors();
        form.reset();
        form.defaults({ ...initialState });
    };

    return {
        form,
        setJugador,
        limpiarJugador,
    };
};
